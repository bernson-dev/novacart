<?php
class ModelDesignSeoNotFound extends Model {
	public function isAvailable() {
		require_once DIR_SYSTEM . 'library/novacart/seo_not_found.php';
		return (new \Novacart\SeoNotFound($this->db))->isAvailable();
	}

	private function where($search) {
		return $search === '' ? '' : " WHERE n.path LIKE '%" . $this->db->escape($search) . "%'";
	}

	public function getEntries($search, $start, $limit) {
		return $this->db->query("SELECT n.*, s.name AS store, l.name AS language FROM " . DB_PREFIX . "seo_not_found n LEFT JOIN " . DB_PREFIX . "store s ON s.store_id = n.store_id LEFT JOIN " . DB_PREFIX . "language l ON l.language_id = n.language_id" . $this->where($search) . " ORDER BY n.date_last DESC, n.seo_not_found_id DESC LIMIT " . max(0, (int)$start) . "," . max(1, (int)$limit))->rows;
	}

	public function getTotalEntries($search) {
		return (int)$this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "seo_not_found n" . $this->where($search))->row['total'];
	}

	public function getEntry($id) {
		return $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_not_found WHERE seo_not_found_id = '" . (int)$id . "'")->row;
	}

	public function deleteEntry($id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_not_found WHERE seo_not_found_id = '" . (int)$id . "'");
	}

	public function prune($days) {
		require_once DIR_SYSTEM . 'library/novacart/seo_not_found.php';
		return (new \Novacart\SeoNotFound($this->db))->prune($days);
	}
}
