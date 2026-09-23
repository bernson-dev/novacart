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

		/*
		 * Preserve the current entity when switching language.
		 *
		 * On an SEO URL the original "route" parameter may already be absent by
		 * the time this controller renders. Falling back directly to common/home
		 * therefore sends product/category/article pages to the homepage.
		 * Reconstruct the route from the decoded entity parameters instead.
		 */
		$url_data = $this->request->get;
		unset($url_data['_route_']);

		$route = isset($url_data['route']) && is_scalar($url_data['route'])
			? (string)$url_data['route']
			: $this->resolveCurrentRoute($url_data);

		unset($url_data['route']);

		if (!preg_match('/^[a-zA-Z0-9_\/]+$/', $route)) {
			$route = 'common/home';
			$url_data = array();
		}

		$params = '';

		if ($url_data) {
			$params = '&' . urldecode(http_build_query($url_data, '', '&'));
		}

		$redirect_data = array(
			'route' => $route,
			'params' => $params,
			'protocol' => $this->isSecure()
		);

		$data['redirect'] = base64_encode(json_encode($redirect_data));

		return $this->load->view('common/language', $data);
	}

	public function language() {
		$this->load->model('localisation/language');
		$languages = $this->model_localisation_language->getLanguages();
		$code = '';

		if (isset($this->request->post['code']) && is_scalar($this->request->post['code'])) {
			$code = (string)$this->request->post['code'];

			if (!isset($languages[$code]) || empty($languages[$code]['status'])) {
				$code = '';
			}
		}

		if ($code !== '') {
			$this->session->data['language'] = $code;
			$this->config->set('config_language_id', $languages[$code]['language_id']);

			// Persist the explicit user choice immediately. The following GET
			// request will also confirm it, but writing the cookie here avoids
			// losing the preference if the redirect target changes.
			if (PHP_SAPI !== 'cli' && !headers_sent()) {
				setcookie('language', $code, time() + 60 * 60 * 24 * 30, '/');
			}
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

	/**
	 * Recover the logical OpenCart route from parameters produced by SEO URL
	 * decoding. This keeps language switching on the same page even when the
	 * original pretty URL no longer has an explicit route parameter.
	 */
	private function resolveCurrentRoute($url_data) {
		if (!is_array($url_data)) {
			return 'common/home';
		}

		if (isset($url_data['product_id'])) {
			return 'product/product';
		}

		if (isset($url_data['path'])) {
			return 'product/category';
		}

		if (isset($url_data['manufacturer_id'])) {
			return 'product/manufacturer/info';
		}

		if (isset($url_data['information_id'])) {
			return 'information/information';
		}

		if (isset($url_data['article_id'])) {
			return 'blog/article';
		}

		if (isset($url_data['blog_category_id'])) {
			return 'blog/category';
		}

		return 'common/home';
	}


	// Вспомогательный метод для проверки HTTPS
	private function isSecure() {
		return (!empty($this->request->server['HTTPS']) && strtolower((string)$this->request->server['HTTPS']) !== 'off');
	}
}