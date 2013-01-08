LOAD DATA LOCAL INFILE '/home/revans/projects/analytics/csv/staging_day_sessions.csv' INTO TABLE staging.stage_session_day 
FIELDS TERMINATED BY ','
ENCLOSED BY '\'';

SHOW WARNINGS;
