<?php
namespace Novacart;

/**
 * Partitioned sitemap, generated on demand. Uses the existing URL rewrite
 * layers and per-store configuration; does not create image thumbnails.
 */
class Sitemap {
	private $registry;
	private $db;
	private $config;
	private $url;
	private $types = array(
		'products' => array('product', 'product/product', 'product_id'),
		'categories' => array('category', 'product/category', 'path'),
		'manufacturers' => array('manufacturer', 'product/manufacturer/info', 'manufacturer_id'),
		'information' => array('information', 'information/information', 'information_id'),
		'articles' => array('article', 'blog/article', 'article_id'),
		'blog-categories' => array('blog_category', 'blog/category', 'blog_category_id')
	);
	private $languages = array();
	private $shared = false;
	private $default = 0;
	private $base;
	private $ssl;
	private $store;
	private $size;
	private $ttl;

	public function __construct($registry) {
		$this->registry = $registry;
		$this->db = $registry->get('db');
		$this->config = $registry->get('config');
		$this->url = $registry->get('url');
		$this->store = (int)$this->config->get('config_store_id');
		$this->ssl = !empty($registry->get('request')->server['HTTPS']) && strtolower((string)$registry->get('request')->server['HTTPS']) !== 'off';
		$this->base = rtrim((string)$this->config->get($this->ssl && $this->config->get('config_ssl') ? 'config_ssl' : 'config_url'), '/') . '/';
		$this->size = self::pageSize($this->config->get('feed_google_sitemap_limit'));
		$this->ttl = self::cacheMinutes($this->config->get('feed_google_sitemap_cache')) * 60;
		$seo = $registry->get('seo_language');
		$this->shared = $seo instanceof \SeoLanguage && $seo->isEnabled();
		$this->languages = $this->db->query("SELECT language_id, code FROM " . DB_PREFIX . "language WHERE status = 1 ORDER BY language_id")->rows;
		foreach ($this->languages as $language) {
			if ($language['code'] === $this->config->get('config_language')) {
				$this->default = (int)$language['language_id'];
			}
		}
		if (!$this->default && $this->languages) {
			$this->default = (int)$this->languages[0]['language_id'];
		}
		// Without language prefixes retain the store's default-language feed.
		if (!$this->shared) {
			$this->languages = array_values(array_filter($this->languages, function ($language) {
				return (int)$language['language_id'] === $this->default;
			}));
		}
	}

	public static function pageSize($value) {
		return is_numeric($value) && (int)$value >= 1 && (int)$value <= 1000 ? (int)$value : 500;
	}

	public static function cacheMinutes($value) {
		return $value !== null && $value !== '' && is_numeric($value) && (int)$value >= 0 && (int)$value <= 1440 ? (int)$value : 60;
	}

	public static function xml($value) {
		return htmlspecialchars(html_entity_decode((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8'), ENT_XML1 | ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
	}

	public static function clearCache($expiredOnly = false) {
		$count = 0;
		foreach (glob(rtrim(DIR_CACHE, '/\\') . '/novacart-sitemap-*.xml') ?: array() as $file) {
			if (!is_file($file)) continue;
			// Files include their expiry; avoid deleting other cache files.
			if ($expiredOnly && (!preg_match('/-(\d+)\.xml$/D', $file, $match) || (int)$match[1] >= time())) continue;
			if (@unlink($file)) $count++;
		}
		return $count;
	}

	private function cached($key, $build) {
		if (!$this->ttl) return $build();
		// Include URL/settings that affect the XML, and never vary by cookie.
		$hash = hash('sha256', serialize(array('presentation-v2', $key, $this->store, $this->base, $this->size, $this->ttl, $this->languages, $this->default,
			$this->shared, $this->config->get('config_seo_url'), $this->config->get('config_seo_pro'),
			$this->config->get('config_seo_language_prefix'), $this->config->get('config_page_postfix'),
			$this->config->get('config_seopro_addslash'), $this->config->get('config_seopro_lowercase'))));
		$prefix = rtrim(DIR_CACHE, '/\\') . '/novacart-sitemap-' . $hash . '-';
		foreach (glob($prefix . '*.xml') ?: array() as $file) {
			if (preg_match('/-(\d+)\.xml$/D', $file, $match) && (int)$match[1] >= time()) {
				$xml = @file_get_contents($file);
				if (is_string($xml) && $xml !== '') return $xml;
			} else {
				@unlink($file);
			}
		}
		$xml = $build();
		if (!is_string($xml)) return $xml;
		$temp = @tempnam(DIR_CACHE, 'sitemap-tmp-');
		if ($temp !== false) {
			if (@file_put_contents($temp, $xml, LOCK_EX) === strlen($xml)) {
				@rename($temp, $prefix . (time() + $this->ttl) . '.xml');
			}
			if (is_file($temp)) @unlink($temp);
		}
		return $xml;
	}

	private function childUrl($type, $part) {
		return $this->base . ($type === 'home' ? 'sitemap-home.xml' : 'sitemap-' . $type . '-' . $part . '.xml');
	}

	/** Recognize only reserved sitemap filenames, not arbitrary SEO keywords. */
	public static function routeParams($path) {
		if ($path === 'sitemap-home.xml') {
			return array('sitemap_type' => 'home', 'sitemap_part' => 1);
		}
		if (is_string($path) && preg_match('/^sitemap-(products|categories|manufacturers|information|articles|blog-categories)-([1-9][0-9]{0,6})\.xml$/D', $path, $match)) {
			if ((int)$match[2] <= 1000000) {
				return array('sitemap_type' => $match[1], 'sitemap_part' => (int)$match[2]);
			}
		}
		return null;
	}

	private function xmlHeader() {
		return '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
			. '<?xml-stylesheet type="text/xsl" href="sitemap.xsl"?>' . "\n";
	}

	private function where($type) {
		$table = $this->types[$type][0];
		$id = $table . '_id';
		$sql = " FROM " . DB_PREFIX . $table . " e WHERE EXISTS (SELECT 1 FROM " . DB_PREFIX . $table . "_to_store s WHERE s." . $id . " = e." . $id . " AND s.store_id = " . $this->store . ")";
		if ($table !== 'manufacturer') $sql .= " AND e.status = 1";
		if ($table === 'product' || $table === 'article') $sql .= " AND e.date_available <= NOW()";
		return $sql;
	}

	private function count($type) {
		return (int)$this->db->query("SELECT COUNT(*) AS total" . $this->where($type))->row['total'];
	}

	private function rows($type, $part) {
		$table = $this->types[$type][0];
		$id = $table . '_id';
		return $this->db->query("SELECT e.*" . $this->where($type) . " ORDER BY e." . $id . " LIMIT " . (($part - 1) * $this->size) . "," . $this->size)->rows;
	}

	public function serve($params) {
		$type = $params['sitemap_type'] ?? '';
		$part = $params['sitemap_part'] ?? '';
		if ($type === '' && $part === '') {
			return $this->cached('index', function () { return $this->indexXml(); });
		}
		if (!is_string($type) || ($type !== 'home' && !isset($this->types[$type])) || !is_scalar($part) || !ctype_digit((string)$part) || (int)$part < 1 || (int)$part > 1000000) return null;
		$part = (int)$part;
		if ($type === 'home' && $part !== 1) return null;
		return $this->cached($type . '-' . $part, function () use ($type, $part) {
			return $this->partXml($type, $part);
		});
	}

	private function indexXml() {
		$xml = $this->xmlHeader() . '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
		$xml .= '<sitemap><loc>' . self::xml($this->childUrl('home', 1)) . "</loc></sitemap>\n";
		foreach ($this->types as $type => $definition) {
			$parts = (int)ceil($this->count($type) / $this->size);
			for ($part = 1; $part <= $parts; $part++) {
				$xml .= '<sitemap><loc>' . self::xml($this->childUrl($type, $part)) . "</loc></sitemap>\n";
			}
		}
		return $xml . '</sitemapindex>';
	}

	private function categoryPath($table, $id) {
		$path = array();
		$seen = array();
		$key = $table . '_id';
		while ($id > 0) {
			if (isset($seen[$id]) || count($seen) >= 100) return '';
			$seen[$id] = true;
			$row = $this->db->query("SELECT e.parent_id" . $this->where($table === 'category' ? 'categories' : 'blog-categories') . " AND e." . $key . " = " . $id . " LIMIT 1");
			if (!$row->num_rows) return '';
			array_unshift($path, $id);
			$id = (int)$row->row['parent_id'];
		}
		return implode('_', $path);
	}

	private function imageUrl($image) {
		$parts = explode('/', str_replace('\\', '/', (string)$image));
		if (!$image || in_array('..', $parts, true) || in_array('.', $parts, true) || !is_file(DIR_IMAGE . implode('/', $parts))) return '';
		return $this->base . 'image/' . implode('/', array_map('rawurlencode', $parts));
	}

	private function partXml($type, $part) {
		$xml = $this->xmlHeader() . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";
		$seen = array();
		if ($type === 'home') {
			$xml .= $this->render('common/home', array(), '', '', $seen);
		} else {
			$rows = $this->rows($type, $part);
			if (!$rows) return null;
			list($table, $route, $param) = $this->types[$type];
			foreach ($rows as $row) {
				$id = (int)$row[$table . '_id'];
				$value = $table === 'category' || $table === 'blog_category' ? $this->categoryPath($table, $id) : $id;
				if (!$value) continue;
				$xml .= $this->render($route, array($param => $value), $row['date_modified'] ?? '', $table === 'product' ? $this->imageUrl($row['image'] ?? '') : '', $seen);
			}
		}
		return $xml . '</urlset>';
	}

	private function render($route, $params, $date, $image, &$seen) {
		$seo = $this->registry->get('seo_language');
		$links = array();
		if ($this->shared) {
			$links = $seo->getAvailableAlternateLinks($route, $params, $this->ssl);
		} elseif ($this->default) {
			$original = $this->config->get('config_language_id');
			try {
				$this->config->set('config_language_id', $this->default);
				if (!($seo instanceof \SeoLanguage) || $seo->isRouteAvailableForLanguage($route, $params, $this->default)) {
					$links['default'] = $this->url->link($route, http_build_query($params, '', '&', PHP_QUERY_RFC3986), $this->ssl);
				}
			} finally {
				$this->config->set('config_language_id', $original);
			}
		}
		// Decode URL::link's HTML entities once. Reject non-local or empty links.
		foreach ($links as $lang => $link) {
			$link = html_entity_decode((string)$link, ENT_QUOTES | ENT_HTML5, 'UTF-8');
			if (strpos($link, $this->base) !== 0) unset($links[$lang]);
			else $links[$lang] = $link;
		}
		$localized = $links;
		unset($localized['x-default']);
		$out = '';
		foreach (array_unique($localized) as $loc) {
			if (isset($seen[$loc])) continue;
			$seen[$loc] = true;
			$out .= '<url><loc>' . self::xml($loc) . '</loc>';
			if ($this->shared && count($localized) > 1) {
				foreach ($links as $lang => $href) {
					$out .= '<xhtml:link rel="alternate" hreflang="' . self::xml($lang) . '" href="' . self::xml($href) . '"/>';
				}
			}
			if ($date && ($time = strtotime($date)) && $time > 0) $out .= '<lastmod>' . gmdate('Y-m-d\TH:i:s\Z', $time) . '</lastmod>';
			if ($image !== '') $out .= '<image:image><image:loc>' . self::xml($image) . '</image:loc></image:image>';
			$out .= "</url>\n";
		}
		return $out;
	}
}
