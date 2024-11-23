-- We must explicitly set this (which we did not at creation time) because server default may differ.
-- Our other tables have this explicitly set in schema.sql.
ALTER TABLE sessions CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
