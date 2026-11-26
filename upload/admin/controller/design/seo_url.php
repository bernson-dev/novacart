<?php
class ControllerDesignSeoUrl extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('design/seo_url');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('design/seo_url');

		$this->getList();
	}

	public function redirects() {
		$this->load->language('design/seo_url');
		$this->load->model('design/seo_redirect');
		$this->load->model('setting/store');
		$this->load->model('setting/setting');
		$this->load->model('localisation/language');

		$model = $this->model_design_seo_redirect;
		$data['available'] = $model->isAvailable();
		$data['can_modify'] = $this->user->hasPermission('modify', 'design/seo_url');
		$data['stores'] = $this->model_setting_store->getStores();
		$data['languages'] = $this->model_localisation_language->getLanguages();
		$store_urls = array(0 => HTTP_CATALOG);
		foreach ($data['stores'] as $store) {
			$store_urls[(int)$store['store_id']] = $store['url'];
		}
		$language_ids = array(0);
		foreach ($data['languages'] as $language) {
			$language_ids[] = (int)$language['language_id'];
		}
		$token = 'user_token=' . $this->session->data['user_token'];
		$data['error_warning'] = '';
		$data['success'] = $this->session->data['seo_redirect_success'] ?? '';
		unset($this->session->data['seo_redirect_success']);
		$data['filter_search'] = is_string($this->request->get['filter_search'] ?? null) ? trim(html_entity_decode($this->request->get['filter_search'], ENT_QUOTES, 'UTF-8')) : '';
		$filter_url = $data['filter_search'] !== '' ? '&filter_search=' . rawurlencode($data['filter_search']) : '';
		$data['form'] = array('seo_redirect_id' => 0, 'source_path' => '', 'target_path' => '', 'store_id' => 0, 'language_id' => 0, 'http_code' => 301, 'status' => 1);
		if ($data['available'] && isset($this->request->get['edit_id'])) {
			$row = $model->getRule((int)$this->request->get['edit_id']);
			if ($row) {
				$data['form'] = $row;
			}
		}

		if ($this->request->server['REQUEST_METHOD'] === 'POST') {
			if (!$data['can_modify']) {
				$data['error_warning'] = $this->language->get('error_permission');
			} elseif ($data['available']) {
				$post = $this->request->post;
				$error = '';
				if (($post['operation'] ?? '') === 'delete') {
					foreach (is_array($post['selected'] ?? null) ? $post['selected'] : array() as $id) {
						if (is_scalar($id) && ctype_digit((string)$id) && (int)$id > 0) {
							$model->deleteRule((int)$id);
						}
					}
				} elseif (($post['operation'] ?? '') === 'save') {
					foreach ($data['form'] as $key => $default) {
						if (array_key_exists($key, $post)) {
							$data['form'][$key] = is_scalar($post[$key]) ? html_entity_decode((string)$post[$key], ENT_QUOTES, 'UTF-8') : '';
						}
					}
					$form = $data['form'];
					$store_id = (int)$form['store_id'];
					$language_id = (int)$form['language_id'];
					if (!ctype_digit((string)$form['store_id']) || !ctype_digit((string)$form['language_id']) || !array_key_exists($store_id, $store_urls) || !in_array($language_id, $language_ids, true)) {
						$error = 'error_redirect_scope';
					} else {
						$settings = $this->model_setting_setting->getSetting('config', 0);
						if ($store_id) {
							$settings = array_replace($settings, $this->model_setting_setting->getSetting('config', $store_id));
						}
						$base = parse_url($store_urls[$store_id], PHP_URL_PATH) ?: '/';
						$error = $model->saveRule($form, $base, $settings, $data['languages']);
					}
				} else {
					$error = 'error_request_method';
				}
				if ($error === '') {
					$this->session->data['seo_redirect_success'] = $this->language->get('text_success');
					$this->response->redirect($this->url->link('design/seo_url/redirects', $token . $filter_url, true));
					return;
				}
				$data['error_warning'] = $this->language->get($error);
			}
		}

		$limit = max(1, (int)$this->config->get('config_limit_admin'));
		$total = $data['available'] ? $model->getTotalRules($data['filter_search']) : 0;
		$page = min(max(1, (int)($this->request->get['page'] ?? 1)), max(1, (int)ceil($total / $limit)));
		$start = ($page - 1) * $limit;
		$data['rows'] = $data['available'] ? $model->getRules($data['filter_search'], $start, $limit) : array();
		$data['action'] = $this->url->link('design/seo_url/redirects', $token . $filter_url, true);
		$data['reset'] = $this->url->link('design/seo_url/redirects', $token, true);
		$data['back'] = $this->url->link('design/seo_url', $token, true);
		foreach ($data['rows'] as &$row) {
			$row['edit'] = $this->url->link('design/seo_url/redirects', $token . $filter_url . '&edit_id=' . (int)$row['seo_redirect_id'], true);
		}
		unset($row);
		$data['user_token'] = $this->session->data['user_token'];
		$data['heading_title'] = $this->language->get('text_redirects');
		$this->document->setTitle($data['heading_title']);
		$data['breadcrumbs'] = array(
			array('text' => $this->language->get('text_home'), 'href' => $this->url->link('common/dashboard', $token, true)),
			array('text' => $this->language->get('heading_title'), 'href' => $data['back']),
			array('text' => $data['heading_title'], 'href' => $data['reset'])
		);
		$pagination = new Pagination();
		$pagination->total = $total;
		$pagination->page = $page;
		$pagination->limit = $limit;
		$pagination->url = $this->url->link('design/seo_url/redirects', $token . $filter_url . '&page={page}', true);
		$data['pagination'] = $pagination->render();
		$data['results'] = sprintf($this->language->get('text_pagination'), $total ? $start + 1 : 0, min($total, $start + $limit), $total, ceil($total / $limit));
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		$this->response->setOutput($this->load->view('design/seo_redirect_list', $data));
	}

	public function history() {
		$this->load->language('design/seo_url');
		$this->load->model('design/seo_url');
		$this->load->model('setting/store');
		$this->load->model('localisation/language');

		$token = 'user_token=' . $this->session->data['user_token'];
		$data['available'] = $this->model_design_seo_url->isHistoryAvailable();
		$data['can_modify'] = $this->user->hasPermission('modify', 'design/seo_url');
		$data['error_warning'] = '';
		$data['success'] = '';
		$data['filter_search'] = isset($this->request->get['filter_search']) && is_string($this->request->get['filter_search']) ? trim($this->request->get['filter_search']) : '';
		foreach (array('store_id', 'language_id') as $key) {
			$value = $this->request->get['filter_' . $key] ?? '';
			$data['filter_' . $key] = is_scalar($value) && ctype_digit((string)$value) ? (string)(int)$value : '';
		}

		$filter_url = '';
		foreach (array('filter_search', 'filter_store_id', 'filter_language_id') as $key) {
			if ($data[$key] !== '') {
				$filter_url .= '&' . $key . '=' . rawurlencode($data[$key]);
			}
		}
		$page = max(1, (int)($this->request->get['page'] ?? 1));
		if ($this->request->server['REQUEST_METHOD'] === 'POST') {
			if (!$data['can_modify']) {
				$data['error_warning'] = $this->language->get('error_permission');
			} elseif ($data['available'] && isset($this->request->post['selected']) && is_array($this->request->post['selected'])) {
				foreach ($this->request->post['selected'] as $id) {
					if (is_scalar($id) && ctype_digit((string)$id) && (int)$id > 0) {
						$this->model_design_seo_url->deleteHistory((int)$id);
					}
				}
				$this->session->data['seo_history_success'] = $this->language->get('text_success');
				$this->response->redirect($this->url->link('design/seo_url/history', $token . $filter_url, true));
				return;
			}
		}
		if (isset($this->session->data['seo_history_success'])) {
			$data['success'] = $this->session->data['seo_history_success'];
			unset($this->session->data['seo_history_success']);
		}

		$limit = max(1, (int)$this->config->get('config_limit_admin'));
		$total = $data['available'] ? $this->model_design_seo_url->getTotalHistory($data) : 0;
		$page = min($page, max(1, (int)ceil($total / $limit)));
		$data['start'] = ($page - 1) * $limit;
		$data['limit'] = $limit;
		$data['rows'] = $data['available'] ? $this->model_design_seo_url->getHistory($data) : array();
		$data['stores'] = $this->model_setting_store->getStores();
		$data['languages'] = $this->model_localisation_language->getLanguages();
		$data['user_token'] = $this->session->data['user_token'];
		$data['back'] = $this->url->link('design/seo_url', $token, true);
		$data['reset'] = $this->url->link('design/seo_url/history', $token, true);
		$data['action'] = $this->url->link('design/seo_url/history', $token . $filter_url, true);
		$data['heading_title'] = $this->language->get('text_history');
		$this->document->setTitle($data['heading_title']);
		$data['breadcrumbs'] = array(
			array('text' => $this->language->get('text_home'), 'href' => $this->url->link('common/dashboard', $token, true)),
			array('text' => $this->language->get('heading_title'), 'href' => $data['back']),
			array('text' => $data['heading_title'], 'href' => $data['reset'])
		);
		$pagination = new Pagination();
		$pagination->total = $total;
		$pagination->page = $page;
		$pagination->limit = $limit;
		$pagination->url = $this->url->link('design/seo_url/history', $token . $filter_url . '&page={page}', true);
		$data['pagination'] = $pagination->render();
		$data['results'] = sprintf($this->language->get('text_pagination'), $total ? $data['start'] + 1 : 0, min($total, $data['start'] + $limit), $total, ceil($total / $limit));
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		$this->response->setOutput($this->load->view('design/seo_url_history', $data));
	}

	public function add() {
		$this->load->language('design/seo_url');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('design/seo_url');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$seo_url_id = $this->model_design_seo_url->addSeoUrl($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['filter_keyword'])) {
				$url .= '&filter_keyword=' . urlencode(html_entity_decode((string)$this->request->get['filter_keyword'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_query'])) {
				$url .= '&filter_query=' . urlencode(html_entity_decode((string)$this->request->get['filter_query'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_search']) && $this->request->get['filter_search'] !== '') {
				$url .= '&filter_search=' . urlencode((string)$this->request->get['filter_search']);
			}

			if (isset($this->request->get['filter_issue']) && $this->request->get['filter_issue'] !== '') {
				$url .= '&filter_issue=' . urlencode((string)$this->request->get['filter_issue']);
			}

			if (isset($this->request->get['filter_store_id'])) {
				$url .= '&filter_store_id=' . (int)$this->request->get['filter_store_id'];
			}

			if (isset($this->request->get['filter_language_id'])) {
				$url .= '&filter_language_id=' . (int)$this->request->get['filter_language_id'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . (string)$this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . (string)$this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . (int)$this->request->get['page'];
			}

			if(isset($this->request->post['apply']) && $this->request->post['apply'] == 1) {
				$url .= '&seo_url_id=' . $seo_url_id;
				$this->response->redirect($this->url->link('design/seo_url/edit', 'user_token=' . $this->session->data['user_token'] . $url, true));
			} else {
			$this->response->redirect($this->url->link('design/seo_url', 'user_token=' . $this->session->data['user_token'] . $url, true));
			}
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('design/seo_url');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('design/seo_url');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_design_seo_url->editSeoUrl($this->request->get['seo_url_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['filter_keyword'])) {
				$url .= '&filter_keyword=' . urlencode(html_entity_decode((string)$this->request->get['filter_keyword'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_query'])) {
				$url .= '&filter_query=' . urlencode(html_entity_decode((string)$this->request->get['filter_query'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_store_id'])) {
				$url .= '&filter_store_id=' . (int)$this->request->get['filter_store_id'];
			}

			if (isset($this->request->get['filter_language_id'])) {
				$url .= '&filter_language_id=' . (int)$this->request->get['filter_language_id'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . (string)$this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . (string)$this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . (int)$this->request->get['page'];
			}

			if(isset($this->request->post['apply']) && $this->request->post['apply'] == 1) {
				$url .= '&seo_url_id=' . $this->request->get['seo_url_id'];
				$this->response->redirect($this->url->link('design/seo_url/edit', 'user_token=' . $this->session->data['user_token'] . $url, true));
			} else {
			$this->response->redirect($this->url->link('design/seo_url', 'user_token=' . $this->session->data['user_token'] . $url, true));
			}
		}

		$this->getForm();
	}

	public function saveKeyword() {
		$this->load->language('design/seo_url');
		$this->load->model('design/seo_url');

		$json = array();

		if (!$this->user->hasPermission('modify', 'design/seo_url')) {
			$json['error'] = $this->language->get('error_permission');
		} elseif ($this->request->server['REQUEST_METHOD'] !== 'POST') {
			$json['error'] = $this->language->get('error_request_method');
		} else {
			$seo_url_id = isset($this->request->post['seo_url_id'])
				? (int)$this->request->post['seo_url_id']
				: 0;
			$keyword = isset($this->request->post['keyword'])
				? trim((string)$this->request->post['keyword'])
				: '';

			$seo_url = $this->model_design_seo_url->getSeoUrl($seo_url_id);

			if (!$seo_url) {
				$json['error'] = $this->language->get('error_not_found');
			} else {
				$error = $this->validateKeywordValue(
					$keyword,
					(string)$seo_url['query'],
					(int)$seo_url['store_id'],
					(int)$seo_url['language_id'],
					$seo_url_id
				);

				if ($error !== '') {
					$json['error'] = $error;
				} else {
					$this->model_design_seo_url->editSeoUrlKeyword($seo_url_id, $keyword);

					$json['success'] = $this->language->get('text_inline_saved');
					$json['keyword'] = $keyword;
				}
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function delete() {
		$this->load->language('design/seo_url');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('design/seo_url');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $seo_url_id) {
				$this->model_design_seo_url->deleteSeoUrl($seo_url_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['filter_keyword'])) {
				$url .= '&filter_keyword=' . urlencode(html_entity_decode((string)$this->request->get['filter_keyword'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_query'])) {
				$url .= '&filter_query=' . urlencode(html_entity_decode((string)$this->request->get['filter_query'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_store_id'])) {
				$url .= '&filter_store_id=' . (int)$this->request->get['filter_store_id'];
			}

			if (isset($this->request->get['filter_language_id'])) {
				$url .= '&filter_language_id=' . (int)$this->request->get['filter_language_id'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . (string)$this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . (string)$this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . (int)$this->request->get['page'];
			}

			$this->response->redirect($this->url->link('design/seo_url', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	protected function getList() {
		if (isset($this->request->get['filter_keyword'])) {
			$filter_keyword = (string)$this->request->get['filter_keyword'];
		} else {
			$filter_keyword = '';
		}

		if (isset($this->request->get['filter_query'])) {
			$filter_query = (string)$this->request->get['filter_query'];
		} else {
			$filter_query = '';
		}

		if (isset($this->request->get['filter_search'])) {
			$filter_search = (string)$this->request->get['filter_search'];
		} else {
			$filter_search = '';
		}

		if (isset($this->request->get['filter_store_id'])) {
			$filter_store_id = (int)$this->request->get['filter_store_id'];
		} else {
			$filter_store_id = '';
		}

		if (isset($this->request->get['filter_language_id'])) {
			$filter_language_id = (int)$this->request->get['filter_language_id'];
		} else {
			$filter_language_id = '';
		}

		if (isset($this->request->get['filter_issue'])) {
			$filter_issue = (string)$this->request->get['filter_issue'];
		} else {
			$filter_issue = '';
		}

		if (isset($this->request->get['sort'])) {
			$sort = (string)$this->request->get['sort'];
		} else {
			$sort = 'query';
		}

		if (isset($this->request->get['order'])) {
			$order = (string)$this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

		if (isset($this->request->get['filter_keyword'])) {
			$url .= '&filter_keyword=' . urlencode(html_entity_decode((string)$this->request->get['filter_keyword'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_query'])) {
			$url .= '&filter_query=' . urlencode(html_entity_decode((string)$this->request->get['filter_query'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_search']) && $this->request->get['filter_search'] !== '') {
			$url .= '&filter_search=' . urlencode((string)$this->request->get['filter_search']);
		}

		if (isset($this->request->get['filter_store_id'])) {
			$url .= '&filter_store_id=' . (int)$this->request->get['filter_store_id'];
		}

		if (isset($this->request->get['filter_language_id'])) {
			$url .= '&filter_language_id=' . (int)$this->request->get['filter_language_id'];
		}

		if (isset($this->request->get['filter_issue']) && $this->request->get['filter_issue'] !== '') {
			$url .= '&filter_issue=' . urlencode((string)$this->request->get['filter_issue']);
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . (string)$this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . (string)$this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . (int)$this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('design/seo_url', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['redirects'] = $this->url->link('design/seo_url/redirects', 'user_token=' . $this->session->data['user_token'], true);
		$data['history'] = $this->url->link('design/seo_url/history', 'user_token=' . $this->session->data['user_token'], true);
		$data['add'] = $this->url->link('design/seo_url/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('design/seo_url/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['seo_urls'] = array();

		$filter_data = array(
			'filter_keyword'     => $filter_keyword,
			'filter_query'       => $filter_query,
			'filter_search'      => $filter_search,
			'filter_store_id'    => $filter_store_id,
			'filter_language_id' => $filter_language_id,
			'filter_issue'       => $filter_issue,
			'sort'               => $sort,
			'order'              => $order,
			'start'              => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'              => $this->config->get('config_limit_admin')
		);

		$audit = $this->model_design_seo_url->getSeoUrlAudit();
		$data['audit_summary'] = $audit['summary'];

		$seo_url_total = $this->model_design_seo_url->getTotalSeoUrls($filter_data);

		$results = $this->model_design_seo_url->getSeoUrls($filter_data);
		$sources = $this->model_design_seo_url->getSeoUrlSources($results);

		foreach ($results as $result) {
			$source = isset($sources[(int)$result['seo_url_id']])
				? $sources[(int)$result['seo_url_id']]
				: array();

			$source_edit = '';

			if (!empty($source['route']) && !empty($source['parameter']) && !empty($source['id'])) {
				$source_edit = $this->url->link(
					$source['route'],
					'user_token=' . $this->session->data['user_token']
						. '&' . $source['parameter'] . '=' . (int)$source['id'],
					true
				);
			}
			$source_name = isset($source['name']) ? (string)$source['name'] : '';
			$generator_value = $this->model_design_seo_url->getSeoUrlGeneratorValue(
				$result,
				$source_name
			);

			$data['seo_urls'][] = array(
				'seo_url_id' => $result['seo_url_id'],
				'keyword'    => $result['keyword'],
				'query'      => htmlspecialchars($result['query'], ENT_COMPAT, 'UTF-8'),
				'store'      => $result['store_id'] ? $result['store'] : $this->language->get('text_default'),
				'language'   => $result['language'],
				'store_id'   => (int)$result['store_id'],
				'language_id'=> (int)$result['language_id'],
				'source_name'=> $source_name,
				'generator_value' => $generator_value,
				'source_edit'=> $source_edit,
				'preview'    => $this->model_design_seo_url->getSeoUrlPreview($result),
				'preview_enabled' => !isset($source['enabled']) || !empty($source['enabled']),
				'issues'     => isset($audit['issues'][(int)$result['seo_url_id']])
					? $audit['issues'][(int)$result['seo_url_id']]
					: array(
						'query' => false,
						'keyword' => false,
						'prefix' => false,
						'shared_language' => false,
						'orphan' => false
					),
				'group'      => (
					in_array($filter_issue, array('keyword', 'query', 'shared_language'))
					&& isset($audit['groups'][$filter_issue][(int)$result['seo_url_id']])
				) ? $audit['groups'][$filter_issue][(int)$result['seo_url_id']] : array(),
				'edit'       => $this->url->link('design/seo_url/edit', 'user_token=' . $this->session->data['user_token'] . '&seo_url_id=' . $result['seo_url_id'] . $url, true)
			);
		}

		$data['user_token'] = $this->session->data['user_token'];


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

		if (isset($this->request->get['filter_keyword'])) {
			$url .= '&filter_keyword=' . urlencode(html_entity_decode((string)$this->request->get['filter_keyword'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_query'])) {
			$url .= '&filter_query=' . urlencode(html_entity_decode((string)$this->request->get['filter_query'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_search']) && $this->request->get['filter_search'] !== '') {
			$url .= '&filter_search=' . urlencode((string)$this->request->get['filter_search']);
		}

		if (isset($this->request->get['filter_store_id'])) {
			$url .= '&filter_store_id=' . (int)$this->request->get['filter_store_id'];
		}

		if (isset($this->request->get['filter_language_id'])) {
			$url .= '&filter_language_id=' . (int)$this->request->get['filter_language_id'];
		}

		if (isset($this->request->get['filter_issue']) && $this->request->get['filter_issue'] !== '') {
			$url .= '&filter_issue=' . urlencode((string)$this->request->get['filter_issue']);
		}

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_keyword'] = $this->url->link('design/seo_url', 'user_token=' . $this->session->data['user_token'] . '&sort=keyword' . $url, true);
		$data['sort_query'] = $this->url->link('design/seo_url', 'user_token=' . $this->session->data['user_token'] . '&sort=query' . $url, true);
		$data['sort_store'] = $this->url->link('design/seo_url', 'user_token=' . $this->session->data['user_token'] . '&sort=store' . $url, true);
		$data['sort_language'] = $this->url->link('design/seo_url', 'user_token=' . $this->session->data['user_token'] . '&sort=language' . $url, true);

		$url = '';

		if (isset($this->request->get['filter_keyword'])) {
			$url .= '&filter_keyword=' . urlencode(html_entity_decode((string)$this->request->get['filter_keyword'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_query'])) {
			$url .= '&filter_query=' . urlencode(html_entity_decode((string)$this->request->get['filter_query'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_search']) && $this->request->get['filter_search'] !== '') {
			$url .= '&filter_search=' . urlencode((string)$this->request->get['filter_search']);
		}

		if (isset($this->request->get['filter_store_id'])) {
			$url .= '&filter_store_id=' . (int)$this->request->get['filter_store_id'];
		}

		if (isset($this->request->get['filter_language_id'])) {
			$url .= '&filter_language_id=' . (int)$this->request->get['filter_language_id'];
		}

		if (isset($this->request->get['filter_issue']) && $this->request->get['filter_issue'] !== '') {
			$url .= '&filter_issue=' . urlencode((string)$this->request->get['filter_issue']);
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . (string)$this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . (string)$this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $seo_url_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('design/seo_url', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($seo_url_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($seo_url_total - $this->config->get('config_limit_admin'))) ? $seo_url_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $seo_url_total, ceil($seo_url_total / $this->config->get('config_limit_admin')));

		$data['filter_keyword'] = $filter_keyword;
		$data['filter_query'] = $filter_query;
		$data['filter_search'] = $filter_search;
		$data['filter_store_id'] = $filter_store_id;
		$data['filter_language_id'] = $filter_language_id;
		$data['filter_issue'] = $filter_issue;

		$data['sort'] = $sort;
		$data['order'] = $order;

		$this->load->model('setting/store');

		$data['stores'] = $this->model_setting_store->getStores();

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('design/seo_url_list', $data));
	}

	protected function getForm() {
		$data['text_form'] = !isset($this->request->get['seo_url_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

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

		if (isset($this->error['keyword'])) {
			$data['error_keyword'] = $this->error['keyword'];
		} else {
			$data['error_keyword'] = '';
		}

		if (isset($this->error['query'])) {
			$data['error_query'] = $this->error['query'];
		} else {
			$data['error_query'] = '';
		}

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . (string)$this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . (string)$this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . (int)$this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('design/seo_url', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		if (!isset($this->request->get['seo_url_id'])) {
			$data['action'] = $this->url->link('design/seo_url/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		} else {
			$data['action'] = $this->url->link('design/seo_url/edit', 'user_token=' . $this->session->data['user_token'] . '&seo_url_id=' . $this->request->get['seo_url_id'] . $url, true);
		}

		$data['cancel'] = $this->url->link('design/seo_url', 'user_token=' . $this->session->data['user_token'] . $url, true);

		if (isset($this->request->get['seo_url_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$seo_url_info = $this->model_design_seo_url->getSeoUrl($this->request->get['seo_url_id']);
		}

		if (isset($this->request->post['keyword'])) {
			$data['keyword'] = $this->request->post['keyword'];
		} elseif (!empty($seo_url_info)) {
			$data['keyword'] = $seo_url_info['keyword'];
		} else {
			$data['keyword'] = '';
		}

		if (isset($this->request->post['query'])) {
			$data['query'] = $this->request->post['query'];
		} elseif (!empty($seo_url_info)) {
			$data['query'] = htmlspecialchars($seo_url_info['query'], ENT_COMPAT, 'UTF-8');
		} else {
			$data['query'] = '';
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

		if (isset($this->request->post['store_id'])) {
			$data['store_id'] = $this->request->post['store_id'];
		} elseif (!empty($seo_url_info)) {
			$data['store_id'] = $seo_url_info['store_id'];
		} else {
			$data['store_id'] = '';
		}

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->post['language_id'])) {
			$data['language_id'] = $this->request->post['language_id'];
		} elseif (!empty($seo_url_info)) {
			$data['language_id'] = $seo_url_info['language_id'];
		} else {
			$data['language_id'] = '';
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('design/seo_url_form', $data));
	}

	protected function validateForm() {
		$this->load->language('design/seo_url');
		$this->load->model('design/seo_url');

		$store_id = (int) ($this->request->post['store_id'] ?? 0);
		$language_id = (int) ($this->request->post['language_id'] ?? 0);
		$seo_url_id = (int) ($this->request->get['seo_url_id'] ?? 0);
		$query = trim($this->request->post['query'] ?? '');
		$keyword = trim($this->request->post['keyword'] ?? '');

		if (!$this->user->hasPermission('modify', 'design/seo_url')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		// Проверка query
		$len = utf8_strlen($query);
		if ($len < 3 || $len > 255) {
			$this->error['query'] = $this->language->get('error_query');
		} elseif ($this->hasDuplicate('query', $query, $store_id, $language_id, $seo_url_id)) {
			$this->error['query'] = $this->language->get('error_query_exists');
		}

		// Проверка keyword
		$keyword_error = $this->validateKeywordValue(
			$keyword,
			$query,
			$store_id,
			$language_id,
			$seo_url_id
		);

		if ($keyword_error !== '') {
			$this->error['keyword'] = $keyword_error;
		}

		return !$this->error;
	}

	private function validateKeywordValue($keyword, $query, $store_id, $language_id, $seo_url_id) {
		if ($keyword === '' && $query !== 'common/home') {
			return $this->language->get('error_keyword');
		}

		if ($keyword === '') {
			return '';
		}

		if (preg_match('/[^a-zA-Z0-9_-]/', $keyword)) {
			return $this->language->get('error_keyword');
		}

		if (
			$this->model_design_seo_url->isReservedLanguagePrefix($keyword, $store_id)
			&& !(
				$query === 'common/home'
				&& $this->model_design_seo_url->isOwnLanguagePrefix(
					$keyword,
					$store_id,
					$language_id
				)
			)
		) {
			return $this->language->get('error_keyword_exists');
		}

		if ($this->hasDuplicate('keyword', $keyword, $store_id, $language_id, $seo_url_id)) {
			return $this->language->get('error_keyword_exists');
		}

		return '';
	}

	/**
	 * Keep keyword uniqueness per store even in prefix mode. Secondary-language
	 * values remain valid fallbacks when prefixes are disabled later.
	 */
	protected function hasDuplicate($type, $value, $store_id, $language_id, $exclude_id) {
		if ($type === 'keyword') {
			$rows = $this->model_design_seo_url->getSeoUrlsByKeyword($value, $store_id, null);
		} else {
			$rows = $this->model_design_seo_url->getSeoUrlsByQuery($value);
		}

		foreach ($rows as $row) {
			if ((int)$row['store_id'] !== $store_id) {
				continue;
			}

			if ($type !== 'keyword' && (int)$row['language_id'] !== $language_id) {
				continue;
			}

			if ((int)$row['seo_url_id'] === $exclude_id) {
				continue;
			}

			return true;
		}

		return false;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'design/seo_url')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}
