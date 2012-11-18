drop table if exists tmp.dau;
drop table if exists tmp.installs;
drop table if exists tmp.last;

create table tmp.dau(index(user_id), index(stat_date)) engine=MyIsam
as
select stat_date,
game_id,
user_id
from star.s_user_day
where game_id in (1,22,16,5)
and client_id=1
group by 1,2,3
;

create table tmp.installs(index(user_id), index(install_date)) engine=MyIsam
as select game_id, 
user_id,
min(stat_date) as install_date
from tmp.dau
group by 1,2
;

create table tmp.last(index(user_id), index(last_date)) engine=MyIsam
as 
select game_id, 
user_id, 
max(stat_date) as last_date
from tmp.dau
group by 1,2
;

select g.game_name, 
a.install_date,
count(distinct a.user_id) as installs,
count(distinct b.user_id) as quitters
from tmp.installs a
left join 
tmp.last b
on a.user_id=b.user_id
and a.game_id=b.game_id
and datediff('2012-09-28',last_date)=31
inner join
lookups.l_flurry_game g
on a.game_id=g.game_id
group by 1,2
order by 1,2
;
