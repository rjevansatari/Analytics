use staging;

DROP TABLE if EXISTS staging.l_fx_rate_load;

CREATE TABLE IF NOT EXISTS staging.l_fx_rate_load (
  `fx_date` date NOT NULL,
  `currency_code` char(3) COLLATE utf8_unicode_ci NOT NULL,
  `fx_rate` decimal(10,3) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOAD DATA LOCAL INFILE '/home/revans/projects/analytics/csv/fx_rates.csv'
INTO TABLE staging.l_fx_rate_load
FIELDS TERMINATED BY ',' 
LINES TERMINATED by '\n' 
(fx_date,currency_code,fx_rate)
;

DROP TABLE IF EXISTS staging.l_fx_rate;

CREATE TABLE staging.l_fx_rate
as
SELECT * from lookups.l_fx_rate
where fx_date not in ( select distinct fx_date from staging.l_fx_rate_load )
GROUP BY 1,2,3
;

INSERT INTO staging.l_fx_rate
SELECT * from staging.l_fx_rate_load
GROUP BY 1,2,3
;

create index fx_date on staging.l_fx_rate(fx_date);
drop table if exists lookups.l_fx_rate_bkup;
rename table lookups.l_fx_rate to lookups.l_fx_rate_bkup;
rename table staging.l_fx_rate to lookups.l_fx_rate;
