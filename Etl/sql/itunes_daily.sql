use staging;

# Load The Data

DROP TABLE if EXISTS staging.itunes_sales_load;

CREATE TABLE IF NOT EXISTS staging.itunes_sales_load (
  `provider` varchar(255) CHARACTER SET utf8 COLLATE utf8_swedish_ci NOT NULL,
  `provider_country` varchar(255) CHARACTER SET utf8 COLLATE utf8_swedish_ci NOT NULL,
  `sku` varchar(255) CHARACTER SET utf8 COLLATE utf8_swedish_ci NOT NULL,
  `developer` varchar(255) CHARACTER SET utf8 COLLATE utf8_swedish_ci NOT NULL,
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_swedish_ci NOT NULL,
  `version` varchar(255) CHARACTER SET utf8 COLLATE utf8_swedish_ci NOT NULL,
  `product_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_swedish_ci NOT NULL,
  `units` int(11) NOT NULL,
  `net_revenue` float NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `customer_currency` varchar(255) CHARACTER SET utf8 COLLATE utf8_swedish_ci NOT NULL,
  `country_code` varchar(255) CHARACTER SET utf8 COLLATE utf8_swedish_ci NOT NULL,
  `currency_of_proceeds` varchar(255) CHARACTER SET utf8 COLLATE utf8_swedish_ci NOT NULL,
  `apple_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_swedish_ci NOT NULL,
  `price` float NOT NULL,
  `promo_code` varchar(255) CHARACTER SET utf8 COLLATE utf8_swedish_ci DEFAULT NULL,
  `parent_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_swedish_ci DEFAULT NULL,
  `subscription` varchar(255) CHARACTER SET utf8 COLLATE utf8_swedish_ci DEFAULT NULL,
  `period` varchar(255) CHARACTER SET utf8 COLLATE utf8_swedish_ci DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOAD DATA LOCAL INFILE '/home/revans/projects/analytics/csv/itunes/itunes_daily_sales.txt'
INTO TABLE staging.itunes_sales_load
FIELDS TERMINATED BY '\t' 
LINES TERMINATED by '\n' 
IGNORE 1 LINES 
(provider,provider_country,sku,developer,title,version,product_id,units,net_revenue,@start_date,@end_date,customer_currency,country_code,currency_of_proceeds,apple_id,price,promo_code,parent_id,subscription,period) 
SET start_date=str_to_date(@start_date, '%m/%d/%Y'),
end_date=str_to_date(@end_date, '%m/%d/%Y')
;

CREATE INDEX start_date on staging.itunes_sales_load(start_date);

DROP TABLE IF EXISTS staging.itunes_sales;

CREATE TABLE staging.itunes_sales
as
SELECT * from star.itunes_sales
where start_date not in ( select distinct start_date from staging.itunes_sales_load )
GROUP BY 1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20
;

INSERT INTO staging.itunes_sales
SELECT * from staging.itunes_sales_load
GROUP BY 1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20
;

drop table if exists star.itunes_sales_bkup;
rename table star.itunes_sales to star.itunes_sales_bkup;
rename table staging.itunes_sales to star.itunes_sales;
CREATE INDEX start_date on star.itunes_sales(start_date);
