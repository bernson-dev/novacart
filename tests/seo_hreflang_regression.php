<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

define('DB_PREFIX', 'oc_');
define('DIR_SYSTEM', dirname(__DIR__) . '/upload/system/');

require DIR_SYSTEM . 'engine/registry.php';
require DIR_SYSTEM . 'library/config.php';
require DIR_SYSTEM . 'library/seolanguage.php';

class SeoHreflangTestDb {
	public function query($sql) {
		$result = new stdClass();
		$result->num_rows = 0;
		$result->row = array();
		$result->rows = array();

		if (strpos($sql, 'FROM oc_language') !== false) {
			$result->rows = array(
				array('language_id' => 1, 'name' => 'Русский', 'code' => 'ru-ru', 'status' => 1),
				array('language_id' => 2, 'name' => 'Українська', 'code' => 'uk-ua', 'status' => 1)
			);
			$result->num_rows = 2;
		}

		return $result;
	}

	public function escape($value) {
		return addslashes((string)$value);
	}
}

class SeoHreflangTestUrl {
	private $config;

	public function __construct($config) {
		$this->config = $config;
	}

	public function link($route, $args = '', $secure = false) {
		$language_id = (int)$this->config->get('config_language_id');
		$default = (string)$this->config->get('config_language');

		$code = $language_id === 2 ? 'uk-ua' : 'ru-ru';
		$prefix = $code === 'uk-ua' ? 'ua' : 'ru';

		$base = 'https://store.test/';

		if ($code !== $default) {
			$base .= $prefix . '/';
		}

		return $base;
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

$config = new Config();
$config->set('config_seo_url', 1);
$config->set('config_seo_language', 1);
$config->set('config_seo_language_prefix', array(
	'ru-ru' => 'ru',
	'uk-ua' => 'ua'
));
$config->set('config_language', 'ru-ru');
$config->set('config_language_id', 2);
$config->set('config_store_id', 0);
$config->set('config_url', 'https://store.test/');
$config->set('config_ssl', 'https://store.test/');

$registry = new Registry();
$registry->set('config', $config);
$registry->set('request', new stdClass());
$registry->set('response', new stdClass());
$registry->set('session', new stdClass());
$registry->set('db', new SeoHreflangTestDb());
$registry->set('url', new SeoHreflangTestUrl($config));

$seo_language = new SeoLanguage($registry);

$links = $seo_language->getAlternateLinks('common/home', array(), true);

assertSameValue('https://store.test/', $links['ru-RU'], 'Default RU hreflang URL');
assertSameValue('https://store.test/ua/', $links['uk-UA'], 'UA hreflang URL');
assertSameValue('https://store.test/', $links['x-default'], 'RU x-default URL');
assertSameValue(2, (int)$config->get('config_language_id'), 'Active language changed after hreflang generation');

$config->set('config_language', 'uk-ua');
$config->set('config_language_id', 1);

$links = $seo_language->getAlternateLinks('common/home', array(), true);

assertSameValue('https://store.test/ru/', $links['ru-RU'], 'RU URL after default language change');
assertSameValue('https://store.test/', $links['uk-UA'], 'Default UA hreflang URL');
assertSameValue('https://store.test/', $links['x-default'], 'UA x-default URL');
assertSameValue(1, (int)$config->get('config_language_id'), 'Active language changed after second hreflang generation');



/*
 * Public rewrite regression: SeoLanguage must be able to work as the second
 * Url rewrite layer, including the storefront root. Internal index.php actions
 * must remain untouched.
 */
$config->set('config_language', 'ru-ru');
$config->set('config_language_id', 2);
$config->set('config_seo_language', 1);
$config->set('config_seo_url', 1);
$config->set('config_seo_language_prefix', array(
	'ru-ru' => 'ru',
	'uk-ua' => 'ua'
));

assertSameValue(
	'https://store.test/ua/',
	html_entity_decode($seo_language->rewrite('https://store.test/'), ENT_QUOTES, 'UTF-8'),
	'Non-default homepage lost its language prefix'
);

assertSameValue(
	'https://store.test/ua/test-product',
	html_entity_decode($seo_language->rewrite('https://store.test/test-product'), ENT_QUOTES, 'UTF-8'),
	'Non-default entity URL lost its language prefix'
);

assertSameValue(
	'https://store.test/index.php?route=common/language/language',
	html_entity_decode($seo_language->rewrite('https://store.test/index.php?route=common/language/language'), ENT_QUOTES, 'UTF-8'),
	'Internal language action was incorrectly prefixed'
);


echo "SEO hreflang regression checks passed\n";
