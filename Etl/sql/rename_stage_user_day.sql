drop table if exists star.s_user_day_bkup;
alter table star.s_user_day rename star.s_user_day_bkup;
alter table staging.stage_user_day rename star.s_user_day;
