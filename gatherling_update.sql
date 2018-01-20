---
--- This script takes the current db on Gatherling.com, and puts it back on the migration path.
--- Once it's been applied, /admin/db-upgrade.php will work as expected.
---

ALTER TABLE `cardsets` ADD COLUMN `code` VARCHAR(7);
ALTER TABLE `deckerrors` ADD COLUMN `id` INT NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`id`);
ALTER TABLE `events` ADD COLUMN `player_editdecks` tinyint(1) NOT NULL DEFAULT '1';
ALTER TABLE `series` ADD COLUMN `mtgo_room` varchar(10) DEFAULT NULL;
ALTER TABLE `standings`
  CHANGE COLUMN `OP_Match` `OP_Match` DECIMAL(4,3) NULL DEFAULT '0.000',
  CHANGE COLUMN `PL_Game` `PL_Game` DECIMAL(4,3) NULL DEFAULT '0.000',
  CHANGE COLUMN `OP_Game` `OP_Game` DECIMAL(4,3) NULL DEFAULT '0.000';

UPDATE db_version SET version = 23;
