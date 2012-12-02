drop table if exists tmp.events_outlaw_daily_dash;

create table tmp.events_outlaw_daily_dash(index(user_id, event_id))
as
select user_id, a.event_id, stat_date, stat_time, parm_id, value
from star.s_user_event a
where game_id=23
and client_id=1
and a.stat_date between date_sub('2012-11-12',interval 7 day) and '2012-11-12'
and user_id % 100 = 1
;
