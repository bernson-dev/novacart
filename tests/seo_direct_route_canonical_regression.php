<?php
/**
 * Regression checks for canonical redirects from direct index.php routes
 * when standard seo_url works together with SEO Language.
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

define('DB_PREFIX', 'oc_');
define('DIR_SYSTEM', dirname(__DIR__) . '/upload/system/');
define('DIR_APPLICATION', dirname(__DIR__) . '/upload/catalog/');

require DIR_SYSTEM . 'engine/registry.php';
require DIR_SYSTEM . 'engine/controller.php';
require DIR_SYSTEM . 'library/config.php';
require DIR_SYSTEM . 'library/seolanguage.php';
require DIR_APPLICATION . 'controller/startup/seo_url.php';

class SeoCanonicalTestDb {
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

		if (strpos($sql, 'FROM oc_seo_url') !== false && strpos($sql, "product_id=50") !== false) {
			$result->row = array(
				'keyword' => 'test-product',
				'query' => 'product_id=50'
			);
			$result->rows = array($result->row);
			$result->num_rows = 1;
		}

		return $result;
	}

	public function escape($value) {
		return addslashes((string)$value);
	}
}

class SeoCanonicalTestUrl {
	private $config;

	public function __construct($config) {
		$this->config = $config;
	}

	public function addRewrite($rewrite) {
		$this->rewrite = $rewrite;
	}

	public function link($route, $args = '', $secure = false) {
		parse_str(str_replace('&amp;', '&', (string)$args), $params);

		if ($route === 'product/product' && isset($params['product_id']) && (int)$params['product_id'] === 50) {
			$target = (int)$this->config->get('config_language_id') === 2
				? 'http://store.test/uk/test-product'
				: 'http://store.test/test-product';

			unset($params['product_id']);

			if ($params) {
				$target .= '?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
			}

			return $target;
		}

		return 'http://store.test/index.php?route=' . $route . ($args !== '' ? '&' . $args : '');
	}
}

class SeoCanonicalTestResponse {
	public $redirect_url = '';
	public $redirect_status = 0;

	public function redirect($url, $status = 302) {
		$this->redirect_url = $url;
		$this->redirect_status = $status;
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

function callPrivate($object, $name) {
	$method = new ReflectionMethod(get_class($object), $name);
	$method->setAccessible(true);

	return $method->invoke($object);
}

$config = new Config();
$config->set('config_seo_url', 1);
$config->set('config_seo_pro', 0);
$config->set('config_seo_language', 1);
$config->set('config_seo_language_prefix', array(
	'ru-ru' => 'ru',
	'uk-ua' => 'uk'
));
$config->set('config_language', 'ru-ru');
$config->set('config_language_id', 1);
$config->set('config_store_id', 0);
$config->set('config_url', 'http://store.test/');
$config->set('config_ssl', 'https://store.test/');

$request = new stdClass();
$request->get = array(
	'route' => 'product/product',
	'product_id' => 50
);
$request->post = array();
$request->server = array(
	'REQUEST_METHOD' => 'GET',
	'REQUEST_URI' => '/index.php?route=product/product&product_id=50',
	'HTTPS' => ''
);

$response = new SeoCanonicalTestResponse();
$db = new SeoCanonicalTestDb();
$url = new SeoCanonicalTestUrl($config);

$registry = new Registry();
$registry->set('config', $config);
$registry->set('request', $request);
$registry->set('response', $response);
$registry->set('session', new stdClass());
$registry->set('db', $db);
$registry->set('url', $url);

$seo_language = new SeoLanguage($registry);
$registry->set('seo_language', $seo_language);

$controller = new ControllerStartupSeoUrl($registry);

assertSameValue(
	'http://store.test/test-product',
	callPrivate($controller, 'getDirectRouteCanonicalUrl'),
	'Default-language direct route was not canonicalized'
);

$config->set('config_seo_language', 0);

assertSameValue(
	'http://store.test/test-product',
	callPrivate($controller, 'getDirectRouteCanonicalUrl'),
	'Direct route must remain canonicalized when SEO Language is disabled'
);

$config->set('config_seo_language', 1);
$config->set('config_language_id', 2);

assertSameValue(
	'http://store.test/uk/test-product',
	callPrivate($controller, 'getDirectRouteCanonicalUrl'),
	'Non-default-language direct route lost its language prefix'
);

$request->get['utm_source'] = 'campaign';
$request->server['REQUEST_URI'] = '/index.php?route=product/product&product_id=50&utm_source=campaign';

assertSameValue(
	'http://store.test/uk/test-product?utm_source=campaign',
	callPrivate($controller, 'getDirectRouteCanonicalUrl'),
	'Direct route canonicalization lost unrelated query parameters'
);

unset($request->get['utm_source']);
$request->server['REQUEST_URI'] = '/index.php?route=product/product&product_id=50';

$config->set('config_seo_url', 0);

assertSameValue(
	'',
	callPrivate($controller, 'getDirectRouteCanonicalUrl'),
	'Disabled standard SEO URLs must not trigger canonical redirect'
);

$config->set('config_seo_url', 1);
$request->server['REQUEST_METHOD'] = 'POST';

assertSameValue(
	'',
	callPrivate($controller, 'getDirectRouteCanonicalUrl'),
	'POST request must never be canonicalized'
);

$request->server['REQUEST_METHOD'] = 'GET';
$request->server['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

assertSameValue(
	'',
	callPrivate($controller, 'getDirectRouteCanonicalUrl'),
	'Ajax request must never be canonicalized'
);

unset($request->server['HTTP_X_REQUESTED_WITH']);
$request->server['REQUEST_URI'] = '/test-product';

assertSameValue(
	'',
	callPrivate($controller, 'getDirectRouteCanonicalUrl'),
	'Already rewritten SEO path must not redirect'
);



/*
 * Standard seo_url must keep homepage language URLs unique even when the full
 * language-prefix mode is disabled: default language uses '/', secondary
 * language uses its stable home alias.
 */
$config->set('config_seo_language', 0);
$config->set('config_seo_pro', 0);
$config->set('config_language', 'ru-ru');
$config->set('config_language_id', 1);

assertSameValue(
	'http://store.test/',
	$controller->rewrite('http://store.test/index.php?route=common/home'),
	'Default-language homepage must stay at the store root'
);

$config->set('config_language_id', 2);

assertSameValue(
	'http://store.test/uk/',
	$controller->rewrite('http://store.test/index.php?route=common/home'),
	'Secondary-language homepage must have a unique alias with standard seo_url'
);


echo "SEO direct-route canonical regression checks passed\n";
