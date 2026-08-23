<?php
// admin/model/extension/currency/privatbank.php
class ModelExtensionCurrencyPrivatbank extends Model {
	public function editValueByCode($code, $value) {
		$query = $this->db->query("SELECT `correction_rate` FROM `" . DB_PREFIX . "currency` WHERE `code` = '" . $this->db->escape((string)$code) . "' LIMIT 1");

		$correction_rate = 1.00000000;

		if ($query->num_rows && isset($query->row['correction_rate']) && (float)$query->row['correction_rate'] > 0) {
			$correction_rate = (float)$query->row['correction_rate'];
		}

		$final_value = (float)$value / $correction_rate;

		$this->db->query("UPDATE `" . DB_PREFIX . "currency` SET `value` = '" . (float)$final_value . "', `date_modified` = NOW() WHERE `code` = '" . $this->db->escape((string)$code) . "'");
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
			if (isset($rate['ccy']) && isset($rate['sale']) && $rate['ccy'] !== 'BTC') {
				$code = trim($rate['ccy']);
				$sale = (float)$rate['sale'];

				if ($code !== '' && $sale > 0) {
					$data[] = array(
						'code'          => $code,
						'title'         => '',
						'rate'          => $sale,
						'date_modified' => $date_modified,
						'buy'           => isset($rate['buy']) ? (float)$rate['buy'] : null,
						'sale'          => $sale
					);
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
			if (isset($currency['code']) && isset($currency['rate']) && (float)$currency['rate'] > 0) {
				$currency_rates[$currency['code']] = (float)$currency['rate'];
			}
		}

		$default = $this->config->get('config_currency');

		if (!$default || !isset($currency_rates[$default]) || (float)$currency_rates[$default] <= 0) {
			return array(
				'success' => false,
				'message' => 'error_base_currency_missing'
			);
		}

		$results = $this->db->query("SELECT `code` FROM `" . DB_PREFIX . "currency`")->rows;

		$updated = 0;

		foreach ($results as $result) {
			if (!isset($result['code'])) {
				continue;
			}

			$code = $result['code'];

			if (isset($currency_rates[$code]) && (float)$currency_rates[$code] > 0) {
				$value = $currency_rates[$default] / $currency_rates[$code];
				$this->editValueByCode($code, $value);
				$updated++;
			}
		}

		// Для базовой валюты correction_rate не применяется.
		$this->db->query("UPDATE `" . DB_PREFIX . "currency` SET `value` = '1.00000000', `date_modified` = NOW() WHERE `code` = '" . $this->db->escape((string)$default) . "'");

		if ($updated === 0) {
			return array(
				'success' => false,
				'message' => 'error_no_matching_currencies'
			);
		}

		$this->cache->delete('currency');

		return array(
			'success' => true,
			'message' => 'text_success_refresh'
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

	public function getBaseCurrencyCode() {
		return 'UAH';
	}
}