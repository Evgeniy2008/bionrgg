-- SQL запит для додавання нових платформ, банків та криптобірж в таблицю users_info
-- Виконайте цей запит в вашій базі даних

ALTER TABLE users_info 
ADD COLUMN IF NOT EXISTS dj VARCHAR(255) DEFAULT '',
ADD COLUMN IF NOT EXISTS privatBank VARCHAR(255) DEFAULT '',
ADD COLUMN IF NOT EXISTS monoBank VARCHAR(255) DEFAULT '',
ADD COLUMN IF NOT EXISTS alfaBank VARCHAR(255) DEFAULT '',
ADD COLUMN IF NOT EXISTS abank VARCHAR(255) DEFAULT '',
ADD COLUMN IF NOT EXISTS pumbBank VARCHAR(255) DEFAULT '',
ADD COLUMN IF NOT EXISTS raiffeisenBank VARCHAR(255) DEFAULT '',
ADD COLUMN IF NOT EXISTS senseBank VARCHAR(255) DEFAULT '',
ADD COLUMN IF NOT EXISTS trustWallet VARCHAR(255) DEFAULT '';

-- Якщо IF NOT EXISTS не підтримується вашою версією MySQL, використовуйте окремі запити:

-- ALTER TABLE users_info ADD COLUMN dj VARCHAR(255) DEFAULT '';
-- ALTER TABLE users_info ADD COLUMN privatBank VARCHAR(255) DEFAULT '';
-- ALTER TABLE users_info ADD COLUMN monoBank VARCHAR(255) DEFAULT '';
-- ALTER TABLE users_info ADD COLUMN alfaBank VARCHAR(255) DEFAULT '';
-- ALTER TABLE users_info ADD COLUMN abank VARCHAR(255) DEFAULT '';
-- ALTER TABLE users_info ADD COLUMN pumbBank VARCHAR(255) DEFAULT '';
-- ALTER TABLE users_info ADD COLUMN raiffeisenBank VARCHAR(255) DEFAULT '';
-- ALTER TABLE users_info ADD COLUMN senseBank VARCHAR(255) DEFAULT '';
-- ALTER TABLE users_info ADD COLUMN trustWallet VARCHAR(255) DEFAULT '';

-- Примітка: Якщо колонки dou, olx, amazon, prom, binance, fhunt ще не існують, додайте їх також:
-- ALTER TABLE users_info ADD COLUMN dou VARCHAR(255) DEFAULT '';
-- ALTER TABLE users_info ADD COLUMN olx VARCHAR(255) DEFAULT '';
-- ALTER TABLE users_info ADD COLUMN amazon VARCHAR(255) DEFAULT '';
-- ALTER TABLE users_info ADD COLUMN prom VARCHAR(255) DEFAULT '';
-- ALTER TABLE users_info ADD COLUMN binance VARCHAR(255) DEFAULT '';
-- ALTER TABLE users_info ADD COLUMN fhunt VARCHAR(255) DEFAULT '';










