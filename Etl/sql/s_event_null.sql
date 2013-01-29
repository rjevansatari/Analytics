CREATE TABLE IF NOT EXISTS star.s_user_event (
  `game_id` smallint(11) NOT NULL,
  `client_id` smallint(11) NOT NULL,
  `device_gen_id` smallint(80) NOT NULL,
  `user_id` int(16) NOT NULL,
  `stat_date` date NOT NULL,
  `stat_time` time NOT NULL,
  `event_id` smallint(6) NOT NULL,
  `parm_id` smallint(6) DEFAULT NULL,
  `value` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

insert into star.s_user_event
select * from staging.s_user_event
;

create index game on star.s_user_event(stat_date, game_id, client_id, event_id);
