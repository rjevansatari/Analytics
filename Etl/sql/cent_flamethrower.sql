select stat_date, count(distinct user_id), count(*)
from tmp.centipede_events_20121211
where (value like ('Flame%') or
     value like ('Lanzallamas%') or
     value like ('Flammen-werfer%') or
     value like ('Lance-flammes%') or
     value like ('Lanciafiamme%'))
group by 1
order by 1
;
