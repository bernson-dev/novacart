<?php
// *    @source     See SOURCE.txt for source and other copyright.
// *    @license    GNU General Public License version 3; see LICENSE.txt

class ControllerStartupSeoUrl extends Controller {

	//seopro start
	private $seo_pro;
	public function __construct($registry) {
		parent::__construct($registry);
		// Инициализируем SeoPro только если он включен в конфиге
		if ($this->config->get('config_seo_pro')) {
			$this->seo_pro = new SeoPro($registry);
		}
	}
	//seopro end

	public function index() {

		// Add rewrite to url class
		if ($this->config->get('config_seo_url')) {
			$this->url->addRewrite($this);
		}

		// Decode URL
		if (isset($this->request->get['_route_'])) {
			$parts = explode('/', $this->request->get['_route_']);

			//seopro prepare route
			if ($this->config->get('config_seo_pro')) {
				$parts = $this->seo_pro->prepareRoute($parts);
			}
			//seopro prepare route end

			// remove any empty arrays from trailing
			if (strlen(end($parts)) == 0) {
				array_pop($parts);
			}

			foreach ($parts as $part) {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url
                    WHERE keyword = '" . $this->db->escape($part) . "'
                      AND store_id = '" . (int)$this->config->get('config_store_id') . "'
                    LIMIT 1");

				if ($query->num_rows) {
					$url = explode('=', $query->row['query']);

					if ($url[0] == 'product_id') {
						$this->request->get['product_id'] = $url[1];
					}

					if ($url[0] == 'category_id') {
						if (!isset($this->request->get['path'])) {
							$this->request->get['path'] = $url[1];
						} else {
							$this->request->get['path'] .= '_' . $url[1];
						}
					}

					if ($url[0] == 'manufacturer_id') {
						$this->request->get['manufacturer_id'] = $url[1];
					}

					if ($url[0] == 'information_id') {
						$this->request->get['information_id'] = $url[1];
					}

					// Blog
					if ($url[0] == 'blog_category_id') {
						$this->request->get['blog_category_id'] = $url[1];
					}

					if ($url[0] == 'article_id') {
						$this->request->get['article_id'] = $url[1];
					}

					if ($query->row['query']
					&& $url[0] != 'information_id'
					&& $url[0] != 'manufacturer_id'
					&& $url[0] != 'category_id'
					&& $url[0] != 'product_id'
					&& $url[0] != 'blog_category_id'
					&& $url[0] != 'article_id') {
						$this->request->get['route'] = $query->row['query'];
					}
				} else {
					if (!$this->config->get('config_seo_pro')) {
						$this->request->get['route'] = 'error/not_found';
					}
					break;
				}
			}

			if (!isset($this->request->get['route'])) {
				if (isset($this->request->get['product_id'])) {
					$this->request->get['route'] = 'product/product';
				} elseif (isset($this->request->get['path'])) {
					$this->request->get['route'] = 'product/category';
				} elseif (isset($this->request->get['manufacturer_id'])) {
					$this->request->get['route'] = 'product/manufacturer/info';
				} elseif (isset($this->request->get['information_id'])) {
					$this->request->get['route'] = 'information/information';
				} elseif (isset($this->request->get['blog_category_id'])) {
					$this->request->get['route'] = 'blog/category';
				} elseif (isset($this->request->get['article_id'])) {
					$this->request->get['route'] = 'blog/article';
				}
			}
		}

		// Валидация SeoPro
		if ($this->config->get('config_seo_pro') && $this->seo_pro) {
			$this->seo_pro->validate();
		}
		//seopro validate
	}

	public function rewrite($link) {
		$url_info = parse_url(str_replace('&amp;', '&', $link));

		$data = array();
		if (isset($url_info['query'])) {
			parse_str($url_info['query'], $data);
		}

		// Если ссылка ведёт на главную страницу — возвращаем корень
		if (isset($data['route']) && $data['route'] == 'common/home' && !$this->config->get('config_seo_pro')) {
			return $url_info['scheme'] . '://' . $url_info['host'] . (isset($url_info['port']) ? ':' . $url_info['port'] : '') . '/';
		}

		if ($this->config->get('config_seo_pro')) {
			$url = null;
		} else {
			$url = '';
		}

		//seo_pro baseRewrite
		if ($this->config->get('config_seo_pro')) {
			list($url, $data, $postfix) = $this->seo_pro->baseRewrite($data, (int)$this->config->get('config_language_id'));
		}
		//seo_pro baseRewrite

		foreach ($data as $key => $value) {
			if (isset($data['route'])) {
				// стандартные сущности
				if (($data['route'] == 'product/product' && $key == 'product_id')
				|| (($data['route'] == 'product/manufacturer/info' || $data['route'] == 'product/product') && $key == 'manufacturer_id')
				|| ($data['route'] == 'information/information' && $key == 'information_id')) {

					$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url
                        WHERE `query` = '" . $this->db->escape($key . '=' . (int)$value) . "'
                          AND store_id = '" . (int)$this->config->get('config_store_id') . "'
                          AND language_id = '" . (int)$this->config->get('config_language_id') . "'");

					if ($query->num_rows && $query->row['keyword']) {
						$url .= '/' . $query->row['keyword'];
						unset($data[$key]);
					}
				} elseif ($key == 'path') {
					$categories = explode('_', $value);

					foreach ($categories as $category) {
						$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url
                            WHERE `query` = 'category_id=" . (int)$category . "'
                              AND store_id = '" . (int)$this->config->get('config_store_id') . "'
                              AND language_id = '" . (int)$this->config->get('config_language_id') . "'");

						if ($query->num_rows && $query->row['keyword']) {
							$url .= '/' . $query->row['keyword'];
						} else {
							$url = '';

							break;
						}
					}

					unset($data[$key]);
				}

				// поддержка блога: категории и статьи
				if ($data['route'] == 'blog/category' && $key == 'blog_category_id') {
					$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url
                        WHERE `query` = 'blog_category_id=" . (int)$value . "'
                          AND store_id = '" . (int)$this->config->get('config_store_id') . "'
                          AND language_id = '" . (int)$this->config->get('config_language_id') . "'");

					if ($query->num_rows && $query->row['keyword']) {
						$url .= '/' . $query->row['keyword'];
						unset($data[$key]);
					}
				}

				if ($data['route'] == 'blog/article' && $key == 'article_id') {
					$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url
                        WHERE `query` = 'article_id=" . (int)$value . "'
                          AND store_id = '" . (int)$this->config->get('config_store_id') . "'
                          AND language_id = '" . (int)$this->config->get('config_language_id') . "'");

					if ($query->num_rows && $query->row['keyword']) {
						$url .= '/' . $query->row['keyword'];
						unset($data[$key]);
					}
				}
			}
		}

		//seo_pro add blank url
		unset($data['route']);

		$query = '';

		if ($data) {
			foreach ($data as $key => $value) {
				$query .= '&' . rawurlencode((string)$key) . '=' . rawurlencode((is_array($value) ? http_build_query($value) : (string)$value));
			}

			if ($query) {
				$query = '?' . str_replace('&', '&amp;', trim($query, '&'));
			}
		}

		if ($this->config->get('config_seo_pro')) {
			$condition = ($url !== null);
		} else {
			$condition = $url;
		}

		if ($condition) {
			if ($this->config->get('config_seo_pro')) {
				if ($this->config->get('config_page_postfix') && !empty($postfix)) {
					$url .= $this->config->get('config_page_postfix');
				} elseif ($this->config->get('config_seopro_addslash') || !empty($query)) {
					$url .= '/';
				}
			}

			return $url_info['scheme'] . '://' . $url_info['host'] . (isset($url_info['port']) ? ':' . $url_info['port'] : '') . str_replace('/index.php', '', $url_info['path']) . $url . $query;
		} else {
			return $link;
		}
	}
}
