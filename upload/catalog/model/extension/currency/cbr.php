<?php
// catalog/model/extension/currency/cbr.php
class ModelExtensionCurrencyCbr extends Model {
	public function editValueByCode($code, $value) {
		$this->db->query("UPDATE `" . DB_PREFIX . "currency` SET `value` = '" . (float)$value . "', `date_modified` = NOW() WHERE `code` = '" . $this->db->escape((string)$code) . "'");
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
						$code = $code_node->nodeValue;
						$value = (float)str_replace(',', '.', $value_node->nodeValue);
						$nominal = (int)$nominal_node->nodeValue;

						if ($nominal > 0) {
							$data[] = array(
							'code'          => $code,
							'title'         => $name_node ? $name_node->nodeValue : '',
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

		$currency_rates = array(
		'RUB' => 1.0000
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