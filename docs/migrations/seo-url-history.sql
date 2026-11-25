-- Existing installations: replace oc_ with your configured DB_PREFIX if different.
CREATE TABLE IF NOT EXISTS `oc_seo_url_history` (
  `seo_url_history_id` int(11) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `query` varchar(255) NOT NULL,
  `keyword` varchar(255) NOT NULL,
  `keyword_hash` char(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `date_modified` datetime NOT NULL,
  PRIMARY KEY (`seo_url_history_id`),
  UNIQUE KEY `scope_keyword` (`store_id`,`language_id`,`keyword_hash`),
  KEY `query_scope` (`query`(191),`store_id`,`language_id`),
  KEY `lookup_keyword` (`store_id`,`language_id`,`keyword`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
