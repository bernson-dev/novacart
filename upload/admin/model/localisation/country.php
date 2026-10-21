<?php
class ModelLocalisationCountry extends Model {
	private function saveDescriptions($country_id, $data) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "country_description` WHERE `country_id` = '" . (int)$country_id . "'");
		if (!empty($data['country_description']) && is_array($data['country_description'])) {
			foreach ($data['country_description'] as $language_id => $description) {
				$name = isset($description['name']) ? trim($description['name']) : '';
				if ($name !== '' && utf8_strlen($name) <= 128) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "country_description` SET `country_id` = '" . (int)$country_id . "', `language_id` = '" . (int)$language_id . "', `name` = '" . $this->db->escape($name) . "'");
				}
			}
		}
	}

	public function getCountryDescriptions($country_id) {
		$descriptions = array();
		$query = $this->db->query("SELECT language_id, name FROM `" . DB_PREFIX . "country_description` WHERE `country_id` = '" . (int)$country_id . "'");
		foreach ($query->rows as $row) {
			$descriptions[$row['language_id']] = array('name' => $row['name']);
		}
		return $descriptions;
	}

	public function addCountry($data) {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "country` SET `name` = '" . $this->db->escape($data['name']) . "', `iso_code_2` = '" . $this->db->escape($data['iso_code_2']) . "', `iso_code_3` = '" . $this->db->escape($data['iso_code_3']) . "', `address_format` = '" . $this->db->escape($data['address_format']) . "', `postcode_required` = '" . (int)$data['postcode_required'] . "', `status` = '" . (int)$data['status'] . "'");

		$this->cache->delete('country');
		$this->cache->delete('country.catalog.*');
		
		$country_id = $this->db->getLastId();
		$this->saveDescriptions($country_id, $data);
		return $country_id;
	}

	public function editCountry($country_id, $data) {
		$this->db->query("UPDATE `" . DB_PREFIX . "country` SET `name` = '" . $this->db->escape($data['name']) . "', `iso_code_2` = '" . $this->db->escape($data['iso_code_2']) . "', `iso_code_3` = '" . $this->db->escape($data['iso_code_3']) . "', `address_format` = '" . $this->db->escape($data['address_format']) . "', `postcode_required` = '" . (int)$data['postcode_required'] . "', `status` = '" . (int)$data['status'] . "' WHERE `country_id` = '" . (int)$country_id . "'");

		$this->cache->delete('country');
		$this->cache->delete('country.catalog.*');
	}

	public function deleteCountry($country_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "country` WHERE `country_id` = '" . (int)$country_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "country_description` WHERE `country_id` = '" . (int)$country_id . "'");

		$this->cache->delete('country');
		$this->cache->delete('country.catalog.*');
	}

	public function enableCountry($country_id) {
		$this->db->query("UPDATE `" . DB_PREFIX . "country` SET `status` = '1' WHERE `country_id` = '" . (int)$country_id . "'");
		$this->cache->delete('country');
		$this->cache->delete('country.catalog.*');
	}

	public function disableCountry($country_id) {
		$this->db->query("UPDATE `" . DB_PREFIX . "country` SET `status` = '0' WHERE `country_id` = '" . (int)$country_id . "'");
		$this->cache->delete('country');
		$this->cache->delete('country.catalog.*');
	}

	public function getCountry($country_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "country` WHERE `country_id` = '" . (int)$country_id . "'");

		return $query->row;
	}

	public function getCountries($data = array()) {
		if ($data) {
			$sql = "SELECT c.*, COALESCE(NULLIF(cd.name, ''), c.name) AS name FROM `" . DB_PREFIX . "country` c LEFT JOIN `" . DB_PREFIX . "country_description` cd ON (cd.country_id = c.country_id AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "')";

			$implode = array();
			if (!empty($data['filter_name'])) {
				$implode[] = "(c.name LIKE '" . $this->db->escape($data['filter_name']) . "%' OR EXISTS (SELECT 1 FROM `" . DB_PREFIX . "country_description` search_translation WHERE search_translation.country_id = c.country_id AND search_translation.name LIKE '" . $this->db->escape($data['filter_name']) . "%'))";
			}

			if (!empty($data['filter_iso_code_2'])) {
				$implode[] = "c.`iso_code_2` LIKE '" . $this->db->escape($data['filter_iso_code_2']) . "%'";
			}

			if (!empty($data['filter_iso_code_3'])) {
				$implode[] = "c.`iso_code_3` LIKE '" . $this->db->escape($data['filter_iso_code_3']) . "%'";
			}

			if ($implode) {
				$sql .= " WHERE " . implode(" AND ", $implode);
			}

			$sort_data = array('name', 'status', 'iso_code_2', 'iso_code_3');
			$sort = isset($data['sort']) ? trim($data['sort'], '`') : 'name';
			if (!in_array($sort, $sort_data)) {
				$sort = 'name';
			}
			$sql .= " ORDER BY " . ($sort == 'name' ? "COALESCE(NULLIF(cd.name, ''), c.name)" : "c.`" . $sort . "`");

			if (isset($data['order']) && ($data['order'] == 'DESC')) {
				$sql .= " DESC";
			} else {
				$sql .= " ASC";
			}

			if (isset($data['start']) || isset($data['limit'])) {
				if ($data['start'] < 0) {
					$data['start'] = 0;
				}

				if ($data['limit'] < 1) {
					$data['limit'] = 20;
				}

				$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
			}

			$query = $this->db->query($sql);

			return $query->rows;
		} else {
			//$country_data = $this->cache->get('country.admin');

			//if (!$country_data) {
				$query = $this->db->query("SELECT c.*, COALESCE(NULLIF(cd.name, ''), c.name) AS name FROM `" . DB_PREFIX . "country` c LEFT JOIN `" . DB_PREFIX . "country_description` cd ON (cd.country_id = c.country_id AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "') ORDER BY COALESCE(NULLIF(cd.name, ''), c.name) ASC");
				$country_data = $query->rows;

				//$this->cache->set('country.admin', $country_data);
			//}

			return $country_data;
		}
	}

	public function getTotalCountries($data = array()) {
		$sql = "SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "country` c LEFT JOIN `" . DB_PREFIX . "country_description` cd ON (cd.country_id = c.country_id AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "')";

		$implode = array();
		if (!empty($data['filter_name'])) {
			$implode[] = "(c.name LIKE '" . $this->db->escape($data['filter_name']) . "%' OR EXISTS (SELECT 1 FROM `" . DB_PREFIX . "country_description` search_translation WHERE search_translation.country_id = c.country_id AND search_translation.name LIKE '" . $this->db->escape($data['filter_name']) . "%'))";
		}

		if (!empty($data['filter_iso_code_2'])) {
			$implode[] = "c.`iso_code_2` LIKE '" . $this->db->escape($data['filter_iso_code_2']) . "%'";
		}

		if (!empty($data['filter_iso_code_3'])) {
			$implode[] = "c.`iso_code_3` LIKE '" . $this->db->escape($data['filter_iso_code_3']) . "%'";
		}

		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}

		$query = $this->db->query($sql);
		return (int)$query->row['total'];
	}
}
