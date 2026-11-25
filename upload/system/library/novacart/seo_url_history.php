<?php
namespace Novacart;

/**
 * Records replaced SEO keywords. Historical aliases refer to entities, not
 * another alias, so subsequent renames do not create redirect chains.
 */
class SeoUrlHistory {
	private $db;
	private $available;

	public function __construct($db) {
		$this->db = $db;
	}

	private function isAvailable() {
		if ($this->available === null) {
			$result = $this->db->query("SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '" . $this->db->escape(DB_PREFIX . 'seo_url_history') . "' LIMIT 1");
			$this->available = (bool)$result->num_rows;
		}
		return $this->available;
	}

	public function snapshot($query) {
		if (!$this->isAvailable()) {
			return array();
		}

		return $this->db->query("SELECT store_id, language_id, `query`, keyword FROM `" . DB_PREFIX . "seo_url` WHERE `query` = '" . $this->db->escape($query) . "'")->rows;
	}

	public function snapshotById($id) {
		if (!$this->isAvailable()) {
			return array();
		}
		return $this->db->query("SELECT store_id, language_id, `query`, keyword FROM `" . DB_PREFIX . "seo_url` WHERE seo_url_id = '" . (int)$id . "'")->rows;
	}

	/**
	 * Only record a replacement for the same entity, store and language.
	 * A missing replacement or an unchanged alias is not a rename.
	 */
	public static function replacements(array $before, array $after) {
		$active = array();
		foreach ($after as $row) {
			$key = json_encode(array((int)$row['store_id'], (int)$row['language_id'], $row['query']));
			if ((string)$row['keyword'] !== '') {
				$active[$key][] = (string)$row['keyword'];
			}
		}

		$changed = array();
		foreach ($before as $row) {
			$key = json_encode(array((int)$row['store_id'], (int)$row['language_id'], $row['query']));
			$keyword = (string)$row['keyword'];
			if ($keyword !== '' && isset($active[$key]) && !in_array($keyword, $active[$key], true)) {
				$changed[] = $row;
			}
		}
		return $changed;
	}

	public function record(array $before, array $after) {
		if (!$before || !$this->isAvailable()) {
			return;
		}

		foreach (self::replacements($before, $after) as $row) {
			$store = (int)$row['store_id'];
			$language = (int)$row['language_id'];
			$keyword = $this->db->escape($row['keyword']);
			$query = $this->db->escape($row['query']);

			// An active alias always wins, including case/accent equivalents
			// according to the current seo_url database collation.
			$active = $this->db->query("SELECT seo_url_id FROM `" . DB_PREFIX . "seo_url` WHERE store_id = '" . $store . "' AND language_id = '" . $language . "' AND keyword = '" . $keyword . "' LIMIT 1");
			if ($active->num_rows) {
				continue;
			}

			$hash = hash('sha256', (string)$row['keyword']);
			$this->db->query("INSERT INTO `" . DB_PREFIX . "seo_url_history` SET store_id = '" . $store . "', language_id = '" . $language . "', `query` = '" . $query . "', keyword = '" . $keyword . "', keyword_hash = '" . $hash . "', date_modified = NOW() ON DUPLICATE KEY UPDATE `query` = VALUES(`query`), date_modified = NOW()");
		}
	}
}
