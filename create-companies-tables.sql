-- SQL запит для створення таблиць для функціоналу компаній/груп
-- Виконайте цей запит в phpMyAdmin

USE `u743896667_bionrgg`;

-- Таблиця компаній
CREATE TABLE IF NOT EXISTS `companies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_key` varchar(20) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `owner_username` varchar(50) NOT NULL,
  `unified_design_enabled` tinyint(1) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `company_key` (`company_key`),
  KEY `idx_owner` (`owner_username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблиця учасників компаній
CREATE TABLE IF NOT EXISTS `company_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `role` enum('owner', 'member') DEFAULT 'member',
  `joined_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_member` (`company_id`, `username`),
  KEY `idx_company_id` (`company_id`),
  KEY `idx_username` (`username`),
  FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Додавання колонки для типу профілю в таблицю users_info
-- Перевірка та додавання колонок (для сумісності з MySQL < 8.0)
SET @dbname = DATABASE();
SET @tablename = 'users_info';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = 'profile_type')
  ) > 0,
  'SELECT 1',
  'ALTER TABLE users_info ADD COLUMN profile_type enum(\'personal\', \'company\') DEFAULT \'personal\''
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = 'company_id')
  ) > 0,
  'SELECT 1',
  'ALTER TABLE users_info ADD COLUMN company_id int(11) DEFAULT NULL'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Додавання індексів (якщо їх ще немає)
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (INDEX_NAME = 'idx_company_id')
  ) > 0,
  'SELECT 1',
  'ALTER TABLE users_info ADD KEY idx_company_id (company_id)'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (INDEX_NAME = 'idx_profile_type')
  ) > 0,
  'SELECT 1',
  'ALTER TABLE users_info ADD KEY idx_profile_type (profile_type)'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Таблиця дизайну компанії (для зберігання фірмового стилю)
CREATE TABLE IF NOT EXISTS `company_designs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `profileColor` varchar(7) DEFAULT '#2572ad',
  `textColor` varchar(7) DEFAULT '#ffffff',
  `textBgColor` varchar(7) DEFAULT '',
  `profileOpacity` int(11) DEFAULT 100,
  `textOpacity` int(11) DEFAULT 100,
  `textBgOpacity` int(11) DEFAULT 100,
  `socialBgColor` varchar(7) DEFAULT '#000000',
  `socialTextColor` varchar(7) DEFAULT '#ffffff',
  `socialOpacity` int(11) DEFAULT 90,
  `avatar` longtext,
  `bg` longtext,
  `blockImage` longtext,
  `socialBgImage` longtext,
  `profileBgType` varchar(10) DEFAULT 'color',
  `socialBgType` varchar(10) DEFAULT 'color',
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `company_id` (`company_id`),
  FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

