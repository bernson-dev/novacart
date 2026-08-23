<?php
// catalog/model/extension/currency/ecb.php
class ModelExtensionCurrencyEcb extends Model {
	public function editValueByCode($code, $value) {
		$this->db->query("UPDATE `" . DB_PREFIX . "currency` SET `value` = '" . (float)$value . "', `date_modified` = NOW() WHERE `code` = '" . $this->db->escape((string)$code) . "'");
	}

	public function getRates() {
		$url = 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';

		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);

		$response = curl_exec($curl);

		if (version_compare(phpversion(), '8.0.', '>=')) {
			unset($curl);
		} else {
			curl_close($curl);
		}

		$data = array();

		if ($response) {
			$dom = new DOMDocument('1.0', 'UTF-8');

			if (@$dom->loadXML($response)) {
				$date_modified = '';

				$cubes = $dom->getElementsByTagName('Cube');

				foreach ($cubes as $cube) {
					if ($cube->hasAttribute('time')) {
						$date_modified = $cube->getAttribute('time');

						foreach ($cube->getElementsByTagName('Cube') as $currency) {
							if ($currency->hasAttribute('currency') && $currency->hasAttribute('rate')) {
								$data[] = array(
								'code'          => $currency->getAttribute('currency'),
								'title'         => '',
								'rate'          => (float)$currency->getAttribute('rate'),
								'date_modified' => $date_modified,
								'buy'           => null,
								'sale'          => null
								);
							}
						}

						break;
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
		'EUR' => 1.0000
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