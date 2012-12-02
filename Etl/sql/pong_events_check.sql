drop table if exists tmp.pong_events;
create table tmp.pong_events
as
select stat_date, stat_time, user_id, event_id, parm_id, value
from star.s_user_event
where game_id=25
and client_id=1
and stat_date > '2012-11-10'
group by 1,2,3,4,5,6
;
