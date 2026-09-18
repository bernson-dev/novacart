<?php
class ControllerCommonCurrency extends Controller {
	public function index() {
		$this->load->language('common/currency');

		$data['action'] = $this->url->link('common/currency/currency', '', $this->request->server['HTTPS']);

		$data['code'] = $this->session->data['currency'];

		$this->load->model('localisation/currency');

		$data['currencies'] = array();

		$results = $this->model_localisation_currency->getCurrencies();

		foreach ($results as $result) {
			if ($result['status']) {
				$data['currencies'][] = array(
					'title'        => $result['title'],
					'code'         => $result['code'],
					'symbol_left'  => $result['symbol_left'],
					'symbol_right' => $result['symbol_right']
				);
			}
		}

		if (!isset($this->request->get['route'])) {
			$data['redirect'] = $this->url->link('common/home');
		} else {
			$url_data = $this->request->get;

			unset($url_data['_route_']);

			$route = $url_data['route'];

			unset($url_data['route']);

			$url = '';

			if ($url_data) {
				$url = '&' . urldecode(http_build_query($url_data, '', '&'));
			}

			$data['redirect'] = $this->url->link($route, $url, $this->request->server['HTTPS']);
		}

		return $this->load->view('common/currency', $data);
	}

	public function currency() {
		if (isset($this->request->post['code'])) {
			$this->load->model('localisation/currency');

			$currencies = $this->model_localisation_currency->getCurrencies();
			$code = $this->request->post['code'];

			if (isset($currencies[$code])) {
				$this->session->data['currency'] = $code;

				unset($this->session->data['shipping_method']);
				unset($this->session->data['shipping_methods']);
				unset($this->session->data['payment_method']);
				unset($this->session->data['payment_methods']);
			}
		}

		if (isset($this->request->post['redirect']) && $this->isSafeRedirect($this->request->post['redirect'])) {
			$this->response->redirect(html_entity_decode($this->request->post['redirect'], ENT_QUOTES, 'UTF-8'));
		} else {
			$this->response->redirect($this->url->link('common/home'));
		}
	}

	protected function isSafeRedirect($url) {
		if (!is_string($url) || $url === '') {
			return false;
		}

		$url = html_entity_decode($url, ENT_QUOTES, 'UTF-8');
		$target = parse_url($url);

		if ($target === false) {
			return false;
		}

		if (empty($target['host'])) {
			return isset($url[0]) && $url[0] === '/' && (!isset($url[1]) || $url[1] !== '/');
		}

		if (!empty($target['user']) || !empty($target['pass'])) {
			return false;
		}

		$scheme = isset($target['scheme']) ? strtolower($target['scheme']) : '';

		if ($scheme !== 'http' && $scheme !== 'https') {
			return false;
		}

		$target_port = isset($target['port']) ? (int)$target['port'] : ($scheme === 'https' ? 443 : 80);
		$target_origin = $scheme . '://' . strtolower($target['host']) . ':' . $target_port;
		$allowed_origins = array();

		foreach (array($this->config->get('config_url'), $this->config->get('config_ssl')) as $store_url) {
			$store = parse_url($store_url);

			if (!$store || empty($store['host']) || empty($store['scheme'])) {
				continue;
			}

			$store_scheme = strtolower($store['scheme']);

			if ($store_scheme !== 'http' && $store_scheme !== 'https') {
				continue;
			}

			$store_port = isset($store['port']) ? (int)$store['port'] : ($store_scheme === 'https' ? 443 : 80);
			$allowed_origins[] = $store_scheme . '://' . strtolower($store['host']) . ':' . $store_port;
		}

		return in_array($target_origin, array_unique($allowed_origins), true);
	}
}
