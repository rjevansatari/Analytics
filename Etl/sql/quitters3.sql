select g.game_name, 
a.install_date,
datediff(last_date, install_date) as days_to_quit,
count(distinct a.user_id) as installs,
count(distinct b.user_id) as quitters
from tmp.installs a
left join 
tmp.last b
on a.user_id=b.user_id
and a.game_id=b.game_id
and datediff('2012-09-28',last_date)>31
inner join
lookups.l_flurry_game g
on a.game_id=g.game_id
group by 1,2,3
order by 1,2,3
;
