--
-- phpBB Backup Script
-- Dump of tables for phpbb_
-- DATE : 18-10-2017 15:41:26 GMT
--
BEGIN TRANSACTION;
-- Table: phpbb_smilies
DROP TABLE phpbb_smilies;
CREATE TABLE phpbb_smilies (
	 smiley_id  INTEGER PRIMARY KEY AUTOINCREMENT,
	 code  VARCHAR(50) NOT NULL DEFAULT '',
	 emotion  VARCHAR(255) NOT NULL DEFAULT '',
	 smiley_url  VARCHAR(50) NOT NULL DEFAULT '',
	 smiley_width  INTEGER UNSIGNED NOT NULL DEFAULT '0',
	 smiley_height  INTEGER UNSIGNED NOT NULL DEFAULT '0',
	 smiley_order  INTEGER UNSIGNED NOT NULL DEFAULT '0',
	 display_on_posting  INTEGER UNSIGNED NOT NULL DEFAULT '1'
);
CREATE INDEX phpbb_smilies_display_on_post ON phpbb_smilies (display_on_posting);

INSERT INTO phpbb_smilies (smiley_id, code, emotion, smiley_url, smiley_width, smiley_height, smiley_order, display_on_posting) VALUES (1, ':D', 'Very Happy', 'icon_e_biggrin.gif', 15, 17, 1, 1);
COMMIT;
