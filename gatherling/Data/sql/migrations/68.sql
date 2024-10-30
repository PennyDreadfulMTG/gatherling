-- More cleanup and adding foreign keys that should exist
DELETE FROM series_seasons WHERE series = 'Giovedi Pauper Chanllenges';
ALTER TABLE series_seasons ADD FOREIGN KEY (series) REFERENCES series (name);
DELETE FROM ratings WHERE event = 'Stand-alone events 1.01';
ALTER TABLE ratings ADD FOREIGN KEY (event) REFERENCES events (name);
ALTER TABLE ratings ADD FOREIGN KEY (player) REFERENCES players (name);
UPDATE series_organizers SET player = 'mikeyk159' WHERE player = 'Mikey k159';
ALTER TABLE series_organizers ADD FOREIGN KEY (player) REFERENCES players (name);
