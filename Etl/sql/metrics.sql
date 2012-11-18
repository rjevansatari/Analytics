select stat_date,
metric,
game_name,
platform,
sum(value)
from star.s_game_day a, lookups.l_flurry_game g
where a.game_id=g.game_id
group by 1,2,3,4
order by 1,2,3,4
;
