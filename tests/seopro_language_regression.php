<?php
/**
 * Regression checks for SeoPro language-home URL rules.
 *
 * These tests intentionally exercise the private helpers through Reflection so
 * a future refactor cannot silently restore dependence on stale common/home
 * rows after config_language changes.
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

define('DIR_SYSTEM', dirname(__DIR__, 2) . '/upload/system/');

require DIR_SYSTEM . 'library/language.php';
require DIR_SYSTEM . 'library/seopro.php';

class SeoLanguageTestConfig {
	private $data = array();

	public function __construct($data) {
		$this->data = $data;
	}

	public function get($key) {
		return isset($this->data[$key]) ? $this->data[$key] : null;
	}

	public function set($key, $value) {
		$this->data[$key] = $value;
	}
}

class SeoLanguageTestDb {
	public $languages = array();

	public function __construct($languages) {
		$this->languages = $languages;
	}

	public function escape($value) {
		return addslashes((string)$value);
	}

	public function query($sql) {
		$result = new stdClass();
		$result->num_rows = 0;
		$result->row = array();
		$result->rows = array();

		if (strpos($sql, 'FROM oc_language') !== false) {
			foreach ($this->languages as $language) {
				if (preg_match("/WHERE language_id = '([0-9]+)'/", $sql, $m)) {
					if ((int)$language['language_id'] === (int)$m[1]) {
						$result->row = $language;
						$result->rows = array($language);
						$result->num_rows = 1;
						return $result;
					}
				}

				if (preg_match("/WHERE code = '([^']+)'/", $sql, $m)) {
					if ($language['code'] === stripslashes($m[1])) {
						$result->row = $language;
						$result->rows = array($language);
						$result->num_rows = 1;
						return $result;
					}
				}
			}

			$result->rows = $this->languages;
			$result->num_rows = count($this->languages);
		}

		return $result;
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

function setPrivateProperty($object, $name, $value) {
	$property = new ReflectionProperty(get_class($object), $name);
	$property->setAccessible(true);
	$property->setValue($object, $value);
}

function callPrivate($object, $name, array $args = array()) {
	$method = new ReflectionMethod(get_class($object), $name);
	$method->setAccessible(true);

	return $method->invokeArgs($object, $args);
}

$languages = array(
	array('language_id' => 1, 'code' => 'ru-ru', 'status' => 1),
	array('language_id' => 2, 'code' => 'uk-ua', 'status' => 1)
);

$seo = (new ReflectionClass('SeoPro'))->newInstanceWithoutConstructor();
$config = new SeoLanguageTestConfig(array(
	'config_language' => 'ru-ru',
	'config_theme' => 'oct_deals'
));
$db = new SeoLanguageTestDb($languages);

setPrivateProperty($seo, 'config', $config);
setPrivateProperty($seo, 'db', $db);

assertSameValue(1, callPrivate($seo, 'getDefaultLanguageId'), 'RU default language id');
assertSameValue('ru', callPrivate($seo, 'getLanguageHomeAlias', array('ru-ru')), 'RU Deals alias');
assertSameValue('ua', callPrivate($seo, 'getLanguageHomeAlias', array('uk-ua')), 'UA Deals alias');

$config->set('config_language', 'uk-ua');
assertSameValue(2, callPrivate($seo, 'getDefaultLanguageId'), 'UA default language id');

$config->set('config_theme', 'default');
assertSameValue('uk', callPrivate($seo, 'getLanguageHomeAlias', array('uk-ua')), 'Standard UK alias');

echo "SeoPro language regression checks passed\n";
