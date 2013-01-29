create table tmp.wb_events
as
select stat_date, a.event_id, event_name, a.parm_id, parm_name, value, count(*) as freq
from star.s_user_event a, lookups.l_event e, lookups.l_parm p
where game_id=11
and stat_date between '2012-12-11' and '2013-01-06'
and client_id=1
and a.event_id=e.event_id
and a.parm_id=p.parm_id
group by 1,2,3,4,5,6
order by 1,2,3,4,5,6
;

select stat_date, event_id, event_name, parm_id, parm_name, value, freq
from tmp.wb_events
order by 1,2,3,4,5,6
;
