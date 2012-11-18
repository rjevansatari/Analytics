#select stat_date, 
#screen,
#case when value=0 then                     '   0'
#     when value between 1 and 25 then      '   1-25'
#     when value between 26 and 50 then     '  26-50'
#     when value between 51 and 100 then    '  51-100'
#     when value between 101 and 200 then   ' 101-200'
#     when value between 201 and 300 then   ' 201-300'
#     when value between 301 and 400 then   ' 301-400'
#     when value between 401 and 500 then   ' 401-500'
#     when value between 501 and 1000 then  ' 501-1000'
#     when value between 1001 and 2000 then '1001-2000'
#     when value >2000 then                 '2001+    '
#end as Health,
#count(distinct user_id) as Users, game_id
#from tmp.sbb_endgame_status
#where parm='EndHealth'
#group by 1,2,3,5
#order by 1,2,3,5
#;

select a.stat_date, 
a.screen, 
case when a.value=0 then                     '   0'
     when a.value between 1 and 10 then      '   1-10'
     when a.value between 11 and 20 then     '  11-20'
     when a.value between 21 and 50 then     '  21-50'
     when a.value between 51 and 100 then    '  51-100'
     when a.value between 101 and 200 then   ' 101-200'
     when a.value between 201 and 300 then   ' 201-300'
     when a.value between 301 and 500 then   ' 301-500'
     when a.value between 501 and 1000 then  ' 501-1000'
     when a.value between 1001 and 2000 then '1001-2000'
     when a.value >2000 then                 '2001+    '
end as Coins,
count(distinct a.user_id) as Users, a.game_id
from tmp.sbb_endgame_status a,
tmp.sbb_endgame_status b
where a.parm='EndCoin'
and b.parm='EndHealth'
and b.value=0
and a.user_id=b.user_id
and a.stat_date=b.stat_date
and a.stat_time=b.stat_time
and a.game_id=b.game_id
and a.stat_date <'2012-10-31'
group by 1,2,3,5
order by 1,2,3,5
