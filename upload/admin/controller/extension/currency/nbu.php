<?php
// admin/controller/extension/currency/nbu.php
class ControllerExtensionCurrencyNbu extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/currency/nbu');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('currency_nbu', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link(
			'marketplace/extension',
			'user_token=' . $this->session->data['user_token'] . '&type=currency',
			true
			));
		}

		$data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';

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
		'href' => $this->url->link('extension/currency/nbu', 'user_token=' . $this->session->data['user_token'], true)
		)
		);

		$data['action'] = $this->url->link('extension/currency/nbu', 'user_token=' . $this->session->data['user_token'], true);
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=currency', true);
		$data['refresh'] = $this->url->link('extension/currency/nbu/refresh', 'user_token=' . $this->session->data['user_token'], true);

		$data['text_edit'] = $this->language->get('text_edit');
		$setting_link_clean = html_entity_decode($this->url->link('setting/setting', 'user_token=' . $this->session->data['user_token'], true));

		$data['text_information'] = str_replace(
		array('%1', '%2'),
		array(
		$this->url->link('localisation/currency', 'user_token=' . $this->session->data['user_token'], true),
		$setting_link_clean . '#tab-local'
		),
		$this->language->get('text_information')
		);

		$data['currency_nbu_cron'] = 'curl -s &quot;' . HTTPS_CATALOG . 'index.php?route=extension/currency/nbu/refresh&quot;';

		$data['currency_nbu_status'] = isset($this->request->post['currency_nbu_status'])
		? $this->request->post['currency_nbu_status']
		: $this->config->get('currency_nbu_status');

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/currency/nbu', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/currency/nbu')) {
			$this->error['warning'] = $this->language->get('error_permission');
		} elseif (!empty($this->request->post['currency_nbu_status'])) {
			$this->load->model('localisation/currency');

			$uah_currency = $this->model_localisation_currency->getCurrencyByCode('UAH');

			if (empty($uah_currency)) {
				$this->error['warning'] = $this->language->get('error_uah');
			}
		}

		return !$this->error;
	}

	public function refresh() {
		$this->load->language('extension/currency/nbu');
		$this->load->model('extension/currency/nbu');

		if ($this->model_extension_currency_nbu->refresh()) {
			$this->session->data['success'] = $this->language->get('text_success');
		} else {
			$this->session->data['error_warning'] = $this->language->get('error_refresh');
		}

		$this->response->redirect($this->url->link(
		'localisation/currency',
		'user_token=' . $this->session->data['user_token'],
		true
		));
	}

	public function install() {
	}
	public function uninstall() {
	}
}