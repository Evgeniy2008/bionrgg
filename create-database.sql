-- Создание базы данных Bionrgg с нуля
-- Выполните этот запрос в phpMyAdmin или MySQL консоли

-- Создание базы данных
CREATE DATABASE IF NOT EXISTS `u743896667_bionrgg` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `u743896667_bionrgg`;

-- Установка кодировки
SET NAMES utf8mb4;

-- Таблица пользователей (логины и пароли)
CREATE TABLE `users` (
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица информации о пользователях
CREATE TABLE `users_info` (
  `username` varchar(50) NOT NULL,
  `descr` text,
  
  -- Основные социальные сети
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
  `upwork` varchar(255) DEFAULT '',
  `fiverr` varchar(255) DEFAULT '',
  `djinni` varchar(255) DEFAULT '',
  
  -- Дополнительные социальные сети
  `spotify` varchar(255) DEFAULT '',
  `soundcloud` varchar(255) DEFAULT '',
  
  -- Статистика
  `views` int(11) DEFAULT 0,
  
  -- Медиа файлы
  `avatar` longtext,
  `bg` longtext,
  `blockImage` longtext,
  
  -- Цвета и дизайн профиля
  `color` varchar(7) DEFAULT '#c27eef',
  `colorText` varchar(7) DEFAULT '#ffffff',
  `textBgColor` varchar(7) DEFAULT '',
  `profileOpacity` int(11) DEFAULT 100,
  `textOpacity` int(11) DEFAULT 100,
  `textBgOpacity` int(11) DEFAULT 100,
  
  -- Настройки социальных ссылок
  `socialBgColor` varchar(7) DEFAULT '#000000',
  `socialTextColor` varchar(7) DEFAULT '#ffffff',
  `socialOpacity` int(11) DEFAULT 90,
  `socialBgImage` longtext,
  
  -- Типы фона
  `profileBgType` varchar(10) DEFAULT 'color',
  `socialBgType` varchar(10) DEFAULT 'color',
  
  -- Временные метки
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`username`),
  KEY `idx_views` (`views`),
  KEY `idx_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Индексы для оптимизации
CREATE INDEX `idx_users_info_views_desc` ON `users_info` (`views` DESC);
CREATE INDEX `idx_users_info_username` ON `users_info` (`username`);

-- Таблица макетов профилей (для будущих функций)
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

-- Проверка структуры таблиц
DESCRIBE `users`;
DESCRIBE `users_info`;

-- Показываем все поля социальных сетей
SELECT 
    COLUMN_NAME,
    DATA_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'u743896667_bionrgg' 
AND TABLE_NAME = 'users_info' 
AND COLUMN_NAME IN (
    'inst', 'youtube', 'tiktok', 'fb', 'x', 'linkedin',
    'twitch', 'steam', 'discord', 'tg', 'telegram',
    'spotify', 'soundcloud',
    'github', 'site',
    'upwork', 'fiverr', 'djinni',
    'reddit', 'whatsapp',
    'color', 'colorText', 'textBgColor', 'profileOpacity', 'textOpacity', 'textBgOpacity',
    'socialBgColor', 'socialTextColor', 'socialOpacity', 'socialBgImage',
    'profileBgType', 'socialBgType'
)
ORDER BY COLUMN_NAME;

-- Показываем статистику
SELECT 
    'Database created successfully!' as message,
    COUNT(*) as total_tables
FROM INFORMATION_SCHEMA.TABLES 
WHERE TABLE_SCHEMA = 'u743896667_bionrgg';