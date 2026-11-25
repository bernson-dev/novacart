-- Existing installations: replace oc_ with your configured DB_PREFIX if different.
CREATE TABLE IF NOT EXISTS `oc_seo_redirect` (
  `seo_redirect_id` int(11) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) NOT NULL DEFAULT '0',
  `language_id` int(11) NOT NULL DEFAULT '0',
  `source_path` varchar(2048) NOT NULL,
  `source_hash` char(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `target_path` varchar(2048) NOT NULL DEFAULT '',
  `http_code` smallint(3) NOT NULL DEFAULT '301',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `date_modified` datetime NOT NULL,
  PRIMARY KEY (`seo_redirect_id`),
  UNIQUE KEY `scope_source` (`store_id`,`language_id`,`source_hash`),
  KEY `store_source` (`store_id`,`source_hash`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
