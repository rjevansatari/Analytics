select a.stat_date, 
a.value as chapter,
b.value as stage,
count(distinct user_id)) as freq 
from tmp.user_death a, 
tmp.user_death b
where a.event_id=310
and a.event_id=b.event_id
and a.parm in ('chapter')
and b.parm in ('stage')
and a.stat_time=b.stat_time
and a.stat_date=b.stat_date
and a.stat_date>='2012-11-05'
group by 1,2,3
;

select a.stat_date, 
a.value as chapter,
b.value as stage,
count(distinct user_id):wq
) as freq 
from tmp.user_death a, 
tmp.user_death b
where a.event_id=311
and a.event_id=b.event_id
and a.parm in ('chapter')
and b.parm in ('stage')
and a.stat_time=b.stat_time
and a.stat_date=b.stat_date
and a.stat_date>='2012-11-05'
group by 1,2,3
;
