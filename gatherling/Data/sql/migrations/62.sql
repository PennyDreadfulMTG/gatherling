-- Fix a discrepancy in season_points and players
UPDATE season_points SET player = 'mikeyk159' WHERE player = 'Mikey k159';
-- Now make sure that can't happen again
ALTER TABLE season_points ADD CONSTRAINT fk_players_name FOREIGN KEY (player) REFERENCES players(name) ON UPDATE CASCADE ON DELETE CASCADE;
