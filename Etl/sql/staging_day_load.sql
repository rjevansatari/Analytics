DROP DATABASE staging;

CREATE DATABASE staging DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
use staging;


DROP TABLE if EXISTS staging.stage_day;

CREATE TABLE staging.stage_day (
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
) ENGINE=MyISAM DEFAULT CHARSET=latin1
;

LOAD DATA LOCAL INFILE '/home/revans/projects/analytics/csv/staging_day.csv' INTO TABLE staging.stage_day 
FIELDS TERMINATED BY ','
ENCLOSED BY '\'';

SHOW WARNINGS;

# Get Min Date
SELECT date(log_ts) as Date,
count(*)
from staging.stage_day
group by 1
;
