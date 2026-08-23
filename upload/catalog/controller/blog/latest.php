<?php
// catalog/controller/blog/latest.php
class ControllerBlogLatest extends Controller {
	public function index() {
		$this->load->language('blog/latest');
		$this->load->model('blog/article');
		$this->load->model('tool/image');

		$disallow_params = array();

		if ($this->config->get('config_noindex_disallow_params')) {
			$params = explode("\r\n", $this->config->get('config_noindex_disallow_params'));

			if (!empty($params)) {
				$disallow_params = $params;
			}
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];

			if (!in_array('sort', $disallow_params, true) && $this->config->get('config_noindex_status')) {
				$this->document->setRobots('noindex,follow');
			}
		} else {
			$sort = 'p.date_added';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];

			if (!in_array('order', $disallow_params, true) && $this->config->get('config_noindex_status')) {
				$this->document->setRobots('noindex,follow');
			}
		} else {
			$order = 'DESC';
		}

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];

			if (!in_array('page', $disallow_params, true) && $this->config->get('config_noindex_status')) {
				$this->document->setRobots('noindex,follow');
			}
		} else {
			$page = 1;
		}

		if (isset($this->request->get['limit'])) {
			$limit = (int)$this->request->get['limit'];

			if (!in_array('limit', $disallow_params, true) && $this->config->get('config_noindex_status')) {
				$this->document->setRobots('noindex,follow');
			}
		} else {
			$limit = (int)$this->config->get('configblog_article_limit');
		}

		$configblog_html_h1 = $this->config->get('configblog_html_h1');

		if (is_array($configblog_html_h1)) {
			$h1 = $configblog_html_h1[$this->config->get('config_language_id')] ?? '';
		} else {
			$h1 = $configblog_html_h1;
		}

		$data['heading_title'] = !empty($h1) ? $h1 : $this->language->get('heading_title');

		$configblog_meta_title = $this->config->get('configblog_meta_title');

		if (is_array($configblog_meta_title)) {
			$meta_title = $configblog_meta_title[$this->config->get('config_language_id')] ?? '';
		} else {
			$meta_title = $configblog_meta_title;
		}

		$this->document->setTitle(!empty($meta_title) ? $meta_title : $this->language->get('heading_title'));

		$configblog_meta_description = $this->config->get('configblog_meta_description');

		if (is_array($configblog_meta_description)) {
			$meta_description = $configblog_meta_description[$this->config->get('config_language_id')] ?? '';
		} else {
			$meta_description = $configblog_meta_description;
		}

		$this->document->setDescription($meta_description);

		$configblog_meta_keyword = $this->config->get('configblog_meta_keyword');

		if (is_array($configblog_meta_keyword)) {
			$meta_keyword = $configblog_meta_keyword[$this->config->get('config_language_id')] ?? '';
		} else {
			$meta_keyword = $configblog_meta_keyword;
		}

		$this->document->setKeywords($meta_keyword);
		$this->document->addLink($this->url->link('blog/latest'), 'canonical');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
		'text' => $this->language->get('text_home'),
		'href' => $this->url->link('common/home')
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

		$data['text_views'] = $this->language->get('text_views');
		$data['text_empty'] = $this->language->get('text_empty');
		$data['text_sort'] = $this->language->get('text_sort');
		$data['text_limit'] = $this->language->get('text_limit');

		$data['button_more'] = $this->language->get('button_more');
		$data['button_continue'] = $this->language->get('button_continue');

		$data['configblog_review_status'] = $this->config->get('configblog_review_status');

		$data['articles'] = array();

		$article_data = array(
		'sort'  => $sort,
		'order' => $order,
		'start' => ($page - 1) * $limit,
		'limit' => $limit
		);

		$article_total = $this->model_blog_article->getTotalArticles($article_data);
		$results = $this->model_blog_article->getArticles($article_data);

		$image_width = (int)$this->config->get('configblog_image_article_width');
		$image_height = (int)$this->config->get('configblog_image_article_height');

		$data['image_width'] = $image_width;
		$data['image_height'] = $image_height;

		foreach ($results as $result) {
			if ($result['image']) {
				$image = $this->model_tool_image->resize($result['image'], $image_width, $image_height);
			} else {
				$image = $this->model_tool_image->resize('placeholder.png', $image_width, $image_height);
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
			'description' => utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('configblog_article_description_length')) . '...',
			'date_added'  => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
			'viewed'      => $result['viewed'],
			'rating'      => $rating,
			'reviews'     => sprintf($this->language->get('text_reviews'), (int)$result['reviews']),
			'href'        => $this->url->link('blog/article', 'article_id=' . $result['article_id'])
			);
		}

		$url = '';

		if (isset($this->request->get['limit'])) {
			$url .= '&limit=' . $this->request->get['limit'];
		}

		$data['sorts'] = array();

		$data['sorts'][] = array(
		'text'  => $this->language->get('text_default'),
		'value' => 'p.sort_order-ASC',
		'href'  => $this->url->link('blog/latest', 'sort=p.sort_order&order=ASC' . $url)
		);

		$data['sorts'][] = array(
		'text'  => $this->language->get('text_name_asc'),
		'value' => 'pd.name-ASC',
		'href'  => $this->url->link('blog/latest', 'sort=pd.name&order=ASC' . $url)
		);

		$data['sorts'][] = array(
		'text'  => $this->language->get('text_name_desc'),
		'value' => 'pd.name-DESC',
		'href'  => $this->url->link('blog/latest', 'sort=pd.name&order=DESC' . $url)
		);

		$data['sorts'][] = array(
		'text'  => $this->language->get('text_date_asc'),
		'value' => 'p.date_added-ASC',
		'href'  => $this->url->link('blog/latest', 'sort=p.date_added&order=ASC' . $url)
		);

		$data['sorts'][] = array(
		'text'  => $this->language->get('text_date_desc'),
		'value' => 'p.date_added-DESC',
		'href'  => $this->url->link('blog/latest', 'sort=p.date_added&order=DESC' . $url)
		);

		if ($this->config->get('configblog_review_status')) {
			$data['sorts'][] = array(
			'text'  => $this->language->get('text_rating_desc'),
			'value' => 'rating-DESC',
			'href'  => $this->url->link('blog/latest', 'sort=rating&order=DESC' . $url)
			);

			$data['sorts'][] = array(
			'text'  => $this->language->get('text_rating_asc'),
			'value' => 'rating-ASC',
			'href'  => $this->url->link('blog/latest', 'sort=rating&order=ASC' . $url)
			);
		}

		$data['sorts'][] = array(
		'text'  => $this->language->get('text_viewed_desc'),
		'value' => 'p.viewed-DESC',
		'href'  => $this->url->link('blog/latest', 'sort=p.viewed&order=DESC' . $url)
		);

		$data['sorts'][] = array(
		'text'  => $this->language->get('text_viewed_asc'),
		'value' => 'p.viewed-ASC',
		'href'  => $this->url->link('blog/latest', 'sort=p.viewed&order=ASC' . $url)
		);

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$data['limits'] = array();

		$limits = array_unique(array((int)$this->config->get('configblog_article_limit'), 25, 50, 75, 100));
		sort($limits);

		foreach ($limits as $value) {
			$data['limits'][] = array(
			'text'  => $value,
			'value' => $value,
			'href'  => $this->url->link('blog/latest', ltrim($url, '&') . ($url ? '&' : '') . 'limit=' . $value)
			);
		}

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['limit'])) {
			$url .= '&limit=' . $this->request->get['limit'];
		}

		$pagination = new Pagination();
		$pagination->total = $article_total;
		$pagination->page = $page;
		$pagination->limit = $limit;
		$pagination->url = $this->url->link('blog/latest', ltrim($url, '&') . ($url ? '&' : '') . 'page={page}');

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf(
		$this->language->get('text_pagination'),
		($article_total) ? (($page - 1) * $limit) + 1 : 0,
		((($page - 1) * $limit) > ($article_total - $limit)) ? $article_total : ((($page - 1) * $limit) + $limit),
		$article_total,
		ceil($article_total / $limit)
		);

		$data['continue'] = $this->url->link('common/home');
		$data['sort'] = $sort;
		$data['order'] = $order;
		$data['limit'] = $limit;

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('blog/latest', $data));
	}
}