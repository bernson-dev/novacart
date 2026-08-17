<?php
class ModelBlogReview extends Model {
	public function addReview($data) {
		$date_added = !empty($data['date_added']) ? $this->db->escape($data['date_added']) : date('Y-m-d H:i:s');

		$this->db->query("INSERT INTO " . DB_PREFIX . "review_article SET author = '" . $this->db->escape($data['author']) . "', article_id = '" . (int)$data['article_id'] . "', text = '" . $this->db->escape(strip_tags($data['text'])) . "', rating = '" . (int)$data['rating'] . "', status = '" . (int)$data['status'] . "', date_added = '" . $date_added . "'");

		$this->cache->delete('article');
		return $this->db->getLastId();
	}

	public function editReview($review_article_id, $data) {
		$date_added = !empty($data['date_added']) ? $this->db->escape($data['date_added']) : date('Y-m-d H:i:s');

		$this->db->query("UPDATE " . DB_PREFIX . "review_article SET author = '" . $this->db->escape($data['author']) . "', article_id = '" . (int)$data['article_id'] . "', text = '" . $this->db->escape(strip_tags($data['text'])) . "', rating = '" . (int)$data['rating'] . "', status = '" . (int)$data['status'] . "', date_added = '" . $date_added . "', date_modified = NOW() WHERE review_article_id = '" . (int)$review_article_id . "'");

		$this->cache->delete('article');
	}

	public function deleteReview($review_article_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "review_article WHERE review_article_id = '" . (int)$review_article_id . "'");

		$this->cache->delete('article');
	}

	public function getReview($review_article_id) {
		$query = $this->db->query("SELECT DISTINCT r.*, (SELECT pd.name FROM " . DB_PREFIX . "article_description pd WHERE pd.article_id = r.article_id AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS article FROM " . DB_PREFIX . "review_article r WHERE r.review_article_id = '" . (int)$review_article_id . "'");

		return $query->row;
	}

	public function getReviews($data = array()) {
		$sql = "SELECT r.review_article_id, pd.name, r.author, r.rating, r.status, r.date_added FROM " . DB_PREFIX . "review_article r LEFT JOIN " . DB_PREFIX . "article_description pd ON (r.article_id = pd.article_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_article'])) {
			$sql .= " AND pd.name LIKE '" . $this->db->escape($data['filter_article']) . "%'";
		}

		if (!empty($data['filter_author'])) {
			$sql .= " AND r.author LIKE '" . $this->db->escape($data['filter_author']) . "%'";
		}

		if (isset($data['filter_status']) && $data['filter_status'] !== '') {
			$sql .= " AND r.status = '" . (int)$data['filter_status'] . "'";
		}

		if (!empty($data['filter_date_added'])) {
			$sql .= " AND DATE(r.date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}

		$sort_data = array(
			'pd.name',
			'r.author',
			'r.rating',
			'r.status',
			'r.date_added'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data, true)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY r.date_added";
		}

		if (isset($data['order']) && $data['order'] == 'DESC') {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ((int)$data['start'] < 0) {
				$data['start'] = 0;
			}

			if ((int)$data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getTotalReviews($data = array()) {
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "review_article r LEFT JOIN " . DB_PREFIX . "article_description pd ON (r.article_id = pd.article_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_article'])) {
			$sql .= " AND pd.name LIKE '" . $this->db->escape($data['filter_article']) . "%'";
		}

		if (!empty($data['filter_author'])) {
			$sql .= " AND r.author LIKE '" . $this->db->escape($data['filter_author']) . "%'";
		}

		if (isset($data['filter_status']) && $data['filter_status'] !== '') {
			$sql .= " AND r.status = '" . (int)$data['filter_status'] . "'";
		}

		if (!empty($data['filter_date_added'])) {
			$sql .= " AND DATE(r.date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}

		$query = $this->db->query($sql);

		return (int)$query->row['total'];
	}

	public function getTotalReviewsAwaitingApproval() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "review_article WHERE status = '0'");

		return (int)$query->row['total'];
	}

	public function editStatus($review_article_id, $status) {
		$this->db->query("UPDATE " . DB_PREFIX . "review_article SET status = '" . (int)$status . "' WHERE review_article_id = '" . (int)$review_article_id . "'");
	}
}