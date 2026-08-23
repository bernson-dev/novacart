<?php
// admin/model/extension/currency/nbu.php
class ModelExtensionCurrencyNbu extends Model {
	public function editValueByCode($code, $value) {
		$query = $this->db->query("SELECT `correction_rate` FROM `" . DB_PREFIX . "currency` WHERE `code` = '" . $this->db->escape((string)$code) . "' LIMIT 1");

		$correction_rate = 1.00000000;

		if ($query->num_rows && isset($query->row['correction_rate']) && (float)$query->row['correction_rate'] > 0) {
			$correction_rate = (float)$query->row['correction_rate'];
		}

		$final_value = (float)$value / $correction_rate;

		$this->db->query("UPDATE `" . DB_PREFIX . "currency`
			SET `value` = '" . (float)$final_value . "', `date_modified` = NOW()
			WHERE `code` = '" . $this->db->escape((string)$code) . "'");
	}

	protected function requestRates() {
		$url = 'https://bank.gov.ua/NBUStatService/v1/statdirectory/exchange?json';

		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);

		$response = curl_exec($curl);

		curl_close($curl);

		if ($response === false || !$response) {
			return array();
		}

		$data = json_decode($response, true);

		if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
			return array();
		}

		return $data;
	}

	public function getRates() {
		$rows = $this->requestRates();
		$data = array();

		foreach ($rows as $row) {
			if (isset($row['cc']) && isset($row['rate'])) {
				$code = trim($row['cc']);
				$rate = (float)$row['rate'];

				if ($code !== '' && $rate > 0) {
					$data[] = array(
						'code'          => $code,
						'title'         => isset($row['txt']) ? $row['txt'] : '',
						'rate'          => $rate,
						'date_modified' => isset($row['exchangedate']) ? $row['exchangedate'] : '',
						'buy'           => null,
						'sale'          => null
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
		$this->db->query("UPDATE `" . DB_PREFIX . "currency`
			SET `value` = '1.00000000', `date_modified` = NOW()
			WHERE `code` = '" . $this->db->escape((string)$default) . "'");

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

	public function getCurrencyRate($code) {
		$query = $this->db->query("SELECT `value` FROM `" . DB_PREFIX . "currency`
			WHERE `code` = '" . $this->db->escape((string)$code) . "'");

		if ($query->num_rows) {
			return (float)$query->row['value'];
		}

		return 0;
	}

	public function getBaseCurrencyCode() {
		return 'UAH';
	}
}