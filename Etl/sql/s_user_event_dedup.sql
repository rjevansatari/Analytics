use staging;

select count(*) from star.s_user_event;

drop table if exists staging.stage_user_event;

CREATE TABLE IF NOT EXISTS staging.stage_user_event (
  `game_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `stat_datetime` timestamp NULL DEFAULT NULL,
  `event` varchar(255) NOT NULL,
  `parm` varchar(255) NOT NULL,
  `value` varchar(80) NOT NULL
) ENGINE=MyISAM DEFAULT ;

truncate table staging.stage_user_event;

insert into staging.stage_user_event(game_id, client_id, user_id, stat_datetime, event, parm, value)
select distinct
a.game_id,
a.client_id,
a.user_id, 
a.stat_datetime, 
a.event,
a.parm,
a.value
from star.s_user_event a
where date(stat_datetime) >= date_sub(CURDATE(),60)
;

select count(*) from staging.stage_user_event;

create index user on staging.stage_user_event(user_id, game_id, client_id);
