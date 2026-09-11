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
				$archive_safe = true;

				for ($index = 0; $index < $zip->numFiles; $index++) {
					$entry = $zip->getNameIndex($index);

					if ($entry === false || !$this->isSafeZipEntry($zip, $index, $entry)) {
						$archive_safe = false;
						break;
					}
				}

				if (!$archive_safe) {
					$zip->close();
					$json['error'] = $this->language->get('error_unzip');
				} else {
					$old_umask = umask(0022);
					$extracted = $zip->extractTo($directory);
					umask($old_umask);

					if ($extracted) {
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

					if (!$this->isSafeInstallPath($destination)) {
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

					if (!$safe) {
						$json['error'] = sprintf($this->language->get('error_allowed'), $destination);
						break;
					}
				}

				if (!$json) {
					$this->load->model('setting/extension');

					foreach ($files as $file) {
						$destination = str_replace('\\', '/', substr($file, strlen($directory . 'upload/')));
						$path = $this->getInstallPath($destination);

						if ($path === '') {
							$json['error'] = sprintf($this->language->get('error_allowed'), $destination);
							break;
						}

						if (is_dir($file)) {
							if (!is_dir($path)) {
								if (!mkdir($path, 0777, true)) {
									$json['error'] = sprintf($this->language->get('error_move'), $destination);
									break;
								}

								$this->model_setting_extension->addExtensionPath($extension_install_id, $destination);
							}
						}

						if (is_file($file)) {
							$backup = $directory . 'backup/' . $destination;
							$had_backup = false;

							if (is_file($path)) {
								$backup_directory = dirname($backup);

								if (!is_dir($backup_directory) && !mkdir($backup_directory, 0777, true)) {
									$json['error'] = sprintf($this->language->get('error_move'), $destination);
									break;
								}

								if (!copy($path, $backup)) {
									$json['error'] = sprintf($this->language->get('error_move'), $destination);
									break;
								}

								$had_backup = true;

								if (!unlink($path)) {
									unlink($backup);
									$json['error'] = sprintf($this->language->get('error_move'), $destination);
									break;
								}
							}

							if (rename($file, $path)) {
								$this->model_setting_extension->addExtensionPath($extension_install_id, $destination);
							} else {
								if ($had_backup && is_file($backup)) {
									copy($backup, $path);
								}

								$json['error'] = sprintf($this->language->get('error_move'), $destination);
								break;
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
								$this->session->data['install_modification_backup'] = $modification_info;
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
				$this->removeDirectory($directory);
			}

			$file = DIR_UPLOAD . $this->session->data['install'] . '.tmp';

			if (is_file($file)) {
				unlink($file);
			}

			unset($this->session->data['install'], $this->session->data['extension_install_id'], $this->session->data['install_modification_backup']);
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
			$results = array_reverse($results);

			foreach ($results as $result) {
				$source = $this->getInstallPath($result['path']);

				if ($source !== '' && is_file($source)) {
					unlink($source);
				}

				if ($source !== '' && is_dir($source)) {
					@rmdir($source);
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

		if (!$extension_install_id || !isset($this->session->data['extension_install_id']) || (int)$this->session->data['extension_install_id'] !== $extension_install_id) {
			return;
		}

		$this->load->model('setting/extension');
		$results = array_reverse($this->model_setting_extension->getExtensionPathsByExtensionInstallId($extension_install_id));
		$directory = '';

		if (!empty($this->session->data['install'])) {
			$directory = DIR_UPLOAD . 'tmp-' . $this->session->data['install'] . '/';
		}

		foreach ($results as $result) {
			$destination = str_replace('\\', '/', $result['path']);
			$path = $this->getInstallPath($destination);
			$backup = $directory ? $directory . 'backup/' . $destination : '';

			if ($path !== '') {
				if ($backup && is_file($backup)) {
					if (is_file($path)) {
						unlink($path);
					}

					$target_directory = dirname($path);

					if (is_dir($target_directory) || mkdir($target_directory, 0777, true)) {
						if (copy($backup, $path)) {
							unlink($backup);
						}
					}
				} elseif (is_file($path)) {
					unlink($path);
				} elseif (is_dir($path)) {
					@rmdir($path);
				}
			}
		}

		$this->model_setting_extension->deleteExtensionPathsByExtensionInstallId($extension_install_id);
		$this->model_setting_extension->deleteExtensionInstall($extension_install_id);

		$this->load->model('setting/modification');
		$this->model_setting_modification->deleteModificationsByExtensionInstallId($extension_install_id);

		if (!empty($this->session->data['install_modification_backup']) && is_array($this->session->data['install_modification_backup'])) {
			$backup_modification = $this->session->data['install_modification_backup'];
			$this->model_setting_modification->addModification(array(
				'extension_install_id' => isset($backup_modification['extension_install_id']) ? (int)$backup_modification['extension_install_id'] : 0,
				'name'                 => isset($backup_modification['name']) ? $backup_modification['name'] : '',
				'code'                 => isset($backup_modification['code']) ? $backup_modification['code'] : '',
				'author'               => isset($backup_modification['author']) ? $backup_modification['author'] : '',
				'version'              => isset($backup_modification['version']) ? $backup_modification['version'] : '',
				'link'                 => isset($backup_modification['link']) ? $backup_modification['link'] : '',
				'xml'                  => isset($backup_modification['xml']) ? $backup_modification['xml'] : '',
				'status'               => isset($backup_modification['status']) ? (int)$backup_modification['status'] : 1
			));
		}

		if ($directory && is_dir($directory)) {
			$this->removeDirectory($directory);
		}

		if (!empty($this->session->data['install'])) {
			$file = DIR_UPLOAD . $this->session->data['install'] . '.tmp';

			if (is_file($file)) {
				unlink($file);
			}
		}

		unset($this->session->data['install'], $this->session->data['extension_install_id'], $this->session->data['install_modification_backup']);
	}

	private function isSafeZipEntry($zip, $index, $entry) {
		$entry = str_replace('\\', '/', (string)$entry);
		$path = rtrim($entry, '/');

		if (!$this->isSafeInstallPath($path)) {
			return false;
		}

		$opsys = 0;
		$attributes = 0;

		if ($zip->getExternalAttributesIndex((int)$index, $opsys, $attributes) && $opsys === ZipArchive::OPSYS_UNIX) {
			$type = ($attributes >> 16) & 0170000;

			if ($type === 0120000) {
				return false;
			}
		}

		return true;
	}

	private function isSafeInstallPath($destination) {
		$destination = str_replace('\\', '/', (string)$destination);

		if ($destination === '' || $destination[0] === '/' || strpos($destination, "\0") !== false || strpos($destination, ':') !== false) {
			return false;
		}

		return !preg_match('#(^|/)\.\.?(/|$)#', $destination);
	}

	private function getInstallPath($destination) {
		$destination = str_replace('\\', '/', (string)$destination);

		if (!$this->isSafeInstallPath($destination)) {
			return '';
		}

		if ($destination === 'admin' || strpos($destination, 'admin/') === 0) {
			return DIR_APPLICATION . ($destination === 'admin' ? '' : substr($destination, 6));
		}

		if ($destination === 'catalog' || strpos($destination, 'catalog/') === 0) {
			return DIR_CATALOG . ($destination === 'catalog' ? '' : substr($destination, 8));
		}

		if ($destination === 'image' || strpos($destination, 'image/') === 0) {
			return DIR_IMAGE . ($destination === 'image' ? '' : substr($destination, 6));
		}

		if ($destination === 'system' || strpos($destination, 'system/') === 0) {
			return DIR_SYSTEM . ($destination === 'system' ? '' : substr($destination, 7));
		}

		return DIR_CATALOG . '../' . $destination;
	}

	private function removeDirectory($directory) {
		if (!is_dir($directory)) {
			return;
		}

		$files = array_diff(scandir($directory), array('.', '..'));

		foreach ($files as $file) {
			$path = $directory . '/' . $file;

			if (is_dir($path)) {
				$this->removeDirectory($path);
			} elseif (is_file($path)) {
				unlink($path);
			}
		}

		@rmdir($directory);
	}
}
