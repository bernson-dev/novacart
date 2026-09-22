<?php
class ModelBlogArticle extends Model {
	public function addArticle($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "article SET status = '" . (int)$data['status'] . "', noindex = '" . (int)$data['noindex'] . "', sort_order = '" . (int)$data['sort_order'] . "', image = '" . $this->db->escape(isset($data['image']) ? $data['image'] : '') . "', date_added = NOW()");

		$article_id = $this->db->getLastId();

		if (isset($data['article_description']) && is_array($data['article_description'])) {
			foreach ($data['article_description'] as $language_id => $value) {
				$name = isset($value['name']) ? $value['name'] : '';
				$description = isset($value['description']) ? $value['description'] : '';
				$meta_title = isset($value['meta_title']) ? $value['meta_title'] : '';
				$meta_h1 = isset($value['meta_h1']) ? $value['meta_h1'] : '';
				$meta_description = isset($value['meta_description']) ? $value['meta_description'] : '';
				$meta_keyword = isset($value['meta_keyword']) ? $value['meta_keyword'] : '';

				$this->db->query("INSERT INTO " . DB_PREFIX . "article_description SET article_id = '" . (int)$article_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($name) . "', description = '" . $this->db->escape($description) . "', meta_title = '" . $this->db->escape($meta_title) . "', meta_h1 = '" . $this->db->escape($meta_h1) . "', meta_description = '" . $this->db->escape($meta_description) . "', meta_keyword = '" . $this->db->escape($meta_keyword) . "'");
			}
		}

		if (isset($data['article_store']) && is_array($data['article_store'])) {
			foreach ($data['article_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "article_to_store SET article_id = '" . (int)$article_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		if (isset($data['article_image']) && is_array($data['article_image'])) {
			foreach ($data['article_image'] as $article_image) {
				$image = isset($article_image['image']) ? $article_image['image'] : '';
				$sort_order = isset($article_image['sort_order']) ? (int)$article_image['sort_order'] : 0;

				$this->db->query("INSERT INTO " . DB_PREFIX . "article_image SET article_id = '" . (int)$article_id . "', image = '" . $this->db->escape($image) . "', sort_order = '" . $sort_order . "'");
			}
		}

		if (isset($data['article_download']) && is_array($data['article_download'])) {
			foreach ($data['article_download'] as $download_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "article_to_download SET article_id = '" . (int)$article_id . "', download_id = '" . (int)$download_id . "'");
			}
		}

		if (isset($data['article_blog_category']) && is_array($data['article_blog_category'])) {
			foreach ($data['article_blog_category'] as $blog_category_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "article_to_blog_category SET article_id = '" . (int)$article_id . "', blog_category_id = '" . (int)$blog_category_id . "'");
			}
		}

		$this->setArticleMainCategory($article_id, $data);

		if (isset($data['article_related']) && is_array($data['article_related'])) {
			foreach ($data['article_related'] as $related_id) {
				$related_id = (int)$related_id;

				$this->db->query("DELETE FROM " . DB_PREFIX . "article_related WHERE article_id = '" . (int)$article_id . "' AND related_id = '" . $related_id . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "article_related SET article_id = '" . (int)$article_id . "', related_id = '" . $related_id . "'");

				$this->db->query("DELETE FROM " . DB_PREFIX . "article_related WHERE article_id = '" . $related_id . "' AND related_id = '" . (int)$article_id . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "article_related SET article_id = '" . $related_id . "', related_id = '" . (int)$article_id . "'");
			}
		}

		if (isset($data['product_related']) && is_array($data['product_related'])) {
			foreach ($data['product_related'] as $related_id) {
				$this->db->query("DELETE FROM " . DB_PREFIX . "article_related_product WHERE article_id = '" . (int)$article_id . "' AND product_id = '" . (int)$related_id . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "article_related_product SET article_id = '" . (int)$article_id . "', product_id = '" . (int)$related_id . "'");
			}
		}

		if (isset($data['article_seo_url']) && is_array($data['article_seo_url'])) {
			foreach ($data['article_seo_url'] as $store_id => $language) {
				if (!is_array($language)) {
					continue;
				}

				foreach ($language as $language_id => $keyword) {
					$keyword = trim((string)$keyword);

					if ($keyword !== '') {
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'article_id=" . (int)$article_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}

		if (isset($data['article_layout']) && is_array($data['article_layout'])) {
			foreach ($data['article_layout'] as $store_id => $layout_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "article_to_layout SET article_id = '" . (int)$article_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
			}
		}

		$this->cache->delete('article');

		if ($this->config->get('config_seo_pro')) {
			$this->cache->delete('seopro');
		}

		return $article_id;
	}

	public function editArticle($article_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "article SET status = '" . (int)$data['status'] . "', noindex = '" . (int)$data['noindex'] . "', sort_order = '" . (int)$data['sort_order'] . "', image = '" . $this->db->escape(isset($data['image']) ? $data['image'] : '') . "', date_modified = NOW() WHERE article_id = '" . (int)$article_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "article_description WHERE article_id = '" . (int)$article_id . "'");

		if (isset($data['article_description']) && is_array($data['article_description'])) {
			foreach ($data['article_description'] as $language_id => $value) {
				$name = isset($value['name']) ? $value['name'] : '';
				$description = isset($value['description']) ? $value['description'] : '';
				$meta_title = isset($value['meta_title']) ? $value['meta_title'] : '';
				$meta_h1 = isset($value['meta_h1']) ? $value['meta_h1'] : '';
				$meta_description = isset($value['meta_description']) ? $value['meta_description'] : '';
				$meta_keyword = isset($value['meta_keyword']) ? $value['meta_keyword'] : '';

				$this->db->query("INSERT INTO " . DB_PREFIX . "article_description SET article_id = '" . (int)$article_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($name) . "', description = '" . $this->db->escape($description) . "', meta_title = '" . $this->db->escape($meta_title) . "', meta_h1 = '" . $this->db->escape($meta_h1) . "', meta_description = '" . $this->db->escape($meta_description) . "', meta_keyword = '" . $this->db->escape($meta_keyword) . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "article_to_store WHERE article_id = '" . (int)$article_id . "'");

		if (isset($data['article_store']) && is_array($data['article_store'])) {
			foreach ($data['article_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "article_to_store SET article_id = '" . (int)$article_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "article_image WHERE article_id = '" . (int)$article_id . "'");

		if (isset($data['article_image']) && is_array($data['article_image'])) {
			foreach ($data['article_image'] as $article_image) {
				$image = isset($article_image['image']) ? $article_image['image'] : '';
				$sort_order = isset($article_image['sort_order']) ? (int)$article_image['sort_order'] : 0;

				$this->db->query("INSERT INTO " . DB_PREFIX . "article_image SET article_id = '" . (int)$article_id . "', image = '" . $this->db->escape($image) . "', sort_order = '" . $sort_order . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "article_to_download WHERE article_id = '" . (int)$article_id . "'");

		if (isset($data['article_download']) && is_array($data['article_download'])) {
			foreach ($data['article_download'] as $download_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "article_to_download SET article_id = '" . (int)$article_id . "', download_id = '" . (int)$download_id . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "article_to_blog_category WHERE article_id = '" . (int)$article_id . "'");

		if (isset($data['article_blog_category']) && is_array($data['article_blog_category'])) {
			foreach ($data['article_blog_category'] as $blog_category_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "article_to_blog_category SET article_id = '" . (int)$article_id . "', blog_category_id = '" . (int)$blog_category_id . "'");
			}
		}

		$this->setArticleMainCategory($article_id, $data);

		$this->db->query("DELETE FROM " . DB_PREFIX . "article_related WHERE article_id = '" . (int)$article_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "article_related WHERE related_id = '" . (int)$article_id . "'");

		if (isset($data['article_related']) && is_array($data['article_related'])) {
			foreach ($data['article_related'] as $related_id) {
				$related_id = (int)$related_id;

				$this->db->query("INSERT INTO " . DB_PREFIX . "article_related SET article_id = '" . (int)$article_id . "', related_id = '" . $related_id . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "article_related SET article_id = '" . $related_id . "', related_id = '" . (int)$article_id . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "article_related_product WHERE article_id = '" . (int)$article_id . "'");

		if (isset($data['product_related']) && is_array($data['product_related'])) {
			foreach ($data['product_related'] as $related_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "article_related_product SET article_id = '" . (int)$article_id . "', product_id = '" . (int)$related_id . "'");
			}
		}

		// SEO URL
		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'article_id=" . (int)$article_id . "'");

		if (isset($data['article_seo_url']) && is_array($data['article_seo_url'])) {
			foreach ($data['article_seo_url'] as $store_id => $language) {
				if (!is_array($language)) {
					continue;
				}

				foreach ($language as $language_id => $keyword) {
					$keyword = trim((string)$keyword);

					if ($keyword !== '') {
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'article_id=" . (int)$article_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "article_to_layout WHERE article_id = '" . (int)$article_id . "'");

		if (isset($data['article_layout']) && is_array($data['article_layout'])) {
			foreach ($data['article_layout'] as $store_id => $layout_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "article_to_layout SET article_id = '" . (int)$article_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
			}
		}

		$this->cache->delete('article');

		if ($this->config->get('config_seo_pro')) {
			$this->cache->delete('seopro');
		}
	}

	public function editArticleStatus($article_id, $status) {
		$this->db->query("UPDATE " . DB_PREFIX . "article SET status = '" . (int)$status . "', date_modified = NOW() WHERE article_id = '" . (int)$article_id . "'");

		$this->cache->delete('article');

		if ($this->config->get('config_seo_pro')) {
			$this->cache->delete('seopro');
		}

		return $article_id;
	}

	public function copyArticle($article_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "article p LEFT JOIN " . DB_PREFIX . "article_description pd ON (p.article_id = pd.article_id) WHERE p.article_id = '" . (int)$article_id . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		if ($query->num_rows) {
			$data = $query->row;

			$data['status'] = '0';
			$data['noindex'] = '0';

			$data['article_description'] = $this->getArticleDescriptions($article_id);
			$data['article_image'] = $this->getArticleImages($article_id);
			$data['article_related'] = $this->getArticleRelated($article_id);
			$data['product_related'] = $this->getProductRelated($article_id);
			$data['article_blog_category'] = $this->getArticleCategories($article_id);
			$data['main_blog_category_id'] = $this->getArticleMainCategoryId($article_id);
			$data['article_download'] = $this->getArticleDownloads($article_id);
			$data['article_layout'] = $this->getArticleLayouts($article_id);
			$data['article_store'] = $this->getArticleStores($article_id);

			$this->addArticle($data);
		}
	}

	public function deleteArticle($article_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "article WHERE article_id = '" . (int)$article_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "article_description WHERE article_id = '" . (int)$article_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "article_image WHERE article_id = '" . (int)$article_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "article_related WHERE article_id = '" . (int)$article_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "article_related WHERE related_id = '" . (int)$article_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "article_related_product WHERE article_id = '" . (int)$article_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "article_to_blog_category WHERE article_id = '" . (int)$article_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "article_to_download WHERE article_id = '" . (int)$article_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "article_to_layout WHERE article_id = '" . (int)$article_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "article_to_store WHERE article_id = '" . (int)$article_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "review_article WHERE article_id = '" . (int)$article_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'article_id=" . (int)$article_id . "'");

		$this->cache->delete('article');

		if ($this->config->get('config_seo_pro')) {
			$this->cache->delete('seopro');
		}
	}

	public function getArticle($article_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "article p LEFT JOIN " . DB_PREFIX . "article_description pd ON (p.article_id = pd.article_id) WHERE p.article_id = '" . (int)$article_id . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	public function getArticles($data = array()) {
		$sql = "SELECT p.article_id, p.image, p.status, p.noindex, p.sort_order, pd.name
			FROM " . DB_PREFIX . "article p
			LEFT JOIN " . DB_PREFIX . "article_description pd ON (p.article_id = pd.article_id)
			WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND pd.name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (isset($data['filter_status']) && $data['filter_status'] !== null) {
			$sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";
		}

		if (isset($data['filter_noindex']) && $data['filter_noindex'] !== null) {
			$sql .= " AND p.noindex = '" . (int)$data['filter_noindex'] . "'";
		}

		$sort_data = array(
			'pd.name',
			'p.status',
			'p.noindex',
			'p.sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data, true)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY pd.name";
		}

		$sql .= (isset($data['order']) && $data['order'] === 'DESC') ? " DESC" : " ASC";

		if (isset($data['start']) || isset($data['limit'])) {
			$start = isset($data['start']) ? (int)$data['start'] : 0;
			$limit = isset($data['limit']) ? (int)$data['limit'] : 20;

			if ($start < 0) {
				$start = 0;
			}

			if ($limit < 1) {
				$limit = 20;
			}

			$sql .= " LIMIT " . $start . "," . $limit;
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getArticlesByCategoryId($blog_category_id) {
		$query = $this->db->query("SELECT DISTINCT p.article_id, p.image, p.status, p.noindex, p.sort_order, pd.name FROM " . DB_PREFIX . "article p LEFT JOIN " . DB_PREFIX . "article_description pd ON (p.article_id = pd.article_id) LEFT JOIN " . DB_PREFIX . "article_to_blog_category p2c ON (p.article_id = p2c.article_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p2c.blog_category_id = '" . (int)$blog_category_id . "' ORDER BY pd.name ASC");

		return $query->rows;
	}

	public function getArticleDescriptions($article_id) {
		$article_description_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "article_description WHERE article_id = '" . (int)$article_id . "'");

		foreach ($query->rows as $result) {
			$article_description_data[$result['language_id']] = array(
				'name'             => $result['name'],
				'description'      => $result['description'],
				'meta_title'       => $result['meta_title'],
				'meta_h1'          => isset($result['meta_h1']) ? $result['meta_h1'] : '',
				'meta_description' => $result['meta_description'],
				'meta_keyword'     => $result['meta_keyword']
			);
		}

		return $article_description_data;
	}

	public function getArticleCategories($article_id) {
		$article_blog_category_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "article_to_blog_category WHERE article_id = '" . (int)$article_id . "'");

		foreach ($query->rows as $result) {
			$article_blog_category_data[] = $result['blog_category_id'];
		}

		return $article_blog_category_data;
	}

	public function getArticleMainCategoryId($article_id) {
		$query = $this->db->query("SELECT blog_category_id FROM " . DB_PREFIX . "article_to_blog_category WHERE article_id = '" . (int)$article_id . "' AND main_blog_category = '1' LIMIT 1");

		return ($query->num_rows ? (int)$query->row['blog_category_id'] : 0);
	}

	public function getArticleImages($article_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "article_image WHERE article_id = '" . (int)$article_id . "' ORDER BY sort_order ASC");

		return $query->rows;
	}

	public function getArticleDownloads($article_id) {
		$article_download_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "article_to_download WHERE article_id = '" . (int)$article_id . "'");

		foreach ($query->rows as $result) {
			$article_download_data[] = $result['download_id'];
		}

		return $article_download_data;
	}

	public function getArticleStores($article_id) {
		$article_store_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "article_to_store WHERE article_id = '" . (int)$article_id . "'");

		foreach ($query->rows as $result) {
			$article_store_data[] = $result['store_id'];
		}

		return $article_store_data;
	}

	public function getArticleSeoUrls($article_id) {
		$article_seo_url_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE query = 'article_id=" . (int)$article_id . "'");

		foreach ($query->rows as $result) {
			$article_seo_url_data[$result['store_id']][$result['language_id']] = $result['keyword'];
		}

		return $article_seo_url_data;
	}

	public function getArticleLayouts($article_id) {
		$article_layout_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "article_to_layout WHERE article_id = '" . (int)$article_id . "'");

		foreach ($query->rows as $result) {
			$article_layout_data[$result['store_id']] = $result['layout_id'];
		}

		return $article_layout_data;
	}

	public function getArticleRelated($article_id) {
		$article_related_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "article_related WHERE article_id = '" . (int)$article_id . "'");

		foreach ($query->rows as $result) {
			$article_related_data[] = $result['related_id'];
		}

		return $article_related_data;
	}

	public function getProductRelated($article_id) {
		$article_related_product = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "article_related_product WHERE article_id = '" . (int)$article_id . "'");

		foreach ($query->rows as $result) {
			$article_related_product[] = $result['product_id'];
		}

		return $article_related_product;
	}

	public function getTotalArticles($data = array()) {
		$sql = "SELECT COUNT(DISTINCT p.article_id) AS total FROM " . DB_PREFIX . "article p LEFT JOIN " . DB_PREFIX . "article_description pd ON (p.article_id = pd.article_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND pd.name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (isset($data['filter_status']) && $data['filter_status'] !== null) {
			$sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";
		}

		if (isset($data['filter_noindex']) && $data['filter_noindex'] !== null) {
			$sql .= " AND p.noindex = '" . (int)$data['filter_noindex'] . "'";
		}

		$query = $this->db->query($sql);

		return (int)$query->row['total'];
	}

	public function getTotalArticlesByDownloadId($download_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "article_to_download WHERE download_id = '" . (int)$download_id . "'");

		return (int)$query->row['total'];
	}

	public function getTotalArticlesByLayoutId($layout_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "article_to_layout WHERE layout_id = '" . (int)$layout_id . "'");

		return (int)$query->row['total'];
	}

	private function setArticleMainCategory($article_id, $data) {
		$main_blog_category_id = 0;

		if (isset($data['main_blog_category_id']) && (int)$data['main_blog_category_id'] > 0) {
			$main_blog_category_id = (int)$data['main_blog_category_id'];
		} elseif (!empty($data['article_blog_category']) && is_array($data['article_blog_category'])) {
			$first_category_id = reset($data['article_blog_category']);
			$main_blog_category_id = (int)$first_category_id;
		}

		if ($main_blog_category_id > 0) {
			$this->db->query("UPDATE " . DB_PREFIX . "article_to_blog_category SET main_blog_category = '0' WHERE article_id = '" . (int)$article_id . "'");

			$this->db->query("DELETE FROM " . DB_PREFIX . "article_to_blog_category WHERE article_id = '" . (int)$article_id . "' AND blog_category_id = '" . $main_blog_category_id . "'");
			$this->db->query("INSERT INTO " . DB_PREFIX . "article_to_blog_category SET article_id = '" . (int)$article_id . "', blog_category_id = '" . $main_blog_category_id . "', main_blog_category = '1'");
		}
	}
}