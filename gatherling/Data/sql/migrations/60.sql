-- Events should always have a host. Update the 18 exceptions to have me as a host.
UPDATE events SET host = 'bakert99' WHERE host IS NULL;
-- And then insist on a host going forward.
ALTER TABLE events MODIFY host VARCHAR(40) NOT NULL;
