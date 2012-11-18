select game_id, length(parm), count(*)
from star.s_user_event
group by 1,2
order by 1,2
;

select game_id, length(value), count(*)
from star.s_user_event
group by 1,2
order by 1,2
;
