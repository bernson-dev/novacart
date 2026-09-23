<?php
/**
 * Regression checks for native multilingual SEO store settings.
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

define('DB_PREFIX', 'oc_');
define('DIR_SYSTEM', dirname(__DIR__) . '/upload/system/');
define('DIR_APPLICATION', dirname(__DIR__) . '/upload/admin/');

require DIR_SYSTEM . 'engine/registry.php';
require DIR_SYSTEM . 'engine/model.php';
require DIR_APPLICATION . 'model/setting/seo_language.php';

class SeoLanguageSettingsTestDb {
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
			$store_id = $this->extractInt($sql, 'store_id');
			$code = $this->extractString($sql, 'code');

			foreach ($this->settings as $setting) {
				if ((int)$setting['store_id'] !== $store_id || (string)$setting['code'] !== $code) {
					continue;
				}

				$result->rows[] = array(
					'key' => $setting['key'],
					'value' => $setting['value'],
					'serialized' => isset($setting['serialized']) ? (int)$setting['serialized'] : 0
				);
			}

			$result->num_rows = count($result->rows);
			$result->row = $result->num_rows ? $result->rows[0] : array();

			return $result;
		}

		if (strpos($sql, 'FROM oc_seo_url') !== false) {
			return $result;
		}

		return $result;
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

$db = new SeoLanguageSettingsTestDb();
$db->settings = array(
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
		'store_id' => 0,
		'code' => 'module_seo_language',
		'key' => 'module_seo_language_status',
		'value' => '0'
	),
	array(
		'store_id' => 2,
		'code' => 'config',
		'key' => 'config_seo_language',
		'value' => '0'
	),
	array(
		'store_id' => 3,
		'code' => 'module_seo_language',
		'key' => 'module_seo_language_status',
		'value' => '1'
	),
	array(
		'store_id' => 3,
		'code' => 'module_seo_language',
		'key' => 'module_seo_language_prefix',
		'value' => json_encode(array('ru-ru' => 'legacy-ru', 'uk-ua' => 'legacy-uk')),
		'serialized' => 1
	)
);

$registry = new Registry();
$registry->set('db', $db);

$model = new ModelSettingSeoLanguage($registry);

$default = $model->getSettings(0);
assertSameValue(1, $default['status'], 'Native status did not override legacy module status');
assertSameValue('ru', $default['prefix']['ru-ru'], 'Native prefix was not loaded');
assertSameValue('uk', $default['prefix']['uk-ua'], 'Native prefix was not loaded');

$inherited = $model->getSettings(1);
assertSameValue(1, $inherited['status'], 'Additional store did not inherit default status');
assertSameValue('uk', $inherited['prefix']['uk-ua'], 'Additional store did not inherit default prefixes');

$disabled = $model->getSettings(2);
assertSameValue(0, $disabled['status'], 'Explicit store status did not override inherited status');
assertSameValue('uk', $disabled['prefix']['uk-ua'], 'Missing store prefixes did not inherit defaults');

$legacy = $model->getSettings(3);
assertSameValue(1, $legacy['status'], 'Legacy module status fallback failed');
assertSameValue('legacy-ru', $legacy['prefix']['ru-ru'], 'Legacy module prefix fallback failed');

$normalized = $model->normalizePrefixes(array(
	'ru-ru' => ' /RU/ ',
	'uk-ua' => 'UK',
	'bad' => array('x')
));
assertSameValue('ru', $normalized['ru-ru'], 'Prefix normalization changed');
assertSameValue('uk', $normalized['uk-ua'], 'Prefix normalization changed');
assertSameValue(false, isset($normalized['bad']), 'Non-scalar prefix was not ignored');

echo "SEO language settings regression checks passed\n";
