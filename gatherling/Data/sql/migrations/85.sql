-- This column needs to hold semiarbitrary text and is not queried on so make it a TEXT
-- "No more than four of any card is allowed in this format, except basic lands.\nYou entered 4 Engineered Plague in your sideboard\nand 4 Engineered Plague in your mainboard."
ALTER TABLE deckerrors MODIFY COLUMN error TEXT;
