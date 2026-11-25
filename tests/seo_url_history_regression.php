<?php
require dirname(__DIR__) . '/upload/system/library/novacart/seo_url_history.php';

use Novacart\SeoUrlHistory;

function historyRow($keyword, $language = 1, $store = 0, $query = 'product_id=42') {
	return array('store_id' => $store, 'language_id' => $language, 'query' => $query, 'keyword' => $keyword);
}

function expectHistory($expected, $before, $after) {
	$actual = SeoUrlHistory::replacements($before, $after);
	if ($actual !== $expected) {
		throw new RuntimeException('Unexpected SEO history: ' . json_encode($actual));
	}
}

$old = historyRow('old');
$new = historyRow('new');
expectHistory(array($old), array($old), array($new));
expectHistory(array(), array($old), array($old));
expectHistory(array(), array($old), array());
expectHistory(array(), array($old), array(historyRow('new', 2)));
expectHistory(array(), array($old), array(historyRow('new', 1, 2)));
expectHistory(array(), array($old), array(historyRow('new', 1, 0, 'product_id=43')));
expectHistory(array(), array(historyRow('')), array($new));
expectHistory(array($old), array($old, historyRow('old', 2)), array($new, historyRow('old', 2)));
expectHistory(array(), array($old), array($old, $new));
expectHistory(array($old), array($old), array(historyRow('0')));
function expectRoute($expected, $queries) {
	if (SeoUrlHistory::routeFromQueries($queries) !== $expected) {
		throw new RuntimeException('Unexpected historical route: ' . json_encode($queries));
	}
}

expectRoute(array('route' => 'product/product', 'params' => array('product_id' => 42)), array('product_id=42'));
expectRoute(array('route' => 'product/product', 'params' => array('path' => '2_5', 'product_id' => 42)), array('category_id=2', 'category_id=5', 'product_id=42'));
expectRoute(array('route' => 'blog/article', 'params' => array('blog_category_id' => '3', 'article_id' => 7)), array('blog_category_id=3', 'article_id=7'));
expectRoute(array(), array('product_id=42', 'product_id=43'));
expectRoute(array(), array('category_id=2', 'article_id=7'));
expectRoute(array(), array('account/login'));
expectRoute(array(), array('product_id=0'));
echo "SEO URL history regression: OK\n";
