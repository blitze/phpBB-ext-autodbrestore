--
-- phpBB Backup Script
-- Dump of tables for phpbb_
-- DATE : 18-10-2017 15:44:17 GMT
--
BEGIN TRANSACTION;
-- Table: phpbb_smilies
DROP TABLE phpbb_smilies;
DROP SEQUENCE IF EXISTS phpbb_smilies_seq;
CREATE SEQUENCE phpbb_smilies_seq;
CREATE TABLE phpbb_smilies(
  smiley_id int4 DEFAULT nextval('phpbb_smilies_seq'::regclass) NOT NULL, 
  code varchar(50) DEFAULT ''::character varying NOT NULL, 
  emotion varchar(255) DEFAULT ''::character varying NOT NULL, 
  smiley_url varchar(50) DEFAULT ''::character varying NOT NULL, 
  smiley_width int2 DEFAULT '0'::smallint NOT NULL, 
  smiley_height int2 DEFAULT '0'::smallint NOT NULL, 
  smiley_order int4 DEFAULT 0 NOT NULL, 
  display_on_posting int2 DEFAULT '1'::smallint NOT NULL, 
  CONSTRAINT phpbb_smilies_pkey PRIMARY KEY (smiley_id), 
  CONSTRAINT phpbb_smilies_display_on_posting_check CHECK (display_on_posting >= 0), 
  CONSTRAINT phpbb_smilies_smiley_order_check CHECK (smiley_order >= 0), 
  CONSTRAINT phpbb_smilies_smiley_height_check CHECK (smiley_height >= 0), 
  CONSTRAINT phpbb_smilies_smiley_width_check CHECK (smiley_width >= 0), 
  CONSTRAINT phpbb_smilies_smiley_id_check CHECK (smiley_id >= 0)
);
CREATE INDEX phpbb_smilies_display_on_post ON phpbb_smilies (display_on_posting);

COPY phpbb_smilies (smiley_id, code, emotion, smiley_url, smiley_width, smiley_height, smiley_order, display_on_posting) FROM stdin;
1	:D	Very Happy	icon_e_biggrin.gif	15	17	1	1
\.
SELECT SETVAL('phpbb_smilies_seq',(select case when max(smiley_id)>0 then max(smiley_id)+1 else 1 end FROM phpbb_smilies));
CREATE DOMAIN varchar_ci as character varying(255) NOT NULL DEFAULT ''::character varying;
COMMIT;
