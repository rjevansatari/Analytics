select count(*) from star.s_user_day;
select count(*) from star.s_user_event;
select event_name, count(*) from lookups.l_event group by 1;
