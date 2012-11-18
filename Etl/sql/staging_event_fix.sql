use staging;

create temporary table staging.l_event engine=Memory
as
select * from lookups.l_event
;

create index event on staging.l_event(event_name(7));

truncate table staging.stage_daily;

LOAD DATA LOCAL INFILE '/home/revans/projects/analytics/csv/staging_day.csv' INTO TABLE staging.stage_daily
FIELDS TERMINATED BY ','
ENCLOSED BY '\'';

create index device_id on staging.stage_daily(device_id(10));
create index category on staging.stage_daily(category(7));

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
