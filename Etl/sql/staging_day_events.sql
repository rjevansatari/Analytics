# Clean up
DROP DATABASE staging;
CREATE DATABASE staging DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;

use staging;

drop table if exists staging.stage_event_day_raw;

CREATE TABLE staging.stage_event_day_raw (
 `game_id` smallint(11) NOT NULL,
 `client_id` smallint(11) NOT NULL,
 `device_id` varchar(80) NOT NULL,
 `version` varchar(16) NOT NULL,
 `device_gen_id` smallint NOT NULL,
 `log_ts` timestamp NOT NULL,
 `event_id` smallint NOT NULL,
 `parm_id` smallint,
 `value` varchar(32)
) ENGINE=MyISAM DEFAULT CHARSET=latin1
;

LOAD DATA LOCAL INFILE '/home/revans/projects/analytics/csv/staging_day_events.csv' INTO TABLE staging.stage_event_day_raw
FIELDS TERMINATED BY ','
ENCLOSED BY '\''
(game_id, client_id, device_id, @IGNORE, @IGNORE, log_ts, event_id, parm_id, value)
;

DROP TABLE IF EXISTS staging.stage_event_day;

CREATE table staging.stage_event_day
as
SELECT
game_id,
client_id,
device_id,
log_ts,
event_id,
parm_id,
value
FROM staging.stage_event_day_raw
WHERE date(log_ts) between @start_date and @end_date
group by 1,2,3,4,5,6,7
;

DROP TABLE staging.stage_event_day_raw;

SHOW WARNINGS;

# Create all the indexes that we need
CREATE INDEX device_id  on staging.stage_event_day(device_id(10));

# Dump a summary the data we just loaded
SELECT game_id, date(log_ts) as date,
count(*)
from staging.stage_event_day
group by 1,2
order by 1,2
;

# Now remove any data that has since been updated
DROP TABLE if EXISTS staging.s_user_event;

CREATE TABLE staging.min_ts engine=myisam
as
select game_id, client_id, min(date(log_ts)) as log_date, min(log_ts) as log_ts
from staging.stage_event_day
where date(log_ts) between @start_date and @end_date
group by 1,2
;

CREATE INDEX game ON staging.min_ts(game_id, client_id);

# Now remove any data that has since been updated
DROP TABLE if EXISTS staging.s_user_event;

CREATE TABLE staging.s_user_event engine=myisam
as
select a.*
from star.s_user_event a
LEFT JOIN 
staging.min_ts b
ON a.game_id=b.game_id
and a.client_id=b.client_id
and timestamp(a.stat_date, a.stat_time)<b.log_ts
WHERE b.game_id is NULL
and b.client_id is NULL
and a.stat_date>=date_sub(@yesterday, interval 45 day)
;

SHOW WARNINGS;

# Do not load any data after date
insert into staging.s_user_event
select a.* 
from star.s_user_event a
join
staging.min_ts b
ON a.game_id=b.game_id
and a.client_id=b.client_id
and timestamp(a.stat_date, a.stat_time)<b.log_ts
and a.stat_date>=date_sub(@yesterday, interval 45 day)
;

# Insert new data
insert into staging.s_user_event(game_id, client_id, device_gen_id, user_id, 
                              stat_date, stat_time, event_id, parm_id, value)
select a.game_id,
a.client_id, 
b.device_gen_id,
b.user_id, 
date(a.log_ts),
time(a.log_ts),
a.event_id,
a.parm_id,
a.value
from staging.stage_event_day a
join star.s_device_master b
on a.device_id=b.device_id
;

SHOW WARNINGS;

drop table if exists staging.stage_event_day;
drop table if exists star.s_user_event_bkup;
create index game on staging.s_user_event(stat_date, game_id, client_id, event_id);
rename table star.s_user_event to star.s_user_event_bkup;
rename table staging.s_user_event to star.s_user_event;
