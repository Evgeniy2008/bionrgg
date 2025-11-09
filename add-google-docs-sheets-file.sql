-- SQL запрос для добавления колонок googleDocs, googleSheets и fileUpload в таблицу users_info
-- Виконайте цей запит у вашій базі даних

USE `bionrgg`;

-- Додавання колонки googleDocs
ALTER TABLE `users_info` 
ADD COLUMN `googleDocs` varchar(255) DEFAULT '' AFTER `site`;

-- Додавання колонки googleSheets
ALTER TABLE `users_info` 
ADD COLUMN `googleSheets` varchar(255) DEFAULT '' AFTER `googleDocs`;

-- Додавання колонки fileUpload
ALTER TABLE `users_info` 
ADD COLUMN `fileUpload` varchar(255) DEFAULT '' AFTER `googleSheets`;

-- Перевірка структури таблиці (опціонально)
-- DESCRIBE `users_info`;










