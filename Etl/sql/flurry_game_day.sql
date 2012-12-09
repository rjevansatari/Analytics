use staging;

# Load The Data

DROP TABLE IF EXISTS staging.stage_game_day_raw;

CREATE TABLE IF NOT EXISTS staging.stage_game_day_raw (
  `game_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `stat_date` date NOT NULL,
  `metric` varchar(32) NOT NULL,
  `value` int(11) NOT NULL,
  `create_datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOAD DATA LOCAL INFILE '/home/revans/projects/analytics/csv/flurry_game_day.csv' INTO TABLE staging.stage_game_day_raw
FIELDS TERMINATED BY ','
ENCLOSED BY '\'';


DROP TABLE IF EXISTS staging.stage_game_day;

CREATE TABLE IF NOT EXISTS staging.stage_game_day
as
select game_id,
client_id,
stat_date,
metric,
sum(value) as value,
max(create_datetime) as create_datetime
from staging.stage_game_day_raw
group by 1,2,3,4
;

create temporary table staging.d_dates engine=MyIsam
as
select game_id, stat_date
from staging.stage_game_day
where metric='ActiveUsersByDay'
and stat_date between @start_date and @end_date
group by 1,2
;

drop table if exists staging.stage_game_day_bkup;

create table if not exists staging.stage_game_day_bkup
as
select * from star.s_game_day
where (game_id, stat_date) not in (select game_id, stat_date from staging.d_dates) 
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
