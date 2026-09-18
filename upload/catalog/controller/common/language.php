<?php
class ControllerCommonLanguage extends Controller {
	public function index() {
		$this->load->language('common/language');

		$data['action'] = $this->url->link('common/language/language', '', $this->isSecure());
		$data['code'] = $this->session->data['language'];

		$this->load->model('localisation/language');
		$data['languages'] = array();

		$results = $this->model_localisation_language->getLanguages();
		foreach ($results as $result) {
			if ($result['status']) {
				$data['languages'][] = array(
				'name' => $result['name'],
				'code' => $result['code']
				);
			}
		}

		// Формируем параметры редиректа
		if (!isset($this->request->get['route'])) {
			$redirect_data = ['route' => 'common/home', 'params' => '', 'protocol' => $this->isSecure()];
		} else {
			$url_data = $this->request->get;
			unset($url_data['_route_']);

			$route = $url_data['route'];
			unset($url_data['route']);

			$params = '';
			if ($url_data) {
				$params = '&' . urldecode(http_build_query($url_data, '', '&'));
			}

			$redirect_data = ['route' => $route, 'params' => $params, 'protocol' => $this->isSecure()];
		}

		$data['redirect'] = base64_encode(json_encode($redirect_data));

		return $this->load->view('common/language', $data);
	}

	public function language() {
		$this->load->model('localisation/language');
		$languages = $this->model_localisation_language->getLanguages();

		if (isset($this->request->post['code']) && isset($languages[$this->request->post['code']])) {
			$code = $this->request->post['code'];

			$this->session->data['language'] = $code;
			$this->config->set('config_language_id', $languages[$code]['language_id']);
		}

		$redirect_data = array();

		if (isset($this->request->post['redirect'])) {
			$decoded = base64_decode($this->request->post['redirect'], true);

			if ($decoded !== false) {
				$redirect_data = json_decode($decoded, true);

				if (!is_array($redirect_data)) {
					$redirect_data = array();
				}
			}
		}

		$route = isset($redirect_data['route']) ? $redirect_data['route'] : 'common/home';
		$params = isset($redirect_data['params']) && is_string($redirect_data['params']) ? $redirect_data['params'] : '';
		$protocol = isset($redirect_data['protocol']) ? (bool)$redirect_data['protocol'] : $this->isSecure();

		if (!is_string($route) || !preg_match('/^[a-zA-Z0-9_\/]+$/', $route)) {
			$route = 'common/home';
			$params = '';
		}

		// теперь язык уже обновлён, и url->link отдаст правильный SEO URL
		$this->response->redirect($this->url->link($route, $params, $protocol));
	}

	// Вспомогательный метод для проверки HTTPS
	private function isSecure() {
		return (!empty($this->request->server['HTTPS']) && $this->request->server['HTTPS'] !== 'off');
	}
}