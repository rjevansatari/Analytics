#
#ALTER TABLE star.s_user_day MODIFY game_id SMALLINT NOT NULL;
#ALTER TABLE star.s_user_day MODIFY client_id SMALLINT NOT NULL;
#ALTER TABLE star.s_user_day MODIFY device_gen_id SMALLINT NOT NULL;

#
#ALTER TABLE star.s_user MODIFY game_id SMALLINT NOT NULL;
#ALTER TABLE star.s_user MODIFY client_id SMALLINT NOT NULL;

#
ALTER TABLE star.s_device_master MODIFY device_gen_id SMALLINT NOT NULL;

#
ALTER TABLE lookups.l_event MODIFY event_id SMALLINT NOT NULL;

#
ALTER TABLE lookups.l_device_gen MODIFY device_gen_id SMALLINT NOT NULL;

#
#ALTER TABLE star.s_user_event 
#MODIFY game_id SMALLINT NOT NULL,
#MODIFY client_id SMALLINT NOT NULL,
#MODIFY device_gen_id SMALLINT NOT NULL,
#MODIFY event_id SMALLINT NOT NULL,
#MODIFY parm varchar(32) NULL,
#MODIFY value varchar(32) NULL;

#
ALTER TABLE lookups.l_flurry_game 
#MODIFY game_id SMALLINT NOT NULL,
MODIFY device_id SMALLINT NOT NULL;
