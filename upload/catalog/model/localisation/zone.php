<?php
class ModelLocalisationZone extends Model {
	public function getZone($zone_id) {
		$query = $this->db->query("SELECT t.*, COALESCE(NULLIF(d.name, ''), t.name) AS name FROM " . DB_PREFIX . "zone t LEFT JOIN " . DB_PREFIX . "zone_description d ON (d.zone_id = t.zone_id AND d.language_id = '" . (int)$this->config->get('config_language_id') . "') WHERE t.zone_id = '" . (int)$zone_id . "' AND t.status = '1'");

		return $query->row;
	}

	public function getZonesByCountryId($country_id) {
		$zone_data = $this->cache->get('zone.' . (int)$country_id . '.' . (int)$this->config->get('config_language_id'));

		if (!$zone_data) {
			$query = $this->db->query("SELECT t.*, COALESCE(NULLIF(d.name, ''), t.name) AS name FROM " . DB_PREFIX . "zone t LEFT JOIN " . DB_PREFIX . "zone_description d ON (d.zone_id = t.zone_id AND d.language_id = '" . (int)$this->config->get('config_language_id') . "') WHERE t.country_id = '" . (int)$country_id . "' AND t.status = '1' ORDER BY name");

			$zone_data = $query->rows;

			$this->cache->set('zone.' . (int)$country_id . '.' . (int)$this->config->get('config_language_id'), $zone_data);
		}

		return $zone_data;
	}
}
