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

	public function isAvailable() {
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

	/**
	 * Resolve a complete path only after normal routing misses a segment.
	 * Current aliases win; every historical alias must still have a current
	 * replacement in the same store and keyword language.
	 */
	public function resolvePath(array $parts, $store_id, $language_id, $unscoped = false) {
		if (!$parts || !$this->isAvailable()) {
			return array();
		}

		$store = (int)$store_id;
		$language = (int)$language_id;
		$scope = "store_id = '" . $store . "'";
		$lang = " AND language_id = '" . $language . "'";
		$queries = array();
		$historical = false;

		foreach ($parts as $part) {
			if (!is_string($part) || $part === '') {
				return array();
			}
			$keyword = $this->db->escape($part);
			$active = $this->db->query("SELECT DISTINCT `query` FROM `" . DB_PREFIX . "seo_url` WHERE " . $scope . ($unscoped ? '' : $lang) . " AND keyword = '" . $keyword . "' LIMIT 2");
			if ($active->num_rows > 1) {
				return array();
			}
			if ($active->num_rows) {
				$queries[] = $active->row['query'];
				continue;
			}

			$old = $this->db->query("SELECT DISTINCT `query` FROM `" . DB_PREFIX . "seo_url_history` WHERE " . $scope . $lang . " AND keyword = '" . $keyword . "' LIMIT 2");
			if ($old->num_rows !== 1) {
				return array();
			}
			$query = $this->db->escape($old->row['query']);
			$current = $this->db->query("SELECT seo_url_id FROM `" . DB_PREFIX . "seo_url` WHERE " . $scope . $lang . " AND `query` = '" . $query . "' AND keyword <> '' LIMIT 1");
			if (!$current->num_rows) {
				return array();
			}
			$historical = true;
			$queries[] = $old->row['query'];
		}

		return $historical ? self::routeFromQueries($queries) : array();
	}

	/**
	 * Accept only known entity paths, with a single terminal item.
	 */
	public static function routeFromQueries(array $queries) {
		$routes = array(
			'product_id' => 'product/product',
			'category_id' => 'product/category',
			'manufacturer_id' => 'product/manufacturer/info',
			'information_id' => 'information/information',
			'article_id' => 'blog/article',
			'blog_category_id' => 'blog/category'
		);
		$params = array();
		$last = '';
		$tree = '';
		foreach ($queries as $query) {
			if (!preg_match('/^([a-z_]+)=([1-9][0-9]*)$/D', $query, $match) || !isset($routes[$match[1]])) {
				return array();
			}
			$key = $match[1];
			$id = (int)$match[2];
			if ($id < 1 || ($last !== '' && $last !== 'category_id' && $last !== 'blog_category_id')) {
				return array();
			}
			if ($key === 'category_id' || $key === 'blog_category_id') {
				if ($tree !== '' && $tree !== $key) {
					return array();
				}
				$tree = $key;
				$param = $key === 'category_id' ? 'path' : 'blog_category_id';
				$params[$param] = isset($params[$param]) ? $params[$param] . '_' . $id : (string)$id;
			} else {
				if ($tree !== '' && !(($tree === 'category_id' && $key === 'product_id') || ($tree === 'blog_category_id' && $key === 'article_id'))) {
					return array();
				}
				$params[$key] = $id;
			}
			$last = $key;
		}
		return $last !== '' ? array('route' => $routes[$last], 'params' => $params) : array();
	}

	public function record(array $before, array $after) {
		if ((!$before && !$after) || !$this->isAvailable()) {
			return;
		}

		foreach ($after as $row) {
			if ((string)$row['keyword'] !== '') {
				$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url_history` WHERE store_id = '" . (int)$row['store_id'] . "' AND language_id = '" . (int)$row['language_id'] . "' AND keyword = '" . $this->db->escape($row['keyword']) . "'");
			}
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
