<?php
// *	@source		See SOURCE.txt for source and other copyright.
// *	@license	GNU General Public License version 3; see LICENSE.txt

class ControllerAffiliateLogin extends Controller {
	private $error = array();

	public function index() {
		if ($this->customer->isLogged()) {
			$this->response->redirect($this->url->link('account/account', '', true));
		}

		$this->load->language('affiliate/login');

		$this->document->setTitle($this->language->get('heading_title'));
		$this->document->setRobots('noindex,follow');

		$this->load->model('account/customer');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if (isset($this->request->post['redirect']) && $this->isSafeRedirect($this->request->post['redirect'])) {
				$this->response->redirect(str_replace('&amp;', '&', $this->request->post['redirect']));
			} else {
				$this->response->redirect($this->url->link('account/account', '', true));
			}
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('account/account', '', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_login'),
			'href' => $this->url->link('affiliate/login', '', true)
		);

		$data['text_description'] = sprintf($this->language->get('text_description'), $this->config->get('config_name'), $this->config->get('config_name'), $this->config->get('config_affiliate_commission') . '%');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['action'] = $this->url->link('affiliate/login', '', true);
		$data['register'] = $this->url->link('affiliate/register', '', true);
		$data['forgotten'] = $this->url->link('account/forgotten', '', true);

		if (isset($this->request->post['redirect']) && $this->isSafeRedirect($this->request->post['redirect'])) {
			$data['redirect'] = (string)$this->request->post['redirect'];
		} elseif (isset($this->session->data['redirect'])) {
			$data['redirect'] = $this->session->data['redirect'];

			unset($this->session->data['redirect']);
		} else {
			$data['redirect'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->request->post['email']) && is_scalar($this->request->post['email'])) {
			$data['email'] = (string)$this->request->post['email'];
		} else {
			$data['email'] = '';
		}

		if (isset($this->request->post['password']) && is_scalar($this->request->post['password'])) {
			$data['password'] = (string)$this->request->post['password'];
		} else {
			$data['password'] = '';
		}

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('affiliate/login', $data));
	}

	protected function validate() {
		$email = isset($this->request->post['email']) && is_scalar($this->request->post['email']) ? trim((string)$this->request->post['email']) : '';
		$password = $this->request->getRawPost('password');

		if ($email === '' || $password === '') {
			$this->error['warning'] = $this->language->get('error_login');
			return false;
		}

		$this->request->post['email'] = $email;

		// Check how many login attempts have been made.
		$login_info = $this->model_account_customer->getLoginAttempts($email);

		if ($login_info && ($login_info['total'] >= $this->config->get('config_login_attempts')) && strtotime('-1 hour') < strtotime($login_info['date_modified'])) {
			$this->error['warning'] = $this->language->get('error_attempts');
		}

		// Check if customer has been approved.
		$customer_info = $this->model_account_customer->getCustomerByEmail($email);

		if ($customer_info && !$customer_info['status']) {
			$this->error['warning'] = $this->language->get('error_approved');
		}

		if (!$this->error) {
			if (!$this->customer->login($email, $password)) {
				$this->error['warning'] = $this->language->get('error_login');

				$this->model_account_customer->addLoginAttempt($email);
			} else {
				$this->model_account_customer->deleteLoginAttempts($email);
			}
		}

		return !$this->error;
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
