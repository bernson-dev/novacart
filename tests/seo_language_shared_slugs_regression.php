<?php
/**
 * Regression checks for language-scoped SEO keywords.
 *
 * Identical keywords are safe across languages only while SEO Language is
 * enabled for that store. With the module disabled, legacy OpenCart
 * per-store uniqueness must remain in force.
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

define('DB_PREFIX', 'oc_');
define('DIR_SYSTEM', dirname(__DIR__) . '/upload/system/');
define('DIR_APPLICATION', dirname(__DIR__) . '/upload/admin/');

require DIR_SYSTEM . 'engine/registry.php';
require DIR_SYSTEM . 'engine/model.php';
require DIR_SYSTEM . 'engine/controller.php';
require DIR_APPLICATION . 'model/design/seo_url.php';
require DIR_APPLICATION . 'controller/design/seo_url.php';

class SeoSharedSlugTestDb {
	public $seo_urls = array();
	public $settings = array();

	public function escape($value) {
		return addslashes((string)$value);
	}

	public function query($sql) {
		$result = new stdClass();
		$result->num_rows = 0;
		$result->row = array();
		$result->rows = array();

		if (strpos($sql, 'FROM `oc_setting`') !== false) {
			$store_ids = $this->extractStoreIds($sql);
			$code = $this->extractString($sql, 'code');
			$key = $this->extractString($sql, 'key');

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

		if (strpos($sql, 'FROM `oc_language`') !== false) {
			$language_id = $this->extractInt($sql, 'language_id');
			$languages = array(
				1 => 'ru-ru',
				2 => 'uk-ua'
			);

			if (isset($languages[$language_id])) {
				$result->row = array('code' => $languages[$language_id]);
				$result->rows = array($result->row);
				$result->num_rows = 1;
			}

			return $result;
		}

		if (strpos($sql, 'FROM `oc_seo_url`') !== false && strpos($sql, '`keyword` = ') !== false) {
			$keyword = $this->extractString($sql, 'keyword');
			$store_id = $this->hasField($sql, 'store_id') ? $this->extractInt($sql, 'store_id') : null;
			$language_id = $this->hasField($sql, 'language_id') ? $this->extractInt($sql, 'language_id') : null;

			foreach ($this->seo_urls as $row) {
				if ((string)$row['keyword'] !== $keyword) {
					continue;
				}

				if ($store_id !== null && (int)$row['store_id'] !== $store_id) {
					continue;
				}

				if ($language_id !== null && (int)$row['language_id'] !== $language_id) {
					continue;
				}

				$result->rows[] = $row;
			}

			$result->num_rows = count($result->rows);
			$result->row = $result->num_rows ? $result->rows[0] : array();
		}

		return $result;
	}

	private function hasField($sql, $field) {
		return strpos($sql, '`' . $field . '` = ') !== false;
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

function callProtected($object, $name, array $args = array()) {
	$method = new ReflectionMethod(get_class($object), $name);
	$method->setAccessible(true);

	return $method->invokeArgs($object, $args);
}

$db = new SeoSharedSlugTestDb();
$db->settings = array(
	array(
		'store_id' => 0,
		'code' => 'config',
		'key' => 'config_seo_url',
		'value' => '1'
	),
	array(
		'store_id' => 0,
		'code' => 'config',
		'key' => 'config_seo_language',
		'value' => '1'
	),
	array(
		'store_id' => 0,
		'code' => 'config',
		'key' => 'config_seo_language_prefix',
		'value' => json_encode(array('ru-ru' => 'ru', 'uk-ua' => 'uk')),
		'serialized' => 1
	),
	array(
		'store_id' => 1,
		'code' => 'config',
		'key' => 'config_seo_url',
		'value' => '1'
	),
	array(
		'store_id' => 1,
		'code' => 'module_seo_language',
		'key' => 'module_seo_language_status',
		'value' => '0'
	),
	array(
		'store_id' => 2,
		'code' => 'config',
		'key' => 'config_seo_url',
		'value' => '0'
	),
	array(
		'store_id' => 2,
		'code' => 'config',
		'key' => 'config_seo_language',
		'value' => '1'
	)
);
$db->seo_urls = array(
	array(
		'seo_url_id' => 10,
		'store_id' => 0,
		'language_id' => 1,
		'query' => 'product_id=100',
		'keyword' => 'shared-slug'
	),
	array(
		'seo_url_id' => 20,
		'store_id' => 1,
		'language_id' => 1,
		'query' => 'product_id=200',
		'keyword' => 'legacy-slug'
	)
);

$registry = new Registry();
$registry->set('db', $db);

$model = new ModelDesignSeoUrl($registry);
$registry->set('model_design_seo_url', $model);

$controller = new ControllerDesignSeoUrl($registry);

assertSameValue(true, $model->usesLanguageScopedKeywords(0), 'Enabled SEO Language store was not language scoped');
assertSameValue(false, $model->usesLanguageScopedKeywords(1), 'Disabled SEO Language store became language scoped');
assertSameValue(false, $model->usesLanguageScopedKeywords(2), 'SEO-disabled store became language scoped');

assertSameValue(true, $model->isReservedLanguagePrefix('uk', 0), 'Configured language prefix was not reserved');
assertSameValue(true, $model->isReservedLanguagePrefix('/RU/', 0), 'Reserved prefix normalization changed');
assertSameValue(false, $model->isReservedLanguagePrefix('shared-slug', 0), 'Normal SEO keyword was treated as a prefix');
assertSameValue(false, $model->isReservedLanguagePrefix('uk', 1), 'Disabled SEO Language store reserved a prefix');
assertSameValue(true, $model->isOwnLanguagePrefix('uk', 0, 2), 'Language did not own its configured prefix');
assertSameValue(false, $model->isOwnLanguagePrefix('uk', 0, 1), 'Language incorrectly owned another language prefix');

assertSameValue(
	false,
	callProtected($controller, 'hasDuplicate', array('keyword', 'shared-slug', 0, 2, 0)),
	'Same keyword in another language must be allowed when SEO Language is enabled'
);

assertSameValue(
	true,
	callProtected($controller, 'hasDuplicate', array('keyword', 'shared-slug', 0, 1, 0)),
	'Same keyword in the same language must remain a conflict'
);

assertSameValue(
	true,
	callProtected($controller, 'hasDuplicate', array('keyword', 'legacy-slug', 1, 2, 0)),
	'Same keyword across languages must remain a conflict when SEO Language is disabled'
);

assertSameValue(
	false,
	callProtected($controller, 'hasDuplicate', array('keyword', 'shared-slug', 0, 1, 10)),
	'Current SEO URL record must be excluded while editing'
);

echo "SEO shared-slug regression checks passed\n";
