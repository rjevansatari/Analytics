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
