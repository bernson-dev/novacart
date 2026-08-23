<?php
// catalog/model/extension/currency/privatbank.php
class ModelExtensionCurrencyPrivatbank extends Model {
	public function editValueByCode($code, $value) {
		$this->db->query("UPDATE `" . DB_PREFIX . "currency` SET `value` = '" . (float)$value . "', `date_modified` = NOW() WHERE `code` = '" . $this->db->escape((string)$code) . "'");
	}

	public function getRates() {
		$api_choice = $this->config->get('currency_privatbank_api_choice');

		$api_options = array(
		'cash'     => 'https://api.privatbank.ua/p24api/pubinfo?json&exchange&coursid=5',
		'non_cash' => 'https://api.privatbank.ua/p24api/pubinfo?json&exchange&coursid=11'
		);

		if (!isset($api_options[$api_choice])) {
			$api_choice = 'cash';
		}

		$response = $this->requestRates($api_options[$api_choice]);

		if (!$response) {
			return array();
		}

		$data = array();
		$date_modified = date('Y-m-d H:i:s');

		foreach ($response as $rate) {
			if (
			isset($rate['ccy']) &&
			isset($rate['sale']) &&
			$rate['ccy'] !== 'BTC'
			) {
				$data[] = array(
				'code'          => $rate['ccy'],
				'title'         => '',
				'rate'          => (float)$rate['sale'],
				'date_modified' => $date_modified,
				'buy'           => isset($rate['buy']) ? (float)$rate['buy'] : null,
				'sale'          => isset($rate['sale']) ? (float)$rate['sale'] : null
				);
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

	private function requestRates($api_url) {
		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, $api_url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);

		$response = curl_exec($curl);

		if (curl_errno($curl)) {
			curl_close($curl);
			return false;
		}

		curl_close($curl);

		$data = json_decode($response, true);

		if (!$data || !is_array($data)) {
			return false;
		}

		return $data;
	}
}