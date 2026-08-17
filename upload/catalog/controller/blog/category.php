<?php
// catalog/controller/blog/category.php
class ControllerBlogCategory extends Controller {
	public function index() {
		$this->load->language('blog/category');
		$this->load->model('blog/category');
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

		if (isset($this->request->get['blog_category_id'])) {
			$category_path = (string)$this->request->get['blog_category_id'];
			$parts = explode('_', $category_path);
			$blog_category_id = (int)array_pop($parts);

			$path = '';
			$path_url = '';

			if (isset($this->request->get['sort'])) {
				$path_url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$path_url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['limit'])) {
				$path_url .= '&limit=' . $this->request->get['limit'];
			}

			foreach ($parts as $path_id) {
				$path_id = (int)$path_id;

				if (!$path) {
					$path = $path_id;
				} else {
					$path .= '_' . $path_id;
				}

				$category_info = $this->model_blog_category->getCategory($path_id);

				if ($category_info) {
					$data['breadcrumbs'][] = array(
					'text' => $category_info['name'],
					'href' => $this->url->link('blog/category', 'blog_category_id=' . $path . $path_url)
					);
				}
			}
		} else {
			$category_path = '';
			$blog_category_id = 0;
		}

		$category_info = $this->model_blog_category->getCategory($blog_category_id);

		if ($category_info) {
			$this->document->setTitle($category_info['meta_title'] ? $category_info['meta_title'] : $category_info['name']);

			// ocStore 3: noindex=0 → запретить индексацию (инвертированная логика!)
			$should_noindex = empty($category_info['noindex']);
			if ($should_noindex && $this->config->get('config_noindex_status')) {
				$this->document->setRobots('noindex,follow');
			}

			$data['heading_title'] = $category_info['meta_h1'] ? $category_info['meta_h1'] : $category_info['name'];

			$this->document->setDescription($category_info['meta_description']);
			$this->document->setKeywords($category_info['meta_keyword']);
			$this->document->addLink($this->url->link('blog/category', 'blog_category_id=' . $category_path), 'canonical');

			$data['text_refine'] = $this->language->get('text_refine');
			$data['text_empty'] = $this->language->get('text_empty');
			$data['text_sort'] = $this->language->get('text_sort');
			$data['text_limit'] = $this->language->get('text_limit');
			$data['text_views'] = $this->language->get('text_views');

			$data['button_continue'] = $this->language->get('button_continue');
			$data['button_list'] = $this->language->get('button_list');
			$data['button_grid'] = $this->language->get('button_grid');
			$data['button_more'] = $this->language->get('button_more');

			$data['breadcrumbs'][] = array(
			'text' => $category_info['name'],
			'href' => $this->url->link('blog/category', 'blog_category_id=' . $category_path)
			);

			if ($category_info['image']) {
				$data['thumb'] = $this->model_tool_image->resize(
				$category_info['image'],
				$this->config->get('configblog_image_category_width'),
				$this->config->get('configblog_image_category_height')
				);
			} else {
				$data['thumb'] = '';
			}

			$data['description'] = html_entity_decode($category_info['description'], ENT_QUOTES, 'UTF-8');
			$data['configblog_review_status'] = $this->config->get('configblog_review_status');

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

			$data['categories'] = array();

			$results = $this->model_blog_category->getCategories($blog_category_id);

			foreach ($results as $result) {
				$filter_data = array(
				'filter_blog_category_id' => $result['blog_category_id'],
				'filter_sub_category'     => true
				);

				$data['categories'][] = array(
				'name' => $result['name'] . ($this->config->get('configblog_article_count') ? ' (' . $this->model_blog_article->getTotalArticles($filter_data) . ')' : ''),
				'href' => $this->url->link('blog/category', 'blog_category_id=' . ($category_path ? $category_path . '_' . $result['blog_category_id'] : $result['blog_category_id']) . $url)
				);
			}

			$data['articles'] = array();

			$article_data = array(
			'filter_blog_category_id' => $blog_category_id,
			'sort'                    => $sort,
			'order'                   => $order,
			'start'                   => ($page - 1) * $limit,
			'limit'                   => $limit
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

				$article_href = 'article_id=' . $result['article_id'];

				if ($category_path !== '') {
					$article_href = 'blog_category_id=' . $category_path . '&' . $article_href;
				}

				$article_href .= $url;

				$data['articles'][] = array(
				'article_id'  => $result['article_id'],
				'thumb'       => $image,
				'name'        => $result['name'],
				'description' => utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('configblog_article_description_length')) . '...',
				'date_added'  => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'viewed'      => $result['viewed'],
				'rating'      => $rating,
				'href'        => $this->url->link('blog/article', $article_href)
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
			'href'  => $this->url->link('blog/category', 'blog_category_id=' . $category_path . '&sort=p.sort_order&order=ASC' . $url)
			);

			$data['sorts'][] = array(
			'text'  => $this->language->get('text_name_asc'),
			'value' => 'pd.name-ASC',
			'href'  => $this->url->link('blog/category', 'blog_category_id=' . $category_path . '&sort=pd.name&order=ASC' . $url)
			);

			$data['sorts'][] = array(
			'text'  => $this->language->get('text_name_desc'),
			'value' => 'pd.name-DESC',
			'href'  => $this->url->link('blog/category', 'blog_category_id=' . $category_path . '&sort=pd.name&order=DESC' . $url)
			);

			$data['sorts'][] = array(
			'text'  => $this->language->get('text_date_asc'),
			'value' => 'p.date_added-ASC',
			'href'  => $this->url->link('blog/category', 'blog_category_id=' . $category_path . '&sort=p.date_added&order=ASC' . $url)
			);

			$data['sorts'][] = array(
			'text'  => $this->language->get('text_date_desc'),
			'value' => 'p.date_added-DESC',
			'href'  => $this->url->link('blog/category', 'blog_category_id=' . $category_path . '&sort=p.date_added&order=DESC' . $url)
			);

			if ($this->config->get('configblog_review_status')) {
				$data['sorts'][] = array(
				'text'  => $this->language->get('text_rating_desc'),
				'value' => 'rating-DESC',
				'href'  => $this->url->link('blog/category', 'blog_category_id=' . $category_path . '&sort=rating&order=DESC' . $url)
				);

				$data['sorts'][] = array(
				'text'  => $this->language->get('text_rating_asc'),
				'value' => 'rating-ASC',
				'href'  => $this->url->link('blog/category', 'blog_category_id=' . $category_path . '&sort=rating&order=ASC' . $url)
				);
			}

			$data['sorts'][] = array(
			'text'  => $this->language->get('text_viewed_asc'),
			'value' => 'p.viewed-ASC',
			'href'  => $this->url->link('blog/category', 'blog_category_id=' . $category_path . '&sort=p.viewed&order=ASC' . $url)
			);

			$data['sorts'][] = array(
			'text'  => $this->language->get('text_viewed_desc'),
			'value' => 'p.viewed-DESC',
			'href'  => $this->url->link('blog/category', 'blog_category_id=' . $category_path . '&sort=p.viewed&order=DESC' . $url)
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
				'href'  => $this->url->link('blog/category', 'blog_category_id=' . $category_path . $url . '&limit=' . $value)
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
			$pagination->url = $this->url->link('blog/category', 'blog_category_id=' . $category_path . $url . '&page={page}');

			$data['pagination'] = $pagination->render();

			$data['results'] = sprintf(
			$this->language->get('text_pagination'),
			($article_total) ? (($page - 1) * $limit) + 1 : 0,
			((($page - 1) * $limit) > ($article_total - $limit)) ? $article_total : ((($page - 1) * $limit) + $limit),
			$article_total,
			ceil($article_total / $limit)
			);

			$data['sort'] = $sort;
			$data['order'] = $order;
			$data['limit'] = $limit;
			$data['continue'] = $this->url->link('common/home');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('blog/category', $data));
		} else {
			$url = '';

			if ($category_path !== '') {
				$url .= '&blog_category_id=' . $category_path;
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_error'),
			'href' => $this->url->link('blog/category', ltrim($url, '&'))
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
}