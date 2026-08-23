<?php
// catalog/controller/extension/currency/cbr.php
class ControllerExtensionCurrencyCbr extends Controller {
	public function refresh() {
		if (!$this->config->get('currency_cbr_status')) {
			return array(
			'success' => false,
			'message' => 'error_extension_disabled'
			);
		}

		$config_currency_engine = $this->config->get('config_currency_engine');

		if (!$config_currency_engine || $config_currency_engine != 'cbr') {
			return array(
			'success' => false,
			'message' => 'error_engine_not_active'
			);
		}

		$this->load->model('extension/currency/cbr');

		return $this->model_extension_currency_cbr->refresh();
	}
}