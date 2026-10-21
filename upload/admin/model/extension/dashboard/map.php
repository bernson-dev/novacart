<?php
class ModelExtensionDashboardMap extends Model {
	public function getTotalOrdersByCountry() {
		$implode = array();

		if (is_array($this->config->get('config_complete_status'))) {
			foreach ($this->config->get('config_complete_status') as $order_status_id) {
				$implode[] = (int)$order_status_id;
			}
		}

		if ($implode) {
		//	$query = $this->db->query("SELECT COUNT(*) AS total, SUM(o.total) AS amount, c.iso_code_2 FROM `" . DB_PREFIX . "order` o INNER JOIN `" . DB_PREFIX . "country` c ON (o.payment_country_id = c.country_id) WHERE o.order_status_id IN(" . implode(',', $implode) . ") GROUP BY o.payment_country_id");
		// оптимизированный вариант запроса для статистики заказов по странам, который будет работать быстрее на больших таблицах
			$query = $this->db->query("
				SELECT 
					COUNT(o.order_id) AS total,
					SUM(o.total) AS amount,
					c.iso_code_2, c.name
				FROM `" . DB_PREFIX . "order` o
				INNER JOIN `" . DB_PREFIX . "country` c 
					ON o.payment_country_id = c.country_id
				WHERE o.order_status_id IN(" . implode(',', $implode) . ")
					AND c.iso_code_2 IS NOT NULL
					AND c.iso_code_2 != ''
				GROUP BY o.payment_country_id, c.iso_code_2, c.name
			");

			return $query->rows;
		} else {
			return array();
		}
	}

	public function getTotalOrdersByUkraineZone() {
		$statuses = $this->config->get('config_complete_status');
		if (!is_array($statuses) || !$statuses) { return array(); }
		$ids = array_map('intval', $statuses);
		$ids = array_filter($ids);
		if (!$ids) { return array(); }
		// Shipping address takes priority. Payment address is used for virtual orders.
		// Match the country by ISO code rather than relying on a fixed country_id.
		$query = $this->db->query("SELECT z.zone_id, z.name, COUNT(o.order_id) AS total, SUM(o.total) AS amount
			FROM `" . DB_PREFIX . "order` o
			INNER JOIN `" . DB_PREFIX . "country` c ON c.country_id = (CASE WHEN o.shipping_country_id > 0 THEN o.shipping_country_id ELSE o.payment_country_id END) AND c.iso_code_2 = 'UA'
			INNER JOIN `" . DB_PREFIX . "zone` z ON z.zone_id = (CASE WHEN o.shipping_country_id > 0 THEN o.shipping_zone_id ELSE o.payment_zone_id END) AND z.country_id = c.country_id
			WHERE o.order_status_id IN (" . implode(',', $ids) . ")
			GROUP BY z.zone_id, z.name");
		return $query->rows;
	}
}
