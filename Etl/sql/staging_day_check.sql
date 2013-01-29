use staging;

# Load The Data

DROP TABLE if EXISTS tmp.stage_day;

CREATE TEMPORARY TABLE tmp.stage_day (
 `game_id` int(11) NOT NULL,
 `client_id` int(11) NOT NULL,
 `type` varchar(8) NOT NULL,
 `device_id` varchar(80) NOT NULL,
 `version` varchar(16) NOT NULL,
 `device_gen` varchar(80) NOT NULL,
 `log_ts` timestamp NULL DEFAULT NULL,
 `category` varchar(255) NOT NULL,
 `parm` varchar(255) NOT NULL,
 `value` varchar(80) NOT NULL
) ENGINE=MyISAM DEFAULT 
;

LOAD DATA LOCAL INFILE '/home/revans/projects/analytics/csv/staging_day.csv' INTO TABLE tmp.stage_day 
FIELDS TERMINATED BY ','
ENCLOSED BY '\'';

SHOW WARNINGS;
