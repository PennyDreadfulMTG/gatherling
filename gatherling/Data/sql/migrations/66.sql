-- Clean up some seriously funky data so that we can get rid of the format '' (empty string)
DELETE FROM events WHERE name = 'Stand-alone events 1.01';
DELETE FROM events WHERE name = 'Grinder''s Classic League Serra Division';
-- Make things that reference format into propert foreign keys where that is possible without data updates
ALTER TABLE restricted ADD FOREIGN KEY (format) REFERENCES formats (name);
ALTER TABLE bans ADD FOREIGN KEY (format) REFERENCES formats (name);
ALTER TABLE setlegality ADD FOREIGN KEY (format) REFERENCES formats (name);
-- These decks have no cards they are fine to remove
DELETE FROM decks WHERE format = '' AND id NOT IN (SELECT deck FROM deckcontents);
-- Now do the final cleanup
DELETE FROM formats WHERE name = '';
