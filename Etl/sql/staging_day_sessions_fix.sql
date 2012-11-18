drop table if exists star.s_user_bkup;
rename table star.s_user to star.s_user_bkup;
rename table staging.s_user to star.s_user;

