-- NovaCart country/zone localized names migration.
-- Run ONCE BEFORE deploying the updated PHP files on an existing shop.
-- Replace oc_ with the store's actual DB_PREFIX.
-- Legacy name columns remain unchanged; no automatic translation or misattribution.
CREATE TABLE IF NOT EXISTS `oc_country_description` (
  `country_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `name` varchar(128) NOT NULL,
  PRIMARY KEY (`country_id`, `language_id`),
  KEY `language_id` (`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `oc_zone_description` (
  `zone_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `name` varchar(128) NOT NULL,
  PRIMARY KEY (`zone_id`, `language_id`),
  KEY `language_id` (`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
