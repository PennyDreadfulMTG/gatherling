-- Tighten up the cardset table. These things are already true.
ALTER TABLE cardsets MODIFY type ENUM('Core','Block','Extra') NOT NULL;
ALTER TABLE cardsets MODIFY standard_legal TINYINT(1) NOT NULL;
ALTER TABLE cardsets MODIFY modern_legal TINYINT(1) NOT NULL;
-- Tighten up the cards table.
ALTER TABLE cards MODIFY rarity VARCHAR(40) NOT NULL;
