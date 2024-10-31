UPDATE formats SET description = '' WHERE description IS NULL;
ALTER TABLE formats MODIFY description MEDIUMTEXT NOT NULL;
UPDATE formats SET limitless = 0 WHERE limitless IS NULL;
ALTER TABLE formats MODIFY limitless TINYINT(3) NOT NULL;
