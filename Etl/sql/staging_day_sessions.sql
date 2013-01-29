# Clean up
DROP DATABASE staging;
CREATE DATABASE staging DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
use staging;

DROP TABLE if EXISTS staging.stage_session_day;

CREATE TABLE staging.stage_session_day (
`game_id` smallint NOT NULL,
`client_id` smallint NOT NULL,
`device_id` varchar(80) NOT NULL,
`version` varchar(16) NOT NULL,
`device_gen_id` smallint NOT NULL,
`log_ts` timestamp NOT NULL 
) ENGINE=MyISAM
;

LOAD DATA LOCAL INFILE '/home/revans/projects/analytics/csv/staging_day_sessions.csv' INTO TABLE staging.stage_session_day 
FIELDS TERMINATED BY ','
ENCLOSED BY '\'';

SHOW WARNINGS;

# Create all the indexes that we need
CREATE INDEX device_id on staging.stage_session_day(device_id(10));

# Get Min Date
SELECT game_id, date(log_ts) as Date,
count(*)
from staging.stage_session_day
where date(log_ts) between @start_date and @end_date
group by 1,2
;

DROP TABLE if EXISTS staging.min_ts;

CREATE TABLE staging.min_ts engine=myisam
as
select game_id, client_id, min(date(log_ts)) as log_date, min(log_ts) as log_ts
from staging.stage_session_day
where date(log_ts) between @start_date and @end_date
group by 1,2
;

CREATE index game on staging.min_ts(game_id, client_id);

# Now remove any data that has since been updated
DROP TABLE if EXISTS staging.s_user_day;

# Make sure we get any different games that are not in staging table loaded
CREATE TABLE staging.s_user_day engine=myisam
as
select a.* 
from star.s_user_day a
left join
staging.min_ts b
ON a.game_id=b.game_id
and a.client_id=b.client_id
and timestamp(a.stat_date, a.stat_time)<b.log_ts
WHERE b.game_id is NULL
and b.client_id is NULL
;

SHOW WARNINGS;

# Do not load any same game data after date
insert into staging.s_user_day
select a.* 
from star.s_user_day a
join
staging.min_ts b
ON a.game_id=b.game_id
and a.client_id=b.client_id
and timestamp(a.stat_date, a.stat_time)<b.log_ts
group by 1,2,3,4,5,6,7,8
;

SHOW WARNINGS;

# Create New Device ids to user_ids
insert into star.s_device_master(device_id, device_gen_id, first_date)
select a.device_id,
min(a.device_gen_id) as device_gen_id, 
min(date(a.log_ts)) as first_date
from staging.stage_session_day a
left join
star.s_device_master b
on a.device_id=b.device_id
where b.device_id is NULL
group by 1
;

SHOW WARNINGS;

# Now process the new data
insert into staging.s_user_day(game_id, client_id, device_gen_id, user_id, version, stat_date, stat_time, sessions)
select a.game_id,
a.client_id, 
b.device_gen_id,
b.user_id, 
max(a.version) as version,
date(a.log_ts),
min(time(a.log_ts)),
count(distinct a.log_ts) as sessions
from staging.stage_session_day a
join star.s_device_master b
on a.device_id=b.device_id
where date(a.log_ts) between @start_date and @end_date
group by 1,2,3,4,6
;

SHOW WARNINGS;

drop table if exists staging.stage_session_day;
drop table if exists star.s_user_day_bkup;
create index user on staging.s_user_day (stat_date, game_id, client_id);
rename table star.s_user_day to star.s_user_day_bkup;
rename table staging.s_user_day to star.s_user_day;


# Create s_user - user summary by game
drop table if exists staging.s_user;
create table staging.s_user
as
select game_id, 
client_id, 
user_id,
min(version) as version, 
min(stat_date) as first_date, 
max(stat_date) as last_date
from star.s_user_day
group by 1,2,3
;

drop table if exists star.s_user_bkup;
create index game on staging.s_user (first_date, game_id, client_id);
rename table star.s_user to star.s_user_bkup;
rename table staging.s_user to star.s_user;
