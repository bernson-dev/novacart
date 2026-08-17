<?php
// admin/controller/extension/currency/privatbank.php
class ControllerExtensionCurrencyPrivatbank extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/currency/privatbank');
		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$settings = array(
			'currency_privatbank_status'     => $this->request->post['currency_privatbank_status'],
			'currency_privatbank_api_choice' => $this->request->post['currency_privatbank_api_choice']
			);

			$this->model_setting_setting->editSetting('currency_privatbank', $settings);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=currency', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array(
		array(
		'text' => $this->language->get('text_home'),
		'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		),
		array(
		'text' => $this->language->get('text_extension'),
		'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=currency', true)
		),
		array(
		'text' => $this->language->get('heading_title'),
		'href' => $this->url->link('extension/currency/privatbank', 'user_token=' . $this->session->data['user_token'], true)
		)
		);

		$data['action'] = $this->url->link('extension/currency/privatbank', 'user_token=' . $this->session->data['user_token'], true);
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=currency', true);
		$data['refresh'] = $this->url->link('extension/currency/privatbank/refresh', 'user_token=' . $this->session->data['user_token'], true);

		$data['api_options'] = array(
		'cash' => array(
		'name' => $this->language->get('text_cash'),
		'url'  => 'https://api.privatbank.ua/p24api/pubinfo?json&exchange&coursid=5'
		),
		'non_cash' => array(
		'name' => $this->language->get('text_non_cash'),
		'url'  => 'https://api.privatbank.ua/p24api/pubinfo?json&exchange&coursid=11'
		)
		);

		if (isset($this->request->post['currency_privatbank_status'])) {
			$data['currency_privatbank_status'] = $this->request->post['currency_privatbank_status'];
		} else {
			$data['currency_privatbank_status'] = $this->config->get('currency_privatbank_status');
		}

		if (isset($this->request->post['currency_privatbank_api_choice'])) {
			$data['currency_privatbank_api_choice'] = $this->request->post['currency_privatbank_api_choice'];
		} else {
			$data['currency_privatbank_api_choice'] = $this->config->get('currency_privatbank_api_choice');
		}

		$data['text_edit'] = $this->language->get('text_edit');

		$data['text_information'] = $this->language->get('text_information');
		$data['text_information'] = str_replace('%1', $this->url->link('localisation/currency', 'user_token=' . $this->session->data['user_token'], true), $data['text_information']);
		$setting_link_clean = html_entity_decode($this->url->link('setting/setting', 'user_token=' . $this->session->data['user_token'], true));

		$data['text_information'] = str_replace(
		'%2',
		$setting_link_clean . '#tab-local',
		$data['text_information']
		);

		$data['currency_privatbank_cron'] = 'curl -s &quot;' . HTTPS_CATALOG . 'index.php?route=extension/currency/privatbank/refresh&quot;';

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/currency/privatbank', $data));
	}

	public function refresh() {
		$this->load->language('extension/currency/privatbank');
		$this->load->model('extension/currency/privatbank');

		$result = $this->model_extension_currency_privatbank->refresh();

		if (!empty($result['success'])) {
			$this->session->data['success'] = $this->language->get($result['message']);
		} else {
			$this->session->data['warning'] = $this->language->get($result['message']);
		}

		$this->response->redirect($this->url->link('localisation/currency', 'user_token=' . $this->session->data['user_token'], true));
	}

	public function install() {
	}

	public function uninstall() {
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/currency/privatbank')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}