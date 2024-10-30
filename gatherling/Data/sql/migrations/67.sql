-- Make more things that should be foreign keys into foreign keys
ALTER TABLE season_points ADD FOREIGN KEY (series) REFERENCES series (name);
ALTER TABLE playerbans ADD FOREIGN KEY (series) REFERENCES series (name);
ALTER TABLE playerbans ADD FOREIGN KEY (player) REFERENCES players (name);
ALTER TABLE decks ADD FOREIGN KEY (archetype) REFERENCES archetypes (name);
