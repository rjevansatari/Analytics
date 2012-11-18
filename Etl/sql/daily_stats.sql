#Daily Reports on DAU, Installs, MAU

#DAU
create temporary table tmp.a_metrics
as
select stat_date,
'dau' as metric,
sum(value) as value
from star.s_game_day
where metric='ActiveUsersByDay'
and stat_date between date_sub(curdate(), interval 31 day) and date_sub(curdate(), interval 1 day)
group by 1
order by 1
; 

#Installs
insert into tmp.a_metrics
select stat_date,
'installs' as metrics,
sum(value) as value
from star.s_game_day
where metric='NewUsers'
and stat_date between date_sub(curdate(), interval 31 day) and date_sub(curdate(), interval 1 day)
group by 1,2
order by 1,2
; 

#MAU
create temporary table tmp.dau(index(stat_date, user_id)) engine=myisam
as 
select game_id, client_id, stat_date, user_id
from star.s_user_day
where stat_date between date_sub(curdate(), interval 61 day) and date_sub(curdate(), interval 1 day)
group by 1,2,3,4
;

# 7 Day Averages
insert into tmp.a_metrics
select stat_date,
'7DayAvgDau' as metric,
round(sum(value)/7.0) as value
from
( select d.date as stat_date,
  a.game_id,
  a.client_id,
  count(distinct a.user_id) as value
  from tmp.dau a, lookups.d_date d
  where a.stat_date between date_sub(d.date, interval 6 day) and d.date
  and d.date between date_sub(curdate(), interval 30 day) and date_sub(curdate(), interval 1 day)
  group by 1,2,3
) a
group by 1,2
order by 1,2
;

insert into tmp.a_metrics
select stat_date,
'mau' as metric,
sum(value) as value
from
( select d.date as stat_date,
  a.game_id,
  a.client_id,
  count(distinct a.user_id) as value
  from tmp.dau a, lookups.d_date d
  where a.stat_date between date_sub(d.date, interval 29 day) and d.date
  and d.date between date_sub(curdate(), interval 30 day) and date_sub(curdate(), interval 1 day)
  group by 1,2,3
) a
group by 1,2
order by 1,2
;

select stat_date as Date,
sum(case when metric='dau' then value else 0 end) as DAU,
sum(case when metric='installs' then value else 0 end) as Installs,
sum(case when metric='mau' then value else 0 end) as MAU,
100*sum(case when metric='7dayavgdau' then value else 0 end)/
sum(case when metric='mau' then value else 0 end) as Engagement
from tmp.a_metrics
group by 1
order by 1
;
