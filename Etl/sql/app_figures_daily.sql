use staging;

# Load The Data

DROP TABLE if EXISTS staging.app_figures_load;

CREATE TABLE staging.app_figures_load (
  `stat_date` date NOT NULL,
  `game_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `downloads` int(11) NOT NULL,
  `updates` int(11) NOT NULL,
  `revenue` float NOT NULL
) ENGINE=MyISAM ;

LOAD DATA LOCAL INFILE '/home/revans/projects/analytics/csv/app_figures_day.csv' INTO TABLE staging.app_figures_load
FIELDS TERMINATED BY ','
ENCLOSED BY '\''
(stat_date,game_id,client_id,product_id,downloads,updates,revenue);


DROP TABLE IF EXISTS staging.app_figures;

CREATE TABLE staging.app_figures
as
SELECT * from star.app_figures
where stat_date not in ( select distinct stat_date from staging.app_figures_load );
;

INSERT INTO staging.app_figures
SELECT * from staging.app_figures_load;

drop table if exists star.app_figures_bkup;
rename table star.app_figures to star.app_figures_bkup;
rename table staging.app_figures to star.app_figures;
