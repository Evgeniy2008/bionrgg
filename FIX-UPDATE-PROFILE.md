# Исправление сохранения цветов социальных сетей

## Проблема
Цвета социальных сетей (socialBgColor, socialTextColor, socialOpacity) не сохранялись в базу данных при редактировании профиля в My Profile.

## Исправления

### 1. База данных (fix-social-colors.sql)
Выполните SQL скрипт для изменения размера полей:
```sql
ALTER TABLE `users_info` MODIFY COLUMN `socialBgColor` varchar(9) DEFAULT '#000000';
ALTER TABLE `users_info` MODIFY COLUMN `socialTextColor` varchar(9) DEFAULT '#ffffff';
ALTER TABLE `users_info` MODIFY COLUMN `color` varchar(9) DEFAULT '#c27eef';
ALTER TABLE `users_info` MODIFY COLUMN `colorText` varchar(9) DEFAULT '#ffffff';
```

### 2. API update-profile.php
Исправлены типы данных в bind_param для правильного сохранения:
- Добавлена проверка пустых значений
- Исправлен порядок типов параметров
- Добавлено логирование для отладки

### 3. Логирование
Добавлено подробное логирование для отладки:
- Логи полученных значений в update-profile.php
- Логи возвращаемых значений в get-profile.php
- Логи применения стилей в profile.js

## Проверка

1. Выполните SQL скрипт `fix-social-colors.sql` в phpMyAdmin
2. Откройте My Profile (my-profile.html)
3. Измените цвета социальных сетей:
   - Background Color для ссылок
   - Text Color для ссылок
   - Opacity для фона ссылок
4. Сохраните изменения
5. Откройте ваш публичный профиль
6. Проверьте, что цвета применяются

## Технические детали

Параметры для UPDATE запроса:
- 20 строковых полей (social media links)
- 2 строковых поля (color, colorText)
- 2 целочисленных поля (profileOpacity, textOpacity)
- 2 строковых поля (socialBgColor, socialTextColor)
- 1 целочисленное поле (socialOpacity)
- 1 строковое поле (username)

Total: 28 параметров
Types: ssssssssssssssssssssssisissss

## Логи

Проверьте логи PHP (обычно в файле error_log):
```
Received socialBgColor: #ff0000
Received socialTextColor: #00ff00
Received socialOpacity: 90
UPDATE executed successfully
Affected rows: 1
```

## Примечания

- Убедитесь, что размер полей в БД изменен на VARCHAR(9)
- Проверьте, что значения не пустые перед сохранением
- Все изменения обратно совместимы 