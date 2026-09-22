<?php
// *    @source     See SOURCE.txt for source and other copyright.
// *    @license    GNU General Public License version 3; see LICENSE.txt

class ControllerStartupSeoUrl extends Controller {
	private $seo_pro;
	private $seo_pro_enabled = false;

	public function __construct($registry) {
		parent::__construct($registry);

		// config_seo_url is the master switch for both standard SEO URL and SeoPro.
		$this->seo_pro_enabled = (bool)$this->config->get('config_seo_url') && (bool)$this->config->get('config_seo_pro');

		if ($this->seo_pro_enabled) {
			$this->seo_pro = new SeoPro($registry);
		}
	}

	public function index() {
		// Add rewrite to url class.
		if ($this->config->get('config_seo_url')) {
			$this->url->addRewrite($this);
		}

		// Decode URL.
		if (isset($this->request->get['_route_'])) {
			$parts = explode('/', (string)$this->request->get['_route_']);

			if ($this->seo_pro_enabled) {
				$parts = $this->seo_pro->prepareRoute($parts);
			}

			// Remove trailing empty part.
			if ($parts && strlen((string)end($parts)) == 0) {
				array_pop($parts);
			}

			$route_language_id = null;
			$route_parts = array_values(array_filter($parts, function($part) {
				return trim((string)$part) !== '';
			}));

			foreach ($parts as $part) {
				if ($part === '') {
					continue;
				}

				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url
					WHERE keyword = '" . $this->db->escape($part) . "'
					AND store_id = '" . (int)$this->config->get('config_store_id') . "'
					LIMIT 1");

				if ($query->num_rows) {
					// Standard seo_url detects the language from the first unique keyword
					// and rejects mixed-language paths.
					if (!$this->seo_pro_enabled && isset($query->row['language_id'])) {
						$current_language_id = (int)$query->row['language_id'];

						if ($route_language_id === null) {
							$route_language_id = $current_language_id;
							$this->applyLanguage($current_language_id);
						} elseif ($route_language_id !== $current_language_id) {
							$this->request->get['route'] = 'error/not_found';
							break;
						}
					}

					$url = explode('=', (string)$query->row['query'], 2);

					if ($url[0] == 'product_id' && isset($url[1])) {
						$this->request->get['product_id'] = $url[1];
					}

					// Keep this condition as a stable OCMOD anchor used by third-party SEO extensions.
					if ($url[0] == 'category_id') {
						if (isset($url[1])) {
							if (!isset($this->request->get['path'])) {
								$this->request->get['path'] = $url[1];
							} else {
								$this->request->get['path'] .= '_' . $url[1];
							}
						}
					}

					if ($url[0] == 'manufacturer_id' && isset($url[1])) {
						$this->request->get['manufacturer_id'] = $url[1];
					}

					if ($url[0] == 'information_id' && isset($url[1])) {
						$this->request->get['information_id'] = $url[1];
					}

					// Blog.
					if ($url[0] == 'blog_category_id' && isset($url[1])) {
						if (!isset($this->request->get['blog_category_id'])) {
							$this->request->get['blog_category_id'] = $url[1];
						} else {
							$this->request->get['blog_category_id'] .= '_' . $url[1];
						}
					}

					if ($url[0] == 'article_id' && isset($url[1])) {
						$this->request->get['article_id'] = $url[1];
					}

					if (
						$query->row['query']
						&& $url[0] != 'information_id'
						&& $url[0] != 'manufacturer_id'
						&& $url[0] != 'category_id'
						&& $url[0] != 'product_id'
						&& $url[0] != 'blog_category_id'
						&& $url[0] != 'article_id'
					) {
						$this->request->get['route'] = $query->row['query'];
					}
				} else {
					if (!$this->seo_pro_enabled) {
						// Octemplates Deals uses short language-home aliases
						// (/ru, /ua, ...). Resolve only a single unknown segment,
						// so normal SEO keywords keep their standard priority.
						if (count($route_parts) === 1) {
							$language_info = $this->getOctDealsLanguageByAlias($part);

							if ($language_info) {
								$this->applyLanguage((int)$language_info['language_id']);
								$this->request->get['route'] = 'common/home';
								break;
							}
						}

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
				} elseif (isset($this->request->get['article_id'])) {
					$this->request->get['route'] = 'blog/article';
				} elseif (isset($this->request->get['blog_category_id'])) {
					$this->request->get['route'] = 'blog/category';
				}
			}
		}

		if ($this->seo_pro_enabled && $this->seo_pro) {
			$this->seo_pro->validate();
		}
	}

	public function rewrite($link) {
		$url_info = parse_url(str_replace('&amp;', '&', $link));

		if (!$url_info || empty($url_info['scheme']) || empty($url_info['host'])) {
			return $link;
		}

		$data = array();

		if (isset($url_info['query'])) {
			parse_str($url_info['query'], $data);
		}

		$url = '';
		$postfix = false;
		$rewritten = false;

		if ($this->seo_pro_enabled) {
			list($url, $data, $postfix) = $this->seo_pro->baseRewrite($data, (int)$this->config->get('config_language_id'));
			$rewritten = ($url !== null);
		}

		foreach ($data as $key => $value) {
			if (!isset($data['route'])) {
				continue;
			}

			// Standard entities.
			// Keep this expression on one line: third-party OCMOD packages extend this exact condition.
			if (($data['route'] == 'product/product' && $key == 'product_id') || (($data['route'] == 'product/manufacturer/info' || $data['route'] == 'product/product') && $key == 'manufacturer_id') || ($data['route'] == 'information/information' && $key == 'information_id')) {
				$query = $this->db->query("SELECT keyword FROM " . DB_PREFIX . "seo_url
					WHERE `query` = '" . $this->db->escape($key . '=' . (int)$value) . "'
					AND store_id = '" . (int)$this->config->get('config_store_id') . "'
					AND language_id = '" . (int)$this->config->get('config_language_id') . "'
					LIMIT 1");

				if ($query->num_rows && $query->row['keyword'] !== '') {
					$url .= '/' . rawurlencode($query->row['keyword']);
					unset($data[$key]);

					if (!$this->seo_pro_enabled) {
						$rewritten = true;
					}
				}
			} elseif ($key == 'path') {
				$categories = explode('_', (string)$value);
				$path_url = '';
				$path_valid = true;

				foreach ($categories as $category) {
					$query = $this->db->query("SELECT keyword FROM " . DB_PREFIX . "seo_url
						WHERE `query` = 'category_id=" . (int)$category . "'
						AND store_id = '" . (int)$this->config->get('config_store_id') . "'
						AND language_id = '" . (int)$this->config->get('config_language_id') . "'
						LIMIT 1");

					if ($query->num_rows && $query->row['keyword'] !== '') {
						$path_url .= '/' . rawurlencode($query->row['keyword']);
					} else {
						$path_valid = false;
						break;
					}
				}

				if ($path_valid && $path_url !== '') {
					$url .= $path_url;
					unset($data[$key]);

					if (!$this->seo_pro_enabled) {
						$rewritten = true;
					}
				}
			}

			// Blog category paths may contain multiple IDs separated by "_".
			if (
				($data['route'] == 'blog/category' || $data['route'] == 'blog/article')
				&& $key == 'blog_category_id'
			) {
				$blog_categories = explode('_', (string)$value);
				$blog_url = '';
				$blog_valid = true;

				foreach ($blog_categories as $blog_category_id) {
					$query = $this->db->query("SELECT keyword FROM " . DB_PREFIX . "seo_url
						WHERE `query` = 'blog_category_id=" . (int)$blog_category_id . "'
						AND store_id = '" . (int)$this->config->get('config_store_id') . "'
						AND language_id = '" . (int)$this->config->get('config_language_id') . "'
						LIMIT 1");

					if ($query->num_rows && $query->row['keyword'] !== '') {
						$blog_url .= '/' . rawurlencode($query->row['keyword']);
					} else {
						$blog_valid = false;
						break;
					}
				}

				if ($blog_valid && $blog_url !== '') {
					$url .= $blog_url;
					unset($data[$key]);

					if (!$this->seo_pro_enabled) {
						$rewritten = true;
					}
				}
			}

			if ($data['route'] == 'blog/article' && $key == 'article_id') {
				$query = $this->db->query("SELECT keyword FROM " . DB_PREFIX . "seo_url
					WHERE `query` = 'article_id=" . (int)$value . "'
					AND store_id = '" . (int)$this->config->get('config_store_id') . "'
					AND language_id = '" . (int)$this->config->get('config_language_id') . "'
					LIMIT 1");

				if ($query->num_rows && $query->row['keyword'] !== '') {
					$url .= '/' . rawurlencode($query->row['keyword']);
					unset($data[$key]);

					if (!$this->seo_pro_enabled) {
						$rewritten = true;
					}
				}
			}
		}

		// Backward compatibility for OCMOD extensions written for the classic seo_url:
		// older modifications append a valid SEO path to $url but do not set $rewritten.
		if (!$this->seo_pro_enabled && !$rewritten && $url !== '') {
			$rewritten = true;
		}

		// Standard seo_url also supports route aliases stored directly in seo_url.
		// This includes multilingual home aliases such as common/home => "" / "uk".
		if (!$this->seo_pro_enabled && !$rewritten && isset($data['route'])) {
			$route_query = $this->db->query("SELECT keyword FROM " . DB_PREFIX . "seo_url
				WHERE `query` = '" . $this->db->escape((string)$data['route']) . "'
				AND store_id = '" . (int)$this->config->get('config_store_id') . "'
				AND language_id = '" . (int)$this->config->get('config_language_id') . "'
				LIMIT 1");

			if ($route_query->num_rows) {
				$rewritten = true;

				if ($route_query->row['keyword'] !== '') {
					$url = '/' . rawurlencode($route_query->row['keyword']);
				}
			}
		}

		unset($data['route']);

		$query = '';

		if ($data) {
			$query_string = http_build_query($data, '', '&', PHP_QUERY_RFC3986);

			if ($query_string !== '') {
				$query = '?' . str_replace('&', '&amp;', $query_string);
			}
		}

		if ($this->seo_pro_enabled && $rewritten) {
			if ($this->config->get('config_page_postfix') && !empty($postfix)) {
				$url .= $this->config->get('config_page_postfix');
			} elseif ($this->config->get('config_seopro_addslash') || !empty($query)) {
				$url .= '/';
			}
		}

		if (!$rewritten) {
			return $link;
		}

		$base_path = isset($url_info['path']) ? str_replace('/index.php', '', $url_info['path']) : '';
		$base_path = rtrim($base_path, '/');

		if ($url === '') {
			$path = $base_path . '/';
		} else {
			$path = $base_path . $url;
		}

		return $url_info['scheme'] . '://' . $url_info['host']
			. (isset($url_info['port']) ? ':' . $url_info['port'] : '')
			. $path
			. $query;
	}

	private function isOctDealsTheme() {
		$theme = strtolower((string)$this->config->get('config_theme'));

		return $theme === 'oct_deals'
			|| strpos($theme, 'oct_deals') !== false
			|| (bool)$this->config->get('theme_oct_deals_status');
	}

	private function getOctDealsLanguageAlias($code) {
		$code = strtolower((string)$code);

		return substr(str_replace('uk', 'ua', $code), 0, 2);
	}

	private function getOctDealsLanguageByAlias($alias) {
		if (!$this->isOctDealsTheme()) {
			return false;
		}

		$alias = strtolower(trim((string)$alias, '/'));

		if (!preg_match('/^[a-z]{2}$/', $alias)) {
			return false;
		}

		$query = $this->db->query("SELECT language_id, code FROM " . DB_PREFIX . "language
			WHERE status = '1'
			ORDER BY sort_order, name");

		foreach ($query->rows as $language) {
			if ($this->getOctDealsLanguageAlias($language['code']) === $alias) {
				return $language;
			}
		}

		return false;
	}

	private function applyLanguage($language_id) {
		$language_id = (int)$language_id;

		if ($language_id < 1 || $language_id == (int)$this->config->get('config_language_id')) {
			return;
		}

		$query = $this->db->query("SELECT code FROM " . DB_PREFIX . "language
			WHERE language_id = '" . $language_id . "'
			AND status = '1'
			LIMIT 1");

		if (!$query->num_rows) {
			return;
		}

		$code = (string)$query->row['code'];
		$this->session->data['language'] = $code;
		$this->config->set('config_language_id', $language_id);

		$language = new Language($code);
		$language->load($code);
		$this->registry->set('language', $language);
	}
}
