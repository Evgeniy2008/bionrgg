-- Прості SQL запити для додавання нових полів
-- ІНСТРУКЦІЯ: 
-- 1. Відкрийте phpMyAdmin
-- 2. Виберіть базу даних u743896667_bionrgg (зліва в меню)
-- 3. Перейдіть на вкладку "SQL"
-- 4. Скопіюйте та виконайте ці запити по одному (або всі разом)

-- Додавання колонки youtubeMusic
ALTER TABLE `users_info` 
ADD COLUMN `youtubeMusic` varchar(255) DEFAULT '' AFTER `youtube`;

-- Додавання колонки viber
ALTER TABLE `users_info` 
ADD COLUMN `viber` varchar(255) DEFAULT '' AFTER `whatsapp`;

-- Додавання колонки googleDocs
ALTER TABLE `users_info` 
ADD COLUMN `googleDocs` varchar(255) DEFAULT '' AFTER `site`;

-- Додавання колонки googleSheets
ALTER TABLE `users_info` 
ADD COLUMN `googleSheets` varchar(255) DEFAULT '' AFTER `googleDocs`;

-- Додавання колонки fileUpload
ALTER TABLE `users_info` 
ADD COLUMN `fileUpload` varchar(255) DEFAULT '' AFTER `googleSheets`;

-- Примітка: Якщо ви отримаєте помилку "Duplicate column name", це означає що колонка вже існує
-- У такому випадку просто пропустіть цей запит і продовжте з наступним










