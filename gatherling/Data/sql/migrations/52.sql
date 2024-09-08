-- This is mostly just here as an example of a db migration, although it is true this column should not be nullable.
ALTER TABLE db_version MODIFY version INT NOT NULL;
