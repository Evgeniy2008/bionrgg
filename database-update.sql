-- Обновление базы данных Bionrgg для новых функций
-- Выполните этот запрос в phpMyAdmin или MySQL консоли

USE `u743896667_bionrgg`;

-- Добавляем новые поля для настройки социальных ссылок
ALTER TABLE `users_info` 
ADD COLUMN IF NOT EXISTS `socialBgColor` varchar(7) DEFAULT '#000000',
ADD COLUMN IF NOT EXISTS `socialTextColor` varchar(7) DEFAULT '#ffffff',
ADD COLUMN IF NOT EXISTS `socialOpacity` int(11) DEFAULT 90,
ADD COLUMN IF NOT EXISTS `socialBgImage` longtext;

-- Добавляем поля для настройки фона профиля
ALTER TABLE `users_info` 
ADD COLUMN IF NOT EXISTS `profileBgType` varchar(10) DEFAULT 'color',
ADD COLUMN IF NOT EXISTS `socialBgType` varchar(10) DEFAULT 'color';

-- Добавляем поля для фона текста (если текст сливается с фоном)
ALTER TABLE `users_info` 
ADD COLUMN IF NOT EXISTS `textBgColor` varchar(7) DEFAULT '',
ADD COLUMN IF NOT EXISTS `textBgOpacity` int(11) DEFAULT 100;

-- Проверяем, что все поля добавлены
DESCRIBE `users_info`;

-- Показываем все новые поля
SELECT 
    COLUMN_NAME,
    DATA_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'u743896667_bionrgg' 
AND TABLE_NAME = 'users_info' 
AND COLUMN_NAME IN (
    'socialBgColor', 'socialTextColor', 'socialOpacity', 'socialBgImage',
    'profileBgType', 'socialBgType',
    'spotify', 'soundcloud', 'djinni',
    'blockImage', 'profileOpacity', 'textOpacity',
    'textBgColor', 'textBgOpacity'
)
ORDER BY COLUMN_NAME;

-- Обновляем существующие записи с значениями по умолчанию
UPDATE `users_info` SET 
    `socialBgColor` = '#000000',
    `socialTextColor` = '#ffffff',
    `socialOpacity` = 90,
    `profileBgType` = 'color',
    `socialBgType` = 'color'
WHERE `socialBgColor` IS NULL OR `socialBgColor` = '';

-- Показываем статистику
SELECT 
    COUNT(*) as total_users,
    COUNT(CASE WHEN socialBgColor IS NOT NULL THEN 1 END) as with_social_color,
    COUNT(CASE WHEN socialOpacity IS NOT NULL THEN 1 END) as with_social_opacity
FROM `users_info`;