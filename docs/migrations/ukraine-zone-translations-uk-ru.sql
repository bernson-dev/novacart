-- NovaCart: Ukrainian administrative units (uk-ua, ru-ru).
-- Requires country_description and zone_description tables from PR #114.
-- Replace oc_ with your configured DB_PREFIX before running.
-- Re-running updates translations only; legacy names and region IDs are untouched.
SET NAMES utf8mb4;

CREATE TEMPORARY TABLE `tmp_ua_zone_i18n` (
  `code` varchar(32) NOT NULL PRIMARY KEY,
  `name_uk` varchar(128) NOT NULL,
  `name_ru` varchar(128) NOT NULL
) ENGINE=MEMORY DEFAULT CHARSET=utf8mb4;

INSERT INTO `tmp_ua_zone_i18n` (`code`, `name_uk`, `name_ru`) VALUES
('05', 'Вінницька область', 'Винницкая область'),
('07', 'Волинська область', 'Волынская область'),
('12', 'Дніпропетровська область', 'Днепропетровская область'),
('14', 'Донецька область', 'Донецкая область'),
('18', 'Житомирська область', 'Житомирская область'),
('21', 'Закарпатська область', 'Закарпатская область'),
('23', 'Запорізька область', 'Запорожская область'),
('26', 'Івано-Франківська область', 'Ивано-Франковская область'),
('30', 'Київ', 'Киев'),
('32', 'Київська область', 'Киевская область'),
('35', 'Кіровоградська область', 'Кировоградская область'),
('43', 'Автономна Республіка Крим', 'Автономная Республика Крым'),
('09', 'Луганська область', 'Луганская область'),
('46', 'Львівська область', 'Львовская область'),
('48', 'Миколаївська область', 'Николаевская область'),
('51', 'Одеська область', 'Одесская область'),
('53', 'Полтавська область', 'Полтавская область'),
('56', 'Рівненська область', 'Ровненская область'),
('40', 'Севастополь', 'Севастополь'),
('59', 'Сумська область', 'Сумская область'),
('61', 'Тернопільська область', 'Тернопольская область'),
('63', 'Харківська область', 'Харьковская область'),
('65', 'Херсонська область', 'Херсонская область'),
('68', 'Хмельницька область', 'Хмельницкая область'),
('71', 'Черкаська область', 'Черкасская область'),
('74', 'Чернігівська область', 'Черниговская область'),
('77', 'Чернівецька область', 'Черновицкая область');

-- Ukraine country names.
INSERT INTO `oc_country_description` (`country_id`, `language_id`, `name`)
SELECT c.country_id, l.language_id,
       CASE l.code WHEN 'uk-ua' THEN 'Україна' ELSE 'Украина' END
FROM `oc_country` AS c
JOIN `oc_language` AS l ON l.code IN ('uk-ua', 'ru-ru')
WHERE c.iso_code_2 = 'UA'
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- Regions matched by country ISO code + existing region code.
INSERT INTO `oc_zone_description` (`zone_id`, `language_id`, `name`)
SELECT z.zone_id, l.language_id,
       CASE l.code WHEN 'uk-ua' THEN t.name_uk ELSE t.name_ru END
FROM `tmp_ua_zone_i18n` AS t
JOIN `oc_country` AS c ON c.iso_code_2 = 'UA'
JOIN `oc_zone` AS z ON z.country_id = c.country_id AND z.code = t.code
JOIN `oc_language` AS l ON l.code IN ('uk-ua', 'ru-ru')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- Inspect missing region codes before dismissing the result.
SELECT t.code, t.name_uk FROM `tmp_ua_zone_i18n` AS t
WHERE NOT EXISTS (
  SELECT 1 FROM `oc_country` c
  JOIN `oc_zone` z ON z.country_id = c.country_id
  WHERE c.iso_code_2 = 'UA' AND z.code = t.code
);

DROP TEMPORARY TABLE `tmp_ua_zone_i18n`;
