-- Start to remove support for banning card by id (printing), only banning by name (card) is supported now
-- We've never used the ability to ban by id and it doesn't really make sense
-- This also speeds up adding legal cards a lot
ALTER TABLE bans ADD CONSTRAINT unique_card_format UNIQUE (card_name, format);
ALTER TABLE restricted ADD CONSTRAINT unique_card_format UNIQUE (card_name, format);
ALTER TABLE restrictedtotribe ADD CONSTRAINT unique_card_format UNIQUE (card_name, format);
