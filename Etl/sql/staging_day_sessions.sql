use staging;

DROP TABLE if EXISTS staging.load;

CREATE TABLE staging.load (
 `game_id` smallint NOT NULL,
 `client_id` smallint NOT NULL,
 `device_id` varchar(80) NOT NULL,
 `version` varchar(16) NOT NULL,
 `device_gen` varchar(80) NOT NULL,
 `log_ts` timestamp NOT NULL 
) ENGINE=MyISAM DEFAULT CHARSET=latin1
;

LOAD DATA LOCAL INFILE '/home/revans/projects/analytics/csv/staging_day_sessions.csv' INTO TABLE staging.stage_session_day 
FIELDS TERMINATED BY ','
ENCLOSED BY '\'';

select date(log_ts) as Date,
version,
count(*)
from staging.load
group by 1,2
order by 1,2
;
