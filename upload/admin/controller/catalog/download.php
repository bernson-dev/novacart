<?php
class ControllerCatalogDownload extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('catalog/download');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/download');

		$this->getList();
	}

	public function add() {
		$this->load->language('catalog/download');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/download');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$download_id = $this->model_catalog_download->addDownload($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_filename'])) {
				$url .= '&filter_filename=' . urlencode(html_entity_decode($this->request->get['filter_filename'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_mask'])) {
				$url .= '&filter_mask=' . urlencode(html_entity_decode($this->request->get['filter_mask'], ENT_QUOTES, 'UTF-8'));
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

			$this->response->redirect($this->url->link('catalog/download', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('catalog/download');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('catalog/download');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_catalog_download->editDownload($this->request->get['download_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_filename'])) {
				$url .= '&filter_filename=' . urlencode(html_entity_decode($this->request->get['filter_filename'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_mask'])) {
				$url .= '&filter_mask=' . urlencode(html_entity_decode($this->request->get['filter_mask'], ENT_QUOTES, 'UTF-8'));
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

			$this->response->redirect($this->url->link('catalog/download', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('catalog/download');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/download');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $download_id) {
				$this->model_catalog_download->deleteDownload($download_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_filename'])) {
				$url .= '&filter_filename=' . urlencode(html_entity_decode($this->request->get['filter_filename'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_mask'])) {
				$url .= '&filter_mask=' . urlencode(html_entity_decode($this->request->get['filter_mask'], ENT_QUOTES, 'UTF-8'));
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

			$this->response->redirect($this->url->link('catalog/download', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	protected function getList() {
		if (isset($this->request->get['filter_name'])) {
			$filter_name = $this->request->get['filter_name'];
		} else {
			$filter_name = '';
		}

		if (isset($this->request->get['filter_filename'])) {
			$filter_filename = $this->request->get['filter_filename'];
		} else {
			$filter_filename = '';
		}

		if (isset($this->request->get['filter_mask'])) {
			$filter_mask = $this->request->get['filter_mask'];
		} else {
			$filter_mask = '';
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'dd.name';
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

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_filename'])) {
			$url .= '&filter_filename=' . urlencode(html_entity_decode($this->request->get['filter_filename'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_mask'])) {
			$url .= '&filter_mask=' . urlencode(html_entity_decode($this->request->get['filter_mask'], ENT_QUOTES, 'UTF-8'));
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

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('catalog/download', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['add'] = $this->url->link('catalog/download/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('catalog/download/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['downloads'] = array();

		$filter_data = array(
			'filter_name' => $filter_name,
			'filter_filename' => $filter_filename,
			'filter_mask' => $filter_mask,
			'sort'        => $sort,
			'order'       => $order,
			'start'       => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'       => $this->config->get('config_limit_admin')
		);

		$download_total = $this->model_catalog_download->getTotalDownloads($filter_data);

		$results = $this->model_catalog_download->getDownloads($filter_data);

		foreach ($results as $result) {
			$data['downloads'][] = array(
				'download_id' => $result['download_id'],
				'name'        => $result['name'],
				'date_added'  => date($this->language->get('datetime_format'), strtotime($result['date_added'])),
				'edit'        => $this->url->link('catalog/download/edit', 'user_token=' . $this->session->data['user_token'] . '&download_id=' . $result['download_id'] . $url, true)
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

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_filename'])) {
			$url .= '&filter_filename=' . urlencode(html_entity_decode($this->request->get['filter_filename'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_mask'])) {
			$url .= '&filter_mask=' . urlencode(html_entity_decode($this->request->get['filter_mask'], ENT_QUOTES, 'UTF-8'));
		}

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_name'] = $this->url->link('catalog/download', 'user_token=' . $this->session->data['user_token'] . '&sort=dd.name' . $url, true);
		$data['sort_date_added'] = $this->url->link('catalog/download', 'user_token=' . $this->session->data['user_token'] . '&sort=d.date_added' . $url, true);

		$url = '';

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_filename'])) {
			$url .= '&filter_filename=' . urlencode(html_entity_decode($this->request->get['filter_filename'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_mask'])) {
			$url .= '&filter_mask=' . urlencode(html_entity_decode($this->request->get['filter_mask'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $download_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('catalog/download', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($download_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($download_total - $this->config->get('config_limit_admin'))) ? $download_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $download_total, ceil($download_total / $this->config->get('config_limit_admin')));

		$data['filter_name'] = $filter_name;
		$data['filter_filename'] = $filter_filename;
		$data['filter_mask'] = $filter_mask;

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/download_list', $data));
	}

	protected function getForm() {
		$data['text_form'] = !isset($this->request->get['download_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

		// Use the ini_get('upload_max_filesize') for the max file size
		$upload_max_filesize = (int)preg_filter('/[^0-9]/', '', ini_get('upload_max_filesize'));

		$data['error_upload_size'] = sprintf($this->language->get('error_upload_size'), $upload_max_filesize);
		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = array();
		}

		if (isset($this->error['filename'])) {
			$data['error_filename'] = $this->error['filename'];
		} else {
			$data['error_filename'] = '';
		}

		if (isset($this->error['mask'])) {
			$data['error_mask'] = $this->error['mask'];
		} else {
			$data['error_mask'] = '';
		}

		$url = '';

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_filename'])) {
			$url .= '&filter_filename=' . urlencode(html_entity_decode($this->request->get['filter_filename'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_mask'])) {
			$url .= '&filter_mask=' . urlencode(html_entity_decode($this->request->get['filter_mask'], ENT_QUOTES, 'UTF-8'));
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

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('catalog/download', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		if (!isset($this->request->get['download_id'])) {
			$data['action'] = $this->url->link('catalog/download/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		} else {
			$data['action'] = $this->url->link('catalog/download/edit', 'user_token=' . $this->session->data['user_token'] . '&download_id=' . $this->request->get['download_id'] . $url, true);
		}

		$data['cancel'] = $this->url->link('catalog/download', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		$download_info = array();

		if (isset($this->request->get['download_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$download_info = $this->model_catalog_download->getDownload($this->request->get['download_id']);
		}

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->get['download_id'])) {
			$data['download_id'] = (int)$this->request->get['download_id'];
		} else {
			$data['download_id'] = 0;
		}

		if (isset($this->request->post['download_description'])) {
			$data['download_description'] = $this->request->post['download_description'];
		} elseif (isset($this->request->get['download_id'])) {
			$data['download_description'] = $this->model_catalog_download->getDownloadDescriptions($this->request->get['download_id']);
		} else {
			$data['download_description'] = array();
		}

		if (isset($this->request->post['filename'])) {
			$data['filename'] = $this->request->post['filename'];
		} elseif (!empty($download_info)) {
			$data['filename'] = $download_info['filename'];
		} else {
			$data['filename'] = '';
		}

		if (isset($this->request->post['mask'])) {
			$data['mask'] = $this->request->post['mask'];
		} elseif (!empty($download_info)) {
			$data['mask'] = $download_info['mask'];
		} else {
			$data['mask'] = '';
		}

		$data['report'] = $this->getReport();

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/download_form', $data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'catalog/download')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!isset($this->request->post['download_description']) || !is_array($this->request->post['download_description'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		} else {
			foreach ($this->request->post['download_description'] as $language_id => $value) {
				$name = $value['name'] ?? '';

				if ((utf8_strlen($name) < 3) || (utf8_strlen($name) > 512)) {
					$this->error['name'][$language_id] = $this->language->get('error_name');
				}
			}
		}

		$filename = $this->request->post['filename'] ?? '';
		$mask = $this->request->post['mask'] ?? '';

		if ((utf8_strlen($filename) < 3) || (utf8_strlen($filename) > 512)) {
			$this->error['filename'] = $this->language->get('error_filename');
		} elseif (!is_file(DIR_DOWNLOAD . basename($filename))) {
			$this->error['filename'] = $this->language->get('error_exists');
		}

		if ((utf8_strlen($mask) < 3) || (utf8_strlen($mask) > 512)) {
			$this->error['mask'] = $this->language->get('error_mask');
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'catalog/download')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		$this->load->model('catalog/product');

		foreach ($this->request->post['selected'] as $download_id) {
			$product_total = $this->model_catalog_product->getTotalProductsByDownloadId((int)$download_id);

			if ($product_total) {
				$this->error['warning'] = sprintf($this->language->get('error_product'), $product_total);
			}
		}

		return !$this->error;
	}

	public function report() {
		$this->load->language('catalog/download');
		$this->response->setOutput($this->getReport());
	}

	private function getReport() {
		$this->load->language('catalog/download');

		if (isset($this->request->get['download_id'])) {
			$download_id = (int)$this->request->get['download_id'];
		} else {
			$download_id = 0;
		}
		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';
		$limit = 10;

		$data['reports'] = array();

		$this->load->model('catalog/download');
		$this->load->model('customer/customer');
		$this->load->model('setting/store');

		$results = $this->model_catalog_download->getDownloadReports($download_id, ($page - 1) * $limit, $limit);

		foreach ($results as $result) {
			$store_info = $this->model_setting_store->getStore($result['store_id']);

			if ($store_info) {
				$store = $store_info['name'];
			} elseif (!(int)$result['store_id']) {
				$store = $this->config->get('config_name');
			} else {
				$store = '';
			}

			$data['reports'][] = array(
				'ip'         => $result['ip'],
				'account'    => $this->model_customer_customer->getTotalCustomersByIp($result['ip']),
				'store'      => $store,
				'country'    => $result['country'],
				'date_added' => date($this->language->get('datetime_format'), strtotime($result['date_added'])),
				'filter_ip'  => $this->url->link('customer/customer', 'user_token=' . $this->session->data['user_token'] . '&filter_ip=' . $result['ip'], true)
			);
		}

		$report_total = $this->model_catalog_download->getTotalDownloadReports($download_id);

		$pagination = new Pagination();
		$pagination->total = $report_total;
		$pagination->page = $page;
		$pagination->limit = $limit;
		$pagination->url = $this->url->link('catalog/download_report', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($report_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($report_total - $limit)) ? $report_total : ((($page - 1) * $limit) + $limit), $report_total, ceil($report_total / $limit));

		return $this->load->view('catalog/download_report', $data);
	}

	public function upload() {
		$this->load->language('catalog/download');

		$json = array();

		// Check user has permission
		if (!$this->user->hasPermission('modify', 'catalog/download')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
			if (!empty($this->request->files['file']['name']) && is_file($this->request->files['file']['tmp_name'])) {
				// Sanitize the filename
				$filename = basename(html_entity_decode($this->request->files['file']['name'], ENT_QUOTES, 'UTF-8'));

				// Validate the filename length
				if ((utf8_strlen($filename) < 3) || (utf8_strlen($filename) > 128)) {
					$json['error'] = $this->language->get('error_filename');
				}

				// Allowed file extension types
				$allowed = array();
				$extension_allowed = preg_replace('~\r?\n~', "\n", (string)$this->config->get('config_file_ext_allowed'));
				$filetypes = explode("\n", $extension_allowed);

				foreach ($filetypes as $filetype) {
					$filetype = strtolower(trim($filetype));

					if ($filetype !== '') {
						$allowed[] = $filetype;
					}
				}

				$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

				if (!in_array($extension, $allowed)) {
					$json['error'] = $this->language->get('error_filetype');
				}

				// Allowed file mime types
				$allowed = array();
				$mime_allowed = preg_replace('~\r?\n~', "\n", (string)$this->config->get('config_file_mime_allowed'));
				$filetypes = explode("\n", $mime_allowed);

				foreach ($filetypes as $filetype) {
					$filetype = trim($filetype);

					if ($filetype !== '') {
						$allowed[] = $filetype;
					}
				}

				$mime = '';

				if (function_exists('finfo_open')) {
					$finfo = finfo_open(FILEINFO_MIME_TYPE);

					if ($finfo) {
						$mime = finfo_file($finfo, $this->request->files['file']['tmp_name']);
						finfo_close($finfo);
					}
				}

				if ($mime) {
					if (!in_array($mime, $allowed)) {
						$json['error'] = $this->language->get('error_filetype');
					}
				} elseif (!in_array($this->request->files['file']['type'], $allowed)) {
					$json['error'] = $this->language->get('error_filetype');
				}

				// Check to see if any PHP files are trying to be uploaded
				$content = file_get_contents($this->request->files['file']['tmp_name'], false, null, 0, 1024);

				if ($content !== false && preg_match('/<\?(php|=)?/i', $content)) {
					$json['error'] = $this->language->get('error_filetype');
				}

				// Return any upload error
				if ($this->request->files['file']['error'] != UPLOAD_ERR_OK) {
					$error_key = 'error_upload_' . $this->request->files['file']['error'];
					$json['error'] = $this->language->get($error_key);
				}
			} else {
				$json['error'] = $this->language->get('error_upload');
			}
		}

		if (!$json) {
			$file = $filename . '.' . token(32);

			if (!move_uploaded_file($this->request->files['file']['tmp_name'], DIR_DOWNLOAD . $file)) {
				$json['error'] = $this->language->get('error_upload');
			} else {
				$json['filename'] = $file;
				$json['mask'] = $filename;
				$json['success'] = $this->language->get('text_upload');
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function download() {
		$this->load->language('catalog/download');
		$this->load->model('catalog/download');

		$filename = isset($this->request->get['filename']) ? basename($this->request->get['filename']) : '';
		$mask = isset($this->request->get['mask']) ? basename(html_entity_decode($this->request->get['mask'], ENT_QUOTES, 'UTF-8')) : '';

		$file = DIR_DOWNLOAD . $filename;

		if ($filename && is_file($file)) {
			if (!$mask) {
				$download_info = $this->model_catalog_download->getDownloadByFilename($filename);

				if ($download_info && !empty($download_info['mask'])) {
					$mask = basename($download_info['mask']);
				}
			}

			if (!$mask) {
				$mask = $this->extractOriginalFilename($filename);
			}

			if (!headers_sent()) {
				while (ob_get_level()) {
					ob_end_clean();
				}

				header('Content-Type: application/octet-stream');
				header('Content-Description: File Transfer');
				header('Content-Disposition: attachment; filename="' . str_replace('"', '', $mask) . '"');
				header('Content-Transfer-Encoding: binary');
				header('Expires: 0');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Pragma: public');
				header('Content-Length: ' . filesize($file));

				readfile($file);
				exit;
			} else {
				exit($this->language->get('error_headers_sent'));
			}
		} else {
			$this->load->language('error/not_found');

			$this->document->setTitle($this->language->get('heading_title'));

			$data['breadcrumbs'] = array();

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
			);

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('error/not_found', 'user_token=' . $this->session->data['user_token'], true)
			);

			$data['header'] = $this->load->controller('common/header');
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['footer'] = $this->load->controller('common/footer');

			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}

	public function autocomplete() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {
			$this->load->model('catalog/download');

			$filter_data = array(
				'filter_name' => $this->request->get['filter_name'],
				'start'       => 0,
				'limit'       => $this->config->get('config_limit_autocomplete')
			);

			$results = $this->model_catalog_download->getDownloads($filter_data);

			foreach ($results as $result) {
				$json[] = array(
					'download_id' => $result['download_id'],
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

	private function extractOriginalFilename($filename) {
		return preg_replace('/\.[a-f0-9]{32}$/i', '', $filename);
	}
}
