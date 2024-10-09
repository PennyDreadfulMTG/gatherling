-- Events should always have a host. Update the 18 exceptions to have me as a host.
UPDATE events SET host = 'bakert99' WHERE host IS NULL;
-- And then insist on a host going forward.
ALTER TABLE events MODIFY host VARCHAR(40) NOT NULL;
-- While we're here let's disallow a NULL number; there are no existing events with a NULL number
ALTER TABLE events MODIFY number TINYINT UNSIGNED NOT NULL;
-- Same for series.
ALTER TABLE events MODIFY series VARCHAR(40) NOT NULL;
