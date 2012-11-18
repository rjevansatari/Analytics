use staging;

# Load The Data

DROP TABLE IF EXISTS staging.stage_game_day;

CREATE TABLE IF NOT EXISTS staging.stage_game_day (
  `game_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `stat_date` date NOT NULL,
  `metric` varchar(32) NOT NULL,
  `value` int(11) NOT NULL,
  `create_datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

truncate table staging.stage_game_day;

LOAD DATA LOCAL INFILE '/home/revans/projects/analytics/csv/flurry_game_day.csv' INTO TABLE staging.stage_game_day
FIELDS TERMINATED BY ','
ENCLOSED BY '\'';

create temporary table staging.d_dates engine=MyIsam
as
select distinct stat_date
from staging.stage_game_day
where metric='ActiveUsersByDay'
;

drop table if exists staging.stage_game_day_bkup;

create table if not exists staging.stage_game_day_bkup
as
select * from star.s_game_day
where stat_date not in ( select stat_date from staging.d_dates )
;

insert into staging.stage_game_day_bkup(game_id, client_id, stat_date, metric, value)
select
game_id,
client_id,
stat_date,
metric,
value
from staging.stage_game_day
;

drop table if exists star.s_game_day_bkup;
rename table star.s_game_day to star.s_game_day_bkup;
rename table staging.stage_game_day_bkup to star.s_game_day;
