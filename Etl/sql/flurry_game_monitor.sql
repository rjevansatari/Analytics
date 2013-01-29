use staging;

# Load The Data

CREATE TABLE IF NOT EXISTS staging.stage_game_day_monitor (
  `game_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `stat_datetime` datetime NOT NULL,
  `metric` varchar(16) NOT NULL,
  `value` int(11) NOT NULL,
  `create_datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT ;

truncate table staging.stage_game_day_monitor;

LOAD DATA LOCAL INFILE '/home/revans/projects/analytics/csv/flurry_game_monitor.csv' INTO TABLE staging.stage_game_day_monitor
FIELDS TERMINATED BY ','
ENCLOSED BY '\'';

# Put into the monitor table
insert into star.s_game_day_monitor
select * from staging.stage_game_day_monitor
;
