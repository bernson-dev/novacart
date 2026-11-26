<?php
class ControllerCronSeoNotFound extends Controller {
	public function index($args = array()) {
		if (!defined('NOVACART_CRON_ENTRY')) {
			http_response_code(403);
			return array('success' => false, 'message' => 'Forbidden');
		}
		require_once DIR_SYSTEM . 'library/novacart/seo_not_found.php';
		$service = new \Novacart\SeoNotFound($this->db);
		if (!$service->isAvailable()) {
			return array('success' => true, 'message' => 'SEO 404 journal table not installed.');
		}
		// The journal settings are global, regardless of the store used to run cron.
		$this->load->model('setting/setting');
		$settings = $this->model_setting_setting->getSetting('seo_not_found', 0);
		$days = \Novacart\SeoNotFound::retentionDays($settings['seo_not_found_retention_days'] ?? null);
		$deleted = $service->prune($days);
		return array('success' => true, 'message' => 'SEO 404 journal: deleted ' . $deleted . ' entries older than ' . $days . ' days.', 'deleted' => $deleted);
	}
}
