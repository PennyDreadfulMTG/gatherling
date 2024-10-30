-- Deal with a character encoding issue. Fix up an FK to work how we want.
ALTER TABLE ratings DROP FOREIGN KEY ratings_ibfk_1;
ALTER TABLE ratings ADD CONSTRAINT ratings_ibfk_1 FOREIGN KEY (event) REFERENCES events (name) ON DELETE CASCADE ON UPDATE CASCADE;
UPDATE events SET name = 'Giovedì Pauper Challenge' WHERE name = 'GiovedÃ¬ Pauper Challenge';
