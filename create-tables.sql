-- SQL запит для створення бази даних та таблиць Bionrgg
-- Виконайте цей запит в phpMyAdmin

-- Створення бази даних
CREATE DATABASE IF NOT EXISTS `u743896667_bionrgg` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `u743896667_bionrgg`;

-- Таблиця користувачів (логіни та паролі)
CREATE TABLE IF NOT EXISTS `users` (
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблиця інформації про користувачів
CREATE TABLE IF NOT EXISTS `users_info` (
  `username` varchar(50) NOT NULL,
  `descr` text,
  `inst` varchar(255) DEFAULT '',
  `discord` varchar(255) DEFAULT '',
  `fb` varchar(255) DEFAULT '',
  `facebook` varchar(255) DEFAULT '',
  `steam` varchar(255) DEFAULT '',
  `twitch` varchar(255) DEFAULT '',
  `tiktok` varchar(255) DEFAULT '',
  `tg` varchar(255) DEFAULT '',
  `telegram` varchar(255) DEFAULT '',
  `youtube` varchar(255) DEFAULT '',
  `youtubeMusic` varchar(255) DEFAULT '',
  `x` varchar(255) DEFAULT '',
  `linkedin` varchar(255) DEFAULT '',
  `spotify` varchar(255) DEFAULT '',
  `soundcloud` varchar(255) DEFAULT '',
  `github` varchar(255) DEFAULT '',
  `site` varchar(255) DEFAULT '',
  `googleDocs` varchar(255) DEFAULT '',
  `googleSheets` varchar(255) DEFAULT '',
  `fileUpload` varchar(255) DEFAULT '',
  `upwork` varchar(255) DEFAULT '',
  `fiverr` varchar(255) DEFAULT '',
  `djinni` varchar(255) DEFAULT '',
  `reddit` varchar(255) DEFAULT '',
  `whatsapp` varchar(255) DEFAULT '',
  `viber` varchar(255) DEFAULT '',
  `dou` varchar(255) DEFAULT '',
  `olx` varchar(255) DEFAULT '',
  `amazon` varchar(255) DEFAULT '',
  `prom` varchar(255) DEFAULT '',
  `fhunt` varchar(255) DEFAULT '',
  `dj` varchar(255) DEFAULT '',
  `privatBank` varchar(255) DEFAULT '',
  `monoBank` varchar(255) DEFAULT '',
  `alfaBank` varchar(255) DEFAULT '',
  `abank` varchar(255) DEFAULT '',
  `pumbBank` varchar(255) DEFAULT '',
  `raiffeisenBank` varchar(255) DEFAULT '',
  `senseBank` varchar(255) DEFAULT '',
  `binance` varchar(255) DEFAULT '',
  `trustWallet` varchar(255) DEFAULT '',
  `color` varchar(7) DEFAULT '#c27eef',
  `colorText` varchar(7) DEFAULT '#ffffff',
  `textBgColor` varchar(7) DEFAULT '',
  `profileOpacity` int(11) DEFAULT 100,
  `textOpacity` int(11) DEFAULT 100,
  `textBgOpacity` int(11) DEFAULT 100,
  `socialBgColor` varchar(7) DEFAULT '#000000',
  `socialTextColor` varchar(7) DEFAULT '#ffffff',
  `socialOpacity` int(11) DEFAULT 90,
  `views` int(11) DEFAULT 0,
  `avatar` longtext,
  `bg` longtext,
  `blockImage` longtext,
  `blockImage2` longtext,
  `socialBgImage` longtext,
  `profileBgType` varchar(10) DEFAULT 'color',
  `socialBgType` varchar(10) DEFAULT 'color',
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`username`),
  KEY `idx_views` (`views`),
  KEY `idx_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Індекси для оптимізації
CREATE INDEX IF NOT EXISTS `idx_users_info_views_desc` ON `users_info` (`views` DESC);

