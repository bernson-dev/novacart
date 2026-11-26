-- Existing installations: replace oc_ with your configured DB_PREFIX if different.
CREATE TABLE IF NOT EXISTS `oc_seo_not_found` (
  `seo_not_found_id` int(11) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) NOT NULL DEFAULT '0',
  `language_id` int(11) NOT NULL DEFAULT '0',
  `path` varchar(2048) NOT NULL,
  `path_hash` char(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `hits` bigint unsigned NOT NULL DEFAULT '1',
  `date_first` datetime NOT NULL,
  `date_last` datetime NOT NULL,
  PRIMARY KEY (`seo_not_found_id`),
  UNIQUE KEY `scope_path` (`store_id`,`language_id`,`path_hash`),
  KEY `date_last` (`date_last`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
