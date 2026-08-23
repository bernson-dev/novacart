<?php
class ModelLocalisationCurrency extends Model {
	public function addCurrency($data) {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "currency` SET `title` = '" . $this->db->escape($data['title']) . "', `code` = '" . $this->db->escape($data['code']) . "', `symbol_left` = '" . $this->db->escape($data['symbol_left']) . "', `symbol_right` = '" . $this->db->escape($data['symbol_right']) . "', `decimal_place` = '" . $this->db->escape($data['decimal_place']) . "', `value` = '" . (float)$data['value'] . "', `correction_rate` = '" . (isset($data['correction_rate']) ? (float)$data['correction_rate'] : 1.00000000) . "', `status` = '" . (int)$data['status'] . "', `date_modified` = NOW()");

		$currency_id = $this->db->getLastId();

		if ($this->config->get('config_currency_auto')) {
			$this->refresh();
		}

		$this->cache->delete('currency');

		return $currency_id;
	}

	public function editCurrency($currency_id, $data) {
		$this->db->query("UPDATE `" . DB_PREFIX . "currency` SET `title` = '" . $this->db->escape($data['title']) . "', `code` = '" . $this->db->escape($data['code']) . "', `symbol_left` = '" . $this->db->escape($data['symbol_left']) . "', `symbol_right` = '" . $this->db->escape($data['symbol_right']) . "', `decimal_place` = '" . $this->db->escape($data['decimal_place']) . "', `value` = '" . (float)$data['value'] . "', `correction_rate` = '" . (isset($data['correction_rate']) ? (float)$data['correction_rate'] : 1.00000000) . "', `status` = '" . (int)$data['status'] . "', `date_modified` = NOW() WHERE `currency_id` = '" . (int)$currency_id . "'");

		$this->cache->delete('currency');
	}

	public function deleteCurrency($currency_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "currency` WHERE `currency_id` = '" . (int)$currency_id . "'");

		$this->cache->delete('currency');
	}

	public function getCurrency($currency_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "currency` WHERE `currency_id` = '" . (int)$currency_id . "'");

		return $query->row;
	}

	public function getCurrencyByCode($currency) {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "currency` WHERE `code` = '" . $this->db->escape($currency) . "'");

		return $query->row;
	}

	public function getCurrencies($data = array()) {
		$sql = "SELECT * FROM `" . DB_PREFIX . "currency`";

		$sort_data = array(
		'title',
		'code',
		'value',
		'status',
		'date_modified'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY `title`";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if (!isset($data['start']) || $data['start'] < 0) {
				$data['start'] = 0;
			}

			if (!isset($data['limit']) || $data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);
		$results = $query->rows;

		$currency_data = array();

		foreach ($results as $result) {
			$currency_data[$result['code']] = array(
			'currency_id'     => $result['currency_id'],
			'title'           => $result['title'],
			'code'            => $result['code'],
			'symbol_left'     => $result['symbol_left'],
			'symbol_right'    => $result['symbol_right'],
			'decimal_place'   => $result['decimal_place'],
			'value'           => $result['value'],
			'correction_rate' => isset($result['correction_rate']) ? $result['correction_rate'] : '1.00000000',
			'status'          => $result['status'],
			'date_modified'   => $result['date_modified']
			);
		}

		return $currency_data;
	}

	public function refresh() {
		$config_currency_engine = $this->config->get('config_currency_engine');

		if ($config_currency_engine) {
			$this->load->model('extension/currency/' . $config_currency_engine);

			return $this->{'model_extension_currency_' . $config_currency_engine}->refresh();
		}

		return false;
	}

	public function getTotalCurrencies() {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "currency`");

		return (int)$query->row['total'];
	}

	public function getCurrencyCodes() {
		$query = $this->db->query("SELECT `code` FROM `" . DB_PREFIX . "currency`");

		$codes = array();

		foreach ($query->rows as $row) {
			$codes[] = $row['code'];
		}

		return $codes;
	}

	public function getCorrectionRateByCode($code) {
		$query = $this->db->query("SELECT `correction_rate` FROM `" . DB_PREFIX . "currency` WHERE `code` = '" . $this->db->escape($code) . "' LIMIT 1");

		if ($query->num_rows && isset($query->row['correction_rate']) && (float)$query->row['correction_rate'] > 0) {
			return (float)$query->row['correction_rate'];
		}

		return 1.00000000;
	}
}