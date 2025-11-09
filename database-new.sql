-- Bionrgg Database Schema v2
-- Виконайте цей скрипт у phpMyAdmin або через консоль MySQL для повного розгортання оновленої структури.

CREATE DATABASE IF NOT EXISTS `bionrgg` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `bionrgg`;
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `profile_layouts`;
DROP TABLE IF EXISTS `company_designs`;
DROP TABLE IF EXISTS `company_members`;
DROP TABLE IF EXISTS `users_info`;
DROP TABLE IF EXISTS `companies`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- --------------------------
-- Таблиця користувачів
-- --------------------------
CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_users_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------
-- Таблиця компаній
-- --------------------------
CREATE TABLE `companies` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_key` VARCHAR(20) NOT NULL,
  `company_name` VARCHAR(255) NOT NULL,
  `owner_username` VARCHAR(50) NOT NULL,
  `owner_user_id` INT UNSIGNED DEFAULT NULL,
  `unified_design_enabled` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_company_key` (`company_key`),
  KEY `idx_company_owner_username` (`owner_username`),
  KEY `idx_company_owner_user_id` (`owner_user_id`),
  CONSTRAINT `fk_companies_owner_user` FOREIGN KEY (`owner_user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------
-- Таблиця профілів користувачів
-- --------------------------
CREATE TABLE `users_info` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `username` VARCHAR(50) NOT NULL,
  `descr` TEXT,
  `inst` VARCHAR(255) DEFAULT '',
  `instagram` VARCHAR(255) DEFAULT '',
  `discord` VARCHAR(255) DEFAULT '',
  `fb` VARCHAR(255) DEFAULT '',
  `facebook` VARCHAR(255) DEFAULT '',
  `steam` VARCHAR(255) DEFAULT '',
  `twitch` VARCHAR(255) DEFAULT '',
  `tiktok` VARCHAR(255) DEFAULT '',
  `tg` VARCHAR(255) DEFAULT '',
  `telegram` VARCHAR(255) DEFAULT '',
  `youtube` VARCHAR(255) DEFAULT '',
  `youtubeMusic` VARCHAR(255) DEFAULT '',
  `x` VARCHAR(255) DEFAULT '',
  `linkedin` VARCHAR(255) DEFAULT '',
  `spotify` VARCHAR(255) DEFAULT '',
  `soundcloud` VARCHAR(255) DEFAULT '',
  `github` VARCHAR(255) DEFAULT '',
  `site` VARCHAR(255) DEFAULT '',
  `googleDocs` VARCHAR(255) DEFAULT '',
  `googleSheets` VARCHAR(255) DEFAULT '',
  `fileUpload` VARCHAR(255) DEFAULT '',
  `upwork` VARCHAR(255) DEFAULT '',
  `fiverr` VARCHAR(255) DEFAULT '',
  `djinni` VARCHAR(255) DEFAULT '',
  `reddit` VARCHAR(255) DEFAULT '',
  `whatsapp` VARCHAR(255) DEFAULT '',
  `viber` VARCHAR(255) DEFAULT '',
  `dou` VARCHAR(255) DEFAULT '',
  `olx` VARCHAR(255) DEFAULT '',
  `amazon` VARCHAR(255) DEFAULT '',
  `prom` VARCHAR(255) DEFAULT '',
  `fhunt` VARCHAR(255) DEFAULT '',
  `dj` VARCHAR(255) DEFAULT '',
  `privatBank` VARCHAR(255) DEFAULT '',
  `monoBank` VARCHAR(255) DEFAULT '',
  `alfaBank` VARCHAR(255) DEFAULT '',
  `abank` VARCHAR(255) DEFAULT '',
  `pumbBank` VARCHAR(255) DEFAULT '',
  `raiffeisenBank` VARCHAR(255) DEFAULT '',
  `senseBank` VARCHAR(255) DEFAULT '',
  `binance` VARCHAR(255) DEFAULT '',
  `trustWallet` VARCHAR(255) DEFAULT '',
  `color` VARCHAR(7) DEFAULT '#c27eef',
  `colorText` VARCHAR(7) DEFAULT '#ffffff',
  `textBgColor` VARCHAR(7) DEFAULT '',
  `profileOpacity` INT DEFAULT 100,
  `textOpacity` INT DEFAULT 100,
  `textBgOpacity` INT DEFAULT 100,
  `socialBgColor` VARCHAR(7) DEFAULT '#000000',
  `socialTextColor` VARCHAR(7) DEFAULT '#ffffff',
  `socialOpacity` INT DEFAULT 90,
  `avatar` LONGTEXT,
  `bg` LONGTEXT,
  `blockImage` LONGTEXT,
  `blockImage2` LONGTEXT,
  `socialBgImage` LONGTEXT,
  `profileBgType` VARCHAR(10) DEFAULT 'color',
  `socialBgType` VARCHAR(10) DEFAULT 'color',
  `profile_type` ENUM('personal','company') NOT NULL DEFAULT 'personal',
  `company_id` INT UNSIGNED DEFAULT NULL,
  `views` INT DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_users_info_user_id` (`user_id`),
  UNIQUE KEY `uniq_users_info_username` (`username`),
  KEY `idx_users_info_company_id` (`company_id`),
  KEY `idx_users_info_profile_type` (`profile_type`),
  KEY `idx_users_info_views` (`views`),
  CONSTRAINT `fk_users_info_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_users_info_company` FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------
-- Таблиця учасників компаній
-- --------------------------
CREATE TABLE `company_members` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `username` VARCHAR(50) NOT NULL,
  `role` ENUM('owner','member') NOT NULL DEFAULT 'member',
  `joined_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_company_member_user` (`company_id`,`user_id`),
  UNIQUE KEY `uniq_company_member_username` (`company_id`,`username`),
  KEY `idx_company_members_company_id` (`company_id`),
  KEY `idx_company_members_user_id` (`user_id`),
  CONSTRAINT `fk_company_members_company` FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_company_members_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------
-- Таблиця дизайну компаній
-- --------------------------
CREATE TABLE `company_designs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_id` INT UNSIGNED NOT NULL,
  `profileColor` VARCHAR(7) DEFAULT '#2572ad',
  `textColor` VARCHAR(7) DEFAULT '#ffffff',
  `textBgColor` VARCHAR(7) DEFAULT '',
  `profileOpacity` INT DEFAULT 100,
  `textOpacity` INT DEFAULT 100,
  `textBgOpacity` INT DEFAULT 100,
  `socialBgColor` VARCHAR(7) DEFAULT '#000000',
  `socialTextColor` VARCHAR(7) DEFAULT '#ffffff',
  `socialOpacity` INT DEFAULT 90,
  `avatar` LONGTEXT,
  `bg` LONGTEXT,
  `blockImage` LONGTEXT,
  `socialBgImage` LONGTEXT,
  `profileBgType` VARCHAR(10) DEFAULT 'color',
  `socialBgType` VARCHAR(10) DEFAULT 'color',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_company_design_company` (`company_id`),
  CONSTRAINT `fk_company_designs_company` FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------
-- Таблиця макетів профілів
-- --------------------------
CREATE TABLE `profile_layouts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `layout_data` LONGTEXT NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_profile_layout_user` (`user_id`),
  CONSTRAINT `fk_profile_layouts_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------
-- Додаткові індекси/визначення
-- --------------------------
CREATE INDEX IF NOT EXISTS `idx_users_info_views_desc` ON `users_info` (`views` DESC);
CREATE INDEX IF NOT EXISTS `idx_company_members_role` ON `company_members` (`role`);
