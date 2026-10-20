<?php
class ControllerExtensionDashboardHealth extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/dashboard/health');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$data = $this->request->post;
			$data['dashboard_health_low_stock_limit'] = max(0, min(1000000, (int)$data['dashboard_health_low_stock_limit']));
			$data['dashboard_health_log_warning_mb'] = max(1, min(1024, (int)$data['dashboard_health_log_warning_mb']));

			$this->model_setting_setting->editSetting('dashboard_health', $data);
			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=dashboard', true));
			return;
		}

		$data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';

		$data['breadcrumbs'] = array(
			array(
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
			),
			array(
				'text' => $this->language->get('text_extension'),
				'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=dashboard', true)
			),
			array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/dashboard/health', 'user_token=' . $this->session->data['user_token'], true)
			)
		);

		$data['action'] = $this->url->link('extension/dashboard/health', 'user_token=' . $this->session->data['user_token'], true);
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=dashboard', true);

		$data['columns'] = array();

		for ($i = 3; $i <= 12; $i++) {
			$data['columns'][] = $i;
		}

		$data['dashboard_health_width'] = $this->settingValue('dashboard_health_width', 12);
		$data['dashboard_health_status'] = $this->settingValue('dashboard_health_status', 1);
		$data['dashboard_health_sort_order'] = $this->settingValue('dashboard_health_sort_order', 0);
		$data['dashboard_health_low_stock_limit'] = $this->settingValue('dashboard_health_low_stock_limit', 5);
		$data['dashboard_health_log_warning_mb'] = $this->settingValue('dashboard_health_log_warning_mb', 10);

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/dashboard/health_form', $data));
	}

	public function dashboard() {
		$this->load->language('extension/dashboard/health');
		$this->load->model('extension/dashboard/health');

		$report = $this->model_extension_dashboard_health->getReport(
			(int)$this->settingValue('dashboard_health_low_stock_limit', 5),
			(int)$this->settingValue('dashboard_health_log_warning_mb', 10)
		);

		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_settings'] = $this->language->get('text_settings');
		$data['settings'] = $this->url->link('extension/dashboard/health', 'user_token=' . $this->session->data['user_token'], true);

		$links = array(
			'system' => $this->url->link('common/developer', 'user_token=' . $this->session->data['user_token'], true),
			'logs' => $this->url->link('tool/log', 'user_token=' . $this->session->data['user_token'], true),
			'catalog' => $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'], true),
			'seo' => $this->url->link('design/seo_url', 'user_token=' . $this->session->data['user_token'], true),
			'security' => $this->url->link('common/security', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['sections'] = array();
		$data['summary'] = array('ok' => 0, 'warning' => 0, 'error' => 0);

		foreach ($report as $section => $items) {
			$section_data = array(
				'code' => $section,
				'title' => $this->language->get('section_' . $section),
				'icon' => $this->sectionIcon($section),
				'items' => array()
			);

			foreach ($items as $item) {
				$state = isset($item['state']) ? $item['state'] : 'info';

				if (isset($data['summary'][$state])) {
					$data['summary'][$state]++;
				}

				$section_data['items'][] = array(
					'code' => $item['code'],
					'label' => $this->language->get('check_' . $item['code']),
					'help' => $this->language->get('check_' . $item['code'] . '_help'),
					'state' => $state,
					'state_text' => $this->language->get('state_' . $state),
					'class' => $this->stateClass($state),
					'value' => isset($item['value']) ? $item['value'] : '',
					'href' => $this->itemLink($section, $item['code'], $links)
				);
			}

			$data['sections'][] = $section_data;
		}

		return $this->load->view('extension/dashboard/health_info', $data);
	}

	public function install() {
		$this->load->model('setting/setting');

		$this->model_setting_setting->editSetting('dashboard_health', array(
			'dashboard_health_status' => 1,
			'dashboard_health_width' => 12,
			'dashboard_health_sort_order' => 0,
			'dashboard_health_low_stock_limit' => 5,
			'dashboard_health_log_warning_mb' => 10
		));
	}

	public function uninstall() {
		$this->load->model('setting/setting');
		$this->model_setting_setting->deleteSetting('dashboard_health');
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/dashboard/health')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	private function settingValue($key, $default) {
		if (isset($this->request->post[$key])) {
			return $this->request->post[$key];
		}

		$value = $this->config->get($key);

		return $value === null ? $default : $value;
	}

	private function sectionIcon($section) {
		$icons = array(
			'system' => 'fa-server',
			'catalog' => 'fa-cubes',
			'seo' => 'fa-link'
		);

		return isset($icons[$section]) ? $icons[$section] : 'fa-info-circle';
	}

	private function stateClass($state) {
		$classes = array(
			'ok' => 'success',
			'warning' => 'warning',
			'error' => 'danger',
			'info' => 'info'
		);

		return isset($classes[$state]) ? $classes[$state] : 'info';
	}

	private function itemLink($section, $code, $links) {
		if ($code === 'error_log') {
			return $links['logs'];
		}

		if ($code === 'storage_location' || $code === 'install_directory') {
			return $links['security'];
		}

		if ($section === 'catalog') {
			return $links['catalog'];
		}

		if ($section === 'seo') {
			return $links['seo'];
		}

		return $links['system'];
	}
}
