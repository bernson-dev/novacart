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
echo "SEO URL history regression: OK\n";
