# Closet Event to GameSession
drop table if exists tmp.sbb_events;

create table tmp.sbb_events(index(user_id, stat_date, game_id)) engine=myisam
as
select a.stat_date, a.stat_time, a.game_id, a.user_id, a.event_id, a.parm, a.value
from star.s_user_event a
where a.event_id in (374,372)
and a.parm in ('ScreenOnExit','EndHealth','EndCoin')
and a.stat_date > '2012-10-10'
group by 1,2,3,4,5,6,7
; 

drop table if exists tmp.sbb_endgame_detail;

create table tmp.sbb_endgame_detail(index(user_id,stat_date)) engine=myisam
as
select a.game_id, 
a.user_id, 
a.stat_date,
a.stat_time,
a.value as screen,
b.parm, 
b.value, 
abs(time_to_sec(a.stat_time)-time_to_sec(b.stat_time)) as timediff
from tmp.sbb_events a, tmp.sbb_events b 
where a.game_id=b.game_id
and a.user_id=b.user_id
and a.stat_date=b.stat_date
and a.event_id=374 #GameSession
and b.event_id=372 #EndLevel
group by 1,2,3,4,5,6,7
;

drop table if exists tmp.sbb_endgame_event;

create table tmp.sbb_endgame_event(index(user_id, stat_date)) engine=myisam
as
select game_id, user_id, stat_date, parm, value, min(timediff) as timediff
from tmp.sbb_endgame_detail
group by 1,2,3,4,5
;

drop table if exists tmp.sbb_endgame_status;

create table tmp.sbb_endgame_status(index(user_id, stat_date)) engine=myisam
as
select a.game_id, a.user_id, a.stat_date, a.stat_time, a.screen, a.parm, a.value
from tmp.sbb_endgame_detail a, tmp.sbb_endgame_event b
where a.user_id=b.user_id
and a.timediff=b.timediff
and a.stat_date=b.stat_date
and a.game_id=b.game_id
group by 1,2,3,4,5,6,7
;
