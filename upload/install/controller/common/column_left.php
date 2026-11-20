<?php
class ControllerCommonColumnLeft extends Controller {
	public function index() {
		$this->load->language('common/column_left');

		// Step
		$data['text_license']       = $this->language->get('text_license');
		$data['text_installation']  = $this->language->get('text_installation');
		$data['text_configuration'] = $this->language->get('text_configuration');
		$data['text_settings']      = $this->language->get('text_settings');
		$data['text_finished']      = $this->language->get('text_finished');
		$data['text_language']      = $this->language->get('text_language');

		$secure = !empty($this->request->server['HTTPS']) && $this->request->server['HTTPS'] !== 'off';

		$route = isset($this->request->get['route']) ? (string)$this->request->get['route'] : 'install/step_1';

		$data['route']  = $route;

		$data['action'] = $this->url->link('common/column_left/language', '', $secure);

		// Language
		$directories = glob(DIR_LANGUAGE . '*', GLOB_ONLYDIR);
		$directories = $directories ? $directories : array();

		$data['languages'] = array();

		foreach ($directories as $directory) {
			$code = basename($directory);

			$lang = new Language($code);
			$lang->load($code);

			$name = $lang->get('text_language_name');

			$data['languages'][] = array(
				'code' => $code,
				'name' => ($name !== 'text_language_name') ? $name : $code
			);
		}

		usort($data['languages'], function ($a, $b) {
			return strcasecmp($a['name'], $b['name']);
		});

		$available = array_column($data['languages'], 'code');

		$code = isset($this->session->data['language']) ? $this->session->data['language'] : (string)$this->config->get('language_default');

		if (!in_array($code, $available, true)) {
			$code = in_array('en-gb', $available, true) ? 'en-gb' : (string)reset($available);
		}

		$data['code'] = $code;

		// Возврат на текущий шаг
		$url_data = $this->request->get;
		unset($url_data['route']);

		$url = $url_data ? '&' . urldecode(http_build_query($url_data, '', '&')) : '';

		$data['redirect'] = $this->url->link($route, $url, $secure);

		return $this->load->view('common/column_left', $data);
	}

	public function language() {
		if (isset($this->request->post['code'])) {
			$code = basename((string)$this->request->post['code']);

			if (is_dir(DIR_LANGUAGE . $code)) {
				$this->session->data['language'] = $code;
			}
		}

		$redirect = isset($this->request->post['redirect']) ? (string)$this->request->post['redirect'] : '';

		// Только текущий хост
		$host    = isset(parse_url($redirect)['host']) ? strtolower(parse_url($redirect)['host']) : '';
		$current = isset($this->request->server['HTTP_HOST']) ? strtolower($this->request->server['HTTP_HOST']) : '';

		if ($redirect === '' || ($host !== '' && $host !== $current) || strpbrk($redirect, "\r\n") !== false) {
			$redirect = $this->url->link('install/step_1');
		}

		$this->response->redirect($redirect);
	}
}