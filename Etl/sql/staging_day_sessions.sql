# Clean up
DROP DATABASE staging;
CREATE DATABASE staging DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;

use staging;

DROP TABLE if EXISTS staging.stage_session_day;

CREATE TABLE staging.stage_session_day (
`game_id` smallint NOT NULL,
`client_id` smallint NOT NULL,
`device_id` varchar(80) NOT NULL,
`version` varchar(16) NOT NULL,
`device_gen` varchar(80) NOT NULL,
`log_ts` timestamp NOT NULL 
) ENGINE=MyISAM DEFAULT CHARSET=latin1
;

LOAD DATA LOCAL INFILE '/home/revans/projects/analytics/csv/staging_day_sessions.csv' INTO TABLE staging.stage_session_day 
FIELDS TERMINATED BY ','
ENCLOSED BY '\'';

SHOW WARNINGS;

# Create all the indexes that we need
CREATE INDEX device_id  on staging.stage_session_day(device_id(10));

# Get Min Date
SELECT game_id, date(log_ts) as Date,
count(*)
from staging.stage_session_day
where date(log_ts)<curdate()
group by 1,2
;

DROP TABLE if EXISTS staging.min_ts;

CREATE TABLE staging.min_ts engine=myisam
as
select game_id, client_id, min(log_ts) as log_ts
from staging.stage_session_day
where date(log_ts)=@stat_date
group by 1,2
;

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
;

# Process new devices
DROP table if exists staging.device_types;

# Assign New Device Types
create table staging.device_types ENGINE=MyISAM
as
select distinct device_gen
from staging.stage_session_day
order by 1
;

SHOW WARNINGS;

create index device_gen on staging.device_types(device_gen(18)) ;

SHOW WARNINGS;

# Assign New Device Types
DROP table if exists staging.new_device_types;

create table staging.new_device_types ENGINE=MyISAM
as
select a.device_gen 
from staging.device_types a
left join
lookups.l_device_gen b
on a.device_gen=b.device_gen
where b.device_gen is NULL
order by 1
;

SHOW WARNINGS;

# Insert the new device types(device_gen)
insert into lookups.l_device_gen(device_gen)
select device_gen
from staging.new_device_types
;

SHOW WARNINGS;

# Assign New Device/User Ids
drop table if exists staging.device_ids;

create table staging.device_ids ENGINE=MyISAM
as
select device_id,
device_gen,
min(date(log_ts)) as first_date
from staging.stage_session_day
group by 1,2
order by 1,2
;

SHOW WARNINGS;

create index device_id  on staging.device_ids(device_id(10)) ;

SHOW WARNINGS;

# Create new ids
drop table if exists staging.new_device_ids;

create table staging.new_device_ids ENGINE=MyISAM
as
select a.device_id,
min(a.device_gen) as device_gen, 
min(a.first_date) as first_date
from staging.device_ids a
left join
star.s_device_master b
on a.device_id=b.device_id
where b.device_id is NULL
group by 1
order by 1,2
;

SHOW WARNINGS;

create index device_gen on staging.new_device_ids(device_gen(18)) ;

drop index device_id on star.s_device_master;

SHOW WARNINGS;

# Load Master Table
insert into star.s_device_master(device_id, device_gen_id, first_date)
select device_id, device_gen_id, first_date
from staging.new_device_ids a, lookups.l_device_gen b
where a.device_gen=b.device_gen
order by 1
;

SHOW WARNINGS;

create index device_id on star.s_device_master(device_id(10)) ;

# Now process the new data
insert into staging.s_user_day(game_id, client_id, device_gen_id, user_id, stat_date, stat_time, sessions)
select a.game_id,
a.client_id, 
b.device_gen_id,
b.user_id, 
date(a.log_ts),
min(time(a.log_ts)),
count(distinct a.log_ts) as sessions
from staging.stage_session_day a
join star.s_device_master b
on a.device_id=b.device_id
where a.log_ts<now()
group by 1,2,3,4,5
;

SHOW WARNINGS;

drop table if exists staging.stage_session_day;
drop table if exists star.s_user_day_bkup;
rename table star.s_user_day to star.s_user_day_bkup;
rename table staging.s_user_day to star.s_user_day;

create index user on star.s_user_day (game_id, client_id, user_id);

# Create s_user - user summary by game
drop table if exists staging.s_user;
create table staging.s_user
as
select game_id, client_id, user_id, min(stat_date) as first_date, max(stat_date) as last_date
from star.s_user_day
group by 1,2,3
;

drop table if exists star.s_user_bkup;
rename table star.s_user to star.s_user_bkup;
rename table staging.s_user to star.s_user;

create index user on star.s_user (game_id, client_id, user_id);
