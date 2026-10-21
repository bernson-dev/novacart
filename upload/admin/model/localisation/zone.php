<?php
class ModelLocalisationZone extends Model {
	private function saveDescriptions($zone_id, $data) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "zone_description` WHERE `zone_id` = '" . (int)$zone_id . "'");
		if (!empty($data['zone_description']) && is_array($data['zone_description'])) {
			foreach ($data['zone_description'] as $language_id => $description) {
				$name = isset($description['name']) ? trim($description['name']) : '';
				if ($name !== '' && utf8_strlen($name) <= 128) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "zone_description` SET `zone_id` = '" . (int)$zone_id . "', `language_id` = '" . (int)$language_id . "', `name` = '" . $this->db->escape($name) . "'");
				}
			}
		}
	}

	public function getZoneDescriptions($zone_id) {
		$descriptions = array();
		$query = $this->db->query("SELECT language_id, name FROM `" . DB_PREFIX . "zone_description` WHERE `zone_id` = '" . (int)$zone_id . "'");
		foreach ($query->rows as $row) {
			$descriptions[$row['language_id']] = array('name' => $row['name']);
		}
		return $descriptions;
	}

	public function addZone($data) {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "zone` SET `status` = '" . (int)$data['status'] . "', `name` = '" . $this->db->escape($data['name']) . "', `code` = '" . $this->db->escape($data['code']) . "', `country_id` = '" . (int)$data['country_id'] . "'");

		$this->cache->delete('zone');
		$this->cache->delete('zone.*');

		$zone_id = $this->db->getLastId();
		$this->saveDescriptions($zone_id, $data);
		return $zone_id;
	}

	public function editZone($zone_id, $data) {
		$this->db->query("UPDATE `" . DB_PREFIX . "zone` SET `status` = '" . (int)$data['status'] . "', `name` = '" . $this->db->escape($data['name']) . "', `code` = '" . $this->db->escape($data['code']) . "', `country_id` = '" . (int)$data['country_id'] . "' WHERE `zone_id` = '" . (int)$zone_id . "'");

		$this->cache->delete('zone');
		$this->cache->delete('zone.*');
	}

	public function deleteZone($zone_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "zone` WHERE `zone_id` = '" . (int)$zone_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "zone_description` WHERE `zone_id` = '" . (int)$zone_id . "'");

		$this->cache->delete('zone');
		$this->cache->delete('zone.*');
	}

	public function getZone($zone_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "zone` WHERE `zone_id` = '" . (int)$zone_id . "'");

		return $query->row;
	}

	public function getZones($data = array()) {
		$sql = "SELECT z.*, COALESCE(NULLIF(zd.name, ''), z.name) AS name, COALESCE(NULLIF(cd.name, ''), c.name) AS country FROM `" . DB_PREFIX . "zone` z LEFT JOIN `" . DB_PREFIX . "zone_description` zd ON (zd.zone_id = z.zone_id AND zd.language_id = '" . (int)$this->config->get('config_language_id') . "') LEFT JOIN `" . DB_PREFIX . "country` c ON (z.country_id = c.country_id) LEFT JOIN `" . DB_PREFIX . "country_description` cd ON (cd.country_id = c.country_id AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "')";

		$implode = array();
		if (!empty($data['filter_name'])) {
			$implode[] = "COALESCE(NULLIF(zd.name, ''), z.name) LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_country'])) {
			$implode[] = "COALESCE(NULLIF(cd.name, ''), c.name) LIKE '" . $this->db->escape($data['filter_country']) . "%'";
		}

		if (!empty($data['filter_code'])) {
			$implode[] = "z.`code` LIKE '" . $this->db->escape($data['filter_code']) . "%'";
		}

		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}

		$sort = isset($data['sort']) ? str_replace('`', '', $data['sort']) : 'c.name';
		$sort_columns = array('c.name' => "COALESCE(NULLIF(cd.name, ''), c.name)", 'z.name' => "COALESCE(NULLIF(zd.name, ''), z.name)", 'z.status' => 'z.`status`', 'z.code' => 'z.`code`');
		$sql .= " ORDER BY " . (isset($sort_columns[$sort]) ? $sort_columns[$sort] : $sort_columns['c.name']);

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
	}

	public function getZonesByCountryId($country_id) {
		//$zone_data = $this->cache->get('zone.' . (int)$country_id);

		//if (!$zone_data) {
			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE `country_id` = '" . (int)$country_id . "' AND `status` = '1' ORDER BY `name`");

			$zone_data = $query->rows;

			//$this->cache->set('zone.' . (int)$country_id, $zone_data);
		//}

		return $zone_data;
	}

	public function getTotalZones($data = array()) {
		$sql = "SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "zone` z LEFT JOIN `" . DB_PREFIX . "zone_description` zd ON (zd.zone_id = z.zone_id AND zd.language_id = '" . (int)$this->config->get('config_language_id') . "') LEFT JOIN `" . DB_PREFIX . "country` c ON (z.country_id = c.country_id) LEFT JOIN `" . DB_PREFIX . "country_description` cd ON (cd.country_id = c.country_id AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "')";

		$implode = array();
		if (!empty($data['filter_name'])) {
			$implode[] = "COALESCE(NULLIF(zd.name, ''), z.name) LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_country'])) {
			$implode[] = "COALESCE(NULLIF(cd.name, ''), c.name) LIKE '" . $this->db->escape($data['filter_country']) . "%'";
		}

		if (!empty($data['filter_code'])) {
			$implode[] = "z.`code` LIKE '" . $this->db->escape($data['filter_code']) . "%'";
		}

		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}

		$query = $this->db->query($sql);

		return (int)$query->row['total'];
	}

	public function getTotalZonesByCountryId($country_id) {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "zone` WHERE `country_id` = '" . (int)$country_id . "'");

		return (int)$query->row['total'];
	}
}
