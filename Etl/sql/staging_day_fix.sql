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

drop table if exists staging.events;

select distinct event
from staging.stage_user_event
order by 1
;
select distinct event_name from lookups.l_event order by 1
;

create table staging.events ENGINE=MyiSAM
select distinct event
from staging.stage_user_event
order by 1
;

SHOW WARNINGS;

# Assign New Event Type
CREATE temporary table tmp.l_event(index(event_name(10))) engine=Memory
as
select * from lookups.l_event
;
show create table tmp.l_event;

CREATE INDEX event ON staging.stage_user_event(event(10));

drop table if exists staging.new_events;

select distinct a.event, b.event_name
from staging.events a
join
tmp.l_event b
on a.event=b.event_name
#where (b.event_name is NULL or
#       b.event_name='')
order by 1
;

create table staging.new_events ENGINE=MyiSAM
select distinct a.event
from staging.events a
left join
tmp.l_event b
on a.event=b.event_name
where (b.event_name is NULL or
       b.event_name='')
order by 1
;
select * from staging.new_events;

SHOW WARNINGS;

# Insert the new events
insert into lookups.l_event(event_name)
select distinct event
from staging.new_events
;

#select * from lookups.l_event;

SHOW WARNINGS;

