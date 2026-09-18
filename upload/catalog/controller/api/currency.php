<?php
class ControllerApiCurrency extends Controller {
	public function index() {
		$this->load->language('api/currency');

		$json = array();

		if (!isset($this->session->data['api_id'])) {
			$json['error'] = $this->language->get('error_permission');
		} else {
			$this->load->model('localisation/currency');

			$currency = isset($this->request->post['currency']) && is_scalar($this->request->post['currency']) ? (string)$this->request->post['currency'] : '';
			$currency_info = $currency !== '' ? $this->model_localisation_currency->getCurrencyByCode($currency) : array();

			if ($currency_info && !empty($currency_info['status'])) {
				$this->session->data['currency'] = $currency;

				unset($this->session->data['shipping_method']);
				unset($this->session->data['shipping_methods']);
				unset($this->session->data['payment_method']);
				unset($this->session->data['payment_methods']);

				$json['success'] = $this->language->get('text_success');
			} else {
				$json['error'] = $this->language->get('error_currency');
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
