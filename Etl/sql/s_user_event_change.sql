# Load into staging 
insert into staging.stage_user_event(game_id, client_id, user_id, stat_date, stat_time, event, parm, value)
select game_id,
client_id,
user_id,
date(stat_datetime),
time(stat_datetime),
event,
parm,
value
from star.s_user_event
where date(stat_datetime) >= date_sub(curdate(), interval 90 day)
;
