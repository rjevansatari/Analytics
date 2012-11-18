#drop table if exists tmp.sbb_gamesessions;

#create table tmp.sbb_gamesessions
#as
#select stat_date, stat_time, game_id, user_id, parm, value
#from star.s_user_event
#where game_id in (21,24)
#and stat_date>'2012-10-10'
#and event_id=374
#group by 1,2,3,4,5,6
#;

select stat_date, game_id, parm, 
case when value=0                       then '          0'
     when value between 1     and 100   then '      1-100'
     when value between 101   and 200   then '    101-200'
     when value between 201   and 500   then '    201-500'
     when value between 501   and 1000  then '   501-1000'
     when value between 1001  and 2000  then '  1001-2000'
     when value between 2001  and 5000  then '  2001-5000'
     when value between 5001  and 10000 then ' 5001-10000'
     when value between 10001 and 20000 then '10001-20000'
     when value between 20001 and 50000 then '20001-50000'
     when value >       50001           then '50001+     '
end as DurationBetweenSessions,
count(distinct user_id) as users
from tmp.sbb_gamesessions
where parm not in ('UserID','StartTime')
and parm='DurationBetweenSessions'
group by 1,2,3,4
order by 1,2,3,4
;

select stat_date, game_id, parm, 
case when value=0                    then '        0'
     when value between 1   and 10   then '     1-10'
     when value between 11  and 20   then '    11-20'
     when value between 21  and 50   then '    21-50'
     when value between 51  and 100  then '   51-100'
     when value between 101 and 200  then '  101-200'
     when value between 201 and 300  then '  201-300'
     when value between 301 and 400  then '  301-400'
     when value between 401 and 500  then '  401-500'
     when value between 501 and 1000 then ' 500-1000'
     when value >       1001         then '1001+    '
end as SessionDuration,
count(distinct user_id) as users
from tmp.sbb_gamesessions
where parm not in ('UserID','StartTime')
and parm='SessionDuration'
group by 1,2,3,4
order by 1,2,3,4
;
