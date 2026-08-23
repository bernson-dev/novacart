<?php
// catalog/controller/extension/currency/nbu.php
class ControllerExtensionCurrencyNbu extends Controller {
	public function refresh() {
		if (!$this->config->get('currency_nbu_status')) {
			return array(
			'success' => false,
			'message' => 'error_extension_disabled'
			);
		}

		$config_currency_engine = $this->config->get('config_currency_engine');

		if (!$config_currency_engine || $config_currency_engine != 'nbu') {
			return array(
			'success' => false,
			'message' => 'error_engine_not_active'
			);
		}

		$this->load->model('extension/currency/nbu');

		return $this->model_extension_currency_nbu->refresh();
	}
}