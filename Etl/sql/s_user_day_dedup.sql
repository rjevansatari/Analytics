use staging;

select count(*) from star.s_user_day;

drop table if exists staging.stage_user_day_dump;

CREATE TABLE IF NOT EXISTS `stage_user_day_dump` (
  `game_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `stat_date` date NOT NULL,
  `stat_time` time DEFAULT NULL,
  `sessions` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

truncate staging.stage_user_day_dump;

# Load the staging dump file
insert into staging.stage_user_day_dump(game_id, client_id, user_id, stat_date, stat_time, sessions)
select 
game_id, 
client_id, 
user_id,
stat_date,
min(stat_time) as stat_time,
max(0) as sessions
from star.s_user_day
group by 1,2,3,4
;

create index user on staging.stage_user_day_dump(user_id, game_id, client_id, stat_date);
create index user on star.s_user_day(user_id, game_id, client_id, stat_date);

drop table if exists staging.stage_user_day;

CREATE TABLE IF NOT EXISTS staging.stage_user_day (
  `game_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `stat_date` date NOT NULL,
  `stat_time` time DEFAULT NULL,
  `sessions` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

truncate table staging.stage_user_day;

insert into staging.stage_user_day(game_id, client_id, user_id, stat_date, stat_time, sessions)
select a.game_id,
a.client_id,
a.user_id, 
a.stat_date, 
a.stat_time,
max(a.sessions) as sessions
from star.s_user_day a,
staging.stage_user_day_dump b
where a.game_id=b.game_id
and a.client_id=b.client_id
and a.user_id=b.user_id
and a.stat_date=b.stat_date
and a.stat_time=b.stat_time
group by 1,2,3,4,5
order by 3,1,2
;

drop index user on star.s_user_day;
select count(*) from staging.stage_user_day;
