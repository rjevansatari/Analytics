use staging;

# Load The Data

DROP TABLE IF EXISTS staging.stage_game_day;

CREATE TABLE IF NOT EXISTS staging.stage_game_day (
  `game_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `stat_date` date NOT NULL,
  `metric` varchar(16) NOT NULL,
  `value` int(11) NOT NULL,
  `create_datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT ;

truncate table staging.stage_game_day;

LOAD DATA LOCAL INFILE '/home/revans/projects/analytics/csv/flurry_game_day.csv' INTO TABLE staging.stage_game_day
FIELDS TERMINATED BY ','
ENCLOSED BY '\'';
