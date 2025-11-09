-- SQL запрос для добавления колонок youtubeMusic и viber в таблицу users_info
-- Виконайте цей запит у вашій базі даних

USE `bionrgg`;

-- Додавання колонки youtubeMusic
ALTER TABLE `users_info` 
ADD COLUMN `youtubeMusic` varchar(255) DEFAULT '' AFTER `youtube`;

-- Додавання колонки viber
ALTER TABLE `users_info` 
ADD COLUMN `viber` varchar(255) DEFAULT '' AFTER `whatsapp`;

-- Перевірка структури таблиці (опціонально)
-- DESCRIBE `users_info`;










