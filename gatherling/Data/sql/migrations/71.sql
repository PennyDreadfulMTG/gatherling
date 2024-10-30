-- One time blitz to close out events that were left hanging.
-- These will now appear in "Current Events" so we don't want 65 old events.
UPDATE events SET finalized = 1 WHERE start < NOW() - INTERVAL 1 YEAR AND active = 0;
