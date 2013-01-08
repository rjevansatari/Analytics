use staging;

DROP TABLE if EXISTS staging.stage_session_day;

CREATE TABLE staging.stage_session_day (
`game_id` smallint NOT NULL,
`client_id` smallint NOT NULL,
`device_id` varchar(80) NOT NULL,
`version` varchar(16) NOT NULL,
`device_gen_id` smallint NOT NULL,
`log_ts` timestamp NOT NULL 
) ENGINE=MyISAM DEFAULT CHARSET=latin1
;


drop table if exists staging.stage_event_day_raw;

CREATE TABLE staging.stage_event_day_raw (
 `game_id` smallint(11) NOT NULL,
 `client_id` smallint(11) NOT NULL,
 `device_id` varchar(80) NOT NULL,
 `version` varchar(16) NOT NULL,
 `device_gen_id` smallint NOT NULL,
 `log_ts` timestamp NOT NULL,
 `event_id` smallint NOT NULL,
 `parm_id` smallint,
 `value` varchar(32)
) ENGINE=MyISAM DEFAULT CHARSET=latin1
;
