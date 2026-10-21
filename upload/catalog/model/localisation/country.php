<?php
class ModelLocalisationCountry extends Model {
	public function getCountry($country_id) {
		$query = $this->db->query("SELECT t.*, COALESCE(NULLIF(d.name, ''), t.name) AS name FROM " . DB_PREFIX . "country t LEFT JOIN " . DB_PREFIX . "country_description d ON (d.country_id = t.country_id AND d.language_id = '" . (int)$this->config->get('config_language_id') . "') WHERE t.country_id = '" . (int)$country_id . "' AND t.status = '1'");

		return $query->row;
	}

	public function getCountries() {
		$country_data = $this->cache->get('country.catalog.' . (int)$this->config->get('config_language_id'));

		if (!$country_data) {
			$query = $this->db->query("SELECT t.*, COALESCE(NULLIF(d.name, ''), t.name) AS name FROM " . DB_PREFIX . "country t LEFT JOIN " . DB_PREFIX . "country_description d ON (d.country_id = t.country_id AND d.language_id = '" . (int)$this->config->get('config_language_id') . "') WHERE t.status = '1' ORDER BY name ASC");

			$country_data = $query->rows;

			$this->cache->set('country.catalog', $country_data);
		}

		return $country_data;
	}
}
