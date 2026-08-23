<?php
class ControllerBlogArticle extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('blog/article');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('blog/article');

		$this->getList();
	}

	public function add() {
		$this->load->language('blog/article');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('blog/article');

		if (isset($this->request->server['REQUEST_METHOD']) && $this->request->server['REQUEST_METHOD'] == 'POST' && $this->validateForm()) {
			$article_id = $this->model_blog_article->addArticle($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = $this->buildUrl();

			if (isset($this->request->post['apply']) && (int)$this->request->post['apply'] == 1) {
				$url .= '&article_id=' . (int)$article_id;
				$this->response->redirect($this->url->link('blog/article/edit', 'user_token=' . $this->session->data['user_token'] . $url, true));
			} else {
				$this->response->redirect($this->url->link('blog/article', 'user_token=' . $this->session->data['user_token'] . $url, true));
			}
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('blog/article');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('blog/article');

		if (!isset($this->request->get['article_id'])) {
			$this->response->redirect($this->url->link('blog/article', 'user_token=' . $this->session->data['user_token'], true));
		}

		if (isset($this->request->server['REQUEST_METHOD']) && $this->request->server['REQUEST_METHOD'] == 'POST' && $this->validateForm()) {
			$this->model_blog_article->editArticle((int)$this->request->get['article_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = $this->buildUrl();

			if (isset($this->request->post['apply']) && (int)$this->request->post['apply'] == 1) {
				$url .= '&article_id=' . (int)$this->request->get['article_id'];
				$this->response->redirect($this->url->link('blog/article/edit', 'user_token=' . $this->session->data['user_token'] . $url, true));
			} else {
				$this->response->redirect($this->url->link('blog/article', 'user_token=' . $this->session->data['user_token'] . $url, true));
			}
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('blog/article');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('blog/article');

		if (isset($this->request->post['selected']) && $this->validateModifyPermission()) {
			foreach ((array)$this->request->post['selected'] as $article_id) {
				$this->model_blog_article->deleteArticle((int)$article_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = $this->buildUrl();

			$this->response->redirect($this->url->link('blog/article', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	public function copy() {
		$this->load->language('blog/article');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('blog/article');

		if (isset($this->request->post['selected']) && $this->validateModifyPermission()) {
			foreach ((array)$this->request->post['selected'] as $article_id) {
				$this->model_blog_article->copyArticle((int)$article_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = $this->buildUrl();

			$this->response->redirect($this->url->link('blog/article', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	public function enable() {
		$this->load->language('blog/article');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('blog/article');

		if (isset($this->request->post['selected']) && $this->validateModifyPermission()) {
			foreach ((array)$this->request->post['selected'] as $article_id) {
				$this->model_blog_article->editArticleStatus((int)$article_id, 1);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = $this->buildUrl();

			$this->response->redirect($this->url->link('blog/article', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	public function disable() {
		$this->load->language('blog/article');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('blog/article');

		if (isset($this->request->post['selected']) && $this->validateModifyPermission()) {
			foreach ((array)$this->request->post['selected'] as $article_id) {
				$this->model_blog_article->editArticleStatus((int)$article_id, 0);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = $this->buildUrl();

			$this->response->redirect($this->url->link('blog/article', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	protected function getList() {
		$filter_name = isset($this->request->get['filter_name']) ? $this->request->get['filter_name'] : null;
		$filter_status = isset($this->request->get['filter_status']) ? $this->request->get['filter_status'] : null;
		$filter_noindex = isset($this->request->get['filter_noindex']) ? $this->request->get['filter_noindex'] : null;
		$sort = isset($this->request->get['sort']) ? $this->request->get['sort'] : 'pd.name';
		$order = isset($this->request->get['order']) ? $this->request->get['order'] : 'ASC';
		$page = isset($this->request->get['page']) ? (int)$this->request->get['page'] : 1;

		if ($page < 1) {
			$page = 1;
		}

		$url = $this->buildUrl();

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('blog/article', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['add'] = $this->url->link('blog/article/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['copy'] = $this->url->link('blog/article/copy', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('blog/article/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['enabled'] = $this->url->link('blog/article/enable', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['disabled'] = $this->url->link('blog/article/disable', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['articles'] = array();

		$filter_data = array(
			'filter_name'    => $filter_name,
			'filter_status'  => $filter_status,
			'filter_noindex' => $filter_noindex,
			'sort'           => $sort,
			'order'          => $order,
			'start'          => ($page - 1) * (int)$this->config->get('config_limit_admin'),
			'limit'          => (int)$this->config->get('config_limit_admin')
		);

		$this->load->model('tool/image');

		$article_total = $this->model_blog_article->getTotalArticles($filter_data);
		$results = $this->model_blog_article->getArticles($filter_data);

		$statusText = [
			0 => $this->language->get('text_disabled'),
			1 => $this->language->get('text_enabled')
		];

		$noindexText = array(
			0 => $this->language->get('text_noindex'),
			1 => $this->language->get('text_index')
		);

		foreach ($results as $result) {
			$image_path = isset($result['image']) ? $result['image'] : '';

			if ($image_path && is_file(DIR_IMAGE . $image_path)) {
				$image = $this->model_tool_image->resize($image_path, 40, 40);
			} else {
				$image = $this->model_tool_image->resize('no_image.png', 40, 40);
			}

			$data['articles'][] = array(
				'article_id'  => $result['article_id'],
				'image'       => $image,
				'name'        => $result['name'],
				'status'      => (bool)$result['status'],
				'status_text' => $statusText[(int)$result['status']],
				'noindex'     => $noindexText[(int)$result['noindex']],
				'href_shop'   => HTTP_CATALOG . 'index.php?route=blog/article&article_id=' . (int)$result['article_id'],
				'edit'        => $this->url->link('blog/article/edit', 'user_token=' . $this->session->data['user_token'] . '&article_id=' . (int)$result['article_id'] . $url, true)
			);
		}

		$data['user_token'] = $this->session->data['user_token'];

		$data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}

		$sort_url = '';

		if (isset($this->request->get['filter_name'])) {
			$sort_url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_status'])) {
			$sort_url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if (isset($this->request->get['filter_noindex'])) {
			$sort_url .= '&filter_noindex=' . $this->request->get['filter_noindex'];
		}

		if ($order == 'ASC') {
			$sort_url .= '&order=DESC';
		} else {
			$sort_url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$sort_url .= '&page=' . (int)$this->request->get['page'];
		}

		$data['sort_name'] = $this->url->link('blog/article', 'user_token=' . $this->session->data['user_token'] . '&sort=pd.name' . $sort_url, true);
		$data['sort_status'] = $this->url->link('blog/article', 'user_token=' . $this->session->data['user_token'] . '&sort=p.status' . $sort_url, true);
		$data['sort_noindex'] = $this->url->link('blog/article', 'user_token=' . $this->session->data['user_token'] . '&sort=p.noindex' . $sort_url, true);
		$data['sort_order'] = $this->url->link('blog/article', 'user_token=' . $this->session->data['user_token'] . '&sort=p.sort_order' . $sort_url, true);

		$pagination_url = '';

		if (isset($this->request->get['filter_name'])) {
			$pagination_url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_status'])) {
			$pagination_url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if (isset($this->request->get['filter_noindex'])) {
			$pagination_url .= '&filter_noindex=' . $this->request->get['filter_noindex'];
		}

		if (isset($this->request->get['sort'])) {
			$pagination_url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$pagination_url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $article_total;
		$pagination->page = $page;
		$pagination->limit = (int)$this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('blog/article', 'user_token=' . $this->session->data['user_token'] . $pagination_url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf(
			$this->language->get('text_pagination'),
			($article_total) ? (($page - 1) * (int)$this->config->get('config_limit_admin')) + 1 : 0,
			((($page - 1) * (int)$this->config->get('config_limit_admin')) > ($article_total - (int)$this->config->get('config_limit_admin'))) ? $article_total : ((($page - 1) * (int)$this->config->get('config_limit_admin')) + (int)$this->config->get('config_limit_admin')),
			$article_total,
			ceil($article_total / (int)$this->config->get('config_limit_admin'))
		);

		$data['filter_name'] = $filter_name;
		$data['filter_status'] = $filter_status;
		$data['filter_noindex'] = $filter_noindex;
		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('blog/article_list', $data));
	}

	protected function getForm() {
		$article_info = array();

		if ($this->config->get('config_editor_default') == 'tinymce') {
			$this->document->addScript('view/javascript/tinymce/tinymce.min.js');
			$this->document->addScript('view/javascript/tinymce/opencart-tinymce.js');
		} elseif ($this->config->get('config_editor_default') == 'ckeditor') {
			$this->document->addScript('view/javascript/ckeditor/ckeditor.js');
			$this->document->addScript('view/javascript/ckeditor/adapters/jquery.js');
			$this->document->addScript('view/javascript/ckeditor/ckeditor_init.js');
		} else {
			$this->document->addScript('view/javascript/summernote/summernote.min.js');

			if (file_exists('view/javascript/summernote/lang/summernote-' . $this->language->get('summernote') . '.min.js')) {
				$this->document->addScript('view/javascript/summernote/lang/summernote-' . $this->language->get('summernote') . '.min.js');
			}

			$this->document->addScript('view/javascript/summernote/summernote-image-attributes.js');

			if (file_exists('view/javascript/summernote/img-lang/' . $this->language->get('summernote') . '.js')) {
				$this->document->addScript('view/javascript/summernote/img-lang/' . $this->language->get('summernote') . '.js');
			}

			$this->document->addScript('view/javascript/summernote/opencart.js');
			$this->document->addStyle('view/javascript/summernote/summernote.min.css');
			$this->document->addStyle('view/javascript/codemirror/lib/codemirror.css');
			$this->document->addStyle('view/javascript/codemirror/theme/monokai.css');
			$this->document->addScript('view/javascript/codemirror/lib/codemirror.js');
			$this->document->addScript('view/javascript/codemirror/lib/xml.js');
			$this->document->addScript('view/javascript/codemirror/lib/formatting.js');
		}

		$data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';
		$data['text_form'] = !isset($this->request->get['article_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$data['error_name'] = isset($this->error['name']) ? $this->error['name'] : array();
		$data['error_meta_title'] = isset($this->error['meta_title']) ? $this->error['meta_title'] : array();
		$data['error_meta_h1'] = isset($this->error['meta_h1']) ? $this->error['meta_h1'] : array();
		$data['error_keyword'] = isset($this->error['keyword']) ? $this->error['keyword'] : '';

		$url = $this->buildUrl();

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('blog/article', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		if (!isset($this->request->get['article_id'])) {
			$data['action'] = $this->url->link('blog/article/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		} else {
			$data['action'] = $this->url->link('blog/article/edit', 'user_token=' . $this->session->data['user_token'] . '&article_id=' . (int)$this->request->get['article_id'] . $url, true);
		}

		$data['cancel'] = $this->url->link('blog/article', 'user_token=' . $this->session->data['user_token'] . $url, true);

		if (isset($this->request->get['article_id']) && (!isset($this->request->server['REQUEST_METHOD']) || $this->request->server['REQUEST_METHOD'] != 'POST')) {
			$article_info = $this->model_blog_article->getArticle((int)$this->request->get['article_id']);
		}

		$data['user_token'] = $this->session->data['user_token'];

		$this->load->model('localisation/language');
		$data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->post['article_description'])) {
			$data['article_description'] = $this->request->post['article_description'];
		} elseif (isset($this->request->get['article_id'])) {
			$data['article_description'] = $this->model_blog_article->getArticleDescriptions((int)$this->request->get['article_id']);
		} else {
			$data['article_description'] = array();
		}

		// перезаписывать heading_title названием статьи
		$language_id = $this->config->get('config_language_id');
		if (isset($data['article_description'][$language_id]['name'])) {
			$data['heading_title'] = $data['article_description'][$language_id]['name'];
		}

		if (isset($this->request->post['image'])) {
			$data['image'] = $this->request->post['image'];
		} elseif (!empty($article_info) && isset($article_info['image'])) {
			$data['image'] = $article_info['image'];
		} else {
			$data['image'] = '';
		}

		$this->load->model('tool/image');

		if (isset($this->request->post['image']) && $this->request->post['image'] && is_file(DIR_IMAGE . $this->request->post['image'])) {
			$data['thumb'] = $this->model_tool_image->resize($this->request->post['image'], 100, 100);
		} elseif (!empty($article_info) && isset($article_info['image']) && $article_info['image'] && is_file(DIR_IMAGE . $article_info['image'])) {
			$data['thumb'] = $this->model_tool_image->resize($article_info['image'], 100, 100);
		} else {
			$data['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		$this->load->model('setting/store');

		$data['stores'] = array();

		$data['stores'][] = array(
			'store_id' => 0,
			'name'     => $this->language->get('text_default')
		);

		$stores = $this->model_setting_store->getStores();

		foreach ($stores as $store) {
			$data['stores'][] = array(
				'store_id' => $store['store_id'],
				'name'     => $store['name']
			);
		}

		if (isset($this->request->post['article_store'])) {
			$data['article_store'] = $this->request->post['article_store'];
		} elseif (isset($this->request->get['article_id'])) {
			$data['article_store'] = $this->model_blog_article->getArticleStores((int)$this->request->get['article_id']);
		} else {
			$data['article_store'] = array(0);
		}

		if (isset($this->request->post['sort_order'])) {
			$data['sort_order'] = $this->request->post['sort_order'];
		} elseif (!empty($article_info) && isset($article_info['sort_order'])) {
			$data['sort_order'] = $article_info['sort_order'];
		} else {
			$data['sort_order'] = 1;
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($article_info) && isset($article_info['status'])) {
			$data['status'] = $article_info['status'];
		} else {
			$data['status'] = true;
		}

		if (isset($this->request->post['noindex'])) {
			$data['noindex'] = $this->request->post['noindex'];
		} elseif (!empty($article_info) && isset($article_info['noindex'])) {
			$data['noindex'] = $article_info['noindex'];
		} else {
			$data['noindex'] = 1;
		}

		// Blog Categories
		$this->load->model('blog/category');

		$categories = $this->model_blog_category->getAllCategories();
		$data['categories'] = $this->model_blog_category->getCategories($categories);

		if (isset($this->request->post['main_blog_category_id'])) {
			$data['main_blog_category_id'] = $this->request->post['main_blog_category_id'];
		} elseif (!empty($article_info)) {
			$data['main_blog_category_id'] = $this->model_blog_article->getArticleMainCategoryId((int)$this->request->get['article_id']);
		} else {
			$data['main_blog_category_id'] = 0;
		}

		if (isset($this->request->post['article_blog_category'])) {
			$selected_categories = $this->request->post['article_blog_category'];
		} elseif (isset($this->request->get['article_id'])) {
			$selected_categories = $this->model_blog_article->getArticleCategories((int)$this->request->get['article_id']);
		} else {
			$selected_categories = array();
		}

		$data['article_categories'] = array();

		foreach ($selected_categories as $blog_category_id) {
			$category_info = $this->model_blog_category->getCategory($blog_category_id);

			if ($category_info) {
				$data['article_categories'][] = array(
					'blog_category_id' => $category_info['blog_category_id'],
					'name'             => ($category_info['path']) ? $category_info['path'] . ' &gt; ' . $category_info['name'] : $category_info['name']
				);
			}
		}

		if (isset($this->request->post['article_image'])) {
			$article_images = $this->request->post['article_image'];
		} elseif (isset($this->request->get['article_id'])) {
			$article_images = $this->model_blog_article->getArticleImages((int)$this->request->get['article_id']);
		} else {
			$article_images = array();
		}

		$data['article_images'] = array();

		foreach ($article_images as $article_image) {
			$image_path = isset($article_image['image']) ? $article_image['image'] : '';
			$sort_order = isset($article_image['sort_order']) ? $article_image['sort_order'] : 0;

			if ($image_path && is_file(DIR_IMAGE . $image_path)) {
				$image = $image_path;
				$thumb = $image_path;
			} else {
				$image = '';
				$thumb = 'no_image.png';
			}

			$data['article_images'][] = array(
				'image'      => $image,
				'thumb'      => $this->model_tool_image->resize($thumb, 100, 100),
				'sort_order' => $sort_order
			);
		}

		$data['image_row'] = count($data['article_images']);

		// Downloads
		$this->load->model('catalog/download');

		if (isset($this->request->post['article_download'])) {
			$article_downloads = $this->request->post['article_download'];
		} elseif (isset($this->request->get['article_id'])) {
			$article_downloads = $this->model_blog_article->getArticleDownloads((int)$this->request->get['article_id']);
		} else {
			$article_downloads = array();
		}

		$data['article_downloads'] = array();

		foreach ($article_downloads as $download_id) {
			$download_info = $this->model_catalog_download->getDownload($download_id);

			if ($download_info) {
				$data['article_downloads'][] = array(
					'download_id' => $download_info['download_id'],
					'name'        => $download_info['name']
				);
			}
		}

		if (isset($this->request->post['article_related'])) {
			$articles = $this->request->post['article_related'];
		} elseif (isset($this->request->get['article_id'])) {
			$articles = $this->model_blog_article->getArticleRelated((int)$this->request->get['article_id']);
		} else {
			$articles = array();
		}

		$data['article_relateds'] = array();

		foreach ($articles as $article_id) {
			$related_info = $this->model_blog_article->getArticle($article_id);

			if ($related_info) {
				$data['article_relateds'][] = array(
					'article_id' => $related_info['article_id'],
					'name'       => $related_info['name']
				);
			}
		}

		if (isset($this->request->post['product_related'])) {
			$products = $this->request->post['product_related'];
		} elseif (!empty($article_info)) {
			$products = $this->model_blog_article->getProductRelated((int)$this->request->get['article_id']);
		} else {
			$products = array();
		}

		$data['product_relateds'] = array();

		$this->load->model('catalog/product');

		foreach ($products as $product_id) {
			$product_info = $this->model_catalog_product->getProduct($product_id);

			if ($product_info) {
				$data['product_relateds'][] = array(
					'product_id' => $product_info['product_id'],
					'name'       => $product_info['name']
				);
			}
		}

		if (isset($this->request->post['article_seo_url'])) {
			$data['article_seo_url'] = $this->request->post['article_seo_url'];
		} elseif (isset($this->request->get['article_id'])) {
			$data['article_seo_url'] = $this->model_blog_article->getArticleSeoUrls((int)$this->request->get['article_id']);
		} else {
			$data['article_seo_url'] = array();
		}

		if (isset($this->request->post['article_layout'])) {
			$data['article_layout'] = $this->request->post['article_layout'];
		} elseif (isset($this->request->get['article_id'])) {
			$data['article_layout'] = $this->model_blog_article->getArticleLayouts((int)$this->request->get['article_id']);
		} else {
			$data['article_layout'] = array();
		}

		$this->load->model('design/layout');
		$data['layouts'] = $this->model_design_layout->getLayouts();

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('blog/article_form', $data));
	}

	protected function validateForm() {
		if (!$this->validateModifyPermission()) {
			return false;
		}

		if (!isset($this->request->post['article_description']) || !is_array($this->request->post['article_description'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		} else {
			foreach ($this->request->post['article_description'] as $language_id => $value) {
				$name = isset($value['name']) ? $value['name'] : '';
				$meta_title = isset($value['meta_title']) ? $value['meta_title'] : '';
				$meta_h1 = isset($value['meta_h1']) ? $value['meta_h1'] : '';

				if ((utf8_strlen($name) < 3) || (utf8_strlen($name) > 255)) {
					$this->error['name'][$language_id] = $this->language->get('error_name');
				}

				if (utf8_strlen($meta_title) > 255) {
					$this->error['meta_title'][$language_id] = $this->language->get('error_meta_title');
				}

				if (utf8_strlen($meta_h1) > 255) {
					$this->error['meta_h1'][$language_id] = $this->language->get('error_meta_h1');
				}
			}
		}

		if (!empty($this->request->post['article_seo_url']) && is_array($this->request->post['article_seo_url'])) {
			$this->load->model('design/seo_url');

			foreach ($this->request->post['article_seo_url'] as $store_id => $language) {
				if (!is_array($language)) {
					continue;
				}

				foreach ($language as $language_id => $keyword) {
					$keyword = trim((string)$keyword);

					if ($keyword !== '') {
						if (count(array_keys($language, $keyword, true)) > 1) {
							$this->error['keyword'][$store_id][$language_id] = $this->language->get('error_unique');
						}

						$seo_urls = $this->model_design_seo_url->getSeoUrlsByKeyword($keyword);

						foreach ($seo_urls as $seo_url) {
							if ((int)$seo_url['store_id'] == (int)$store_id && (!isset($this->request->get['article_id']) || ($seo_url['query'] != 'article_id=' . (int)$this->request->get['article_id']))) {
								$this->error['keyword'][$store_id][$language_id] = $this->language->get('error_keyword');
								break;
							}
						}
					}
				}
			}
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}

	public function autocomplete() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {
			$this->load->model('blog/article');

			$filter_name = $this->request->get['filter_name'];

			$limit = isset($this->request->get['limit']) ? (int)$this->request->get['limit'] : (int)$this->config->get('config_limit_autocomplete');

			if ($limit < 1) {
				$limit = (int)$this->config->get('config_limit_autocomplete');
			}

			$filter_data = array(
				'filter_name' => $filter_name,
				'start'       => 0,
				'limit'       => $limit
			);

			$results = $this->model_blog_article->getArticles($filter_data);

			foreach ($results as $result) {
				$json[] = array(
					'article_id' => $result['article_id'],
					'name'       => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'))
				);
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json, JSON_UNESCAPED_UNICODE));
	}

	protected function validateDelete() {
		return $this->validateModifyPermission();
	}

	protected function validateCopy() {
		return $this->validateModifyPermission();
	}

	protected function validateEnable() {
		return $this->validateModifyPermission();
	}

	protected function validateDisable() {
		return $this->validateModifyPermission();
	}

	protected function validateModifyPermission() {
		if (!$this->user->hasPermission('modify', 'blog/article')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	private function buildUrl() {
		$url = '';

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if (isset($this->request->get['filter_noindex'])) {
			$url .= '&filter_noindex=' . $this->request->get['filter_noindex'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . (int)$this->request->get['page'];
		}

		return $url;
	}
}