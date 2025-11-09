-- Fix Social Media Colors Database Update
-- This script fixes the VARCHAR size for social media color fields
-- Execute this in phpMyAdmin or MySQL console

USE `bionrgg`;

-- Modify existing columns to have correct size
ALTER TABLE `users_info` MODIFY COLUMN `socialBgColor` varchar(9) DEFAULT '#000000';
ALTER TABLE `users_info` MODIFY COLUMN `socialTextColor` varchar(9) DEFAULT '#ffffff';

-- Also fix color and colorText fields if needed
ALTER TABLE `users_info` MODIFY COLUMN `color` varchar(9) DEFAULT '#c27eef';
ALTER TABLE `users_info` MODIFY COLUMN `colorText` varchar(9) DEFAULT '#ffffff';

-- Verify the changes
DESCRIBE `users_info`;

-- Show all color-related fields
SELECT 
    COLUMN_NAME,
    DATA_TYPE,
    CHARACTER_MAXIMUM_LENGTH,
    IS_NULLABLE,
    COLUMN_DEFAULT
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'bionrgg' 
AND TABLE_NAME = 'users_info' 
AND COLUMN_NAME IN ('color', 'colorText', 'socialBgColor', 'socialTextColor')
ORDER BY COLUMN_NAME; 