<?php
// *	@source		See SOURCE.txt for source and other copyright.
// *	@license	GNU General Public License version 3; see LICENSE.txt

class ControllerCommonHeader extends Controller {
	public function index() {
		// Analytics
		$this->load->model('setting/extension');

		$data['analytics'] = array();

		$analytics = $this->model_setting_extension->getExtensions('analytics');

		foreach ($analytics as $analytic) {
			if ($this->config->get('analytics_' . $analytic['code'] . '_status')) {
				$data['analytics'][] = $this->load->controller('extension/analytics/' . $analytic['code'], $this->config->get('analytics_' . $analytic['code'] . '_status'));
			}
		}

		if ($this->request->server['HTTPS']) {
			$server = $this->config->get('config_ssl');
		} else {
			$server = $this->config->get('config_url');
		}

		// Favicon markup is injected by the built-in catalog/view/common/header/after event.

		$data['title'] = $this->document->getTitle();

		$data['base'] = $server;
		$data['description'] = $this->document->getDescription();
		$data['keywords'] = $this->document->getKeywords();
		$data['links'] = $this->document->getLinks();
		$data['robots'] = $this->document->getRobots();
		$data['styles'] = $this->document->getStyles();

		$data['scripts'] = $this->document->getScripts('header');
		$data['lang'] = $this->language->get('code');
		$data['direction'] = $this->language->get('direction');

		$data['name'] = $this->config->get('config_name');

		// Подключаем хелпер
		require_once(DIR_SYSTEM . 'helper/logo.php');
		// Проверяем наличие загруженного логотипа
		if ($this->config->get('config_logo') && is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
			$data['logo'] = $server . 'image/' . $this->config->get('config_logo');
		} else {
			// Выбираем режим из конфига
			$logo_fallback_mode = $this->config->get('config_logo_fallback_mode') ?? 'initials';
			/**
			* Генерация SVG логотипа для fallback режима
			*
			* Функция возвращает строку‑URI вида `data:image/svg+xml;charset=UTF-8,...`,
			* которую можно напрямую использовать в <img src="..."> или <link rel="icon">.
			*
			* @param string $store_name   Название магазина (обычно $this->config->get('config_name'))
			* @param string $mode         Режим генерации:
			*                             - 'initials' — логотип с инициалами
			*                             - 'name'     — логотип с полным названием
			* @param array  $options      Дополнительные параметры:
			*                             - width (int)       Ширина SVG (по умолчанию 220 для name, 64 для initials)
			*                             - height (int)      Высота SVG (по умолчанию 60 для name, 64 для initials)
			*                             - grad_start (hex)  Цвет начала градиента (по умолчанию #1E88E5)
			*                             - grad_end (hex)    Цвет конца градиента (по умолчанию #42A5F5)
			*
			* @return string              Готовый data:image/svg+xml URI
			*
			* @example
			* // Логотип с инициалами
			* $logo = generateStoreLogo('NovaCart', 'initials');
			*
			* @example
			* // Логотип с названием и кастомной палитрой
			* $logo = generateStoreLogo('NovaCart', 'name', [
			*     'width'      => 240,
			*     'height'     => 70,
			*     'grad_start' => '#FF5722',
			*     'grad_end'   => '#FFC107'
			* ]);
			*/

			$data['logo'] = generateStoreLogo(
			(string)$this->config->get('config_name'),
			$logo_fallback_mode
			);
		}

		$this->load->language('common/header');

		$host = isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1')) ? HTTPS_SERVER : HTTP_SERVER;
		if ($this->request->server['REQUEST_URI'] == '/') {
			$data['og_url'] = $this->url->link('common/home');
		} else {
			$data['og_url'] = $host . substr($this->request->server['REQUEST_URI'], 1, (strlen($this->request->server['REQUEST_URI']) - 1));
		}

		$data['og_image'] = $this->document->getOgImage();

		// Wishlist
		if ($this->customer->isLogged()) {
			$this->load->model('account/wishlist');

			$data['text_wishlist'] = sprintf($this->language->get('text_wishlist'), $this->model_account_wishlist->getTotalWishlist());
		} else {
			$data['text_wishlist'] = sprintf($this->language->get('text_wishlist'), (isset($this->session->data['wishlist']) ? count($this->session->data['wishlist']) : 0));
		}

		// Product comparison counter for the header shortcut.
		$data['text_compare'] = sprintf(
			$this->language->get('text_compare'),
			isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0
		);

		$data['text_logged'] = sprintf($this->language->get('text_logged'), $this->url->link('account/account', '', true), $this->customer->getFirstName(), $this->url->link('account/logout', '', true));

		$data['home'] = $this->url->link('common/home');
		$data['wishlist'] = $this->url->link('account/wishlist', '', true);
		$data['compare'] = $this->url->link('product/compare');
		$data['logged'] = $this->customer->isLogged();
		$data['account'] = $this->url->link('account/account', '', true);
		$data['register'] = $this->url->link('account/register', '', true);
		$data['login'] = $this->url->link('account/login', '', true);
		$data['order'] = $this->url->link('account/order', '', true);
		$data['transaction'] = $this->url->link('account/transaction', '', true);
		$data['download'] = $this->url->link('account/download', '', true);
		$data['logout'] = $this->url->link('account/logout', '', true);
		$data['shopping_cart'] = $this->url->link('checkout/cart');
		$data['checkout'] = $this->url->link('checkout/checkout', '', true);
		$data['contact'] = $this->url->link('information/contact');
		$data['telephone'] = $this->config->get('config_telephone');
		$data['current_route'] = isset($this->request->get['route']) && is_scalar($this->request->get['route'])
			? (string)$this->request->get['route']
			: 'common/home';

		$data['stock_popup_enabled'] = false;
		$data['stock_popup_mode'] = (string)$this->config->get('config_stock_popup_mode');

		if (!$data['stock_popup_mode']) {
			$data['stock_popup_mode'] = 'checkout';
		}

		if ($this->config->get('config_stock_popup_status')) {
			$mode = $data['stock_popup_mode'];

			if (!$mode) {
				$mode = 'checkout';
			}

			if ($mode === 'all') {
				$data['stock_popup_enabled'] = true;
			} elseif ($mode === 'checkout') {
				$data['stock_popup_enabled'] = (strpos($data['current_route'], 'checkout/') === 0);
			} elseif ($mode === 'routes') {
				$config_routes = trim((string)$this->config->get('config_stock_popup_routes'));

				if ($config_routes !== '') {
					$routes = preg_split('/[\r\n,]+/', $config_routes, -1, PREG_SPLIT_NO_EMPTY);
					$routes = array_map('trim', $routes);
					$data['stock_popup_enabled'] = in_array($data['current_route'], $routes, true);
				}
			}
		}

		$data['language'] = $this->load->controller('common/language');
		$data['currency'] = $this->load->controller('common/currency');

		if ($this->config->get('configblog_blog_menu')) {
			$data['blog_menu'] = $this->load->controller('blog/menu');
		} else {
			$data['blog_menu'] = '';
		}

		$data['search'] = $this->load->controller('common/search');
		$data['cart'] = $this->load->controller('common/cart');
		$data['menu'] = $this->load->controller('common/menu');

		return $this->load->view('common/header', $data);
	}
}
