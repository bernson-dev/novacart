<?php
class ControllerMarketplaceInstall extends Controller {
	public function install() {
		$this->load->language('marketplace/install');

		$json = array();

		if (isset($this->request->get['extension_install_id'])) {
			$extension_install_id = $this->request->get['extension_install_id'];
		} else {
			$extension_install_id = 0;
		}

		if (isset($this->request->get['allow_protected'])) {
			$allow_protected = (int)$this->request->get['allow_protected'];
		} else {
			$allow_protected = 0;
		}

		if (!$this->user->hasPermission('modify', 'marketplace/install')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!isset($this->session->data['install'])) {
			$json['error'] = $this->language->get('error_file');
		} elseif (!is_file(DIR_UPLOAD . $this->session->data['install'] . '.tmp')) {
			$json['error'] = $this->language->get('error_file');
		}

		if (!$json) {
			$json['text'] = $this->language->get('text_unzip');
			$json['next'] = str_replace('&amp;', '&', $this->url->link('marketplace/install/unzip', 'user_token=' . $this->session->data['user_token'] . '&extension_install_id=' . $extension_install_id . '&allow_protected=' . $allow_protected, true));
		}

		if (!empty($json['error'])) {
			$this->cleanupFailedInstall($extension_install_id);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function unzip() {
		$this->load->language('marketplace/install');

		$json = array();

		if (isset($this->request->get['extension_install_id'])) {
			$extension_install_id = $this->request->get['extension_install_id'];
		} else {
			$extension_install_id = 0;
		}

		if (isset($this->request->get['allow_protected'])) {
			$allow_protected = (int)$this->request->get['allow_protected'];
		} else {
			$allow_protected = 0;
		}

		if (!$this->user->hasPermission('modify', 'marketplace/install')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!isset($this->session->data['install'])) {
			$json['error'] = $this->language->get('error_file');
		} elseif (!is_file(DIR_UPLOAD . $this->session->data['install'] . '.tmp')) {
			$json['error'] = $this->language->get('error_file');
		}

		if (!$json) {
			$file = DIR_UPLOAD . $this->session->data['install'] . '.tmp';
			$directory = DIR_UPLOAD . 'tmp-' . $this->session->data['install'];
			$zip = new ZipArchive();
			$open_result = $zip->open($file);

			if ($open_result === true) {
				if ($zip->extractTo($directory)) {
					$zip->close();

					if (is_file($file)) {
						unlink($file);
					}

					$json['text'] = $this->language->get('text_move');
					$json['next'] = str_replace('&amp;', '&', $this->url->link('marketplace/install/move', 'user_token=' . $this->session->data['user_token'] . '&extension_install_id=' . $extension_install_id . '&allow_protected=' . $allow_protected, true));
				} else {
					$zip->close();
					$json['error'] = $this->language->get('error_unzip');
				}
			} else {
				$json['error'] = $this->language->get('error_unzip');
			}
		}

		if (!empty($json['error'])) {
			$this->cleanupFailedInstall($extension_install_id);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function move() {
		$this->load->language('marketplace/install');

		$json = array();

		if (isset($this->request->get['extension_install_id'])) {
			$extension_install_id = $this->request->get['extension_install_id'];
		} else {
			$extension_install_id = 0;
		}

		if (isset($this->request->get['allow_protected'])) {
			$allow_protected = (int)$this->request->get['allow_protected'];
		} else {
			$allow_protected = 0;
		}

		if (!$this->user->hasPermission('modify', 'marketplace/install')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!isset($this->session->data['install'])) {
			$json['error'] = $this->language->get('error_directory');
		} elseif (!is_dir(DIR_UPLOAD . 'tmp-' . $this->session->data['install'] . '/')) {
			$json['error'] = $this->language->get('error_directory');
		}

		if (!$json) {
			$directory = DIR_UPLOAD . 'tmp-' . $this->session->data['install'] . '/';

			if (is_dir($directory . 'upload/')) {
				$files = array();
				$path = array($directory . 'upload/*');

				while (count($path) != 0) {
					$next = array_shift($path);

					foreach ((array)glob($next) as $file) {
						if (is_dir($file)) {
							$path[] = $file . '/*';
						}

						$files[] = $file;
					}
				}

				$allowed = array(
					'admin/controller/extension/',
					'admin/language/',
					'admin/model/extension/',
					'admin/view/image/',
					'admin/view/javascript/',
					'admin/view/stylesheet/',
					'admin/view/template/extension/',
					'catalog/controller/extension/',
					'catalog/language/',
					'catalog/model/extension/',
					'catalog/view/javascript/',
					'catalog/view/theme/',
					'system/config/',
					'system/library/',
					'image/catalog/'
				);

				foreach ($files as $file) {
					$destination = str_replace('\\', '/', substr($file, strlen($directory . 'upload/')));

					if ($destination === '' || $destination[0] === '/' || strpos($destination, "\0") !== false || strpos($destination, ':') !== false || preg_match('#(^|/)\.\.?(/|$)#', $destination)) {
						$json['error'] = sprintf($this->language->get('error_allowed'), $destination);
						break;
					}

					if ($allow_protected) {
						$safe = true;
					} else {
						$safe = false;
						$destination_path = rtrim($destination, '/');

						foreach ($allowed as $value) {
							$allowed_path = rtrim($value, '/');

							if ($destination_path === $allowed_path || strpos($allowed_path . '/', $destination_path . '/') === 0 || strpos($destination_path . '/', $allowed_path . '/') === 0) {
								$safe = true;
								break;
							}
						}
					}

					if ($safe) {
						if (substr($destination, 0, 5) == 'admin') {
							$destination = DIR_APPLICATION . substr($destination, 6);
						}

						if (substr($destination, 0, 7) == 'catalog') {
							$destination = DIR_CATALOG . substr($destination, 8);
						}

						if (substr($destination, 0, 5) == 'image') {
							$destination = DIR_IMAGE . substr($destination, 6);
						}

						if (substr($destination, 0, 6) == 'system') {
							$destination = DIR_SYSTEM . substr($destination, 7);
						}
					} else {
						$json['error'] = sprintf($this->language->get('error_allowed'), $destination);
						break;
					}
				}

				if (!$json) {
					$this->load->model('setting/extension');

					foreach ($files as $file) {
						$destination = str_replace('\\', '/', substr($file, strlen($directory . 'upload/')));
						$path = '';

						if (substr($destination, 0, 5) == 'admin') {
							$path = DIR_APPLICATION . substr($destination, 6);
						}

						if (substr($destination, 0, 7) == 'catalog') {
							$path = DIR_CATALOG . substr($destination, 8);
						}

						if (substr($destination, 0, 5) == 'image') {
							$path = DIR_IMAGE . substr($destination, 6);
						}

						if (substr($destination, 0, 6) == 'system') {
							$path = DIR_SYSTEM . substr($destination, 7);
						}

						if (($path == '') && $allow_protected) {
							$path = DIR_CATALOG . '../' . $destination;
						}

						if (is_dir($file) && !is_dir($path)) {
							if (mkdir($path, 0777)) {
								$this->model_setting_extension->addExtensionPath($extension_install_id, $destination);
							}
						}

						if (is_file($file)) {
							if (rename($file, $path)) {
								$this->model_setting_extension->addExtensionPath($extension_install_id, $destination);
							}
						}
					}
				}
			}
		}

		if (!$json) {
			$json['text'] = $this->language->get('text_xml');
			$json['next'] = str_replace('&amp;', '&', $this->url->link('marketplace/install/xml', 'user_token=' . $this->session->data['user_token'] . '&extension_install_id=' . $extension_install_id, true));
		}

		if (!empty($json['error'])) {
			$this->cleanupFailedInstall($extension_install_id);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function xml() {
		$this->load->language('marketplace/install');

		$json = array();

		if (isset($this->request->get['extension_install_id'])) {
			$extension_install_id = $this->request->get['extension_install_id'];
		} else {
			$extension_install_id = 0;
		}

		if (!$this->user->hasPermission('modify', 'marketplace/install')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!isset($this->session->data['install'])) {
			$json['error'] = $this->language->get('error_directory');
		} elseif (!is_dir(DIR_UPLOAD . 'tmp-' . $this->session->data['install'] . '/')) {
			$json['error'] = $this->language->get('error_directory');
		}

		if (!$json) {
			$file = DIR_UPLOAD . 'tmp-' . $this->session->data['install'] . '/install.xml';

			if (is_file($file)) {
				$this->load->model('setting/modification');
				$xml = file_get_contents($file);

				if ($xml) {
					try {
						$dom = new DOMDocument('1.0', 'UTF-8');
						$dom->loadXml($xml);

						$name = $dom->getElementsByTagName('name')->item(0);
						$name = $name ? $name->nodeValue : '';

						$code = $dom->getElementsByTagName('code')->item(0);

						if ($code) {
							$code = $code->nodeValue;
							$modification_info = $this->model_setting_modification->getModificationByCode($code);

							if ($modification_info) {
								$this->model_setting_modification->deleteModification($modification_info['modification_id']);
							}
						} else {
							$json['error'] = $this->language->get('error_code');
						}

						$author = $dom->getElementsByTagName('author')->item(0);
						$author = $author ? $author->nodeValue : '';
						$version = $dom->getElementsByTagName('version')->item(0);
						$version = $version ? $version->nodeValue : '';
						$link = $dom->getElementsByTagName('link')->item(0);
						$link = $link ? $link->nodeValue : '';

						if (!$json) {
							$modification_data = array(
								'extension_install_id' => $extension_install_id,
								'name'                 => $name,
								'code'                 => $code,
								'author'               => $author,
								'version'              => $version,
								'link'                 => $link,
								'xml'                  => $xml,
								'status'               => 1
							);

							$this->model_setting_modification->addModification($modification_data);
						}
					} catch (\Exception $exception) {
						$json['error'] = sprintf($this->language->get('error_exception'), $exception->getCode(), $exception->getMessage(), $exception->getFile(), $exception->getLine());
					}
				}
			}
		}

		if (!$json) {
			$json['text'] = $this->language->get('text_remove');
			$json['next'] = str_replace('&amp;', '&', $this->url->link('marketplace/install/remove', 'user_token=' . $this->session->data['user_token'] . '&extension_install_id=' . $extension_install_id, true));
		}

		if (!empty($json['error'])) {
			$this->cleanupFailedInstall($extension_install_id);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function remove() {
		$this->load->language('marketplace/install');

		$json = array();

		if (!$this->user->hasPermission('modify', 'marketplace/install')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (isset($this->request->get['extension_install_id'])) {
			$extension_install_id = (int)$this->request->get['extension_install_id'];
		} else {
			$extension_install_id = 0;
		}

		if (!isset($this->session->data['install'])) {
			$json['error'] = $this->language->get('error_directory');
		}

		if (!$json) {
			$directory = DIR_UPLOAD . 'tmp-' . $this->session->data['install'] . '/';

			if (is_dir($directory)) {
				$files = array();
				$path = array($directory);

				while (count($path) != 0) {
					$next = array_shift($path);

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

				if (is_dir($directory)) {
					rmdir($directory);
				}
			}

			$file = DIR_UPLOAD . $this->session->data['install'] . '.tmp';

			if (is_file($file)) {
				unlink($file);
			}

			$json['success'] = $this->language->get('text_success');
		}

		if (!empty($json['error'])) {
			$this->cleanupFailedInstall($extension_install_id);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function uninstall() {
		$this->load->language('marketplace/install');

		$json = array();

		if (isset($this->request->get['extension_install_id'])) {
			$extension_install_id = $this->request->get['extension_install_id'];
		} else {
			$extension_install_id = 0;
		}

		if (!$this->user->hasPermission('modify', 'marketplace/install')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
			$this->load->model('setting/extension');
			$results = $this->model_setting_extension->getExtensionPathsByExtensionInstallId($extension_install_id);
			rsort($results);

			foreach ($results as $result) {
				$source = '';

				if (substr($result['path'], 0, 5) == 'admin') {
					$source = DIR_APPLICATION . substr($result['path'], 6);
				}

				if (substr($result['path'], 0, 7) == 'catalog') {
					$source = DIR_CATALOG . substr($result['path'], 8);
				}

				if (substr($result['path'], 0, 5) == 'image') {
					$source = DIR_IMAGE . substr($result['path'], 6);
				}

				if (substr($result['path'], 0, 14) == 'system/library') {
					$source = DIR_SYSTEM . 'library/' . substr($result['path'], 15);
				}

				if (is_file($source)) {
					unlink($source);
				}

				if (is_dir($source)) {
					$files = array();
					$path = array($source);

					while (count($path) != 0) {
						$next = array_shift($path);

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

					if (is_file($source)) {
						unlink($source);
					}

					if (is_dir($source)) {
						rmdir($source);
					}
				}

				$this->model_setting_extension->deleteExtensionPath($result['extension_path_id']);
			}

			$this->model_setting_extension->deleteExtensionInstall($extension_install_id);
			$this->load->model('setting/modification');
			$this->model_setting_modification->deleteModificationsByExtensionInstallId($extension_install_id);
			$json['success'] = $this->language->get('text_success_uninstall');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	private function cleanupFailedInstall($extension_install_id) {
		$extension_install_id = (int)$extension_install_id;

		if (!$extension_install_id) {
			return;
		}

		$this->load->model('setting/extension');
		$this->model_setting_extension->deleteExtensionPathsByExtensionInstallId($extension_install_id);
		$this->model_setting_extension->deleteExtensionInstall($extension_install_id);

		$this->load->model('setting/modification');
		$this->model_setting_modification->deleteModificationsByExtensionInstallId($extension_install_id);
	}
}
