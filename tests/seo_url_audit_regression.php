<?php
/**
 * Regression checks for design/seo_url duplicate audit.
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

define('DB_PREFIX', 'oc_');
define('DIR_SYSTEM', dirname(__DIR__) . '/upload/system/');
define('DIR_APPLICATION', dirname(__DIR__) . '/upload/admin/');

require DIR_SYSTEM . 'engine/registry.php';
require DIR_SYSTEM . 'engine/model.php';
require DIR_APPLICATION . 'model/design/seo_url.php';

class SeoUrlAuditTestDb {
	public $settings = array();
	public $seo_urls = array();
	public $languages = array(
		1 => 'ru-ru',
		2 => 'uk-ua',
		3 => 'en-gb'
	);

	public $entities = array(
		'product' => array(),
		'category' => array(),
		'manufacturer' => array(),
		'information' => array(),
		'article' => array(),
		'blog_category' => array()
	);

	public $entity_status = array(
		'product' => array(),
		'category' => array(),
		'manufacturer' => array(),
		'information' => array(),
		'article' => array(),
		'blog_category' => array()
	);

	public function escape($value) {
		return addslashes((string)$value);
	}

	public function query($sql) {
		$result = new stdClass();
		$result->num_rows = 0;
		$result->row = array();
		$result->rows = array();

		if (strpos($sql, 'FROM `oc_seo_url`') !== false && strpos($sql, 'ORDER BY `seo_url_id` ASC') !== false) {
			$result->rows = $this->seo_urls;
			$result->num_rows = count($result->rows);
			$result->row = $result->num_rows ? $result->rows[0] : array();
			return $result;
		}

		if (strpos($sql, 'FROM `oc_language`') !== false) {
			$code = $this->extractString($sql, 'code');

			if ($code !== '') {
				$language_id = array_search($code, $this->languages, true);

				if ($language_id !== false) {
					$result->row = array(
						'language_id' => (int)$language_id,
						'code' => $code
					);
					$result->rows = array($result->row);
					$result->num_rows = 1;
				}

				return $result;
			}

			$language_id = $this->extractInt($sql, 'language_id');

			if (isset($this->languages[$language_id])) {
				$result->row = array(
					'language_id' => $language_id,
					'code' => $this->languages[$language_id]
				);
				$result->rows = array($result->row);
				$result->num_rows = 1;
			}

			return $result;
		}

		if (strpos($sql, 'FROM `oc_setting`') !== false) {
			$code = $this->extractString($sql, 'code');
			$key = $this->extractString($sql, 'key');
			$store_ids = $this->extractStoreIds($sql);

			foreach ($store_ids as $store_id) {
				foreach ($this->settings as $setting) {
					if (
						(int)$setting['store_id'] === $store_id
						&& (string)$setting['code'] === $code
						&& (string)$setting['key'] === $key
					) {
						$result->row = array(
							'value' => $setting['value'],
							'serialized' => isset($setting['serialized']) ? (int)$setting['serialized'] : 0
						);
						$result->rows = array($result->row);
						$result->num_rows = 1;
						return $result;
					}
				}
			}

			return $result;
		}

		$entity_map = array(
			'product' => 'product_id',
			'category' => 'category_id',
			'manufacturer' => 'manufacturer_id',
			'information' => 'information_id',
			'article' => 'article_id',
			'blog_category' => 'blog_category_id'
		);

		foreach ($entity_map as $table => $key) {
			if (strpos($sql, 'FROM `oc_' . $table . '`') === false) {
				continue;
			}

			foreach ($this->entities[$table] as $id) {
				$row = array($key => (int)$id);

				if (strpos($sql, '`status`') !== false) {
					$row['status'] = isset($this->entity_status[$table][(int)$id])
						? (int)$this->entity_status[$table][(int)$id]
						: 1;
				}

				$result->rows[] = $row;
			}

			$result->num_rows = count($result->rows);
			$result->row = $result->num_rows ? $result->rows[0] : array();

			return $result;
		}

		if (strpos($sql, 'FROM `oc_product_description`') !== false) {
			$result->rows = array(
				array('product_id' => 10, 'language_id' => 1, 'name' => 'Тестовый товар'),
				array('product_id' => 20, 'language_id' => 2, 'name' => 'Тестовий товар')
			);
			$result->num_rows = count($result->rows);
			$result->row = $result->rows[0];

			return $result;
		}

		if (strpos($sql, 'FROM `oc_category_description`') !== false) {
			$result->rows = array(
				array('category_id' => 5, 'language_id' => 1, 'name' => 'Категория')
			);
			$result->num_rows = 1;
			$result->row = $result->rows[0];

			return $result;
		}

		if (strpos($sql, 'FROM `oc_information_description`') !== false) {
			$result->rows = array(
				array('information_id' => 6, 'language_id' => 1, 'title' => 'Информация')
			);
			$result->num_rows = 1;
			$result->row = $result->rows[0];

			return $result;
		}

		if (strpos($sql, 'FROM `oc_article_description`') !== false) {
			$result->rows = array(
				array('article_id' => 7, 'language_id' => 1, 'name' => 'Тестовая статья'),
				array('article_id' => 7, 'language_id' => 2, 'name' => 'Тестова стаття')
			);
			$result->num_rows = count($result->rows);
			$result->row = $result->rows[0];

			return $result;
		}

		if (strpos($sql, 'FROM `oc_blog_category_description`') !== false) {
			$result->rows = array(
				array('blog_category_id' => 8, 'language_id' => 1, 'name' => 'Новости блога')
			);
			$result->num_rows = 1;
			$result->row = $result->rows[0];

			return $result;
		}

		return $result;
	}

	private function extractStoreIds($sql) {
		if (preg_match("/`store_id` IN \(0, '([0-9]+)'\)/", $sql, $match)) {
			$store_id = (int)$match[1];

			return $store_id === 0 ? array(0) : array($store_id, 0);
		}

		return array($this->extractInt($sql, 'store_id'));
	}

	private function extractInt($sql, $field) {
		if (preg_match("/`" . preg_quote($field, '/') . "` = '([0-9]+)'/", $sql, $match)) {
			return (int)$match[1];
		}

		return 0;
	}

	private function extractString($sql, $field) {
		if (preg_match("/`" . preg_quote($field, '/') . "` = '([^']*)'/", $sql, $match)) {
			return stripslashes($match[1]);
		}

		return '';
	}
}

function assertSameValue($expected, $actual, $message) {
	if ($expected !== $actual) {
		throw new RuntimeException(
			$message . ': expected ' . var_export($expected, true)
			. ', got ' . var_export($actual, true)
		);
	}
}

$db = new SeoUrlAuditTestDb();

$db->settings = array(
	array('store_id' => 0, 'code' => 'config', 'key' => 'config_url', 'value' => 'http://store.test/'),
	array('store_id' => 0, 'code' => 'config', 'key' => 'config_ssl', 'value' => ''),
	array('store_id' => 0, 'code' => 'config', 'key' => 'config_language', 'value' => 'ru-ru'),
	array('store_id' => 0, 'code' => 'config', 'key' => 'config_seo_url', 'value' => '1'),
	array('store_id' => 0, 'code' => 'config', 'key' => 'config_seo_language', 'value' => '1'),
	array(
		'store_id' => 0,
		'code' => 'config',
		'key' => 'config_seo_language_prefix',
		'value' => json_encode(array('ru-ru' => 'ru', 'uk-ua' => 'uk')),
		'serialized' => 1
	),
	array('store_id' => 1, 'code' => 'config', 'key' => 'config_seo_url', 'value' => '1'),
	array('store_id' => 1, 'code' => 'config', 'key' => 'config_seo_language', 'value' => '0')
);

$db->seo_urls = array(
	array('seo_url_id' => 1, 'store_id' => 0, 'language_id' => 1, 'query' => 'product_id=10', 'keyword' => 'shared'),
	array('seo_url_id' => 2, 'store_id' => 0, 'language_id' => 2, 'query' => 'product_id=20', 'keyword' => 'shared'),

	array('seo_url_id' => 3, 'store_id' => 0, 'language_id' => 1, 'query' => 'category_id=5', 'keyword' => 'duplicate'),
	array('seo_url_id' => 4, 'store_id' => 0, 'language_id' => 1, 'query' => 'information_id=6', 'keyword' => 'duplicate'),

	array('seo_url_id' => 5, 'store_id' => 0, 'language_id' => 1, 'query' => 'product_id=30', 'keyword' => 'query-a'),
	array('seo_url_id' => 6, 'store_id' => 0, 'language_id' => 1, 'query' => 'product_id=30', 'keyword' => 'query-b'),

	array('seo_url_id' => 7, 'store_id' => 0, 'language_id' => 1, 'query' => 'product_id=99', 'keyword' => 'uk'),
	array('seo_url_id' => 8, 'store_id' => 0, 'language_id' => 2, 'query' => 'common/home', 'keyword' => 'uk'),

	array('seo_url_id' => 9, 'store_id' => 1, 'language_id' => 1, 'query' => 'product_id=40', 'keyword' => 'legacy'),
	array('seo_url_id' => 10, 'store_id' => 1, 'language_id' => 2, 'query' => 'product_id=41', 'keyword' => 'legacy'),
	array('seo_url_id' => 11, 'store_id' => 0, 'language_id' => 1, 'query' => 'product_id=999', 'keyword' => 'orphan-product')
);

$db->entities['product'] = array(10, 20, 30, 40, 41, 99);
$db->entities['category'] = array(5);
$db->entities['information'] = array(6);
$db->entities['article'] = array(7);
$db->entities['blog_category'] = array(8);

$db->entity_status['product'] = array(10 => 1, 20 => 0, 30 => 1, 40 => 1, 41 => 1, 99 => 1);
$db->entity_status['category'] = array(5 => 0);
$db->entity_status['information'] = array(6 => 1);
$db->entity_status['article'] = array(7 => 0);
$db->entity_status['blog_category'] = array(8 => 1);

$registry = new Registry();
$registry->set('db', $db);

$model = new ModelDesignSeoUrl($registry);
$audit = $model->getSeoUrlAudit();

assertSameValue(11, $audit['summary']['all_rows'], 'Unexpected SEO URL row count');

assertSameValue(true, $audit['issues'][1]['keyword'], 'Cross-language fallback collision was not detected');
assertSameValue(true, $audit['issues'][2]['keyword'], 'Cross-language fallback collision was not detected');

assertSameValue(true, $audit['issues'][3]['keyword'], 'Same-language duplicate keyword was not detected');
assertSameValue(true, $audit['issues'][4]['keyword'], 'Same-language duplicate keyword was not detected');

assertSameValue(true, $audit['issues'][5]['query'], 'Duplicate query was not detected');
assertSameValue(true, $audit['issues'][6]['query'], 'Duplicate query was not detected');

assertSameValue(true, $audit['issues'][7]['prefix'], 'Reserved language prefix collision was not detected');
assertSameValue(false, $audit['issues'][8]['prefix'], 'Own common/home language prefix was incorrectly reported');

assertSameValue(true, $audit['issues'][9]['keyword'], 'Legacy global keyword collision was not detected');
assertSameValue(true, $audit['issues'][10]['keyword'], 'Legacy global keyword collision was not detected');

assertSameValue(true, $audit['issues'][1]['shared_language'], 'Cross-language match was not detected');
assertSameValue(true, $audit['issues'][2]['shared_language'], 'Cross-language match was not detected');
assertSameValue(true, $audit['issues'][9]['shared_language'], 'Legacy cross-language match was not detected');
assertSameValue(true, $audit['issues'][10]['shared_language'], 'Legacy cross-language match was not detected');

assertSameValue(false, $audit['issues'][10]['orphan'], 'Existing product was incorrectly marked orphaned');
assertSameValue(true, $audit['issues'][11]['orphan'], 'Missing product SEO URL was not marked orphaned');

assertSameValue(array(1, 2, 3, 4, 9, 10), $model->getSeoUrlIssueIds('keyword'), 'Keyword issue filter returned wrong rows');
assertSameValue(array(5, 6), $model->getSeoUrlIssueIds('query'), 'Query issue filter returned wrong rows');
assertSameValue(array(7), $model->getSeoUrlIssueIds('prefix'), 'Prefix issue filter returned wrong rows');
assertSameValue(array(1, 2, 7, 8, 9, 10), $model->getSeoUrlIssueIds('shared_language'), 'Cross-language issue filter returned wrong rows');
assertSameValue(array(11), $model->getSeoUrlIssueIds('orphan'), 'Orphan issue filter returned wrong rows');

assertSameValue('duplicate', $audit['groups']['keyword'][3]['label'], 'Keyword group label changed');
assertSameValue(2, $audit['groups']['keyword'][3]['count'], 'Keyword group count changed');
assertSameValue('product_id=30', $audit['groups']['query'][5]['label'], 'Query group label changed');
assertSameValue(2, $audit['groups']['query'][5]['count'], 'Query group count changed');
assertSameValue('shared', $audit['groups']['shared_language'][1]['label'], 'Cross-language group label changed');
assertSameValue(2, $audit['groups']['shared_language'][1]['count'], 'Cross-language group count changed');

assertSameValue(
	'http://store.test/index.php?route=product/product&product_id=10',
	$model->getSeoUrlPreview($db->seo_urls[0]),
	'Direct product test URL changed'
);
assertSameValue(
	'http://store.test/index.php?route=product/product&product_id=20',
	$model->getSeoUrlPreview($db->seo_urls[1]),
	'Direct product test URL must not depend on language keyword'
);

$sources = $model->getSeoUrlSources(array($db->seo_urls[0], $db->seo_urls[1], $db->seo_urls[2]));
assertSameValue('Тестовый товар', $sources[1]['name'], 'Product source name was not resolved');
assertSameValue('catalog/product/edit', $sources[1]['route'], 'Product source edit route changed');
assertSameValue('Тестовий товар', $sources[2]['name'], 'Language-specific product source name was not resolved');
assertSameValue('Категория', $sources[3]['name'], 'Category source name was not resolved');
assertSameValue(true, $sources[1]['enabled'], 'Enabled product preview state changed');
assertSameValue(false, $sources[2]['enabled'], 'Disabled product preview state was not detected');
assertSameValue(false, $sources[3]['enabled'], 'Disabled category preview state was not detected');

$blog_rows = array(
	array('seo_url_id' => 101, 'store_id' => 0, 'language_id' => 1, 'query' => 'article_id=7', 'keyword' => ''),
	array('seo_url_id' => 102, 'store_id' => 0, 'language_id' => 2, 'query' => 'article_id=7', 'keyword' => ''),
	array('seo_url_id' => 103, 'store_id' => 0, 'language_id' => 1, 'query' => 'blog_category_id=8', 'keyword' => '')
);

$blog_sources = $model->getSeoUrlSources($blog_rows);

assertSameValue('Тестовая статья', $blog_sources[101]['name'], 'Blog article source name was not resolved');
assertSameValue('blog/article/edit', $blog_sources[101]['route'], 'Blog article edit route changed');
assertSameValue('Тестова стаття', $blog_sources[102]['name'], 'Localized blog article source name was not resolved');
assertSameValue('Новости блога', $blog_sources[103]['name'], 'Blog category source name was not resolved');
assertSameValue('blog/category/edit', $blog_sources[103]['route'], 'Blog category edit route changed');
assertSameValue(false, $blog_sources[101]['enabled'], 'Disabled blog article preview state was not detected');
assertSameValue(false, $blog_sources[102]['enabled'], 'Localized disabled blog article preview state changed');
assertSameValue(true, $blog_sources[103]['enabled'], 'Enabled blog category preview state changed');

assertSameValue(
	'testovaya-statya',
	$model->getSeoUrlGeneratorValue($blog_rows[0], $blog_sources[101]['name']),
	'Blog article generator value changed'
);

assertSameValue(
	'uk_testova-stattya',
	$model->getSeoUrlGeneratorValue($blog_rows[1], $blog_sources[102]['name']),
	'Localized blog article generator value changed'
);

assertSameValue(
	'novosti-bloga',
	$model->getSeoUrlGeneratorValue($blog_rows[2], $blog_sources[103]['name']),
	'Blog category generator value changed'
);

$disabled_route = array(
	'seo_url_id' => 104,
	'store_id' => 0,
	'language_id' => 3,
	'query' => 'extension/feed/ocfilter_sitemap',
	'keyword' => ''
);

assertSameValue(
	'en_ocfilter-sitemap',
	$model->getSeoUrlGeneratorValue($disabled_route),
	'Disabled-language plain route generator value changed'
);

$unknown_module_query = array(
	'seo_url_id' => 105,
	'store_id' => 0,
	'language_id' => 1,
	'query' => 'blogcategory_id=1',
	'keyword' => ''
);

assertSameValue(
	'blogcategory',
	$model->getSeoUrlGeneratorValue($unknown_module_query),
	'Unknown module query must use generic generator fallback'
);

echo "SEO URL audit regression checks passed\n";
