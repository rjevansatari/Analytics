#create table tmp.outlaw_events_20121112
#as
#select user_id, event_id, stat_date, stat_time, parm, value
#from star.s_user_event
#where game_id=23
#and client_id=1
#;

#create index event_id on tmp.outlaw_events_20121112(event_id);

#select event_name, a.event_id, count(*) as freq
#from lookups.l_event a, tmp.outlaw_events_20121112 b
#where a.event_id=b.event_id
#and stat_date='2012-11-09'
#group by 1,2
#;

drop table if exists tmp.a_metrics_outlaw_daily_dash;
drop table if exists tmp.dau_outlaw_daily_dash;

#DAU
create table tmp.a_metrics_outlaw_daily_dash engine=myisam
as
select stat_date,
game_id,
client_id,
cast('dau' as char(13)) as metric,
cast(sum(value) as decimal(10,2)) as value
from star.s_game_day
where metric='ActiveUsersByDay'
and stat_date between date_sub('2012-11-12', interval 30+7 day) and '2012-11-12'
and game_id in (23)
and client_id=1
group by 1,2,3,4
order by 1,2,3,4
;

#Installs
insert into tmp.a_metrics_outlaw_daily_dash
select stat_date,
game_id,
client_id,
'installs' as metric,
sum(value) as value
from star.s_game_day
where metric='NewUsers'
and stat_date between date_sub('2012-11-12', interval 30+7 day) and '2012-11-12'
and game_id in (23)
and client_id=1
group by 1,2,3,4
order by 1,2,3,4
;

#Sessions
insert into tmp.a_metrics_outlaw_daily_dash
select stat_date,
game_id,
client_id,
'sessions' as metric,
1.0*sum(case when metric='Sessions' then value else 0 end)/(1.0*sum(case when metric='ActiveUsersByDay' then value else 0 end)) as value
from star.s_game_day
where metric in ('ActiveUsersByDay','Sessions')
and stat_date between date_sub('2012-11-12', interval 30+7 day) and '2012-11-12'
and game_id in (23)
and client_id=1
group by 1,2,3,4
order by 1,2,3,4
;

#Session Length
insert into tmp.a_metrics_outlaw_daily_dash
select stat_date,
game_id,
client_id,
'sessionlen' as metric,
sum(value) as value
from star.s_game_day
where metric='MedianSessionLength'
and stat_date between date_sub('2012-11-12', interval 30+7 day) and '2012-11-12'
and game_id in (23)
and client_id=1
group by 1,2,3,4
order by 1,2,3,4
;

#MAU

create table tmp.dau_outlaw_daily_dash(index(user_id)) engine=MyIsam
as
select
game_id,
client_id,
user_id,
stat_date
from star.s_user_day
where game_id in (23)
and client_id=1
group by 1,2,3,4
;

# 7 Day Averages
insert into tmp.a_metrics_outlaw_daily_dash
select stat_date,
game_id,
client_id,
'7dau' as metric,
round(sum(value)/7.0) as value
from
( select d.date as stat_date,
a.game_id,
a.client_id,
count(distinct a.user_id) as value
from tmp.dau_outlaw_daily_dash a, lookups.d_date d
where a.stat_date between date_sub(d.date, interval 6 day) and d.date
and d.date between date_sub('2012-11-12', interval 30 day) and '2012-11-12'
group by 1,2,3
) a
group by 1,2,3,4
order by 1,2,3,4
;

# Summarise MAU
insert into tmp.a_metrics_outlaw_daily_dash
select stat_date,
game_id,
client_id,
'mau' as metric,
sum(value) as value
from
( select d.date as stat_date,
a.game_id,
a.client_id,
count(distinct a.user_id) as value
from tmp.dau_outlaw_daily_dash a, lookups.d_date d
where a.stat_date between date_sub(d.date, interval 29 day) and d.date
and d.date between date_sub('2012-11-12', interval 30+7 day) and '2012-11-12'
group by 1,2,3
) a
group by 1,2,3,4
order by 1,2,3,4
;

create temporary table tmp.fx_rate
as
select b.currency_code, b.fx_rate
from
(
        select currency_code, max(fx_date) as fx_date
        from lookups.l_fx_rate
        group by 1
) a,
lookups.l_fx_rate b
where a.currency_code=b.currency_code
and a.fx_date=b.fx_date
group by 1,2
;

# Revenue
create temporary table tmp.revenue_outlaw_daily_dash(index(stat_date, currency_code)) engine=myisam
as
select start_date as stat_date,
23 as game_id,
1 as client_id,
currency_of_proceeds as currency_code,
sum(units*net_revenue)/0.7 as gross_revenue
from star.itunes_sales a
where start_date between date_sub('2012-11-12', interval 30+7 day) and '2012-11-12'
and upper(sku) like '%OUTLAW%'
group by 1,2,3,4
;

#Summarise Revenue
insert into tmp.a_metrics_outlaw_daily_dash
SELECT stat_date,
game_id,
client_id,
'revenue' as metric,
sum(gross_revenue/case when b.fx_rate is null then c.fx_rate else b.fx_rate end) as value
from tmp.revenue_outlaw_daily_dash a
left join
lookups.l_fx_rate b
on a.stat_date=b.fx_date
and a.currency_code=b.currency_code
join tmp.fx_rate c
on a.currency_code=c.currency_code
where stat_date between date_sub('2012-11-12', interval 30+7 day) and '2012-11-12'
group by 1,2,3,4
order by 1,2,3,4
;

#Units
insert into tmp.a_metrics_outlaw_daily_dash
SELECT start_date as stat_date,
23 as game_id,
1 as client_id,
'units' as metric,
sum(case when net_revenue>0 then units else 0 end) as value
from star.itunes_sales a
where start_date between date_sub('2012-11-12', interval 30+7 day) and '2012-11-12'
and upper(sku) like '%OUTLAW%'
group by 1,2,3,4
order by 1,2,3,4
;

select stat_date as Date,
max(a.dau) as DAU,
max(a.installs) as Installs,
max(a.mau) as MAU,
max(a.engagement) as Engagement,
max(a.revenue) as Revenue,
max(a.units) as Units,
sum(b.dau)/(30) as Avg_DAU,
sum(b.installs)/(30) as Avg_Installs,
sum(b.mau)/(30) as Avg_MAU,
max(b.engagement) as Avg_Engagement,
sum(b.revenue)/(30) as Avg_Revenue,
sum(b.units)/(30) as Avg_Units
from
(
        select stat_date,
        sum(case when metric='dau' then value else 0 end) as dau,
        sum(case when metric='installs' then value else 0 end) as installs,
        sum(case when metric='mau' then value else 0 end) as mau,
        100*sum(case when metric='7dau' then value else 0 end)/
        sum(case when metric='mau' then value else 0 end) as engagement,
        sum(case when metric='revenue' then value else 0 end) as revenue,
        sum(case when metric='units' then value else 0 end) as units
        from tmp.a_metrics_outlaw_daily_dash
        where stat_date between date_sub('2012-11-12', interval 30 day) and '2012-11-12'
        group by 1
) a,
(
        select
        sum(case when metric='dau' then value else 0 end) as dau,
        sum(case when metric='installs' then value else 0 end) as installs,
        sum(case when metric='mau' then value else 0 end) as mau,
        100*sum(case when metric='7dau' then value else 0 end)/
        sum(case when metric='mau' then value else 0 end) as engagement,
        sum(case when metric='revenue' then value else 0 end) as revenue,
        sum(case when metric='units' then value else 0 end) as units
        from tmp.a_metrics_outlaw_daily_dash
        where stat_date between date_sub('2012-11-12', interval 30 day) and '2012-11-12'
) b
group by 1
order by 1
;

select
'DAU' as Metric,
format(sum(case when stat_date='2012-11-12' then value else 0 end),0) as Value,
concat(format(100*sum(case when stat_date='2012-11-12' then value else 0 end)/
sum(case when stat_date=date_sub('2012-11-12', interval 1 day) then value else 0 end)-100,2),'%') as 'DoD % Change',
concat(format(100*sum(case when stat_date='2012-11-12' then value else 0 end)/
sum(case when stat_date=date_sub('2012-11-12', interval 7 day) then value else 0 end)-100,2),'%') as 'WoW % Change'
from tmp.a_metrics_outlaw_daily_dash
where metric='dau'
group by 1
union
select
'Installs' as Metric,
format(sum(case when stat_date='2012-11-12' then value else 0 end),0) as Value,
concat(format(100*sum(case when stat_date='2012-11-12' then value else 0 end)/
sum(case when stat_date=date_sub('2012-11-12', interval 1 day) then value else 0 end)-100,2),'%') as 'DoD % Change',
concat(format(100*sum(case when stat_date='2012-11-12' then value else 0 end)/
sum(case when stat_date=date_sub('2012-11-12', interval 7 day) then value else 0 end)-100,2),'%') as 'WoW % Change'
from tmp.a_metrics_outlaw_daily_dash
where metric='installs'
group by 1
union
select
'Sessions/DAU' as Metric,
format(sum(case when stat_date='2012-11-12' then value else 0 end),2) as Value,
concat(format(100*sum(case when stat_date='2012-11-12' then value else 0 end)/
sum(case when stat_date=date_sub('2012-11-12', interval 1 day) then value else 0 end)-100,2),'%') as 'DoD % Change',
concat(format(100*sum(case when stat_date='2012-11-12' then value else 0 end)/
sum(case when stat_date=date_sub('2012-11-12', interval 7 day) then value else 0 end)-100,2),'%') as 'WoW % Change'
from tmp.a_metrics_outlaw_daily_dash
where metric='sessions'
and game_id=-2
group by 1
union
select
'Med Session Length' as Metric,
format(sum(case when stat_date='2012-11-12' then value else 0 end),0) as Value,
concat(format(100*sum(case when stat_date='2012-11-12' then value else 0 end)/
sum(case when stat_date=date_sub('2012-11-12', interval 1 day) then value else 0 end)-100,2),'%') as 'DoD % Change',
concat(format(100*sum(case when stat_date='2012-11-12' then value else 0 end)/
sum(case when stat_date=date_sub('2012-11-12', interval 7 day) then value else 0 end)-100,2),'%') as 'WoW % Change'
from tmp.a_metrics_outlaw_daily_dash
where metric='sessionlen'
and game_id=-2
group by 1
union
select
'MAU' as Metric,
format(sum(case when stat_date='2012-11-12' then value else 0 end),0) as Value,
concat(format(100*sum(case when stat_date='2012-11-12' then value else 0 end)/
sum(case when stat_date=date_sub('2012-11-12', interval 1 day) then value else 0 end)-100,2),'%') as 'DoD % Change',
concat(format(100*sum(case when stat_date='2012-11-12' then value else 0 end)/
sum(case when stat_date=date_sub('2012-11-12', interval 7 day) then value else 0 end)-100,2),'%') as 'WoW % Change'
from tmp.a_metrics_outlaw_daily_dash
where metric='mau'
group by 1
union
select
'Revenue' as Metric,
concat('$',format(sum(case when stat_date='2012-11-12' then value else 0 end),2)) as Value,
concat(format(100*sum(case when stat_date='2012-11-12' then value else 0 end)/
sum(case when stat_date=date_sub('2012-11-12', interval 1 day) then value else 0 end)-100,2),'%') as 'DoD % Change',
concat(format(100*sum(case when stat_date='2012-11-12' then value else 0 end)/
sum(case when stat_date=date_sub('2012-11-12', interval 7 day) then value else 0 end)-100,2),'%') as 'WoW % Change'
from tmp.a_metrics_outlaw_daily_dash
where metric='revenue'
group by 1
union
select
'Revenue/DAU' as Metric,
concat('$',format(sum(case when metric='revenue' and stat_date='2012-11-12' then value else 0 end)/
sum(case when metric='dau'     and stat_date='2012-11-12' then value else 0 end),3)) as Value,
concat(format(100*(sum(case when metric='revenue' and stat_date='2012-11-12' then value else 0 end)/
 sum(case when metric='dau'     and stat_date='2012-11-12' then value else 0 end))/
(sum(case when metric='revenue' and stat_date=date_sub('2012-11-12', interval 1 day) then value else 0 end)/
 sum(case when metric='dau'     and stat_date=date_sub('2012-11-12', interval 1 day) then value else 0 end))-100,2),'%') as 'DoD % Change',
concat(format(100*(sum(case when metric='revenue' and stat_date='2012-11-12' then value else 0 end)/
 sum(case when metric='dau'     and stat_date='2012-11-12' then value else 0 end))/
(sum(case when metric='revenue' and stat_date=date_sub('2012-11-12', interval 7 day) then value else 0 end)/
 sum(case when metric='dau'     and stat_date=date_sub('2012-11-12', interval 7 day) then value else 0 end))-100,2),'%') as 'WoW % Change'
from tmp.a_metrics_outlaw_daily_dash
where metric in('revenue','dau')
group by 1
order by 1
;

drop table if exists tmp.dau_outlaw_daily_dash;

create table tmp.dau_outlaw_daily_dash(index(user_id)) engine=MyIsam
as
select user_id,
date(stat_date) as stat_date
from star.s_user_day
where game_id=23
and client_id=1
group by 1,2
;

select stat_date, count(*)
from star.s_user_day
where game_id=23
and client_id=1
group by 1
order by 1
;

drop table if exists tmp.installs_outlaw_daily_dash;

create table tmp.installs_outlaw_daily_dash(index(user_id)) engine=MyIsam
as
select user_id,
min(stat_date) as install_date
from tmp.dau_outlaw_daily_dash
group by 1
;

select install_date, count(*)
from tmp.installs_outlaw_daily_dash
group by 1
order by 1
;

drop table if exists tmp.retention_summary_outlaw_daily_dash;

create table tmp.retention_summary_outlaw_daily_dash engine=MyIsam
as
SELECT install_date,
datediff(stat_date, install_date) as ddiff,
count(distinct i.user_id) as cnt
FROM tmp.installs_outlaw_daily_dash i
left join tmp.dau_outlaw_daily_dash d
on i.user_id  = d.user_id
where stat_date between date_sub('2012-11-12', interval 30 day) and '2012-11-12'
GROUP BY 1,2
;

SELECT install_date as "Install Date",
users as Installs,
day1 *100.0 / users as "D1%",
#day2 *100.0 / users as "D2%",
#day3 *100.0 / users as "D3%",
#day4 *100.0 / users as "D4%",
#day5 *100.0 / users as "D5%",
#day6 *100.0 / users as "D6%",
day7 *100.0 / users as "D7%",
#day8 *100.0 / users as "D8%",
#day9 *100.0 / users as "D9%",
#day10 *100.0 / users as "D10%",
#day11 *100.0 / users as "D11%",
#day12 *100.0 / users as "D12%",
#day13 *100.0 / users as "D13%",
day14 *100.0 / users as "D14%",
#day15 *100.0 / users as "D15%",
#day16 *100.0 / users as "D16%",
#day17 *100.0 / users as "D17%",
#day18 *100.0 / users as "D18%",
#day19 *100.0 / users as "D19%",
#day20 *100.0 / users as "D20%",
#day21 *100.0 / users as "D21%",
#day22 *100.0 / users as "D22%",
#day23 *100.0 / users as "D23%",
#day24 *100.0 / users as "D24%",
#day25 *100.0 / users as "D25%",
#day26 *100.0 / users as "D26%",
#day27 *100.0 / users as "D27%",
#day28 *100.0 / users as "D28%",
#day29 *100.0 / users as "D29%",
day30 *100.0 / users as "D30%",
day1,
#day2,
#day3,
#day4,
#day5,
#day6,
day7,
#day8,
#day9,
#day10,
#day11,
#day12,
#day13,
day14,
#day15,
#day16,
#day17,
#day18,
#day19,
#day20,
#day21,
#day22,
#day23,
#day24,
#day25,
#day26,
#day27,
#day28,
#day29,
day30
FROM
(
SELECT install_date,
sum(case when ddiff=0 or ddiff is null then cnt else 0 end) users,
sum(case when ddiff = 1 then cnt else 0 end) day1,
#sum(case when ddiff = 2 then cnt else 0 end) day2,
#sum(case when ddiff = 3 then cnt else 0 end) day3,
#sum(case when ddiff = 4 then cnt else 0 end) day4,
#sum(case when ddiff = 5 then cnt else 0 end) day5,
#sum(case when ddiff = 6 then cnt else 0 end) day6,
sum(case when ddiff = 7 then cnt else 0 end) day7,
#sum(case when ddiff = 8 then cnt else 0 end) day8,
#sum(case when ddiff = 9 then cnt else 0 end) day9,
#sum(case when ddiff = 10 then cnt else 0 end) day10,
#sum(case when ddiff = 11 then cnt else 0 end) day11,
#sum(case when ddiff = 12 then cnt else 0 end) day12,
#sum(case when ddiff = 13 then cnt else 0 end) day13,
sum(case when ddiff = 14 then cnt else 0 end) day14,
#sum(case when ddiff = 15 then cnt else 0 end) day15,
#sum(case when ddiff = 16 then cnt else 0 end) day16,
#sum(case when ddiff = 17 then cnt else 0 end) day17,
#sum(case when ddiff = 18 then cnt else 0 end) day18,
#sum(case when ddiff = 19 then cnt else 0 end) day19,
#sum(case when ddiff = 20 then cnt else 0 end) day20,
#sum(case when ddiff = 21 then cnt else 0 end) day21,
#sum(case when ddiff = 22 then cnt else 0 end) day22,
#sum(case when ddiff = 23 then cnt else 0 end) day23,
#sum(case when ddiff = 24 then cnt else 0 end) day24,
#sum(case when ddiff = 25 then cnt else 0 end) day25,
#sum(case when ddiff = 26 then cnt else 0 end) day26,
#sum(case when ddiff = 27 then cnt else 0 end) day27,
#sum(case when ddiff = 28 then cnt else 0 end) day28,
#sum(case when ddiff = 29 then cnt else 0 end) day29,
sum(case when ddiff = 30 then cnt else 0 end) day30
FROM
tmp.retention_summary_outlaw_daily_dash
where install_date between date_sub('2012-11-12',interval 29 day) and '2012-11-12'
GROUP BY install_date
) a
ORDER BY install_date desc;

drop table if exists tmp.ftue_outlaw_daily_dash;

create table tmp.ftue_outlaw_daily_dash(index(install_date, user_id, event_id))
as
select a.user_id, a.event_id, b.install_date, a.stat_date, a.stat_time
from star.s_user_event a, tmp.installs_outlaw_daily_dash b
where a.game_id=23
and a.user_id=b.user_id
and b.install_date between date_sub('2012-11-12',interval 7 day) and '2012-11-12'
and a.event_id in (
475,
476,
477,
478,
479,
480,
481,
482,
483,
484,
485,
486,
487,
488,
489,
490,
491,
492,
493,
494,
495,
496,
497,
498,
499,
500,
501,
502,
503
)
group by 1,2,3,4,5
;

select install_date as 'Install Date',
count(distinct user_id) as Installs,
sum(FT01_Started)/count(distinct user_id) as FT01_Started,
sum(FT01_Completed)/count(distinct user_id) as FT01_Completed,
sum(FT02_Started)/count(distinct user_id) as FT02_Started,
sum(FT02_Completed)/count(distinct user_id) as FT02_Completed,
sum(FT03_Started)/count(distinct user_id) as FT03_Started,
sum(FT03_Completed)/count(distinct user_id) as FT03_Completed,
sum(FT04Aiming_Started)/count(distinct user_id) as FT04Aiming_Started,
sum(FT04Aiming_Completed)/count(distinct user_id) as FT04Aiming_Started,
sum(FT05_Started)/count(distinct user_id) as FT05_Started,
sum(FT05_Completed)/count(distinct user_id) as FT05_Completed,
sum(FT06_Started)/count(distinct user_id) as FT06_Started,
sum(FT06_Completed)/count(distinct user_id) as FT06_Completed,
sum(FT07_Started)/count(distinct user_id) as FT07_Started, 
sum(FT07_Completed)/count(distinct user_id) as FT07_Completed,
sum(FT08_Started)/count(distinct user_id) as FT08_Started,  
sum(FT08_Completed)/count(distinct user_id) as FT08_Completed,
sum(FT09_Started)/count(distinct user_id) as FT09_Started,  
sum(FT09_Completed)/count(distinct user_id) as FT09_Completed,
sum(FT10_Started)/count(distinct user_id) as FT10_Started,
sum(FT10_Completed)/count(distinct user_id) as FT10_Completed,
sum(FT11_Started)/count(distinct user_id) as FT11_Started,
sum(FT11_Completed)/count(distinct user_id) as FT11_Completed,
sum(FT12_Started)/count(distinct user_id) as FT12_Started,
sum(FT12_Completed)/count(distinct user_id) as FT12_Completed,
sum(FT13_Started)/count(distinct user_id) as FT13_Started,
sum(FT13_Completed)/count(distinct user_id) as FT13_Completed,
sum(FT14_Started)/count(distinct user_id) as FT14_Started,
sum(FT14_Completed)/count(distinct user_id) as FT14_Completed
sum(freq) as Freq
from 
(
select install_date, 
user_id, 
max(case when event_id=475 then 1 else 0 end) as FT01_Started,
max(case when event_id=476 then 1 else 0 end) as FT01_Completed,
max(case when event_id=478 then 1 else 0 end) as FT02_Started,
max(case when event_id=477 then 1 else 0 end) as FT02_Completed,
max(case when event_id=480 then 1 else 0 end) as FT03_Started,
max(case when event_id=479 then 1 else 0 end) as FT03_Completed,
max(case when event_id=482 then 1 else 0 end) as FT04Aiming_Started,
max(case when event_id=481 then 1 else 0 end) as FT04Aiming_Completed,
max(case when event_id=485 then 1 else 0 end) as FT05_Started,
max(case when event_id=484 then 1 else 0 end) as FT05_Completed,
max(case when event_id=487 then 1 else 0 end) as FT06_Started,
max(case when event_id=486 then 1 else 0 end) as FT06_Completed,
max(case when event_id=489 then 1 else 0 end) as FT07_Started,  
max(case when event_id=488 then 1 else 0 end) as FT07_Completed,
max(case when event_id=491 then 1 else 0 end) as FT08_Started,  
max(case when event_id=490 then 1 else 0 end) as FT08_Completed,
max(case when event_id=493 then 1 else 0 end) as FT09_Started,  
max(case when event_id=491 then 1 else 0 end) as FT09_Completed,
max(case when event_id=495 then 1 else 0 end) as FT10_Started,
max(case when event_id=494 then 1 else 0 end) as FT10_Completed,
max(case when event_id=497 then 1 else 0 end) as FT11_Started,
max(case when event_id=496 then 1 else 0 end) as FT11_Completed,
max(case when event_id=499 then 1 else 0 end) as FT12_Started,
max(case when event_id=498 then 1 else 0 end) as FT12_Completed,
max(case when event_id=501 then 1 else 0 end) as FT13_Started,
max(case when event_id=500 then 1 else 0 end) as FT13_Completed,
max(case when event_id=503 then 1 else 0 end) as FT14_Started,
max(case when event_id=502 then 1 else 0 end) as FT14_Completed,
count(*) as freq
from tmp.outlaw_daily_dash
group by 1,2
) a
group by 1
;
