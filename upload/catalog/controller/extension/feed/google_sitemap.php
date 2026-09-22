<?php
class ControllerExtensionFeedGoogleSitemap extends Controller {
	public function index() {
		if ($this->config->get('feed_google_sitemap_status')) {
			if (
				$this->registry->has('seo_language')
				&& $this->seo_language instanceof SeoLanguage
				&& $this->seo_language->isEnabled()
			) {
				$this->response->addHeader('Content-Type: application/xml');
				$this->response->setOutput($this->buildMultilingualSitemap());
				return;
			}
			$output = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
			$output .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . PHP_EOL;

			$this->load->model('catalog/product');
			$this->load->model('tool/image');

			$products = $this->model_catalog_product->getProducts();

			foreach ($products as $product) {
				$output .= '<url>' . PHP_EOL;
				$output .= '  <loc>' . $this->url->link('product/product', 'product_id=' . $product['product_id']) . '</loc>' . PHP_EOL;
				$output .= '  <changefreq>weekly</changefreq>';
				$output .= '  <lastmod>' . date('Y-m-d\TH:i:sP', strtotime($product['date_modified'])) . '</lastmod>' . PHP_EOL;
				$output .= '  <priority>1.0</priority>' . PHP_EOL;

				if ($product['image']) {
					$output .= '<image:image>' . PHP_EOL;
					$output .= '  <image:loc>' . $this->model_tool_image->resize($product['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height')) . '</image:loc>' . PHP_EOL;
					$name = html_entity_decode($product['name'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
					$output .= '  <image:caption>' . htmlspecialchars($name, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</image:caption>' . PHP_EOL;
					$output .= '  <image:title>' . htmlspecialchars($name, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</image:title>' . PHP_EOL;
					$output .= '</image:image>' . PHP_EOL;
				}

				$output .= '</url>' . PHP_EOL;
			}

			$this->load->model('catalog/category');

			$output .= $this->getCategories(0);

			$this->load->model('catalog/manufacturer');

			$manufacturers = $this->model_catalog_manufacturer->getManufacturers();

			foreach ($manufacturers as $manufacturer) {
				$output .= '<url>' . PHP_EOL;
				$output .= '  <loc>' . $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $manufacturer['manufacturer_id']) . '</loc>' . PHP_EOL;
				$output .= '  <changefreq>weekly</changefreq>' . PHP_EOL;
				$output .= '  <priority>0.7</priority>' . PHP_EOL;
				$output .= '</url>' . PHP_EOL;

				$products = $this->model_catalog_product->getProducts(array('filter_manufacturer_id' => $manufacturer['manufacturer_id']));

				foreach ($products as $product) {
					$output .= '<url>' . PHP_EOL;
					$output .= '  <loc>' . $this->url->link('product/product', 'manufacturer_id=' . $manufacturer['manufacturer_id'] . '&product_id=' . $product['product_id']) . '</loc>' . PHP_EOL;
					$output .= '  <changefreq>weekly</changefreq>' . PHP_EOL;
					$output .= '  <priority>1.0</priority>' . PHP_EOL;
					$output .= '</url>' . PHP_EOL;
				}
			}

			$this->load->model('catalog/information');

			$informations = $this->model_catalog_information->getInformations();

			foreach ($informations as $information) {
				$output .= '<url>' . PHP_EOL;
				$output .= '  <loc>' . $this->url->link('information/information', 'information_id=' . $information['information_id']) . '</loc>' . PHP_EOL;
				$output .= '  <changefreq>weekly</changefreq>' . PHP_EOL;
				$output .= '  <priority>0.5</priority>' . PHP_EOL;
				$output .= '</url>' . PHP_EOL;
			}

			$output .= '</urlset>';

			$this->response->addHeader('Content-Type: application/xml');
			$this->response->setOutput($output);
		}
	}

	protected function getCategories($parent_id, $current_path = '') {
		$output = '';

		$results = $this->model_catalog_category->getCategories($parent_id);

		foreach ($results as $result) {
			if (!$current_path) {
				$new_path = $result['category_id'];
			} else {
				$new_path = $current_path . '_' . $result['category_id'];
			}

			$output .= '<url>' . PHP_EOL;
			$output .= '  <loc>' . $this->url->link('product/category', 'path=' . $new_path) . '</loc>' . PHP_EOL;
			$output .= '  <changefreq>weekly</changefreq>' . PHP_EOL;
			$output .= '  <priority>0.7</priority>' . PHP_EOL;
			$output .= '</url>' . PHP_EOL;

			$products = $this->model_catalog_product->getProducts(array('filter_category_id' => $result['category_id']));

			foreach ($products as $product) {
				$output .= '<url>' . PHP_EOL;
				$output .= '  <loc>' . $this->url->link('product/product', 'path=' . $new_path . '&product_id=' . $product['product_id']) . '</loc>' . PHP_EOL;
				$output .= '  <changefreq>weekly</changefreq>' . PHP_EOL;
				$output .= '  <priority>1.0</priority>' . PHP_EOL;
				$output .= '</url>' . PHP_EOL;
			}

			$output .= $this->getCategories($result['category_id'], $new_path);
		}

		return $output;
	}

	/**
	 * Build a multilingual sitemap with reciprocal hreflang links.
	 *
	 * Every localized URL is emitted as its own <url> entry and references all
	 * available translations plus x-default. Entity IDs are loaded without
	 * changing config_language_id, avoiding language-model cache side effects.
	 */
	private function buildMultilingualSitemap() {
		$output = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
		$output .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"';
		$output .= ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"';
		$output .= ' xmlns:xhtml="http://www.w3.org/1999/xhtml">' . PHP_EOL;

		$output .= $this->renderMultilingualUrl('common/home', array(), 'daily', '1.0');

		$product_query = $this->db->query("SELECT p.product_id, p.image, p.date_modified
			FROM " . DB_PREFIX . "product p
			INNER JOIN " . DB_PREFIX . "product_to_store p2s ON (p2s.product_id = p.product_id)
			WHERE p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'
			AND p.status = '1'
			AND p.date_available <= NOW()
			ORDER BY p.product_id");

		foreach ($product_query->rows as $product) {
			/*
			 * Sitemap generation must never create image cache files. Calling
			 * ModelToolImage::resize() here scales with the whole catalog and can
			 * easily exhaust max_execution_time on the first sitemap request.
			 * Search engines accept the original product image URL directly.
			 */
			$image = !empty($product['image'])
				? $this->getOriginalImageUrl($product['image'])
				: '';

			$output .= $this->renderMultilingualUrl(
				'product/product',
				array('product_id' => (int)$product['product_id']),
				'weekly',
				'1.0',
				!empty($product['date_modified']) ? $product['date_modified'] : '',
				$image
			);
		}

		$category_paths = $this->getCategoryPaths();
		$category_query = $this->db->query("SELECT c.category_id
			FROM " . DB_PREFIX . "category c
			INNER JOIN " . DB_PREFIX . "category_to_store c2s ON (c2s.category_id = c.category_id)
			WHERE c2s.store_id = '" . (int)$this->config->get('config_store_id') . "'
			AND c.status = '1'
			ORDER BY c.category_id");

		foreach ($category_query->rows as $category) {
			$category_id = (int)$category['category_id'];

			if (!isset($category_paths[$category_id])) {
				continue;
			}

			$output .= $this->renderMultilingualUrl(
				'product/category',
				array('path' => $category_paths[$category_id]),
				'weekly',
				'0.7'
			);
		}

		$manufacturer_query = $this->db->query("SELECT m.manufacturer_id
			FROM " . DB_PREFIX . "manufacturer m
			INNER JOIN " . DB_PREFIX . "manufacturer_to_store m2s ON (m2s.manufacturer_id = m.manufacturer_id)
			WHERE m2s.store_id = '" . (int)$this->config->get('config_store_id') . "'
			ORDER BY m.manufacturer_id");

		foreach ($manufacturer_query->rows as $manufacturer) {
			$output .= $this->renderMultilingualUrl(
				'product/manufacturer/info',
				array('manufacturer_id' => (int)$manufacturer['manufacturer_id']),
				'weekly',
				'0.7'
			);
		}

		$information_query = $this->db->query("SELECT i.information_id
			FROM " . DB_PREFIX . "information i
			INNER JOIN " . DB_PREFIX . "information_to_store i2s ON (i2s.information_id = i.information_id)
			WHERE i2s.store_id = '" . (int)$this->config->get('config_store_id') . "'
			AND i.status = '1'
			ORDER BY i.information_id");

		foreach ($information_query->rows as $information) {
			$output .= $this->renderMultilingualUrl(
				'information/information',
				array('information_id' => (int)$information['information_id']),
				'weekly',
				'0.5'
			);
		}

		if ($this->tableExists(DB_PREFIX . 'blog_category')) {
			$blog_paths = $this->getBlogCategoryPaths();
			$blog_category_query = $this->db->query("SELECT c.blog_category_id
				FROM " . DB_PREFIX . "blog_category c
				INNER JOIN " . DB_PREFIX . "blog_category_to_store c2s ON (c2s.blog_category_id = c.blog_category_id)
				WHERE c2s.store_id = '" . (int)$this->config->get('config_store_id') . "'
				AND c.status = '1'
				ORDER BY c.blog_category_id");

			foreach ($blog_category_query->rows as $category) {
				$blog_category_id = (int)$category['blog_category_id'];

				if (!isset($blog_paths[$blog_category_id])) {
					continue;
				}

				$output .= $this->renderMultilingualUrl(
					'blog/category',
					array('blog_category_id' => $blog_paths[$blog_category_id]),
					'weekly',
					'0.6'
				);
			}
		}

		if ($this->tableExists(DB_PREFIX . 'article')) {
			$article_query = $this->db->query("SELECT a.article_id, a.date_modified
				FROM " . DB_PREFIX . "article a
				INNER JOIN " . DB_PREFIX . "article_to_store a2s ON (a2s.article_id = a.article_id)
				WHERE a2s.store_id = '" . (int)$this->config->get('config_store_id') . "'
				AND a.status = '1'
				AND a.date_available <= NOW()
				ORDER BY a.article_id");

			foreach ($article_query->rows as $article) {
				$output .= $this->renderMultilingualUrl(
					'blog/article',
					array('article_id' => (int)$article['article_id']),
					'weekly',
					'0.7',
					!empty($article['date_modified']) ? $article['date_modified'] : ''
				);
			}
		}

		$output .= '</urlset>';

		return $output;
	}

	private function renderMultilingualUrl($route, $params, $changefreq, $priority, $lastmod = '', $image = '') {
		$links = $this->seo_language->getAvailableAlternateLinks($route, $params, false);

		if (!$links) {
			return '';
		}

		$localized = $links;
		unset($localized['x-default']);

		if (!$localized) {
			return '';
		}

		$output = '';

		foreach ($localized as $loc) {
			$output .= '<url>' . PHP_EOL;
			$output .= '  <loc>' . $this->xml((string)$loc) . '</loc>' . PHP_EOL;

			foreach ($links as $hreflang => $href) {
				$output .= '  <xhtml:link rel="alternate" hreflang="'
					. $this->xml((string)$hreflang)
					. '" href="' . $this->xml((string)$href) . '" />' . PHP_EOL;
			}

			if ($lastmod !== '') {
				$timestamp = strtotime($lastmod);

				if ($timestamp) {
					$output .= '  <lastmod>' . date('Y-m-d\TH:i:sP', $timestamp) . '</lastmod>' . PHP_EOL;
				}
			}

			$output .= '  <changefreq>' . $this->xml($changefreq) . '</changefreq>' . PHP_EOL;
			$output .= '  <priority>' . $this->xml($priority) . '</priority>' . PHP_EOL;

			if ($image !== '') {
				$output .= '  <image:image>' . PHP_EOL;
				$output .= '    <image:loc>' . $this->xml($image) . '</image:loc>' . PHP_EOL;
				$output .= '  </image:image>' . PHP_EOL;
			}

			$output .= '</url>' . PHP_EOL;
		}

		return $output;
	}

	private function getCategoryPaths() {
		$paths = array();
		$query = $this->db->query("SELECT category_id, path_id, level
			FROM " . DB_PREFIX . "category_path
			ORDER BY category_id, level");

		foreach ($query->rows as $row) {
			$category_id = (int)$row['category_id'];

			if (!isset($paths[$category_id])) {
				$paths[$category_id] = array();
			}

			$paths[$category_id][] = (int)$row['path_id'];
		}

		foreach ($paths as $category_id => $path) {
			$paths[$category_id] = implode('_', $path);
		}

		return $paths;
	}

	private function getBlogCategoryPaths() {
		$parents = array();
		$query = $this->db->query("SELECT blog_category_id, parent_id
			FROM " . DB_PREFIX . "blog_category
			ORDER BY blog_category_id");

		foreach ($query->rows as $row) {
			$parents[(int)$row['blog_category_id']] = (int)$row['parent_id'];
		}

		$paths = array();

		foreach ($parents as $category_id => $parent_id) {
			$seen = array();
			$path = array($category_id);
			$current = $parent_id;

			while ($current > 0 && isset($parents[$current]) && !isset($seen[$current])) {
				$seen[$current] = true;
				array_unshift($path, $current);
				$current = $parents[$current];
			}

			$paths[$category_id] = implode('_', $path);
		}

		return $paths;
	}

	private function tableExists($table) {
		$query = $this->db->query("SHOW TABLES LIKE '" . $this->db->escape($table) . "'");

		return (bool)$query->num_rows;
	}

	private function getOriginalImageUrl($filename) {
		$filename = str_replace('\\', '/', ltrim((string)$filename, '/'));

		if ($filename === '') {
			return '';
		}

		$base = $this->config->get('config_ssl')
			? (string)$this->config->get('config_ssl')
			: (string)$this->config->get('config_url');

		$parts = explode('/', $filename);

		foreach ($parts as &$part) {
			$part = rawurlencode(rawurldecode($part));
		}
		unset($part);

		return rtrim($base, '/') . '/image/' . implode('/', $parts);
	}

	private function xml($value) {
		return htmlspecialchars(
			html_entity_decode((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
			ENT_XML1 | ENT_QUOTES,
			'UTF-8'
		);
	}

}
