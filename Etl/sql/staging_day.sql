use staging;

drop table if exists staging.stage_user_day;

CREATE TABLE staging.stage_user_day (
 `game_id` int(11) NOT NULL,
 `client_id` int(11) NOT NULL,
 `device_id` varchar(80) NOT NULL,
 `device_gen` varchar(80) NOT NULL,
 `stat_date` date NOT NULL,
 `stat_time` time DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1
;


drop table if exists staging.stage_user_event;

CREATE TABLE staging.stage_user_event (
 `game_id` int(11) NOT NULL,
 `client_id` int(11) NOT NULL,
 `device_id` varchar(80) NOT NULL,
 `device_gen` varchar(80) NOT NULL,
 `stat_date` date DEFAULT NULL,
 `stat_time` time NOT NULL,
 `event` varchar(255) NOT NULL,
 `parm` varchar(255) NOT NULL,
 `value` varchar(80) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1
;

# Get sessions

insert into staging.stage_user_day(game_id, client_id, device_id, device_gen, stat_date, stat_time)
select game_id,
client_id,
device_id,
device_gen,
date(log_ts),
time(log_ts)
from staging.stage_day
where type='SESSION'
and log_ts < CURRENT_TIMESTAMP()
;

# Get events

insert into staging.stage_user_event(game_id, client_id, device_id, device_gen, stat_date, stat_time, event, parm, value)
select game_id,
client_id,
device_id,
device_gen,
date(log_ts),
time(log_ts),
category,
parm,
value
from staging.stage_day
where date(log_ts) >= date_sub(curdate(), interval 60 day)
and type='EVENT'
and log_ts < CURRENT_TIMESTAMP()
;

DROP TABLE if EXISTS staging.min_ts;

CREATE TABLE staging.min_ts engine=myisam
as
select game_id, client_id, type, min(log_ts) as log_ts
from staging.stage_day
where date(log_ts)=@stat_date
group by 1,2,3
;

DROP TABLE if EXISTS staging.stage_day;

# Now remove any data that has since been updated
DROP TABLE if EXISTS staging.s_user_day;

# Make sure we get any that are not in staging table loaded
CREATE TABLE staging.s_user_day engine=myisam
as
select a.* 
from star.s_user_day a
left join
staging.min_ts b
ON a.game_id=b.game_id
and a.client_id=b.client_id
and b.type='SESSION'
and timestamp(a.stat_date, a.stat_time)<b.log_ts
WHERE b.game_id is NULL
and b.client_id is NULL
;

SHOW WARNINGS;

# Do not load any data after date
insert into staging.s_user_day
select a.* 
from star.s_user_day a
join
staging.min_ts b
ON a.game_id=b.game_id
and a.client_id=b.client_id
and b.type='SESSION'
and timestamp(a.stat_date, a.stat_time)<b.log_ts
;

DROP TABLE if EXISTS staging.s_user_event;

CREATE TABLE staging.s_user_event engine=myisam
as
select a.*
from star.s_user_event a
LEFT JOIN 
staging.min_ts b
ON a.game_id=b.game_id
and a.client_id=b.client_id
and b.type='EVENT'
and timestamp(a.stat_date, a.stat_time)<b.log_ts
and a.stat_date>=date_sub(curdate(), interval 60 day)
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
and b.type='EVENT'
and timestamp(a.stat_date, a.stat_time)<b.log_ts
and a.stat_date>=date_sub(curdate(), interval 60 day)
;

# Create indexes on device ids for joins
create index device_id on staging.stage_user_day(device_id(10));
SHOW WARNINGS;
create index device_id on staging.stage_user_event(device_id(10));
SHOW WARNINGS;


# Process new devices
DROP table if exists staging.device_types;

# Assign New Device Types
create table staging.device_types ENGINE=MyISAM
as
select distinct device_gen
from staging.stage_user_day
order by 1
;

SHOW WARNINGS;

create index device_gen on staging.device_types(device_gen(18));

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
min(stat_date) as first_date
from staging.stage_user_day
group by 1,2
order by 1,2
;

SHOW WARNINGS;

create index device_id on staging.device_ids(device_id(10));

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

create index device_gen on staging.new_device_ids(device_gen(18));

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

create index device_id on star.s_device_master(device_id(10));

SHOW WARNINGS;

# Assign New Events
drop table if exists staging.events;

create table staging.events ENGINE=MyiSAM
select distinct event
from staging.stage_user_event
order by 1
;

SHOW WARNINGS;

# Assign New Event Type

CREATE INDEX event ON staging.stage_user_event(event(25));

drop table if exists staging.new_events;

create table staging.new_events ENGINE=MyiSAM
select distinct a.event
from staging.events a
left join
lookups.l_event b
on a.event=b.event_name
where b.event_name is NULL
order by 1
;

SHOW WARNINGS;

# Insert the new events
insert into lookups.l_event(event_name)
select distinct event
from staging.new_events
;

SHOW WARNINGS;

#drop index user on star.s_user_day;
#drop index user on star.s_user_event;

# Now process the new data
insert into staging.s_user_day(game_id, client_id, device_gen_id, user_id, stat_date, stat_time, sessions)
select a.game_id,
a.client_id, 
b.device_gen_id,
b.user_id, 
a.stat_date,
min(a.stat_time),
count(distinct a.stat_time) as sessions
from staging.stage_user_day a
join star.s_device_master b
on a.device_id=b.device_id
group by 1,2,3,4,5
;

SHOW WARNINGS;

drop table if exists staging.stage_user_day;

insert into staging.s_user_event(game_id, client_id, device_gen_id, user_id, 
                              stat_date, stat_time, event_id, parm, value)
select a.game_id,
a.client_id, 
b.device_gen_id,
b.user_id, 
a.stat_date,
a.stat_time,
c.event_id,
a.parm,
a.value
from staging.stage_user_event a
join star.s_device_master b
on a.device_id=b.device_id
join lookups.l_event c
on a.event=c.event_name
;

SHOW WARNINGS;

drop table if exists staging.stage_user_event;

#create index user on star.s_user_day  (user_id, stat_date, game_id, client_id);
#create index user on star.s_user_event(user_id, stat_date, game_id, client_id);

drop table if exists star.s_user_day_bkup;
drop table if exists star.s_user_event_bkup;
rename table star.s_user_day to star.s_user_day_bkup;
rename table star.s_user_event to star.s_user_event_bkup;
rename table staging.s_user_day to star.s_user_day;
rename table staging.s_user_event to star.s_user_event;

# Tidy Up
DROP TABLE if EXISTS staging.min_ts;
DROP table if exists staging.new_device_types;
DROP table if exists staging.stage_user_day;
DROP table if exists staging.stage_user_day;
DROP table if exists staging.stage_user_event;
