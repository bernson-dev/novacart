<?php
class ControllerExtensionDashboardMap extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/dashboard/map');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('dashboard_map', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=dashboard', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=dashboard', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/dashboard/map', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/dashboard/map', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=dashboard', true);

		if (isset($this->request->post['dashboard_map_width'])) {
			$data['dashboard_map_width'] = $this->request->post['dashboard_map_width'];
		} else {
			$data['dashboard_map_width'] = $this->config->get('dashboard_map_width');
		}

		$data['columns'] = array();

		for ($i = 3; $i <= 12; $i++) {
			$data['columns'][] = $i;
		}

		if (isset($this->request->post['dashboard_map_status'])) {
			$data['dashboard_map_status'] = $this->request->post['dashboard_map_status'];
		} else {
			$data['dashboard_map_status'] = $this->config->get('dashboard_map_status');
		}

		if (isset($this->request->post['dashboard_map_sort_order'])) {
			$data['dashboard_map_sort_order'] = $this->request->post['dashboard_map_sort_order'];
		} else {
			$data['dashboard_map_sort_order'] = $this->config->get('dashboard_map_sort_order');
		}

		$data['dashboard_map_map_type'] = isset($this->request->post['dashboard_map_map_type']) ? $this->request->post['dashboard_map_map_type'] : (in_array($this->config->get('dashboard_map_map_type'), array('world', 'ukraine', 'ukraine_geo'), true) ? $this->config->get('dashboard_map_map_type') : 'world');
		$data['entry_map_type'] = $this->language->get('entry_map_type');
		$data['text_map_world'] = $this->language->get('text_map_world');
		$data['text_map_ukraine'] = $this->language->get('text_map_ukraine');
		$data['text_map_ukraine_geo'] = $this->language->get('text_map_ukraine_geo');

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/dashboard/map_form', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/dashboard/map')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function dashboard() {
		$this->load->language('extension/dashboard/map');
		$map_type = in_array($this->config->get('dashboard_map_map_type'), array('world', 'ukraine', 'ukraine_geo'), true) ? $this->config->get('dashboard_map_map_type') : 'world';
		if ($map_type === 'ukraine_geo') {
			$this->document->addScript('view/javascript/map/novacart-ukraine-map.js?v=3');
			$this->document->addScript('view/javascript/map/novacart-ukraine-geography-data.js?v=1');
			$this->document->addScript('view/javascript/map/novacart-ukraine-geography.js?v=1');
		} elseif ($map_type === 'ukraine') {
			$this->document->addScript('view/javascript/map/novacart-ukraine-map.js?v=2');
		} else {
			$this->document->addScript('view/javascript/map/novacart-world-map-data.js?v=novacart-1');
			$this->document->addScript('view/javascript/map/novacart-world-map.js?v=novacart-1');
		}

		$data['user_token'] = $this->session->data['user_token'];
		$data['map_type'] = $map_type;
		$data['regions'] = array();
		$data['countries'] = array();
		$this->load->model('extension/dashboard/map');

		if ($map_type !== 'world') {
			foreach ($this->model_extension_dashboard_map->getTotalOrdersByUkraineZone() as $row) {
				$data['regions'][] = array('code' => (int)$row['zone_id'], 'name' => $row['name'], 'total' => (int)$row['total'], 'amount' => $this->currency->format((float)$row['amount'], $this->config->get('config_currency')));
			}
			usort($data['regions'], function($a, $b) { return $b['total'] - $a['total']; });
		} else {
			foreach ($this->model_extension_dashboard_map->getTotalOrdersByCountry() as $row) {
				$iso = isset($row['iso_code_2']) ? strtolower((string)$row['iso_code_2']) : '';
				if (!preg_match('/^[a-z]{2}$/', $iso)) {
					continue;
				}
				$data['countries'][] = array(
				'name' => isset($row['name']) ? $row['name'] : strtoupper($iso),
				'iso_code_2' => strtoupper($iso),
				'total' => (int)$row['total'],
				'amount' => $this->currency->format((float)$row['amount'], $this->config->get('config_currency'))
				);
			}
		}
		usort($data['countries'], function($a, $b) {
			return $b['total'] - $a['total'];
		});

		$values = array();
		foreach ($data['countries'] as $country) {
			$values[strtolower($country['iso_code_2'])] = $country['total'];
		}
		if ($map_type !== 'world') {
			$values = array();
			foreach ($data['regions'] as $region) {
				$values[(string)$region['name']] = array('total' => $region['total'], 'amount' => $region['amount']);
			}
		}
		$data['map_values_json'] = json_encode($values, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
		$data['text_map_controls'] = $this->language->get('text_map_controls');
		$data['text_zoom_in'] = $this->language->get('text_zoom_in');
		$data['text_zoom_out'] = $this->language->get('text_zoom_out');
		$data['text_map_reset'] = $this->language->get('text_map_reset');
		$data['text_country'] = $this->language->get('text_country');
		$data['text_region'] = $this->language->get('text_region');
		$data['text_map_schematic'] = $this->language->get('text_map_schematic');
		$data['heading_title'] = $this->language->get($map_type === 'ukraine_geo' ? 'text_map_ukraine_geo' : ($map_type === 'ukraine' ? 'text_map_ukraine' : 'heading_title'));
		$data['text_no_data'] = $this->language->get('text_no_data');
		return $this->load->view('extension/dashboard/map_info', $data);
	}

	public function map() {
		$json = array();

		$this->load->model('extension/dashboard/map');

		$results = $this->model_extension_dashboard_map->getTotalOrdersByCountry();

		foreach ($results as $result) {
			$json[strtolower($result['iso_code_2'])] = array(
				'total'  => $result['total'],
				'amount' => $this->currency->format($result['amount'], $this->config->get('config_currency'))
			);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
