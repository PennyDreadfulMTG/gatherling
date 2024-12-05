-- It doesn't really make sense for a deck to exist without an event, yet there are 2500 such decks
-- The details are here should we decide we need them: https://gist.githubusercontent.com/bakert/b1eb9cecd606cbf541732310d48d11d9/raw/914917e5c0e0cd9e4d22598a301bf5965356c173/gistfile1.txt
DELETE FROM deckcontents WHERE deck IN (SELECT id FROM decks WHERE id NOT IN (SELECT deck FROM entries WHERE deck IS NOT NULL));
DELETE FROM decks WHERE id NOT IN (SELECT deck FROM entries WHERE deck IS NOT NULL);
