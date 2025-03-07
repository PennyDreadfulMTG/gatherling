CREATE TRIGGER prevent_null_deck
BEFORE UPDATE ON entries
FOR EACH ROW
BEGIN
    IF OLD.deck IS NOT NULL AND NEW.deck IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Error: Attempt to set entries.deck to NULL';
    END IF;
END;
