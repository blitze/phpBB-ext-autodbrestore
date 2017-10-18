DROP TABLE IF EXISTS phpbb_styles;
CREATE TABLE phpbb_styles (
  style_id INT4 NOT NULL PRIMARY KEY,
  style_name varchar(255) NOT NULL DEFAULT '' UNIQUE
);

INSERT INTO phpbb_styles (style_id, style_name) VALUES (1, 'prosilver');
