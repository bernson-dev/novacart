<?php
class ControllerCronSeoSitemap extends Controller {
	public function index($args = array()) {
		if (!defined('NOVACART_CRON_ENTRY') || !NOVACART_CRON_ENTRY) {
			http_response_code(403);
			return array('success' => false, 'message' => 'Forbidden');
		}
		require_once DIR_SYSTEM . 'library/novacart/sitemap.php';
		$count = \Novacart\Sitemap::clearCache(true);
		return array('success' => true, 'message' => 'Sitemap cache: removed ' . $count . ' expired files.');
	}
}
