# Clean up
DROP DATABASE staging;
CREATE DATABASE staging DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;

use staging;

drop table if exists staging.stage_event_day;

CREATE TABLE staging.stage_event_day (
 `game_id` int(11) NOT NULL,
 `client_id` int(11) NOT NULL,
 `device_id` varchar(80) NOT NULL,
 `version` varchar(16) NOT NULL,
 `device_gen` varchar(80) NOT NULL,
 `log_ts` timestamp NOT NULL,
 `event_id` int(11) NOT NULL,
 `parm` varchar(255) NOT NULL,
 `value` varchar(80) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1
;

LOAD DATA LOCAL INFILE '/home/revans/projects/analytics/csv/staging_day_events.csv' INTO TABLE staging.stage_event_day 
FIELDS TERMINATED BY ','
ENCLOSED BY '\'';

SHOW WARNINGS;

# Create all the indexes that we need
CREATE INDEX device_id  on staging.stage_event_day(device_id(10));

SELECT date(log_ts) as Date,
count(*)
from staging.stage_event_day
group by 1
;

# Get Min Date
DROP TABLE if EXISTS staging.min_ts;

CREATE TABLE staging.min_ts engine=myisam
as
select game_id, client_id, min(log_ts) as log_ts
from staging.stage_event_day
where date(log_ts)=@stat_date
group by 1,2
;

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
and a.stat_date>=date_sub(curdate(), interval 45 day)
WHERE b.game_id is NULL
and b.client_id is NULL
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
and a.stat_date>=date_sub(curdate(), interval 45 day)
;

insert into staging.s_user_event(game_id, client_id, device_gen_id, user_id, 
                              stat_date, stat_time, event_id, parm, value)
select a.game_id,
a.client_id, 
b.device_gen_id,
b.user_id, 
date(a.log_ts),
time(a.log_ts),
a.event_id,
a.parm,
a.value
from staging.stage_event_day a
join star.s_device_master b
on a.device_id=b.device_id
where date(a.log_ts)>=date_sub(curdate(), interval 45 day) 
and a.log_ts<now()
;

SHOW WARNINGS;

drop table if exists staging.stage_event_day;
drop table if exists star.s_user_event_bkup;
rename table star.s_user_event to star.s_user_event_bkup;
rename table staging.s_user_event to star.s_user_event;
create index user on star.s_user_event(game_id, client_id, user_id);
