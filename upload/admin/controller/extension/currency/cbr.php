<?php
// admin/controller/extension/currency/cbr.php
class ControllerExtensionCurrencyCbr extends Controller {

	private $error = array();

	public function index() {
		$this->load->language('extension/currency/cbr');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('currency_cbr', $this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');
			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=currency', true));
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
		'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=currency', true)
		);

		$data['breadcrumbs'][] = array(
		'text' => $this->language->get('heading_title'),
		'href' => $this->url->link('extension/currency/cbr', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/currency/cbr', 'user_token=' . $this->session->data['user_token'], true);
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=currency', true);
		$data['refresh'] = $this->url->link('extension/currency/cbr/refresh', 'user_token=' . $this->session->data['user_token'], true);

		$data['text_edit'] = $this->language->get('text_edit');

		$data['text_information'] = $this->language->get('text_information');
		$data['text_information'] = str_replace('%1', $this->url->link('localisation/currency', 'user_token=' . $this->session->data['user_token'], true), $data['text_information']);

		$setting_link_clean = html_entity_decode($this->url->link('setting/setting', 'user_token=' . $this->session->data['user_token'], true));

		$data['text_information'] = str_replace(
		'%2',
		$setting_link_clean . '#tab-local',
		$data['text_information']
		);

		$data['currency_cbr_cron'] = 'curl -s &quot;' . HTTPS_CATALOG . 'index.php?route=extension/currency/cbr/refresh&quot;';

		if (isset($this->request->post['currency_cbr_status'])) {
			$data['currency_cbr_status'] = $this->request->post['currency_cbr_status'];
		} else {
			$data['currency_cbr_status'] = $this->config->get('currency_cbr_status');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/currency/cbr', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/currency/cbr')) {
			$this->error['warning'] = $this->language->get('error_permission');
		} else {
			if (!empty($this->request->post['currency_cbr_status'])) {
				$this->load->model('localisation/currency');
				$rub_currency = $this->model_localisation_currency->getCurrencyByCode('RUB');
				if (empty($rub_currency)) {
					$this->error['warning'] = $this->language->get('error_rub');
				}
			}
		}
		return !$this->error;
	}
	
	public function refresh() {
		$this->load->language('extension/currency/cbr');
		$this->load->model('extension/currency/cbr');

		if ($this->model_extension_currency_cbr->refresh()) {
			$this->session->data['success'] = $this->language->get('text_success');
		}

		$this->response->redirect($this->url->link('localisation/currency', 'user_token=' . $this->session->data['user_token'], true));
	}

	public function install() {
	}

	public function uninstall() {
	}
}
