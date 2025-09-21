-- Database update for Google OAuth integration
-- Add new columns to tbl_customer table for Google OAuth support

ALTER TABLE `tbl_customer` 
ADD COLUMN `google_id` VARCHAR(255) NULL AFTER `pass`,
ADD COLUMN `oauth_provider` ENUM('local', 'google') DEFAULT 'local' AFTER `google_id`,
ADD COLUMN `profile_picture` VARCHAR(500) NULL AFTER `oauth_provider`;

-- Add unique index for google_id
ALTER TABLE `tbl_customer` 
ADD UNIQUE INDEX `unique_google_id` (`google_id`);

-- Add index for email (if not exists)
ALTER TABLE `tbl_customer` 
ADD UNIQUE INDEX `unique_email` (`email`);
