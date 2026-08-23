<?php
// catalog/controller/blog/article.php
class ControllerBlogArticle extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('blog/article');
		$this->load->model('blog/category');
		$this->load->model('blog/article');
		$this->load->model('blog/review');
		$this->load->model('tool/image');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
		'text'      => $this->language->get('text_home'),
		'href'      => $this->url->link('common/home'),
		'separator' => false
		);

		$configblog_name = $this->config->get('configblog_name');

		if (is_array($configblog_name)) {
			$name = $configblog_name[$this->config->get('config_language_id')] ?? '';
		} else {
			$name = $configblog_name;
		}

		$data['text_blog'] = !empty($name) ? $name : $this->language->get('text_blog');

		$data['breadcrumbs'][] = array(
		'text' => $data['text_blog'],
		'href' => $this->url->link('blog/latest')
		);

		if (isset($this->request->get['blog_category_id'])) {
			$blog_category_id = '';

			foreach (explode('_', $this->request->get['blog_category_id']) as $path_id) {
				if (!$blog_category_id) {
					$blog_category_id = $path_id;
				} else {
					$blog_category_id .= '_' . $path_id;
				}

				$category_info = $this->model_blog_category->getCategory($path_id);

				if ($category_info) {
					$data['breadcrumbs'][] = array(
					'text' => $category_info['name'],
					'href' => $this->url->link('blog/category', 'blog_category_id=' . $blog_category_id)
					);
				}
			}
		}

		$url = '';

		if (isset($this->request->get['blog_category_id'])) {
			$url .= '&blog_category_id=' . $this->request->get['blog_category_id'];
		}

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . $this->request->get['filter_name'];
		}

		if (isset($this->request->get['filter_tag'])) {
			$url .= '&filter_tag=' . $this->request->get['filter_tag'];
		}

		if (isset($this->request->get['filter_description'])) {
			$url .= '&filter_description=' . $this->request->get['filter_description'];
		}

		if (isset($this->request->get['filter_news_id'])) {
			$url .= '&filter_news_id=' . $this->request->get['filter_news_id'];
		}

		$article_id = isset($this->request->get['article_id']) ? (int)$this->request->get['article_id'] : 0;

		$article_info = $this->model_blog_article->getArticle($article_id);

		if ($article_info) {
			$data['breadcrumbs'][] = array(
			'text' => $article_info['name'],
			'href' => $this->url->link('blog/article', 'article_id=' . $article_id . $url)
			);

			$this->document->setTitle($article_info['meta_title'] ? $article_info['meta_title'] : $article_info['name']);

			// ocStore 3: noindex=0 → запретить индексацию (инвертированная логика!)
			$should_noindex = empty($article_info['noindex']);
			if ($should_noindex && $this->config->get('config_noindex_status')) {
				$this->document->setRobots('noindex,follow');
			}

			$this->document->setDescription($article_info['meta_description']);
			$this->document->setKeywords($article_info['meta_keyword']);
			$this->document->addLink($this->url->link('blog/article', 'article_id=' . $article_id), 'canonical');
			$this->document->addScript('catalog/view/javascript/jquery/magnific/jquery.magnific-popup.min.js');
			$this->document->addStyle('catalog/view/javascript/jquery/magnific/magnific-popup.css');

			$data['heading_title'] = $article_info['meta_h1'] ? $article_info['meta_h1'] : $article_info['name'];

			$data['text_related'] = $this->language->get('text_related');
			$data['text_related_product'] = $this->language->get('text_related_product');
			$data['button_more'] = $this->language->get('button_more');
			$data['text_views'] = $this->language->get('text_views');
			$data['text_tax'] = $this->language->get('text_tax');

			$data['article_id'] = $article_id;
			$data['review_status'] = $this->config->get('configblog_review_status');
			$data['review_guest'] = $this->config->get('configblog_review_guest') || $this->customer->isLogged();

			if ($this->customer->isLogged()) {
				$data['customer_name'] = $this->customer->getFirstName() . '&nbsp;' . $this->customer->getLastName();
			} else {
				$data['customer_name'] = '';
			}

			if (
			$this->config->get($this->config->get('config_captcha') . '_status') &&
			in_array('review', (array)$this->config->get('config_captcha_page'))
			) {
				$data['captcha'] = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha'));
			} else {
				$data['captcha'] = '';
			}

			$data['article_review'] = (int)$article_info['article_review'];
			$data['reviews'] = sprintf($this->language->get('text_reviews'), (int)$article_info['reviews']);
			$data['rating'] = (int)$article_info['rating'];
			$data['gstatus'] = (int)$article_info['gstatus'];
			$data['description'] = html_entity_decode($article_info['description'], ENT_QUOTES, 'UTF-8');

			// Изображения статьи
			$article_width  = (int)$this->config->get('configblog_image_article_width');
			$article_height = (int)$this->config->get('configblog_image_article_height');

			$additional_width  = (int)$this->config->get('config_image_additional_width');
			$additional_height = (int)$this->config->get('config_image_additional_height');

			$popup_width  = (int)$this->config->get('config_image_popup_width');
			$popup_height = (int)$this->config->get('config_image_popup_height');

			if ($article_width <= 0) {
				$article_width = 500;
			}

			if ($article_height <= 0) {
				$article_height = 500;
			}

			if ($additional_width <= 0) {
				$additional_width = 74;
			}

			if ($additional_height <= 0) {
				$additional_height = 74;
			}

			if ($popup_width <= 0) {
				$popup_width = 500;
			}

			if ($popup_height <= 0) {
				$popup_height = 500;
			}

			if (!empty($article_info['image'])) {
				$data['thumb'] = $this->model_tool_image->resize(
				$article_info['image'],
				$article_width,
				$article_height
				);

				$data['popup'] = $this->model_tool_image->resize(
				$article_info['image'],
				$popup_width,
				$popup_height
				);
			} else {
				$data['thumb'] = '';
				$data['popup'] = '';
			}

			// Дополнительные изображения статьи
			$data['images'] = array();

			$results = $this->model_blog_article->getArticleImages($article_id);

			foreach ($results as $result) {
				$data['images'][] = array(
				'thumb' => $this->model_tool_image->resize(
				$result['image'],
				$additional_width,
				$additional_height
				),
				'popup' => $this->model_tool_image->resize(
				$result['image'],
				$popup_width,
				$popup_height
				)
				);
			}

			// Связанные статьи
			$data['articles'] = array();

			$results = $this->model_blog_article->getArticleRelated($article_id);

			foreach ($results as $result) {
				if ($result['image']) {
					$image = $this->model_tool_image->resize(
					$result['image'],
					$this->config->get('configblog_image_related_width'),
					$this->config->get('configblog_image_related_height')
					);
				} else {
					$image = false;
				}

				if ($this->config->get('configblog_review_status')) {
					$rating = (int)$result['rating'];
				} else {
					$rating = false;
				}

				$data['articles'][] = array(
				'article_id'  => $result['article_id'],
				'thumb'       => $image,
				'name'        => $result['name'],
				'description' => utf8_substr(
				strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')),
				0,
				$this->config->get('configblog_article_description_length')
				) . '...',
				'rating'      => $rating,
				'date_added'  => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'viewed'      => $result['viewed'],
				'reviews'     => sprintf($this->language->get('text_reviews'), (int)$result['reviews']),
				'href'        => $this->url->link('blog/article', 'article_id=' . $result['article_id'])
				);
			}

			// Связанные товары
			$data['products'] = array();

			$results = $this->model_blog_article->getArticleRelatedProduct($article_id);

			foreach ($results as $result) {
				if ($result['image']) {
					$image = $this->model_tool_image->resize(
					$result['image'],
					$this->config->get('configblog_image_related_width'),
					$this->config->get('configblog_image_related_height')
					);
				} else {
					$image = false;
				}

				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format(
					$this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')),
					$this->session->data['currency']
					);
				} else {
					$price = false;
				}

				if ((float)$result['special']) {
					$special = $this->currency->format(
					$this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')),
					$this->session->data['currency']
					);
				} else {
					$special = false;
				}

				if ($this->config->get('config_tax')) {
					$tax = $this->currency->format(
					(float)$result['special'] ? $result['special'] : $result['price'],
					$this->session->data['currency']
					);
				} else {
					$tax = false;
				}

				if ($this->config->get('configblog_review_status')) {
					$rating = (int)$result['rating'];
				} else {
					$rating = false;
				}

				$data['products'][] = array(
				'product_id'  => $result['product_id'],
				'thumb'       => $image,
				'name'        => $result['name'],
				'description' => utf8_substr(
				strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')),
				0,
				$this->config->get('configblog_article_description_length')
				) . '...',
				'price'       => $price,
				'special'     => $special,
				'rating'      => $rating,
				'tax'         => $tax,
				'minimum'     => $result['minimum'] > 0 ? $result['minimum'] : 1,
				'reviews'     => sprintf($this->language->get('text_reviews'), (int)$result['reviews']),
				'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id'])
				);
			}

			// Загрузки
			$data['download_status'] = $this->config->get('configblog_article_download');
			$data['downloads'] = array();

			$results = $this->model_blog_article->getDownloads($article_id);

			foreach ($results as $result) {
				$file = DIR_DOWNLOAD . $result['filename'];

				if (file_exists($file)) {
					$size = filesize($file);
					$i = 0;

					$suffix = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');

					while (($size / 1024) > 1 && $i < count($suffix) - 1) {
						$size = $size / 1024;
						$i++;
					}

					$data['downloads'][] = array(
					'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
					'name'       => $result['name'],
					'size'       => round($size, 2) . $suffix[$i],
					'href'       => $this->url->link('blog/article/download', 'article_id=' . $article_id . '&download_id=' . $result['download_id'])
					);
				}
			}

			// Согласие
			if ($this->config->get('config_account_id')) {
				$this->load->model('catalog/information');

				$information_info = $this->model_catalog_information->getInformation($this->config->get('config_account_id'));

				if ($information_info) {
					$data['text_agree'] = sprintf(
					$this->language->get('text_agree'),
					$this->url->link('information/information/agree', 'information_id=' . $this->config->get('config_account_id'), true),
					$information_info['title']
					);
				} else {
					$data['text_agree'] = '';
				}
			} else {
				$data['text_agree'] = '';
			}

			$this->model_blog_article->updateViewed($article_id);

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('blog/article', $data));
		} else {
			$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_error'),
			'href' => $this->url->link('blog/article', 'article_id=' . $article_id . $url)
			);

			$this->document->setTitle($this->language->get('text_error'));

			$data['heading_title'] = $this->language->get('text_error');
			$data['text_error'] = $this->language->get('text_error');
			$data['button_continue'] = $this->language->get('button_continue');
			$data['continue'] = $this->url->link('common/home');

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}

	public function download() {
		$this->load->model('blog/article');

		$download_id = isset($this->request->get['download_id']) ? (int)$this->request->get['download_id'] : 0;
		$article_id = isset($this->request->get['article_id']) ? (int)$this->request->get['article_id'] : 0;

		$download_info = $this->model_blog_article->getDownload($article_id, $download_id);

		if ($download_info) {
			$file = DIR_DOWNLOAD . $download_info['filename'];
			$mask = basename($download_info['mask']);

			if (!headers_sent()) {
				if (file_exists($file)) {
					header('Content-Description: File Transfer');
					header('Content-Type: application/octet-stream');
					header('Content-Disposition: attachment; filename="' . ($mask ? $mask : basename($file)) . '"');
					header('Content-Transfer-Encoding: binary');
					header('Expires: 0');
					header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
					header('Pragma: public');
					header('Content-Length: ' . filesize($file));

					readfile($file);
					exit;
				} else {
					exit('Error: Could not find file ' . $file . '!');
				}
			} else {
				exit('Error: Headers already sent out!');
			}
		} else {
			$this->response->redirect($this->url->link('account/download', '', true));
		}
	}

	public function review() {
		$this->load->language('blog/article');
		$this->load->model('blog/review');

		$data['text_on'] = $this->language->get('text_on');
		$data['text_no_reviews'] = $this->language->get('text_no_reviews');

		$article_id = isset($this->request->get['article_id']) ? (int)$this->request->get['article_id'] : 0;
		$page = isset($this->request->get['page']) ? (int)$this->request->get['page'] : 1;

		$data['reviews'] = array();

		$review_total = $this->model_blog_review->getTotalReviewsByArticleId($article_id);
		$results = $this->model_blog_review->getReviewsByArticleId($article_id, ($page - 1) * 5, 5);

		foreach ($results as $result) {
			$data['reviews'][] = array(
			'author'     => $result['author'],
			'text'       => $result['text'],
			'rating'     => (int)$result['rating'],
			'reviews'    => sprintf($this->language->get('text_reviews'), (int)$review_total),
			'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added']))
			);
		}

		$pagination = new Pagination();
		$pagination->total = $review_total;
		$pagination->page = $page;
		$pagination->limit = 5;
		$pagination->url = $this->url->link('blog/article/review', 'article_id=' . $article_id . '&page={page}');

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf(
		$this->language->get('text_pagination'),
		($review_total) ? (($page - 1) * 5) + 1 : 0,
		((($page - 1) * 5) > ($review_total - 5)) ? $review_total : ((($page - 1) * 5) + 5),
		$review_total,
		ceil($review_total / 5)
		);

		$this->response->setOutput($this->load->view('blog/review', $data));
	}

	public function write() {
		$this->load->language('blog/article');

		$json = array();

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			$name = isset($this->request->post['name']) ? $this->request->post['name'] : '';
			$text = isset($this->request->post['text']) ? $this->request->post['text'] : '';
			$rating = isset($this->request->post['rating']) ? (int)$this->request->post['rating'] : 0;
			$article_id = isset($this->request->get['article_id']) ? (int)$this->request->get['article_id'] : 0;

			if ((utf8_strlen($name) < 3) || (utf8_strlen($name) > 25)) {
				$json['error'] = $this->language->get('error_name');
			}

			if ((utf8_strlen($text) < 25) || (utf8_strlen($text) > 1000)) {
				$json['error'] = $this->language->get('error_text');
			}

			if ($rating < 1 || $rating > 5) {
				$json['error'] = $this->language->get('error_rating');
			}

			if (
			$this->config->get($this->config->get('config_captcha') . '_status') &&
			in_array('review', (array)$this->config->get('config_captcha_page'))
			) {
				$captcha = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha') . '/validate');

				if ($captcha) {
					$json['error'] = $captcha;
				}
			}

			if ($this->config->get('config_account_id')) {
				$this->load->model('catalog/information');

				$information_info = $this->model_catalog_information->getInformation($this->config->get('config_account_id'));

				if ($information_info && !isset($this->request->post['agree'])) {
					$json['error'] = sprintf($this->language->get('error_agree'), $information_info['title']);
				}
			}

			if (!isset($json['error'])) {
				$this->load->model('blog/review');
				$this->model_blog_review->addReview($article_id, $this->request->post);

				$json['success'] = $this->language->get('text_success');
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}