-- Run this once on an EXISTING installation that was created before the
-- "status" column was added to the user table.
-- New installs can simply import the updated school.sql instead.

USE school;

-- Add the status column if it doesn't already exist
SET @col_exists = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = 'school'
      AND TABLE_NAME = 'user'
      AND COLUMN_NAME = 'status'
);

SET @sql = IF(
    @col_exists = 0,
    'ALTER TABLE user ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT ''Pending'' AFTER role',
    'SELECT ''status column already exists'' AS info'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Mark all existing users as Approved so they are not locked out.
-- Newly registered students are created with status = 'Pending' and
-- must be approved by an admin who then sets their password to student@123.
UPDATE user SET status = 'Approved' WHERE status = '' OR status IS NULL;