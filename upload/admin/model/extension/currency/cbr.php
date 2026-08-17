<?php
// admin/model/extension/currency/cbr.php
class ModelExtensionCurrencyCbr extends Model {
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
		$url = 'https://www.cbr.ru/scripts/XML_daily.asp';

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
			$dom = new DOMDocument('1.0', 'windows-1251');

			if (@$dom->loadXML($response)) {
				$date_modified = '';

				$root = $dom->documentElement;

				if ($root && $root->hasAttribute('Date')) {
					$date_modified = $root->getAttribute('Date');
				}

				$items = $dom->getElementsByTagName('Valute');

				foreach ($items as $item) {
					$code_node = $item->getElementsByTagName('CharCode')->item(0);
					$value_node = $item->getElementsByTagName('Value')->item(0);
					$nominal_node = $item->getElementsByTagName('Nominal')->item(0);
					$name_node = $item->getElementsByTagName('Name')->item(0);

					if ($code_node && $value_node && $nominal_node) {
						$code = trim($code_node->nodeValue);
						$value = (float)str_replace(',', '.', $value_node->nodeValue);
						$nominal = (int)$nominal_node->nodeValue;

						if ($code !== '' && $nominal > 0) {
							$data[] = array(
							'code'          => $code,
							'title'         => $name_node ? trim($name_node->nodeValue) : '',
							'rate'          => $value / $nominal,
							'date_modified' => $date_modified,
							'buy'           => null,
							'sale'          => null
							);
						}
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

		$currency_rates = array();
		$currency_rates['RUB'] = 1.0000;

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

		// Базовая валюта магазина всегда должна быть 1, correction_rate для неё не применяется.
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

	public function getBaseCurrencyCode() {
		return 'RUB';
	}
}