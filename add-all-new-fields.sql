-- SQL запроси для додавання всіх нових полів в таблицю users_info
-- Виконайте ці запити у вашій базі даних
-- ВАЖЛИВО: Спочатку виберіть базу даних u743896667_bionrgg в phpMyAdmin

-- Додавання колонки youtubeMusic
-- Якщо колонка вже існує, ви отримаєте помилку - просто пропустіть цей запит
ALTER TABLE `u743896667_bionrgg`.`users_info` 
ADD COLUMN `youtubeMusic` varchar(255) DEFAULT '' AFTER `youtube`;

-- Додавання колонки viber
-- Якщо колонка вже існує, ви отримаєте помилку - просто пропустіть цей запит
ALTER TABLE `u743896667_bionrgg`.`users_info` 
ADD COLUMN `viber` varchar(255) DEFAULT '' AFTER `whatsapp`;

-- Додавання колонки googleDocs
-- Якщо колонка вже існує, ви отримаєте помилку - просто пропустіть цей запит
ALTER TABLE `u743896667_bionrgg`.`users_info` 
ADD COLUMN `googleDocs` varchar(255) DEFAULT '' AFTER `site`;

-- Додавання колонки googleSheets
-- Якщо колонка вже існує, ви отримаєте помилку - просто пропустіть цей запит
ALTER TABLE `u743896667_bionrgg`.`users_info` 
ADD COLUMN `googleSheets` varchar(255) DEFAULT '' AFTER `googleDocs`;

-- Додавання колонки fileUpload
-- Якщо колонка вже існує, ви отримаєте помилку - просто пропустіть цей запит
ALTER TABLE `u743896667_bionrgg`.`users_info` 
ADD COLUMN `fileUpload` varchar(255) DEFAULT '' AFTER `googleSheets`;

-- АБО виконайте ці запити по одному, вибравши базу даних u743896667_bionrgg в phpMyAdmin:
-- (Тоді не потрібно вказувати ім'я бази даних)

-- ALTER TABLE `users_info` ADD COLUMN `youtubeMusic` varchar(255) DEFAULT '' AFTER `youtube`;
-- ALTER TABLE `users_info` ADD COLUMN `viber` varchar(255) DEFAULT '' AFTER `whatsapp`;
-- ALTER TABLE `users_info` ADD COLUMN `googleDocs` varchar(255) DEFAULT '' AFTER `site`;
-- ALTER TABLE `users_info` ADD COLUMN `googleSheets` varchar(255) DEFAULT '' AFTER `googleDocs`;
-- ALTER TABLE `users_info` ADD COLUMN `fileUpload` varchar(255) DEFAULT '' AFTER `googleSheets`;

-- Перевірка структури таблиці (опціонально)
-- DESCRIBE `users_info`;

