-- Update the 76 events where private is not set …
UPDATE events SET private = 0 WHERE private IS NULL;
-- … and now make it not null.
ALTER TABLE events ALTER COLUMN private SET NOT NULL;
