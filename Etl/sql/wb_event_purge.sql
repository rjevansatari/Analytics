# Clean up
use staging;

#DROP TABLE IF EXISTS staging.s_user_event;

#CREATE TABLE staging.s_user_event engine=myisam
#as
#select *
#from star.s_user_event
#where game_id not in (11,13)
;

SELECT game_id, count(*)
from staging.s_user_event
GROUP by 1
ORDER by 2 DESC
;

