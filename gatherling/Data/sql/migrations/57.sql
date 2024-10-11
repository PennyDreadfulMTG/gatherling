-- You can't manage a series that doesn't exist
DELETE FROM series_organizers WHERE series NOT IN (SELECT name FROM series);
-- Now let's enforce that going forward
ALTER TABLE series_organizers ADD FOREIGN KEY (series) REFERENCES series(name);
-- Delete a series that is a typo of a real series (Giovedi Pauper Chanllenges)
-- and another that never had an event (Heirloom Kaleidoscope).
-- This is necessary because the system currently doesn't handle a series without a most recent event
DELETE FROM series WHERE name NOT IN (SELECT series FROM events);
-- There's only one event in all history that has no season so let's make season required
UPDATE events SET season = 0 WHERE season IS NULL;
ALTER TABLE events MODIFY season INT NOT NULL;
