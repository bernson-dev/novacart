<?php
// catalog/model/extension/currency/nbu.php
class ModelExtensionCurrencyNbu extends Model {
	public function editValueByCode($code, $value) {
		$this->db->query("UPDATE `" . DB_PREFIX . "currency` SET `value` = '" . (float)$value . "', `date_modified` = NOW() WHERE `code` = '" . $this->db->escape((string)$code) . "'");
	}

	public function getRates() {
		$url = 'https://bank.gov.ua/NBUStatService/v1/statdirectory/exchange?json';

		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);

		$response = curl_exec($curl);

		curl_close($curl);

		$data = array();

		if ($response) {
			$currencies = json_decode($response, true);

			if (is_array($currencies)) {
				foreach ($currencies as $currency) {
					if (isset($currency['cc']) && isset($currency['rate'])) {
						$data[] = array(
						'code'          => $currency['cc'],
						'title'         => isset($currency['txt']) ? $currency['txt'] : '',
						'rate'          => (float)$currency['rate'],
						'date_modified' => isset($currency['exchangedate']) ? $currency['exchangedate'] : '',
						'buy'           => null,
						'sale'          => null
						);
					}
				}
			}
		}

		return $data;
	}

	public function refresh() {
		$currencies = $this->getRates();

		if (!$currencies) {
			return array(
			'success' => false,
			'message' => 'error_no_rates'
			);
		}

		$currency_rates = array(
		'UAH' => 1.0000
		);

		foreach ($currencies as $currency) {
			$currency_rates[$currency['code']] = $currency['rate'];
		}

		$default = $this->config->get('config_currency');

		if (!isset($currency_rates[$default])) {
			return array(
			'success' => false,
			'message' => 'error_base_currency_missing'
			);
		}

		$results = $this->db->query("SELECT * FROM " . DB_PREFIX . "currency")->rows;

		$updated = 0;

		foreach ($results as $result) {
			if (isset($currency_rates[$result['code']])) {
				$value = $currency_rates[$default] / $currency_rates[$result['code']];
				$this->editValueByCode($result['code'], $value);
				$updated++;
			}
		}

		if ($updated === 0) {
			return array(
			'success' => false,
			'message' => 'error_no_matching_currencies'
			);
		}

		$this->editValueByCode($default, '1.00000');
		$this->cache->delete('currency');

		return array(
		'success' => true,
		'message' => 'text_success'
		);
	}
}