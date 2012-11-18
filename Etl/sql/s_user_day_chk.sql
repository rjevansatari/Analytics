select name, client_id, max(stat_date)
from star.s_user_day a, lookups.l_flurry_game g
where a.game_id=g.game_id
group by 1,2
order by 1,2
