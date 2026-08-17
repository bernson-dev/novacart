<?php
class ControllerCatalogCategory extends Controller {
	private $error = array();
	private $category_id = 0;
	private $path = array();

	public function index() {
		$this->load->language('catalog/category');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/category');

		$this->getList();
	}

	public function add() {
		$this->load->language('catalog/category');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/category');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			// Изменено: сохраненям Id категории
			$category_id = $this->model_catalog_category->addCategory($this->request->post);

			//$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			// имя категории (берём для языка админки/дефолтного)
			$language_id = (int)$this->config->get('config_language_id');
			$name = '';
			if (!empty($this->request->post['category_description'][$language_id]['name'])) {
				$name = $this->request->post['category_description'][$language_id]['name'];
			} else {
				// запасной вариант: взять первое попавшееся имя
				$first = reset($this->request->post['category_description']);
				$name = $first['name'] ?? '';
			}

			// Сохранить и добавить новую категорию (потоковое добавление)
			if (isset($this->request->post['save_new']) && (int)$this->request->post['save_new'] === 1) {
				$this->session->data['success'] = sprintf(
				$this->language->get('text_success_add_new'),
				$name
				);

				$this->response->redirect($this->url->link(
				'catalog/category/add',
				'user_token=' . $this->session->data['user_token'] . $url,
				true
				));
			}

			// apply -> edit (применить: сохранить + продолжить редактирование)
			if (isset($this->request->post['apply']) && (int)$this->request->post['apply'] === 1) {
				$this->session->data['success'] = sprintf(
				$this->language->get('text_success_apply'),
				$name
				);

				$this->response->redirect($this->url->link(
				'catalog/category/edit',
				'user_token=' . $this->session->data['user_token'] . '&category_id=' . (int)$category_id . $url,
				true
				));
			}

			//Сохранить: стандартное повдение переход в список категорий
			$this->session->data['success'] = sprintf(
			$this->language->get('text_success_created'),
			$name
			);
			$this->response->redirect($this->url->link(
			'catalog/category',
			'user_token=' . $this->session->data['user_token'] . $url,
			true
			));
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('catalog/category');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/category');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_catalog_category->editCategory($this->request->get['category_id'], $this->request->post);

			//$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			// имя категории (берём для языка админки/дефолтного)
			$language_id = (int)$this->config->get('config_language_id');
			$name = '';
			if (!empty($this->request->post['category_description'][$language_id]['name'])) {
				$name = $this->request->post['category_description'][$language_id]['name'];
			} else {
				// запасной вариант: взять первое попавшееся имя
				$first = reset($this->request->post['category_description']);
				$name = $first['name'] ?? '';
			}

			// Сохранить и добавить новую категорию (потоковое добавление)
			if (isset($this->request->post['save_new']) && (int)$this->request->post['save_new'] === 1) {
				$this->session->data['success'] = sprintf(
				$this->language->get('text_success_add_new'),
				$name
				);

				$this->response->redirect($this->url->link(
				'catalog/category/add',
				'user_token=' . $this->session->data['user_token'] . $url,
				true
				));
			}

			// apply -> edit (применить: сохранить + продолжить редактирование)
			if (isset($this->request->post['apply']) && (int)$this->request->post['apply'] === 1) {
				$this->session->data['success'] = sprintf(
				$this->language->get('text_success_apply'),
				$name
				);

				$this->response->redirect($this->url->link(
				'catalog/category/edit',
				'user_token=' . $this->session->data['user_token'] . '&category_id=' . $this->request->get['category_id'] . $url,
				true
				));
			}

			//Сохранить: стандартное повдение: в список категорий
			$this->session->data['success'] = sprintf(
			$this->language->get('text_success_created'),
			$name
			);
			$this->response->redirect($this->url->link(
			'catalog/category',
			'user_token=' . $this->session->data['user_token'] . $url,
			true
			));
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('catalog/category');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/category');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $category_id) {
				$this->model_catalog_category->deleteCategory($category_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('catalog/category', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	public function repair() {
		$this->load->language('catalog/category');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/category');

		if ($this->validateRepair()) {
			$this->model_catalog_category->repairCategories();

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('catalog/category', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	protected function getList() {
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'name';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('catalog/category', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['add'] = $this->url->link('catalog/category/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('catalog/category/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['repair'] = $this->url->link('catalog/category/repair', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['enabled'] = $this->url->link('catalog/category/enable', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['disabled'] = $this->url->link('catalog/category/disable', 'user_token=' . $this->session->data['user_token'] . $url, true);

		// добавлено
		$data['button_expand_all'] = $this->language->get('button_expand_all');
		$data['button_collapse_all'] = $this->language->get('button_collapse_all');
		$data['button_clear_filter'] = $this->language->get('button_clear_filter');
		$data['text_quick_find_category'] = $this->language->get('text_quick_find_category');
		// добавлено


		$data['categories'] = $this->getCategories(0, 0, '');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

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

		$url = '';

		$category_total = $this->model_catalog_category->getTotalCategories();

		$data['results'] = $this->language->get('text_category_total') . ($category_total);
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/category_list', $data));
	}

	protected function getForm() {

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

		$data['text_form'] = !isset($this->request->get['category_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		// Добавлено вывод сообщений в форму добавления
		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = array();
		}

		if (isset($this->error['meta_title'])) {
			$data['error_meta_title'] = $this->error['meta_title'];
		} else {
			$data['error_meta_title'] = array();
		}

		if (isset($this->error['meta_h1'])) {
			$data['error_meta_h1'] = $this->error['meta_h1'];
		} else {
			$data['error_meta_h1'] = array();
		}

		if (isset($this->error['keyword'])) {
			$data['error_keyword'] = $this->error['keyword'];
		} else {
			$data['error_keyword'] = '';
		}

		if (isset($this->error['parent'])) {
			$data['error_parent'] = $this->error['parent'];
		} else {
			$data['error_parent'] = '';
		}

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('catalog/category', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		if (!isset($this->request->get['category_id'])) {
			$data['action'] = $this->url->link('catalog/category/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		} else {
			$data['action'] = $this->url->link('catalog/category/edit', 'user_token=' . $this->session->data['user_token'] . '&category_id=' . $this->request->get['category_id'] . $url, true);
		}

		$data['cancel'] = $this->url->link('catalog/category', 'user_token=' . $this->session->data['user_token'] . $url, true);

		if (isset($this->request->get['category_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$category_info = $this->model_catalog_category->getCategory($this->request->get['category_id']);
		}

		if (isset($this->request->get['category_id'])) {
			$data['category_id'] = $this->request->get['category_id'];
			$data['href_shop'] = HTTP_CATALOG . 'index.php?route=product/category&path=' . $this->request->get['category_id'];
		}

		$data['user_token'] = $this->session->data['user_token'];

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->post['category_description'])) {
			$data['category_description'] = $this->request->post['category_description'];
		} elseif (isset($this->request->get['category_id'])) {
			$data['category_description'] = $this->model_catalog_category->getCategoryDescriptions($this->request->get['category_id']);
		} else {
			$data['category_description'] = array();
		}

		$language_id = $this->config->get('config_language_id');
		if (isset($data['category_description'][$language_id]['name'])) {
			$data['heading_title'] = $data['category_description'][$language_id]['name'];
		}

		if (isset($this->request->post['path'])) {
			$data['path'] = $this->request->post['path'];
		} elseif (!empty($category_info)) {
			$data['path'] = $category_info['path'];
		} else {
			$data['path'] = '';
		}

		if (isset($this->request->post['parent_id'])) {
			$data['parent_id'] = $this->request->post['parent_id'];
		} elseif (!empty($category_info)) {
			$data['parent_id'] = $category_info['parent_id'];
		} else {
			$data['parent_id'] = 0;
		}

		$this->load->model('catalog/filter');

		if (isset($this->request->post['category_filter'])) {
			$filters = $this->request->post['category_filter'];
		} elseif (isset($this->request->get['category_id'])) {
			$filters = $this->model_catalog_category->getCategoryFilters($this->request->get['category_id']);
		} else {
			$filters = array();
		}

		$data['category_filters'] = array();

		foreach ($filters as $filter_id) {
			$filter_info = $this->model_catalog_filter->getFilter($filter_id);

			if ($filter_info) {
				$data['category_filters'][] = array(
					'filter_id' => $filter_info['filter_id'],
					'name'      => $filter_info['group'] . ' &gt; ' . $filter_info['name']
				);
			}
		}

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

		if (isset($this->request->post['category_store'])) {
			$data['category_store'] = $this->request->post['category_store'];
		} elseif (isset($this->request->get['category_id'])) {
			$data['category_store'] = $this->model_catalog_category->getCategoryStores($this->request->get['category_id']);
		} else {
			$data['category_store'] = array(0);
		}

		if (isset($this->request->post['image'])) {
			$data['image'] = $this->request->post['image'];
		} elseif (!empty($category_info)) {
			$data['image'] = $category_info['image'];
		} else {
			$data['image'] = '';
		}

		$this->load->model('tool/image');

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		if (isset($this->request->post['image']) && is_file(DIR_IMAGE . $this->request->post['image'])) {
			$data['thumb'] = $this->model_tool_image->resize($this->request->post['image'], 100, 100);
		} elseif (!empty($category_info) && is_file(DIR_IMAGE . $category_info['image'])) {
			$data['thumb'] = $this->model_tool_image->resize($category_info['image'], 100, 100);
		} else {
			$data['thumb'] = $data['placeholder'];
		}

		if (isset($this->request->post['top'])) {
			$data['top'] = $this->request->post['top'];
		} elseif (!empty($category_info)) {
			$data['top'] = $category_info['top'];
		} else {
			$data['top'] = 0;
		}

		if (isset($this->request->post['column'])) {
			$data['column'] = $this->request->post['column'];
		} elseif (!empty($category_info)) {
			$data['column'] = $category_info['column'];
		} else {
			$data['column'] = 1;
		}

		if (isset($this->request->post['sort_order'])) {
			$data['sort_order'] = $this->request->post['sort_order'];
		} elseif (!empty($category_info)) {
			$data['sort_order'] = $category_info['sort_order'];
		} else {
			$data['sort_order'] = 0;
		}

		if (isset($this->request->post['product_related'])) {
			$products = $this->request->post['product_related'];
		} elseif (isset($category_info)) {
			$products = $this->model_catalog_category->getProductRelated($this->request->get['category_id']);
		} else {
			$products = array();
		}

		$data['product_related'] = array();

		$this->load->model('catalog/product');

		foreach ($products as $product_id) {
			$related_info = $this->model_catalog_product->getProduct($product_id);

			if ($related_info) {
				$data['product_related'][] = array(
					'product_id' => $related_info['product_id'],
					'name'       => $related_info['name']
				);
			}
		}

		if (isset($this->request->post['article_related'])) {
			$articles = $this->request->post['article_related'];
		} elseif (isset($category_info)) {
			$articles = $this->model_catalog_category->getArticleRelated($this->request->get['category_id']);
		} else {
			$articles = array();
		}

		$data['article_related'] = array();

		$this->load->model('blog/article');

		foreach ($articles as $article_id) {
			$related_info = $this->model_blog_article->getArticle($article_id);

			if ($related_info) {
				$data['article_related'][] = array(
					'article_id' => $related_info['article_id'],
					'name'       => $related_info['name']
				);
			}
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($category_info)) {
			$data['status'] = $category_info['status'];
		} else {
			$data['status'] = true;
		}

		if (isset($this->request->post['category_seo_url'])) {
			$data['category_seo_url'] = $this->request->post['category_seo_url'];
		} elseif (isset($this->request->get['category_id'])) {
			$data['category_seo_url'] = $this->model_catalog_category->getCategorySeoUrls($this->request->get['category_id']);
		} else {
			$data['category_seo_url'] = array();
		}

		if (isset($this->request->post['noindex'])) {
			$data['noindex'] = $this->request->post['noindex'];
		} elseif (!empty($category_info)) {
			$data['noindex'] = $category_info['noindex'];
		} else {
			$data['noindex'] = 1;
		}

		if (isset($this->request->post['category_layout'])) {
			$data['category_layout'] = $this->request->post['category_layout'];
		} elseif (isset($this->request->get['category_id'])) {
			$data['category_layout'] = $this->model_catalog_category->getCategoryLayouts($this->request->get['category_id']);
		} else {
			$data['category_layout'] = array();
		}

		$this->load->model('design/layout');

		$data['layouts'] = $this->model_design_layout->getLayouts();

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/category_form', $data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'catalog/category')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post['category_description'] as $language_id => $value) {
			if ((utf8_strlen($value['name']) < 1) || (utf8_strlen($value['name']) > 255)) {
				$this->error['name'][$language_id] = $this->language->get('error_name');
			}

			if (utf8_strlen($value['meta_title']) > 255) {
				$this->error['meta_title'][$language_id] = $this->language->get('error_meta_title');
			}

			if (utf8_strlen($value['meta_h1']) > 255) {
				$this->error['meta_h1'][$language_id] = $this->language->get('error_meta_h1');
			}
		}

		if (isset($this->request->get['category_id']) && $this->request->post['parent_id']) {
			$results = $this->model_catalog_category->getCategoryPath($this->request->post['parent_id']);

			foreach ($results as $result) {
				if ($result['path_id'] == $this->request->get['category_id']) {
					$this->error['parent'] = $this->language->get('error_parent');

					break;
				}
			}
		}

		if (!empty($this->request->post['category_seo_url'])) {
			$this->load->model('design/seo_url');

			foreach ($this->request->post['category_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						if (count(array_keys($language, $keyword)) > 1) {
							$this->error['keyword'][$store_id][$language_id] = $this->language->get('error_unique');
						}

						$seo_urls = $this->model_design_seo_url->getSeoUrlsByKeyword($keyword);

						foreach ($seo_urls as $seo_url) {
							if (($seo_url['store_id'] == $store_id) && (!isset($this->request->get['category_id']) || ($seo_url['query'] != 'category_id=' . $this->request->get['category_id']))) {
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

	public function enable() {
		$this->load->language('catalog/category');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('catalog/category');
		if (isset($this->request->post['selected']) && $this->validateEnable()) {
			foreach ($this->request->post['selected'] as $category_id) {
				$this->model_catalog_category->editCategoryStatus($category_id, 1);
			}
			$this->session->data['success'] = $this->language->get('text_success');
			$url = '';
			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}
			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}
			$this->response->redirect($this->url->link('catalog/category', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}
		$this->getList();
	}

	public function disable() {
		$this->load->language('catalog/category');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('catalog/category');
		if (isset($this->request->post['selected']) && $this->validateDisable()) {
			foreach ($this->request->post['selected'] as $category_id) {
				$this->model_catalog_category->editCategoryStatus($category_id, 0);
			}
			$this->session->data['success'] = $this->language->get('text_success');
			$url = '';
			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}
			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}
			$this->response->redirect($this->url->link('catalog/category', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}
		$this->getList();
	}

	protected function validateEnable() {
		if (!$this->user->hasPermission('modify', 'catalog/category')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	protected function validateDisable() {
		if (!$this->user->hasPermission('modify', 'catalog/category')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'catalog/category')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	protected function validateRepair() {
		if (!$this->user->hasPermission('modify', 'catalog/category')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function autocomplete() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {
			$this->load->model('catalog/category');

			$filter_data = array(
				'filter_name' => $this->request->get['filter_name'],
				'sort'        => 'name',
				'order'       => 'ASC',
				'start'       => 0,
				'limit'       => $this->config->get('config_limit_autocomplete')
			);

			$results = $this->model_catalog_category->getCategories($filter_data);

			foreach ($results as $result) {
				$json[] = array(
					'category_id' => $result['category_id'],
					'name'        => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'))
				);
			}
		}

		$sort_order = array();

		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['name'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	private function getCategories($parent_id = 0, $level = 0, $parent_path = '') {
		$output = array();

		static $status_text = null;
		static $noindex_text = null;

		if ($status_text === null) {
			$status_text = array(
			0 => $this->language->get('text_disabled'),
			1 => $this->language->get('text_enabled')
			);
		}

		if ($noindex_text === null) {
			$noindex_text = array(
			0 => $this->language->get('text_noindex'),
			1 => $this->language->get('text_index')
			);
		}

		$results = $this->model_catalog_category->getCategoriesByParentId($parent_id);

		foreach ($results as $result) {
			$category_id = (int)$result['category_id'];
			$current_path = $parent_path ? $parent_path . '_' . $category_id : (string)$category_id;
			$has_children = !empty($result['children']);

			$name = html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8');
			$search_name = utf8_strtolower(strip_tags($name));

			$output[$category_id] = array(
			'category_id'  => $category_id,
			'parent_id'    => (int)$parent_id,
			'level'        => (int)$level,
			'path'         => $current_path,
			'name'         => $name,
			'search_name'  => $search_name,
			'sort_order'   => $result['sort_order'],
			'status'       => (bool)$result['status'],
			'status_text'  => $status_text[(int)$result['status']],
			'noindex'      => isset($noindex_text[(int)$result['noindex']]) ? $noindex_text[(int)$result['noindex']] : '',
			'edit'         => $this->url->link('catalog/category/edit', 'user_token=' . $this->session->data['user_token'] . '&category_id=' . $category_id, true),
			'href_shop'    => HTTP_CATALOG . 'index.php?route=product/category&path=' . $current_path,
			'has_children' => $has_children
			);

			if ($has_children) {
				$output += $this->getCategories($category_id, $level + 1, $current_path);
			}
		}

		return $output;
	}

	private function getAllCategories($categories, $parent_id = 0, $parent_name = '') {
		$output = array();
		if (array_key_exists($parent_id, $categories)) {
			if ($parent_name != '') {
				$parent_name .= $this->language->get('text_separator');
			}
			foreach ($categories[$parent_id] as $category) {
				$output[$category['category_id']] = array(
					'category_id' => $category['category_id'],
					'name'        => $parent_name . $category['name']
				);
				$output += $this->getAllCategories($categories, $category['category_id'], $parent_name . $category['name']);
			}
		}
		return $output;
	}
}
