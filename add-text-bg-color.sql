-- Добавление полей для фона текста (textBgColor и textBgOpacity)
-- Выполните этот запрос в phpMyAdmin или MySQL консоли

USE `u743896667_bionrgg`;

-- Добавляем поля для фона текста
-- Эти поля позволяют задать цвет фона для текста, если он сливается с основным фоном
ALTER TABLE `users_info` 
ADD COLUMN IF NOT EXISTS `textBgColor` varchar(7) DEFAULT '',
ADD COLUMN IF NOT EXISTS `textBgOpacity` int(11) DEFAULT 100;

-- Если ваша версия MySQL не поддерживает IF NOT EXISTS, используйте:
-- ALTER TABLE `users_info` 
-- ADD COLUMN `textBgColor` varchar(7) DEFAULT '',
-- ADD COLUMN `textBgOpacity` int(11) DEFAULT 100;

-- Проверка, что поля добавлены
DESCRIBE `users_info`;

-- Показываем добавленные поля
SELECT 
    COLUMN_NAME,
    DATA_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'u743896667_bionrgg' 
AND TABLE_NAME = 'users_info' 
AND COLUMN_NAME IN ('textBgColor', 'textBgOpacity')
ORDER BY COLUMN_NAME;



