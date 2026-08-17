<?php
// catalog/controller/extension/currency/ecb.php
class ControllerExtensionCurrencyEcb extends Controller {
	public function refresh() {
		// This function can be called as a CRON task
		if (!$this->config->get('currency_ecb_status')) {
			return array(
			'success' => false,
			'message' => 'error_extension_disabled'
			);
		}

		$config_currency_engine = $this->config->get('config_currency_engine');

		if (!$config_currency_engine || $config_currency_engine != 'ecb') {
			return array(
			'success' => false,
			'message' => 'error_engine_not_active'
			);
		}

		$this->load->model('extension/currency/ecb');

		return $this->model_extension_currency_ecb->refresh();
	}
}