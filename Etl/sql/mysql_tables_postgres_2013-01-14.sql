-- MySQL dump 10.13  Distrib 5.5.28, for Linux (x86_64)
--
-- Host: localhost    Database: 
-- ------------------------------------------------------
-- Server version	5.5.28
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO,POSTGRESQL' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: "games"
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ "games" /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci */;

USE "games";

--
-- Table structure for table "g_warbands_economy_lookup"
--

DROP TABLE IF EXISTS "g_warbands_economy_lookup";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "g_warbands_economy_lookup" (
  "id" smallint(6) DEFAULT NULL,
  "class" varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  "name" varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  "gold" int(11) DEFAULT NULL,
  "diamonds" int(11) DEFAULT NULL,
  "price" float DEFAULT NULL,
  "start_date" date DEFAULT NULL,
  "end_date" date DEFAULT NULL,
  UNIQUE KEY "id" ("id","class")
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Current Database: "lookups"
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ "lookups" /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci */;

USE "lookups";

--
-- Table structure for table "d_date"
--

DROP TABLE IF EXISTS "d_date";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "d_date" (
  "date" date NOT NULL,
  KEY "date" ("date")
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table "l_country_code"
--

DROP TABLE IF EXISTS "l_country_code";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "l_country_code" (
  "country" varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  "country_code" char(2) COLLATE utf8_unicode_ci NOT NULL
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table "l_device_gen"
--

DROP TABLE IF EXISTS "l_device_gen";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "l_device_gen" (
  "device_gen_id" smallint(6) NOT NULL,
  "device_gen" varchar(80) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY ("device_gen_id"),
  UNIQUE KEY "device_gen_id" ("device_gen_id"),
  KEY "device_gen" ("device_gen"(18))
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table "l_device_gen_bkup"
--

DROP TABLE IF EXISTS "l_device_gen_bkup";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "l_device_gen_bkup" (
  "device_gen_id" smallint(6) NOT NULL,
  "device_gen" varchar(80) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY ("device_gen_id")
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table "l_event"
--

DROP TABLE IF EXISTS "l_event";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "l_event" (
  "event_id" smallint(6) NOT NULL,
  "event_name" varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY ("event_id"),
  UNIQUE KEY "event_name_2" ("event_name"),
  KEY "event_name" ("event_name"(25))
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table "l_flurry_game"
--

DROP TABLE IF EXISTS "l_flurry_game";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "l_flurry_game" (
  "version" varchar(8) COLLATE utf8_unicode_ci NOT NULL,
  "game_name" varchar(80) COLLATE utf8_unicode_ci NOT NULL,
  "platform" varchar(80) COLLATE utf8_unicode_ci NOT NULL,
  "created_date" date NOT NULL,
  "apicode" varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  "apikey" varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  "game_id" int(11) NOT NULL,
  "device_id" smallint(6) NOT NULL,
  "apple_id" int(11) NOT NULL,
  "raw_extract" smallint(6) NOT NULL DEFAULT '0',
  "is_live" tinyint(1) NOT NULL,
  "ref_name" varchar(16) COLLATE utf8_unicode_ci NOT NULL
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table "l_fx_rate"
--

DROP TABLE IF EXISTS "l_fx_rate";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "l_fx_rate" (
  "fx_date" date NOT NULL,
  "currency_code" char(3) COLLATE utf8_unicode_ci NOT NULL,
  "fx_rate" decimal(10,3) NOT NULL
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table "l_game"
--

DROP TABLE IF EXISTS "l_game";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "l_game" (
  "client_id" smallint(6) NOT NULL,
  "game_id" int(11) NOT NULL,
  "game_name" varchar(80) COLLATE utf8_unicode_ci NOT NULL,
  "is_live" tinyint(4) NOT NULL DEFAULT '1',
  "ref_name" varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  "launch_date" binary(0) DEFAULT NULL
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table "l_parm"
--

DROP TABLE IF EXISTS "l_parm";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "l_parm" (
  "parm_id" smallint(6) NOT NULL,
  "parm_name" varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY ("parm_id"),
  UNIQUE KEY "parm_name_2" ("parm_name"),
  KEY "parm_name" ("parm_name")
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Current Database: "mysql"
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ "mysql" /*!40100 DEFAULT CHARACTER SET latin1 */;

USE "mysql";

--
-- Table structure for table "general_log"
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS "general_log" (
  "event_time" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "user_host" mediumtext NOT NULL,
  "thread_id" int(11) NOT NULL,
  "server_id" int(10) unsigned NOT NULL,
  "command_type" varchar(64) NOT NULL,
  "argument" mediumtext NOT NULL
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table "slow_log"
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS "slow_log" (
  "start_time" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "user_host" mediumtext NOT NULL,
  "query_time" time NOT NULL,
  "lock_time" time NOT NULL,
  "rows_sent" int(11) NOT NULL,
  "rows_examined" int(11) NOT NULL,
  "db" varchar(512) NOT NULL,
  "last_insert_id" int(11) NOT NULL,
  "insert_id" int(11) NOT NULL,
  "server_id" int(10) unsigned NOT NULL,
  "sql_text" mediumtext NOT NULL
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table "columns_priv"
--

DROP TABLE IF EXISTS "columns_priv";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "columns_priv" (
  "Host" char(60) COLLATE utf8_bin NOT NULL DEFAULT '',
  "Db" char(64) COLLATE utf8_bin NOT NULL DEFAULT '',
  "User" char(16) COLLATE utf8_bin NOT NULL DEFAULT '',
  "Table_name" char(64) COLLATE utf8_bin NOT NULL DEFAULT '',
  "Column_name" char(64) COLLATE utf8_bin NOT NULL DEFAULT '',
  "Timestamp" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "Column_priv" set('Select','Insert','Update','References') CHARACTER SET utf8 NOT NULL DEFAULT '',
  PRIMARY KEY ("Host","Db","User","Table_name","Column_name")
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table "db"
--

DROP TABLE IF EXISTS "db";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "db" (
  "Host" char(60) COLLATE utf8_bin NOT NULL DEFAULT '',
  "Db" char(64) COLLATE utf8_bin NOT NULL DEFAULT '',
  "User" char(16) COLLATE utf8_bin NOT NULL DEFAULT '',
  "Select_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "Insert_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "Update_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "Delete_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "Create_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "Drop_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "Grant_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "References_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "Index_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "Alter_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "Create_tmp_table_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "Lock_tables_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "Create_view_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "Show_view_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "Create_routine_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "Alter_routine_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "Execute_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "Event_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "Trigger_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  PRIMARY KEY ("Host","Db","User"),
  KEY "User" ("User")
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table "event"
--

DROP TABLE IF EXISTS "event";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "event" (
  "db" char(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  "name" char(64) NOT NULL DEFAULT '',
  "body" longblob NOT NULL,
  "definer" char(77) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  "execute_at" datetime DEFAULT NULL,
  "interval_value" int(11) DEFAULT NULL,
  "interval_field" enum('YEAR','QUARTER','MONTH','DAY','HOUR','MINUTE','WEEK','SECOND','MICROSECOND','YEAR_MONTH','DAY_HOUR','DAY_MINUTE','DAY_SECOND','HOUR_MINUTE','HOUR_SECOND','MINUTE_SECOND','DAY_MICROSECOND','HOUR_MICROSECOND','MINUTE_MICROSECOND','SECOND_MICROSECOND') DEFAULT NULL,
  "created" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "modified" timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  "last_executed" datetime DEFAULT NULL,
  "starts" datetime DEFAULT NULL,
  "ends" datetime DEFAULT NULL,
  "status" enum('ENABLED','DISABLED','SLAVESIDE_DISABLED') NOT NULL DEFAULT 'ENABLED',
  "on_completion" enum('DROP','PRESERVE') NOT NULL DEFAULT 'DROP',
  "sql_mode" set('REAL_AS_FLOAT','PIPES_AS_CONCAT','ANSI_QUOTES','IGNORE_SPACE','NOT_USED','ONLY_FULL_GROUP_BY','NO_UNSIGNED_SUBTRACTION','NO_DIR_IN_CREATE','POSTGRESQL','ORACLE','MSSQL','DB2','MAXDB','NO_KEY_OPTIONS','NO_TABLE_OPTIONS','NO_FIELD_OPTIONS','MYSQL323','MYSQL40','ANSI','NO_AUTO_VALUE_ON_ZERO','NO_BACKSLASH_ESCAPES','STRICT_TRANS_TABLES','STRICT_ALL_TABLES','NO_ZERO_IN_DATE','NO_ZERO_DATE','INVALID_DATES','ERROR_FOR_DIVISION_BY_ZERO','TRADITIONAL','NO_AUTO_CREATE_USER','HIGH_NOT_PRECEDENCE','NO_ENGINE_SUBSTITUTION','PAD_CHAR_TO_FULL_LENGTH') NOT NULL DEFAULT '',
  "comment" char(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  "originator" int(10) unsigned NOT NULL,
  "time_zone" char(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  "character_set_client" char(32) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  "collation_connection" char(32) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  "db_collation" char(32) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  "body_utf8" longblob,
  PRIMARY KEY ("db","name")
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table "func"
--

DROP TABLE IF EXISTS "func";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "func" (
  "name" char(64) COLLATE utf8_bin NOT NULL DEFAULT '',
  "ret" tinyint(1) NOT NULL DEFAULT '0',
  "dl" char(128) COLLATE utf8_bin NOT NULL DEFAULT '',
  "type" enum('function','aggregate') CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY ("name")
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table "help_category"
--

DROP TABLE IF EXISTS "help_category";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "help_category" (
  "help_category_id" smallint(5) unsigned NOT NULL,
  "name" char(64) NOT NULL,
  "parent_category_id" smallint(5) unsigned DEFAULT NULL,
  "url" char(128) NOT NULL,
  PRIMARY KEY ("help_category_id"),
  UNIQUE KEY "name" ("name")
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table "help_keyword"
--

DROP TABLE IF EXISTS "help_keyword";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "help_keyword" (
  "help_keyword_id" int(10) unsigned NOT NULL,
  "name" char(64) NOT NULL,
  PRIMARY KEY ("help_keyword_id"),
  UNIQUE KEY "name" ("name")
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table "help_relation"
--

DROP TABLE IF EXISTS "help_relation";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "help_relation" (
  "help_topic_id" int(10) unsigned NOT NULL,
  "help_keyword_id" int(10) unsigned NOT NULL,
  PRIMARY KEY ("help_keyword_id","help_topic_id")
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table "help_topic"
--

DROP TABLE IF EXISTS "help_topic";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "help_topic" (
  "help_topic_id" int(10) unsigned NOT NULL,
  "name" char(64) NOT NULL,
  "help_category_id" smallint(5) unsigned NOT NULL,
  "description" text NOT NULL,
  "example" text NOT NULL,
  "url" char(128) NOT NULL,
  PRIMARY KEY ("help_topic_id"),
  UNIQUE KEY "name" ("name")
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table "host"
--

DROP TABLE IF EXISTS "host";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "host" (
  "Host" char(60) COLLATE utf8_bin NOT NULL DEFAULT '',
  "Db" char(64) COLLATE utf8_bin NOT NULL DEFAULT '',
  "Select_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "Insert_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "Update_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "Delete_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "Create_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "Drop_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "Grant_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "References_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "Index_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "Alter_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "Create_tmp_table_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "Lock_tables_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "Create_view_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "Show_view_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "Create_routine_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "Alter_routine_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "Execute_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "Trigger_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  PRIMARY KEY ("Host","Db")
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table "ndb_binlog_index"
--

DROP TABLE IF EXISTS "ndb_binlog_index";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "ndb_binlog_index" (
  "Position" bigint(20) unsigned NOT NULL,
  "File" varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  "epoch" bigint(20) unsigned NOT NULL,
  "inserts" bigint(20) unsigned NOT NULL,
  "updates" bigint(20) unsigned NOT NULL,
  "deletes" bigint(20) unsigned NOT NULL,
  "schemaops" bigint(20) unsigned NOT NULL,
  PRIMARY KEY ("epoch")
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table "plugin"
--

DROP TABLE IF EXISTS "plugin";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "plugin" (
  "name" varchar(64) NOT NULL DEFAULT '',
  "dl" varchar(128) NOT NULL DEFAULT '',
  PRIMARY KEY ("name")
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table "proc"
--

DROP TABLE IF EXISTS "proc";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "proc" (
  "db" char(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  "name" char(64) NOT NULL DEFAULT '',
  "type" enum('FUNCTION','PROCEDURE') NOT NULL,
  "specific_name" char(64) NOT NULL DEFAULT '',
  "language" enum('SQL') NOT NULL DEFAULT 'SQL',
  "sql_data_access" enum('CONTAINS_SQL','NO_SQL','READS_SQL_DATA','MODIFIES_SQL_DATA') NOT NULL DEFAULT 'CONTAINS_SQL',
  "is_deterministic" enum('YES','NO') NOT NULL DEFAULT 'NO',
  "security_type" enum('INVOKER','DEFINER') NOT NULL DEFAULT 'DEFINER',
  "param_list" blob NOT NULL,
  "returns" longblob NOT NULL,
  "body" longblob NOT NULL,
  "definer" char(77) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  "created" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "modified" timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  "sql_mode" set('REAL_AS_FLOAT','PIPES_AS_CONCAT','ANSI_QUOTES','IGNORE_SPACE','NOT_USED','ONLY_FULL_GROUP_BY','NO_UNSIGNED_SUBTRACTION','NO_DIR_IN_CREATE','POSTGRESQL','ORACLE','MSSQL','DB2','MAXDB','NO_KEY_OPTIONS','NO_TABLE_OPTIONS','NO_FIELD_OPTIONS','MYSQL323','MYSQL40','ANSI','NO_AUTO_VALUE_ON_ZERO','NO_BACKSLASH_ESCAPES','STRICT_TRANS_TABLES','STRICT_ALL_TABLES','NO_ZERO_IN_DATE','NO_ZERO_DATE','INVALID_DATES','ERROR_FOR_DIVISION_BY_ZERO','TRADITIONAL','NO_AUTO_CREATE_USER','HIGH_NOT_PRECEDENCE','NO_ENGINE_SUBSTITUTION','PAD_CHAR_TO_FULL_LENGTH') NOT NULL DEFAULT '',
  "comment" text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  "character_set_client" char(32) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  "collation_connection" char(32) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  "db_collation" char(32) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  "body_utf8" longblob,
  PRIMARY KEY ("db","name","type")
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table "procs_priv"
--

DROP TABLE IF EXISTS "procs_priv";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "procs_priv" (
  "Host" char(60) COLLATE utf8_bin NOT NULL DEFAULT '',
  "Db" char(64) COLLATE utf8_bin NOT NULL DEFAULT '',
  "User" char(16) COLLATE utf8_bin NOT NULL DEFAULT '',
  "Routine_name" char(64) CHARACTER SET utf8 NOT NULL DEFAULT '',
  "Routine_type" enum('FUNCTION','PROCEDURE') COLLATE utf8_bin NOT NULL,
  "Grantor" char(77) COLLATE utf8_bin NOT NULL DEFAULT '',
  "Proc_priv" set('Execute','Alter Routine','Grant') CHARACTER SET utf8 NOT NULL DEFAULT '',
  "Timestamp" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY ("Host","Db","User","Routine_name","Routine_type"),
  KEY "Grantor" ("Grantor")
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table "proxies_priv"
--

DROP TABLE IF EXISTS "proxies_priv";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "proxies_priv" (
  "Host" char(60) COLLATE utf8_bin NOT NULL DEFAULT '',
  "User" char(16) COLLATE utf8_bin NOT NULL DEFAULT '',
  "Proxied_host" char(60) COLLATE utf8_bin NOT NULL DEFAULT '',
  "Proxied_user" char(16) COLLATE utf8_bin NOT NULL DEFAULT '',
  "With_grant" tinyint(1) NOT NULL DEFAULT '0',
  "Grantor" char(77) COLLATE utf8_bin NOT NULL DEFAULT '',
  "Timestamp" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY ("Host","User","Proxied_host","Proxied_user"),
  KEY "Grantor" ("Grantor")
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table "servers"
--

DROP TABLE IF EXISTS "servers";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "servers" (
  "Server_name" char(64) NOT NULL DEFAULT '',
  "Host" char(64) NOT NULL DEFAULT '',
  "Db" char(64) NOT NULL DEFAULT '',
  "Username" char(64) NOT NULL DEFAULT '',
  "Password" char(64) NOT NULL DEFAULT '',
  "Port" int(4) NOT NULL DEFAULT '0',
  "Socket" char(64) NOT NULL DEFAULT '',
  "Wrapper" char(64) NOT NULL DEFAULT '',
  "Owner" char(64) NOT NULL DEFAULT '',
  PRIMARY KEY ("Server_name")
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table "tables_priv"
--

DROP TABLE IF EXISTS "tables_priv";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "tables_priv" (
  "Host" char(60) COLLATE utf8_bin NOT NULL DEFAULT '',
  "Db" char(64) COLLATE utf8_bin NOT NULL DEFAULT '',
  "User" char(16) COLLATE utf8_bin NOT NULL DEFAULT '',
  "Table_name" char(64) COLLATE utf8_bin NOT NULL DEFAULT '',
  "Grantor" char(77) COLLATE utf8_bin NOT NULL DEFAULT '',
  "Timestamp" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "Table_priv" set('Select','Insert','Update','Delete','Create','Drop','Grant','References','Index','Alter','Create View','Show view','Trigger') CHARACTER SET utf8 NOT NULL DEFAULT '',
  "Column_priv" set('Select','Insert','Update','References') CHARACTER SET utf8 NOT NULL DEFAULT '',
  PRIMARY KEY ("Host","Db","User","Table_name"),
  KEY "Grantor" ("Grantor")
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table "time_zone"
--

DROP TABLE IF EXISTS "time_zone";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "time_zone" (
  "Time_zone_id" int(10) unsigned NOT NULL,
  "Use_leap_seconds" enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY ("Time_zone_id")
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table "time_zone_leap_second"
--

DROP TABLE IF EXISTS "time_zone_leap_second";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "time_zone_leap_second" (
  "Transition_time" bigint(20) NOT NULL,
  "Correction" int(11) NOT NULL,
  PRIMARY KEY ("Transition_time")
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table "time_zone_name"
--

DROP TABLE IF EXISTS "time_zone_name";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "time_zone_name" (
  "Name" char(64) NOT NULL,
  "Time_zone_id" int(10) unsigned NOT NULL,
  PRIMARY KEY ("Name")
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table "time_zone_transition"
--

DROP TABLE IF EXISTS "time_zone_transition";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "time_zone_transition" (
  "Time_zone_id" int(10) unsigned NOT NULL,
  "Transition_time" bigint(20) NOT NULL,
  "Transition_type_id" int(10) unsigned NOT NULL,
  PRIMARY KEY ("Time_zone_id","Transition_time")
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table "time_zone_transition_type"
--

DROP TABLE IF EXISTS "time_zone_transition_type";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "time_zone_transition_type" (
  "Time_zone_id" int(10) unsigned NOT NULL,
  "Transition_type_id" int(10) unsigned NOT NULL,
  "Offset" int(11) NOT NULL DEFAULT '0',
  "Is_DST" tinyint(3) unsigned NOT NULL DEFAULT '0',
  "Abbreviation" char(8) NOT NULL DEFAULT '',
  PRIMARY KEY ("Time_zone_id","Transition_type_id")
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table "user"
--

DROP TABLE IF EXISTS "user";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "user" (
  "Host" char(60) COLLATE utf8_bin NOT NULL DEFAULT '',
  "User" char(16) COLLATE utf8_bin NOT NULL DEFAULT '',
  "Password" char(41) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  "Select_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "Insert_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "Update_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "Delete_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "Create_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "Drop_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "Reload_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "Shutdown_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "Process_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "File_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "Grant_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "References_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "Index_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "Alter_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "Show_db_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "Super_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "Create_tmp_table_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "Lock_tables_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "Execute_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "Repl_slave_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "Repl_client_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "Create_view_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "Show_view_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "Create_routine_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "Alter_routine_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "Create_user_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "Event_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "Trigger_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "Create_tablespace_priv" enum('N','Y') CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  "ssl_type" enum('','ANY','X509','SPECIFIED') CHARACTER SET utf8 NOT NULL DEFAULT '',
  "ssl_cipher" blob NOT NULL,
  "x509_issuer" blob NOT NULL,
  "x509_subject" blob NOT NULL,
  "max_questions" int(11) unsigned NOT NULL DEFAULT '0',
  "max_updates" int(11) unsigned NOT NULL DEFAULT '0',
  "max_connections" int(11) unsigned NOT NULL DEFAULT '0',
  "max_user_connections" int(11) unsigned NOT NULL DEFAULT '0',
  "plugin" char(64) COLLATE utf8_bin DEFAULT '',
  "authentication_string" text COLLATE utf8_bin,
  PRIMARY KEY ("Host","User")
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Current Database: "reporting"
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ "reporting" /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci */;

USE "reporting";

--
-- Table structure for table "navigation"
--

DROP TABLE IF EXISTS "navigation";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "navigation" (
  "id" int(11) NOT NULL,
  "level" int(11) NOT NULL,
  "name" varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  "parent" int(16) NOT NULL,
  "report" varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  "title" varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY ("id")
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table "report_log"
--

DROP TABLE IF EXISTS "report_log";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "report_log" (
  "report_name" varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  "report_startts" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "report_endts" timestamp NULL DEFAULT NULL,
  "report_parms" varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  "report_html" text COLLATE utf8_unicode_ci,
  "report_sql" text COLLATE utf8_unicode_ci,
  "report_csv" varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  "report_code" smallint(6) NOT NULL
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table "report_subscriptions"
--

DROP TABLE IF EXISTS "report_subscriptions";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "report_subscriptions" (
  "subscription_id" int(11) NOT NULL,
  "subscription_name" varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  "subscription_title" varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  "subscription_parms" varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  "subscription_frequency" varchar(11) COLLATE utf8_unicode_ci NOT NULL,
  "subscription_prty" int(11) NOT NULL DEFAULT '99',
  "subscription_run" smallint(6) NOT NULL,
  "subscription_list" text COLLATE utf8_unicode_ci NOT NULL,
  "subscription_code" int(11) NOT NULL,
  "subscription_create_ts" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY ("subscription_id")
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table "report_tree"
--

DROP TABLE IF EXISTS "report_tree";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "report_tree" (
  "id" int(11) NOT NULL,
  "sequence" int(11) NOT NULL,
  "path" varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  "report" varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY ("id")
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Current Database: "staging"
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ "staging" /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci */;

USE "staging";

--
-- Table structure for table "min_ts"
--

DROP TABLE IF EXISTS "min_ts";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "min_ts" (
  "game_id" smallint(11) NOT NULL,
  "client_id" smallint(11) NOT NULL,
  "log_date" date DEFAULT NULL,
  "log_ts" timestamp NULL DEFAULT NULL,
  KEY "game" ("game_id","client_id")
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Current Database: "star"
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ "star" /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci */;

USE "star";

--
-- Table structure for table "app_figures"
--

DROP TABLE IF EXISTS "app_figures";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "app_figures" (
  "stat_date" date NOT NULL,
  "game_id" int(11) NOT NULL,
  "client_id" int(11) NOT NULL,
  "product_id" int(11) NOT NULL,
  "downloads" int(11) NOT NULL,
  "updates" int(11) NOT NULL,
  "revenue" float NOT NULL
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table "app_figures_bkup"
--

DROP TABLE IF EXISTS "app_figures_bkup";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "app_figures_bkup" (
  "stat_date" date NOT NULL,
  "game_id" int(11) NOT NULL,
  "client_id" int(11) NOT NULL,
  "product_id" int(11) NOT NULL,
  "downloads" int(11) NOT NULL,
  "updates" int(11) NOT NULL,
  "revenue" float NOT NULL
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table "itunes_sales"
--

DROP TABLE IF EXISTS "itunes_sales";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "itunes_sales" (
  "provider" varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  "provider_country" varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  "sku" varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  "developer" varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  "title" varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  "version" varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  "product_id" varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  "units" int(11) NOT NULL,
  "net_revenue" float NOT NULL,
  "start_date" date NOT NULL,
  "end_date" date NOT NULL,
  "customer_currency" varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  "country_code" varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  "currency_of_proceeds" varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  "apple_id" varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  "price" float NOT NULL,
  "promo_code" varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  "parent_id" varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  "subscription" varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  "period" varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  KEY "start_date" ("start_date")
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table "itunes_sales_20121230"
--

DROP TABLE IF EXISTS "itunes_sales_20121230";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "itunes_sales_20121230" (
  "provider" varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  "provider_country" varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  "sku" varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  "developer" varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  "title" varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  "version" varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  "product_id" varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  "units" int(11) NOT NULL,
  "net_revenue" float NOT NULL,
  "start_date" date NOT NULL,
  "end_date" date NOT NULL,
  "customer_currency" varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  "country_code" varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  "currency_of_proceeds" varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  "apple_id" varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  "price" float NOT NULL,
  "promo_code" varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  "parent_id" varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  "subscription" varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  "period" varchar(255) COLLATE utf8_unicode_ci NOT NULL
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table "itunes_sales_bkup"
--

DROP TABLE IF EXISTS "itunes_sales_bkup";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "itunes_sales_bkup" (
  "provider" varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  "provider_country" varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  "sku" varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  "developer" varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  "title" varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  "version" varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  "product_id" varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  "units" int(11) NOT NULL,
  "net_revenue" float NOT NULL,
  "start_date" date NOT NULL,
  "end_date" date NOT NULL,
  "customer_currency" varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  "country_code" varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  "currency_of_proceeds" varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  "apple_id" varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  "price" float NOT NULL,
  "promo_code" varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  "parent_id" varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  "subscription" varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  "period" varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  KEY "start_date" ("start_date")
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table "s_device_master"
--

DROP TABLE IF EXISTS "s_device_master";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "s_device_master" (
  "user_id" int(11) NOT NULL,
  "device_id" varchar(80) COLLATE utf8_unicode_ci NOT NULL,
  "device_gen_id" smallint(6) NOT NULL,
  "first_date" date DEFAULT NULL,
  PRIMARY KEY ("user_id"),
  KEY "device_id" ("device_id"(10))
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table "s_game_day"
--

DROP TABLE IF EXISTS "s_game_day";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "s_game_day" (
  "game_id" int(11) NOT NULL,
  "client_id" int(11) NOT NULL,
  "stat_date" date NOT NULL,
  "metric" varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  "value" int(11) NOT NULL,
  "create_datetime" timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table "s_game_day_bkup"
--

DROP TABLE IF EXISTS "s_game_day_bkup";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "s_game_day_bkup" (
  "game_id" int(11) NOT NULL,
  "client_id" int(11) NOT NULL,
  "stat_date" date NOT NULL,
  "metric" varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  "value" int(11) NOT NULL,
  "create_datetime" timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table "s_user"
--

DROP TABLE IF EXISTS "s_user";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "s_user" (
  "game_id" smallint(6) NOT NULL,
  "client_id" smallint(6) NOT NULL,
  "user_id" int(11) NOT NULL,
  "first_date" date,
  "last_date" date,
  KEY "game" ("first_date","game_id","client_id")
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table "s_user_bkup"
--

DROP TABLE IF EXISTS "s_user_bkup";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "s_user_bkup" (
  "game_id" smallint(6) NOT NULL,
  "client_id" smallint(6) NOT NULL,
  "user_id" int(11) NOT NULL,
  "first_date" date,
  "last_date" date,
  KEY "game" ("first_date","game_id","client_id")
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table "s_user_day"
--

DROP TABLE IF EXISTS "s_user_day";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "s_user_day" (
  "game_id" smallint(6) NOT NULL,
  "client_id" smallint(6) NOT NULL,
  "device_gen_id" smallint(6) NOT NULL,
  "user_id" int(11) NOT NULL,
  "version_id" varchar(8) COLLATE utf8_unicode_ci DEFAULT NULL,
  "stat_date" date NOT NULL,
  "stat_time" time NOT NULL,
  "sessions" int(11) NOT NULL,
  KEY "user" ("stat_date","game_id","client_id")
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table "s_user_day_bkup"
--

DROP TABLE IF EXISTS "s_user_day_bkup";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "s_user_day_bkup" (
  "game_id" smallint(6) NOT NULL,
  "client_id" smallint(6) NOT NULL,
  "device_gen_id" smallint(6) NOT NULL,
  "user_id" int(11) NOT NULL,
  "stat_date" date NOT NULL,
  "stat_time" time NOT NULL,
  "sessions" int(11) NOT NULL,
  KEY "user" ("stat_date","game_id","client_id")
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table "s_user_event"
--

DROP TABLE IF EXISTS "s_user_event";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "s_user_event" (
  "game_id" smallint(11) NOT NULL,
  "client_id" smallint(11) NOT NULL,
  "device_gen_id" smallint(80) NOT NULL,
  "user_id" int(16) NOT NULL,
  "stat_date" date NOT NULL,
  "stat_time" time NOT NULL,
  "event_id" smallint(6) NOT NULL,
  "parm_id" smallint(6) DEFAULT NULL,
  "value" varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  KEY "game" ("stat_date","game_id","client_id","event_id")
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table "s_user_event_bkup"
--

DROP TABLE IF EXISTS "s_user_event_bkup";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "s_user_event_bkup" (
  "game_id" smallint(11) NOT NULL,
  "client_id" smallint(11) NOT NULL,
  "device_gen_id" smallint(80) NOT NULL,
  "user_id" int(16) NOT NULL,
  "stat_date" date NOT NULL,
  "stat_time" time NOT NULL,
  "event_id" smallint(6) NOT NULL,
  "parm_id" smallint(6) DEFAULT NULL,
  "value" varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  KEY "game" ("stat_date","game_id","client_id","event_id")
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Current Database: "tmp"
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ "tmp" /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci */;

USE "tmp";

--
-- Table structure for table "a_metrics_daily_stats"
--

DROP TABLE IF EXISTS "a_metrics_daily_stats";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "a_metrics_daily_stats" (
  "stat_date" date NOT NULL,
  "game_id" int(11) NOT NULL,
  "client_id" int(11) NOT NULL,
  "metric" varchar(8) CHARACTER SET latin1 NOT NULL DEFAULT '',
  "value" decimal(32,0) DEFAULT NULL
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table "a_metrics_outlaw_daily_dash"
--

DROP TABLE IF EXISTS "a_metrics_outlaw_daily_dash";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "a_metrics_outlaw_daily_dash" (
  "stat_date" date NOT NULL,
  "game_id" int(11) NOT NULL,
  "client_id" int(11) NOT NULL,
  "metric" varchar(13) CHARACTER SET latin1 NOT NULL DEFAULT '',
  "value" decimal(10,2) DEFAULT NULL
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table "dau_outlaw_daily_dash"
--

DROP TABLE IF EXISTS "dau_outlaw_daily_dash";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "dau_outlaw_daily_dash" (
  "game_id" smallint(6) NOT NULL,
  "client_id" smallint(6) NOT NULL,
  "user_id" int(11) NOT NULL,
  "stat_date" date NOT NULL,
  KEY "user_id" ("user_id")
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table "events_outlaw_daily_dash"
--

DROP TABLE IF EXISTS "events_outlaw_daily_dash";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "events_outlaw_daily_dash" (
  "user_id" int(16) NOT NULL,
  "event_id" smallint(6) NOT NULL,
  "stat_date" date NOT NULL,
  "stat_time" time NOT NULL,
  "parm_id" smallint(6) DEFAULT NULL,
  "value" varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  KEY "user_id" ("user_id")
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table "ftue_outlaw_daily_dash"
--

DROP TABLE IF EXISTS "ftue_outlaw_daily_dash";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "ftue_outlaw_daily_dash" (
  "user_id" int(16) NOT NULL,
  "event_id" smallint(6) NOT NULL,
  "stat_date" date NOT NULL,
  "stat_time" time NOT NULL,
  KEY "user_id" ("user_id","event_id")
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table "fx_rate_outlaw_daily_dash"
--

DROP TABLE IF EXISTS "fx_rate_outlaw_daily_dash";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "fx_rate_outlaw_daily_dash" (
  "currency_code" char(3) COLLATE utf8_unicode_ci NOT NULL,
  "fx_rate" decimal(10,3) NOT NULL
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table "gunpurchase_outlaw_daily_dash"
--

DROP TABLE IF EXISTS "gunpurchase_outlaw_daily_dash";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "gunpurchase_outlaw_daily_dash" (
  "user_id" int(16) NOT NULL,
  "install_date" date,
  "stat_date" date NOT NULL,
  "stat_time" time NOT NULL,
  "event_name" varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  "gun" varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  "location" varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT ''
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table "gunupgrade_outlaw_daily_dash"
--

DROP TABLE IF EXISTS "gunupgrade_outlaw_daily_dash";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "gunupgrade_outlaw_daily_dash" (
  "user_id" int(16) NOT NULL,
  "install_date" date,
  "stat_date" date NOT NULL,
  "stat_time" time NOT NULL,
  "event_name" varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  "gun" varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  "location" varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  "upgrade" varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT ''
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table "installs_outlaw_daily_dash"
--

DROP TABLE IF EXISTS "installs_outlaw_daily_dash";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "installs_outlaw_daily_dash" (
  "user_id" int(11) NOT NULL,
  "install_date" date,
  KEY "user_id" ("user_id")
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table "l_event_outlaw_daily_dash"
--

DROP TABLE IF EXISTS "l_event_outlaw_daily_dash";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "l_event_outlaw_daily_dash" (
  "event_id" smallint(6) NOT NULL DEFAULT '0',
  "event_name" varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  "type" varchar(4) CHARACTER SET latin1 DEFAULT NULL
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table "retention_summary_outlaw_daily_dash"
--

DROP TABLE IF EXISTS "retention_summary_outlaw_daily_dash";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "retention_summary_outlaw_daily_dash" (
  "install_date" date,
  "ddiff" int(7) DEFAULT NULL,
  "cnt" bigint(21) NOT NULL DEFAULT '0'
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table "revenue_outlaw_daily_dash"
--

DROP TABLE IF EXISTS "revenue_outlaw_daily_dash";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "revenue_outlaw_daily_dash" (
  "stat_date" date NOT NULL,
  "game_id" int(2) NOT NULL DEFAULT '0',
  "client_id" int(1) NOT NULL DEFAULT '0',
  "currency_code" varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  "gross_revenue" double DEFAULT NULL,
  KEY "stat_date" ("stat_date","currency_code")
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table "test"
--

DROP TABLE IF EXISTS "test";
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "test" (
  "col1" varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL
);
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2013-01-14 16:30:29
