-- You can't enter a tournament if you don't exist (10 cases)
DELETE FROM standings WHERE player IS NULL;
-- Enforce that going forward
ALTER TABLE standings MODIFY player VARCHAR(40) NOT NULL;
