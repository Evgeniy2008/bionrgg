-- Bionrgg Database Schema
-- Створіть базу даних та таблиці для роботи Bionrgg

CREATE DATABASE IF NOT EXISTS `bionrgg` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `bionrgg`;

-- Set connection charset
SET NAMES utf8mb4;

-- Таблиця користувачів (логіни та паролі)
CREATE TABLE `users` (
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблиця інформації про користувачів
CREATE TABLE `users_info` (
  `username` varchar(50) NOT NULL,
  `descr` text,
  `inst` varchar(255) DEFAULT '',
  `discord` varchar(255) DEFAULT '',
  `fb` varchar(255) DEFAULT '',
  `steam` varchar(255) DEFAULT '',
  `twitch` varchar(255) DEFAULT '',
  `tiktok` varchar(255) DEFAULT '',
  `tg` varchar(255) DEFAULT '',
  `youtube` varchar(255) DEFAULT '',
  `linkedin` varchar(255) DEFAULT '',
  `github` varchar(255) DEFAULT '',
  `x` varchar(255) DEFAULT '',
  `whatsapp` varchar(255) DEFAULT '',
  `reddit` varchar(255) DEFAULT '',
  `site` varchar(255) DEFAULT '',
  `djinni` varchar(255) DEFAULT '',
  `dou` varchar(255) DEFAULT '',
  `olx` varchar(255) DEFAULT '',
  `amazon` varchar(255) DEFAULT '',
  `prom` varchar(255) DEFAULT '',
  `binance` varchar(255) DEFAULT '',
  `fhunt` varchar(255) DEFAULT '',
  `upwork` varchar(255) DEFAULT '',
  `fiverr` varchar(255) DEFAULT '',
  `views` int(11) DEFAULT 0,
  `avatar` longtext,
  `bg` longtext,
  `color` varchar(7) DEFAULT '#c27eef',
  `colorText` varchar(7) DEFAULT '#ffffff',
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`username`),
  KEY `idx_views` (`views`),
  KEY `idx_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Додавання зовнішнього ключа (опціонально)
-- ALTER TABLE `users_info` ADD CONSTRAINT `fk_users_info_username` 
-- FOREIGN KEY (`username`) REFERENCES `users`(`username`) ON DELETE CASCADE;

-- Індекси для оптимізації
CREATE INDEX `idx_users_info_views_desc` ON `users_info` (`views` DESC);
CREATE INDEX `idx_users_info_username` ON `users_info` (`username`);

-- Таблиця макетів профілів
CREATE TABLE IF NOT EXISTS `profile_layouts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `layout_data` longtext NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Приклад даних для тестування (опціонально)
-- INSERT INTO `users` (`username`, `password`) VALUES 
-- ('testuser', 'password123'),
-- ('demo', 'demo123');

-- INSERT INTO `users_info` (`username`, `descr`, `inst`, `views`) VALUES 
-- ('testuser', 'Тестовий користувач', 'https://instagram.com/testuser', 150),
-- ('demo', 'Демо профіль', 'https://instagram.com/demo', 75);