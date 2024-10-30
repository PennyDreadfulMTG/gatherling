ALTER TABLE series MODIFY COLUMN isactive TINYINT(1) NOT NULL;
ALTER TABLE series MODIFY COLUMN day ENUM('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL;
ALTER TABLE series MODIFY COLUMN normalstart TIME NOT NULL;
ALTER TABLE series MODIFY COLUMN prereg_default TINYINT(1) NOT NULL;
