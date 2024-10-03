-- Matches are between two players, we model byes another way
-- Get rid of the seven anomalies (all unverified 0-0 paired matches)
DELETE FROM matches WHERE playerb IS NULL;
-- Enforce that going forward, matches are between two players
ALTER TABLE matches MODIFY playerb VARCHAR(40) NOT NULL;
