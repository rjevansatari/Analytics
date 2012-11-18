use staging;

# Load The Data

Truncate table staging.stage_daily;
LOAD DATA LOCAL INFILE '/home/revans/projects/analytics/csv/staging_daily.csv' INTO TABLE staging.stage_daily 
FIELDS TERMINATED BY ','
ENCLOSED BY '\'';

# Assign New Events
create temporary table staging.events ENGINE=MyiSAM
select distinct category as event
from staging.stage_daily
where category != 'SESSION'
order by 1
;

SHOW WARNINGS;

# Assign New Event Type
create temporary table staging.new_events ENGINE=MyiSAM
select distinct a.category
from staging.stage_daily a
left join
lookups.l_event b
on a.category=b.event_name
where b.event_name is NULL
order by 1
;

SHOW WARNINGS;

# Insert the new events
insert into lookups.l_event(event_name)
select category
from staging.new_events
;

SHOW WARNINGS;

#drop index user on star.s_user_event;

SHOW WARNINGS;

create temporary table staging.l_event engine=Memory
as
select * from lookups.l_event
;

insert into star.s_user_event(game_id, client_id, user_id, 
                              stat_date, stat_time, event_id, parm, value)
select b.game_id,
b.client_id, 
a.user_id, 
date(b.log_ts),
time(b.log_ts),
c.event_id,
b.parm,
b.value
from star.s_device_master a
join staging.stage_daily b
join staging.l_event c
where a.device_id=b.device_id
and b.category=c.event_name
and b.type='EVENT'
and date(b.log_ts) >= date_sub(curdate(), interval 90 day)
order by 1,3,2
;

SHOW WARNINGS;

create index user on star.s_user_event(user_id, stat_date, game_id, client_id);
