<?php
class ControllerMarketplaceInstaller extends Controller {
	public function index() {
		$this->load->language('marketplace/installer');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
		'text' => $this->language->get('text_home'),
		'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
		'text' => $this->language->get('heading_title'),
		'href' => $this->url->link('marketplace/installer', 'user_token=' . $this->session->data['user_token'], true)
		);

		// Use the ini_get('upload_max_filesize') for the max file size
		$limit_bytes = $this->getPhpUploadLimitBytes();
		$data['config_file_max_size'] = $limit_bytes;
		$data['error_upload_size'] = sprintf($this->language->get('error_upload_size'), (int)($limit_bytes / 1024 / 1024));
		$data['user_token'] = $this->session->data['user_token'];

		//Admin Extensions Installer Refresh Button
		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}
		$data['refresh'] = $this->url->link('marketplace/modification/refresh', 'user_token=' . $this->session->data['user_token'] . '&redirect_installer=1', true);
		//Admin Extensions Installer Refresh Button


		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('marketplace/installer', $data));
	}

	public function history() {
		$this->load->language('marketplace/installer');

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'date_added';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'DESC';
		}

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		$limit = 10;
		$data['histories'] = array();

		$this->load->model('setting/extension');

		//$results = $this->model_setting_extension->getExtensionInstalls(($page - 1) * $limit, $limit);
		$results = $this->model_setting_extension->getExtensionInstalls(
		($page - 1) * $limit,
		$limit,
		$sort,
		$order
		);

		foreach ($results as $result) {
			$data['histories'][] = array(
			'extension_install_id' => $result['extension_install_id'],
			'filename'             => $result['filename'],
			'date_added'           => date($this->language->get('datetime_format'), strtotime($result['date_added']))
			);
		}

		$history_total = $this->model_setting_extension->getTotalExtensionInstalls();

		// Переключение направления
		$order_toggle = ($order == 'ASC') ? 'DESC' : 'ASC';
		$data['sort_filename'] = $this->url->link(
		'marketplace/installer/history',
		'user_token=' . $this->session->data['user_token'] .
		'&sort=filename&order=' . $order_toggle,
		true
		);
		$data['sort_date_added'] = $this->url->link(
		'marketplace/installer/history',
		'user_token=' . $this->session->data['user_token'] .
		'&sort=date_added&order=' . $order_toggle,
		true
		);

		$pagination = new Pagination();
		$pagination->total = $history_total;
		$pagination->page = $page;
		$pagination->limit = $limit;
		//$pagination->url = $this->url->link('marketplace/installer/history', 'user_token=' . $this->session->data['user_token'] . '&page={page}', true);

		$pagination->url = $this->url->link(
		'marketplace/installer/history',
		'user_token=' . $this->session->data['user_token'] .
		'&sort=' . $sort .
		'&order=' . $order .
		'&page={page}',
		true
		);

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($history_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($history_total - $limit)) ? $history_total : ((($page - 1) * $limit) + $limit), $history_total, ceil($history_total / $limit));

		$this->response->setOutput($this->load->view('marketplace/installer_history', $data));
	}

	public function upload() {
		$this->load->language('marketplace/installer');

		$json = array();

		// Check user has permission
		if (!$this->user->hasPermission('modify', 'marketplace/installer')) {
			$json['error'] = $this->language->get('error_permission');
		}

		// Cleanup только если нет ошибок
		if (!$json) {
			$tmp_ttl = 10; // 10 sec
			$dir_ttl = 10; // 10 sec
			// Check if there is an install zip already there
			$files = glob(DIR_UPLOAD . '*.tmp');

			foreach ($files as $file) {
				if (is_file($file) && (filectime($file) < (time() - $tmp_ttl))) {
					unlink($file);
				}

				if (is_file($file)) {
					$json['error'] = $this->language->get('error_install');
					break;
				}
			}

			// Check for any install directories
			$directories = glob(DIR_UPLOAD . 'tmp-*');

			foreach ($directories as $directory) {
				if (is_dir($directory) && (filectime($directory) < (time() - $dir_ttl))) {
					// Get a list of files ready to upload
					$files = array();

					$path = array($directory);

					while (count($path) != 0) {
						$next = array_shift($path);

						// We have to use scandir function because glob will not pick up dot files.
						foreach (array_diff(scandir($next), array('.', '..')) as $file) {
							$file = $next . '/' . $file;

							if (is_dir($file)) {
								$path[] = $file;
							}

							$files[] = $file;
						}
					}

					rsort($files);

					foreach ($files as $file) {
						if (is_file($file)) {
							unlink($file);
						} elseif (is_dir($file)) {
							rmdir($file);
						}
					}

					rmdir($directory);
				}

				if (is_dir($directory)) {
					$json['error'] = $this->language->get('error_install');

					break;
				}
			}
		}

		if (isset($this->request->files['file']['name'])) {
			if (substr($this->request->files['file']['name'], -10) != '.ocmod.zip') {
				$json['error'] = $this->language->get('error_filetype');
			}

			if ($this->request->files['file']['error'] != UPLOAD_ERR_OK) {
				$json['error'] = $this->language->get('error_upload_' . $this->request->files['file']['error']);
			}
		} else {
			$json['error'] = $this->language->get('error_upload');
		}

		$max_size = $this->getPhpUploadLimitBytes();
		if ($max_size > 0 && isset($this->request->files['file']['size']) && (int)$this->request->files['file']['size'] > $max_size) {
			$json['error'] = sprintf($this->language->get('error_upload_size'), (int)($max_size / 1024 / 1024));
		}

		if (!$json) {
			$this->session->data['install'] = token(10);

			$file = DIR_UPLOAD . $this->session->data['install'] . '.tmp';

			move_uploaded_file($this->request->files['file']['tmp_name'], $file);

			if (is_file($file)) {
				$this->load->model('setting/extension');

				//$extension_install_id = $this->model_setting_extension->addExtensionInstall($this->request->files['file']['name']);
				$hash = sha1_file($file, true); // true = бинарный SHA1 (20 байт)
				if (!$hash) {
					$json['error'] = $this->language->get('error_file');
				} else {
					$extension_install_id = $this->model_setting_extension->addExtensionInstall($this->request->files['file']['name'], $hash);
				}

				// Получаем параметр allow_protected
				$allow_protected = isset($this->request->post['allow_protected']) ? (int)$this->request->post['allow_protected'] : 0;

				$json['text'] = $this->language->get('text_install');

				// Передаем allow_protected в install
				$json['next'] = str_replace('&amp;', '&', $this->url->link('marketplace/install/install', 'user_token=' . $this->session->data['user_token'] . '&extension_install_id=' . $extension_install_id . '&allow_protected=' . $allow_protected, true));
			} else {
				$json['error'] = $this->language->get('error_file');
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	private function parseIniSizeToBytes($val) {
		$val = trim($val);
		if ($val === '' || $val == '-1')
			return 0;

		$unit = strtolower(substr($val, -1));
		$num  = (float)$val;

		switch ($unit) {
			case 'g': return (int)($num * 1024 * 1024 * 1024);
			case 'm': return (int)($num * 1024 * 1024);
			case 'k': return (int)($num * 1024);
			default:  return (int)$num; // bytes
		}
	}

	private function getPhpUploadLimitBytes() {
		$upload = $this->parseIniSizeToBytes(ini_get('upload_max_filesize'));
		$post   = $this->parseIniSizeToBytes(ini_get('post_max_size'));

		if ($upload == 0)
			return $post;
		if ($post == 0)
			return $upload;

		return min($upload, $post);
	}
}
