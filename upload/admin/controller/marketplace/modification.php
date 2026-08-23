<?php
/**
* Modification XML Documentation can be found here:
*
* https://github.com/opencart/opencart/wiki/Modification-System
*/
class ControllerMarketplaceModification extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('marketplace/modification');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('setting/modification');
		$this->getList();
	}

	public function edit() {
		$this->load->language('marketplace/modification');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('setting/modification');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$modification = $this->model_setting_modification->getModification($this->request->get['modification_id']);
			if ($modification) {
				$this->model_setting_modification->addModificationBackup($this->request->get['modification_id'], $modification);
			}

			$xml = html_entity_decode($this->request->post['xml'], ENT_QUOTES, 'UTF-8');
			$meta = $this->parseMetaFromXml($xml);
			$cur = $this->model_setting_modification->getModification($this->request->get['modification_id']);

			if (!$cur) {
				$this->error['warning'] = $this->language->get('error_warning');
				$this->getForm();
				return;
			}

			$data = array(
			'name'    => !empty($this->request->post['name']) ? $this->request->post['name'] : ($meta['name'] ?: $cur['name']),
			'code'    => $meta['code'] ?: $cur['code'],
			'author'  => $meta['author'] ?: $cur['author'],
			'version' => $meta['version'] ?: $cur['version'],
			'link'    => $meta['link'] ?: $cur['link'],
			'xml'     => $xml,
			'status'  => isset($this->request->post['status']) ? (int)$this->request->post['status'] : (int)$cur['status'],
			);

			$this->model_setting_modification->editModification($this->request->get['modification_id'], $data);

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

			if (!isset($this->request->get['update'])) {
				$this->response->redirect($this->url->link('marketplace/modification', 'user_token=' . $this->session->data['user_token'] . $url, true));
			} else {
				$this->refresh();
				$this->response->redirect($this->url->link('marketplace/modification/edit', 'user_token=' . $this->session->data['user_token'] . '&modification_id=' . $this->request->get['modification_id'] . $url, true));
			}
		}

		$this->getForm();
	}

	public function restore() {
		$this->load->language('marketplace/modification');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('setting/modification');

		if (isset($this->request->get['modification_id']) && isset($this->request->get['backup_id'])) {
			$backup = $this->model_setting_modification->getModificationBackup($this->request->get['modification_id'], $this->request->get['backup_id']);
			if (!$backup) {
				$this->session->data['error'] = $this->language->get('error_warning');
				$this->response->redirect($this->url->link('marketplace/modification/edit', 'user_token=' . $this->session->data['user_token'] . '&modification_id=' . $this->request->get['modification_id'], true));
			}

			$xml = $backup['xml'];
			$meta = $this->parseMetaFromXml($xml);
			$cur = $this->model_setting_modification->getModification($this->request->get['modification_id']);

			if (!$cur) {
				$this->session->data['error'] = $this->language->get('error_warning');
				$this->response->redirect($this->url->link('marketplace/modification/edit', 'user_token=' . $this->session->data['user_token'] . '&modification_id=' . $this->request->get['modification_id'], true));
			}

			$data = array(
			'name'    => $meta['name'] ?: $cur['name'],
			'code'    => $meta['code'] ?: $cur['code'],
			'author'  => $meta['author'] ?: $cur['author'],
			'version' => $meta['version'] ?: $cur['version'],
			'link'    => $meta['link'] ?: $cur['link'],
			'xml'     => $xml,
			'status'  => (int)$cur['status']
			);

			$this->model_setting_modification->editModification($this->request->get['modification_id'], $data);
			$this->refresh();
			$this->response->redirect($this->url->link('marketplace/modification/edit', 'user_token=' . $this->session->data['user_token'] . '&modification_id=' . $this->request->get['modification_id'], true));
		}

		$this->getForm();
	}

	public function clearHistory() {
		// Check user has permission
		if (!$this->user->hasPermission('modify', 'marketplace/modification')) {
			$this->error['warning'] = $this->language->get('error_permission');
			$this->response->redirect($this->url->link('marketplace/modification', 'user_token=' . $this->session->data['user_token'], true));
		}

		$this->load->model('setting/modification');
		$this->model_setting_modification->deleteModificationBackups($this->request->get['modification_id']);
		$this->response->redirect($this->url->link('marketplace/modification/edit', 'user_token=' . $this->session->data['user_token'] . '&modification_id=' . $this->request->get['modification_id'], true));
	}

	public function download() {
		$this->load->model('setting/modification');
		$modification = $this->model_setting_modification->getModification($this->request->get['modification_id']);
		$xml = $modification ? $modification['xml'] : '';

		$this->response->addHeader('Content-Type: application/xml');
		$this->response->setOutput($xml);
	}

	public function upload() {
		$this->load->language('marketplace/installer');
		$this->load->language('marketplace/modification');
		$json = array();

		// Check user has permission
		if (!$this->user->hasPermission('modify', 'marketplace/modification')) {
			$json['error'] = $this->language->get('error_permission');
		}

		$this->load->model('setting/modification');
		$modification = $this->model_setting_modification->getModification($this->request->get['modification_id']);
		if (!$modification) {
			$json['error'] = $this->language->get('error_warning');
		}

		if (empty($json)) {
			if (empty($this->request->files['file']['name'])) {
				$json['error'] = $this->language->get('error_upload');
			} elseif ($this->request->files['file']['error'] != UPLOAD_ERR_OK) {
				$json['error'] = $this->language->get('error_upload_' . $this->request->files['file']['error']);
			} else {
				$uploaded_name = $this->request->files['file']['name'];
				if (!preg_match('/^[a-zA-Z0-9._-]+\.ocmod\.xml$/i', $uploaded_name)) {
					$json['error'] = $this->language->get('error_filetype');
				}
			}
		}

		if (empty($json)) {
			$path = 'temp-' . token(32);
			if (!is_dir(DIR_UPLOAD . $path) && !mkdir(DIR_UPLOAD . $path, 0777, true)) {
				$json['error'] = $this->language->get('error_directory');
			}

			$file = DIR_UPLOAD . $path . '/install.xml';
			move_uploaded_file($this->request->files['file']['tmp_name'], $file);

			if (!file_exists($file)) {
				$json['error'] = $this->language->get('error_file');
			} else {
				$json['step'][] = array(
				'text' => $this->language->get('text_xml'),
				'url'  => str_replace('&amp;', '&', $this->url->link('marketplace/modification/xml', 'user_token=' . $this->session->data['user_token'] . '&modification_id=' . $modification['modification_id'], true)),
				'path' => $path
				);

				// Clear temporary files
				$json['step'][] = array(
				'text' => $this->language->get('text_remove'),
				'url'  => str_replace('&amp;', '&', $this->url->link('marketplace/modification/remove', 'user_token=' . $this->session->data['user_token'] . '&modification_id=' . $modification['modification_id'], true)),
				'path' => $path
				);
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function xml() {
		$this->load->language('marketplace/installer');
		$this->load->model('setting/modification');

		$json = array();

		if (!$this->user->hasPermission('modify', 'marketplace/modification')) {
			$json['error'] = $this->language->get('error_permission');
		}

		$file = DIR_UPLOAD . $this->request->post['path'] . '/install.xml';

		$real = realpath($file);
		if (!$real || substr(str_replace('\\', '/', $real), 0, strlen(DIR_UPLOAD)) !== DIR_UPLOAD) {
			$json['error'] = $this->language->get('error_file');
		}

		if (empty($json)) {
			$xml = file_get_contents($file);
			if (!$xml) {
				$json['error'] = $this->language->get('error_file');
			} else {
				try {
					$dom = new DOMDocument('1.0', 'UTF-8');
					$dom->loadXML($xml);

					$getName = function ($tag) use ($dom) {
						$el = $dom->getElementsByTagName($tag)->item(0);
						return $el ? trim($el->nodeValue) : '';
					};

					$code = $dom->getElementsByTagName('code')->item(0);
					if (!$code) {
						$json['error'] = $this->language->get('error_code');
					} else {
						$modification = $this->model_setting_modification->getModification($this->request->get['modification_id']);
						if (!$modification) {
							$json['error'] = $this->language->get('error_warning');
						} else {
							$modification_data = array(
							'name'    => $getName('name'),
							'code'    => $getName('code'),
							'author'  => $getName('author'),
							'version' => $getName('version'),
							'link'    => $getName('link'),
							'xml'     => $xml,
							'status'  => 1
							);
							$this->model_setting_modification->editModification($modification['modification_id'], $modification_data);
						}
					}
				} catch (Exception $e) {
					$json['error'] = sprintf($this->language->get('error_exception'), $e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine());
				}
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function remove() {
		$this->load->language('marketplace/modification');
		$json = array();

		if (!$this->user->hasPermission('modify', 'marketplace/modification')) {
			$json['error'] = $this->language->get('error_permission');
		}

		$directory = DIR_UPLOAD . $this->request->post['path'];
		$real = realpath($directory);
		if (!$real || substr(str_replace('\\', '/', $real), 0, strlen(DIR_UPLOAD)) !== DIR_UPLOAD) {
			$json['error'] = $this->language->get('error_directory');
		}

		if (empty($json)) {
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
			if (file_exists($directory)) {
				rmdir($directory);
			}
			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	// Начало. Новая логика удаления модификаций
	public function delete() {
		$this->load->language('marketplace/modification');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('setting/modification');

		if (isset($this->request->post['selected']) && $this->validate()) {
			foreach ($this->request->post['selected'] as $item) {
				if (strpos($item, 'file:') === 0) {
					$filename = substr($item, 5);
					if (!preg_match('/^[a-zA-Z0-9._-]+\.ocmod\.xml$/i', $filename)) {
						continue;
					}

					$file_path = DIR_SYSTEM . $filename;

					// Удаляем файл
					if (file_exists($file_path)) {
						unlink($file_path);
					}

					// отключённый: XXX.ocmod.xm_
					$xm_file = substr($file_path, 0, -strlen('.ocmod.xml')) . '.ocmod.xm_';
					if (file_exists($xm_file)) {
						unlink($xm_file);
					}
				} else {
					$modification_id = (int)$item;
					$this->model_setting_modification->deleteModification($modification_id);
					$this->model_setting_modification->deleteModificationBackups($modification_id);
				}
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = $this->buildUrl();
			$this->response->redirect($this->url->link('marketplace/modification', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	private function buildUrl() {
		$url = '';
		if (isset($this->request->get['sort']))
			$url .= '&sort=' . $this->request->get['sort'];
		if (isset($this->request->get['order']))
			$url .= '&order=' . $this->request->get['order'];
		if (isset($this->request->get['page']))
			$url .= '&page=' . $this->request->get['page'];
		return $url;
	}

	private function makeCenteredLine($modName, $type = '') {
		$text = ' ' . strtoupper($type) . ' ' . $modName . ' ';
		$lineLength = 84;

		// Центрируем текст и дополняем символами '-'
		return str_pad($text, $lineLength, '-', STR_PAD_BOTH);
	}

	public function refresh($data = array()) {

		$this->load->language('marketplace/modification');

		$emergency_link = $this->createEmergencyClearLink();

		$message = sprintf($this->language->get('text_emergency_clear_link'), $emergency_link);

		$js = 'console.warn(' . json_encode(
		$message,
		JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
		) . ');';

		$this->document->addScript(
		'data:text/javascript;charset=utf-8,' . rawurlencode($js)
		);

		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('setting/modification');
		$this->load->model('design/theme');

		if (!$this->validate()) {
			$this->deleteEmergencyClearToken();

			$this->getList();
			return;
		}

		// Очистка логов
		file_put_contents(DIR_LOGS . 'ocmod.log', '');
		file_put_contents(DIR_LOGS . 'ocmod-error.log', '');
		file_put_contents(DIR_LOGS . 'ocmod-success.log', '');

		$maintenance = $this->config->get('config_maintenance');
		$this->load->model('setting/setting');
		$this->model_setting_setting->editSettingValue('config', 'config_maintenance', true);

		//Log
		$log = array();
		$log_error = array();
		$log_success = array();

		// Удаление старых модифицированных файлов
		$files = array();
		// Make path into an array
		$path = array(DIR_MODIFICATION . '*');
		while (count($path) != 0) {
			$next = array_shift($path);
			foreach (glob($next) as $file) {
				// If directory add to path array
				if (is_dir($file)) {
					$path[] = $file . '/*';
				}
				// Add the file to the files to be deleted array
				$files[] = $file;
			}
		}
		// Reverse sort the file array
		rsort($files);
		// Clear all modification files
		foreach ($files as $file) {
			if ($file != DIR_MODIFICATION . 'index.html') {
				if (is_file($file)) {
					unlink($file);
				} elseif (is_dir($file)) {
					rmdir($file);
				}
			}
		}

		// Сбор XML
		$xmlList = array();

		$xmlList[] = array(
		'key'    => 'system:modification.xml',
		'name'   => 'OpenCart System Modification',
		'xml'    => file_get_contents(DIR_SYSTEM . 'modification.xml'),
		'listed' => false
		);

		$fsFiles = glob(DIR_SYSTEM . '*.ocmod.xml');
		if ($fsFiles) {
			foreach ($fsFiles as $file) {
				$xmlList[] = array(
				'key'    => 'file:' . basename($file),
				'name'   => '',
				'xml'    => file_get_contents($file),
				'listed' => true
				);
			}
		}

		$dbMods = $this->model_setting_modification->getModifications();
		foreach ($dbMods as $mod) {
			if ($mod['status']) {
				$xmlList[] = array(
				'key'    => 'db:' . (int)$mod['modification_id'],
				'name'   => $mod['name'],
				'xml'    => $mod['xml'],
				'listed' => true
				);
			}
		}

		$modification = array();
		$original = array();
		$modErrorMap = array();

		foreach ($xmlList as $xmlItem) {
			$xmlContent = isset($xmlItem['xml']) ? $xmlItem['xml'] : '';

			if (empty($xmlContent)) {
				continue;
			}

			$modKey = isset($xmlItem['key']) ? $xmlItem['key'] : '';
			$modListed = !empty($xmlItem['listed']);

			if ($modKey && !isset($modErrorMap[$modKey])) {
				$modErrorMap[$modKey] = array(
				'name'  => isset($xmlItem['name']) ? $xmlItem['name'] : '',
				'count' => 0
				);
			}

			$dom = new DOMDocument('1.0', 'UTF-8');
			$dom->preserveWhiteSpace = false;
			libxml_use_internal_errors(true);
			if (!$dom->loadXML($xmlContent)) {
				continue;
			}
			libxml_clear_errors();

			$modNameEl = $dom->getElementsByTagName('name')->item(0);
			$modName = $modNameEl ? $modNameEl->textContent : 'Unknown';
			$log[] = 'MOD: ' . $modName;

			if ($modKey && empty($modErrorMap[$modKey]['name'])) {
				$modErrorMap[$modKey]['name'] = $modName;
			}

			$recovery = $modification;

			if ($this->config->get('config_theme') == 'default') {
				$theme = $this->config->get('theme_default_directory');
			} else {
				$theme = $this->config->get('config_theme');
			}
			$store_id = (int)$this->config->get('config_store_id');

			$fileNodes = $dom->getElementsByTagName('modification')->item(0)->getElementsByTagName('file');
			foreach ($fileNodes as $fileNode) {
				$operations = $fileNode->getElementsByTagName('operation');
				$paths = explode('|', str_replace("\\", '/', $fileNode->getAttribute('path')));
				foreach ($paths as $pathStr) {
					$fullPath = '';
					if (substr($pathStr, 0, 7) == 'catalog') {
						$fullPath = DIR_CATALOG . substr($pathStr, 8);
					} elseif (substr($pathStr, 0, 5) == 'admin') {
						$fullPath = DIR_APPLICATION . substr($pathStr, 6);
					} elseif (substr($pathStr, 0, 6) == 'system') {
						$fullPath = DIR_SYSTEM . substr($pathStr, 7);
					}

					if (!$fullPath)
						continue;

					$matchedFiles = glob($fullPath, GLOB_BRACE);
					if (!$matchedFiles)
						continue;

					foreach ($matchedFiles as $matchedFile) {
						$key = '';
						if (substr($matchedFile, 0, strlen(DIR_CATALOG)) == DIR_CATALOG) {
							$key = 'catalog/' . substr($matchedFile, strlen(DIR_CATALOG));
						} elseif (substr($matchedFile, 0, strlen(DIR_APPLICATION)) == DIR_APPLICATION) {
							$key = 'admin/' . substr($matchedFile, strlen(DIR_APPLICATION));
						} elseif (substr($matchedFile, 0, strlen(DIR_SYSTEM)) == DIR_SYSTEM) {
							$key = 'system/' . substr($matchedFile, strlen(DIR_SYSTEM));
						}

						if (!isset($modification[$key])) {
							$route = substr(mb_strstr($key, 'template'), 9, -5);
							$theme_info = $this->model_design_theme->getTheme($store_id, $theme, $route);
							$content = $theme_info ? html_entity_decode($theme_info['code'], ENT_QUOTES, 'UTF-8') : file_get_contents($matchedFile);
							$modification[$key] = preg_replace('~\r?\n~', "\n", $content);
							$original[$key] = $modification[$key];
							// Log
							$log[] = PHP_EOL . 'FILE: ' . $key;
						} else {
							$log[] = PHP_EOL . 'FILE: (sub modification) ' . $key;
						}

						// Применение операций
						foreach ($operations as $operation) {
							$error = $operation->getAttribute('error');
							// Ignoreif
							$ignoreif = $operation->getElementsByTagName('ignoreif')->item(0);
							if ($ignoreif) {
								if ($ignoreif->getAttribute('regex') != 'true') {
									if (strpos($modification[$key], $ignoreif->textContent) !== false) {
										continue;
									}
								} else {
									if (preg_match($ignoreif->textContent, $modification[$key])) {
										continue;
									}
								}
							}

							$status = false;
							$searchNode = $operation->getElementsByTagName('search')->item(0);
							$addNode = $operation->getElementsByTagName('add')->item(0);
							if (!$searchNode || !$addNode)
								continue;

							if ($searchNode->getAttribute('regex') != 'true') {
								$search = $searchNode->textContent;
								$trimSearch = $searchNode->getAttribute('trim');
								if (!$trimSearch || $trimSearch == 'true') {
									$search = trim($search);
								}
								$index = $searchNode->getAttribute('index');
								$indexes = $index !== '' ? explode(',', $index) : array();

								$add = $addNode->textContent;
								$trimAdd = $addNode->getAttribute('trim');
								if ($trimAdd == 'true') {
									$add = trim($add);
								}
								$position = $addNode->getAttribute('position') ?: 'replace';
								$offset = (int)($addNode->getAttribute('offset') ?: 0);

								$log[] = 'CODE: ' . $search;

								$lines = explode("\n", $modification[$key]);
								$i = 0;
								for ($line_id = 0; $line_id < count($lines); $line_id++) {
									$line = $lines[$line_id];
									$match = false;
									if (stripos($line, $search) !== false) {
										if (empty($indexes) || in_array($i, $indexes)) {
											$match = true;
										}
										$i++;
									}
									if ($match) {
										switch ($position) {
											case 'before':
												$new_lines = explode("\n", $add);
												array_splice($lines, $line_id - $offset, 0, $new_lines);
												$line_id += count($new_lines);
												break;
											case 'after':
												$new_lines = explode("\n", $add);
												array_splice($lines, ($line_id + 1) + $offset, 0, $new_lines);
												$line_id += count($new_lines);
												break;
											case 'replace':
											default:
												$repl = str_replace($search, $add, $line);
												if ($offset < 0) {
													array_splice($lines, $line_id + $offset, abs($offset) + 1, [$repl]);
													$line_id -= $offset;
												} else {
													array_splice($lines, $line_id, $offset + 1, [$repl]);
												}
												break;
										}
										$log[] = 'LINE: ' . $line_id;
										$status = true;
									}
								}
								$modification[$key] = implode("\n", $lines);
							} else {
								$search = trim($searchNode->textContent);
								$replace = trim($addNode->textContent);
								$limit = (int)($searchNode->getAttribute('limit') ?: -1);

								$match = array();
								preg_match_all($search, $modification[$key], $match, PREG_OFFSET_CAPTURE);
								if ($limit > 0) {
									$match[0] = array_slice($match[0], 0, $limit);
								}
								if (!empty($match[0])) {
									$log[] = 'REGEX: ' . $search;
									for ($i = 0; $i < count($match[0]); $i++) {
										$log[] = 'LINE: ' . (substr_count(substr($modification[$key], 0, $match[0][$i][1]), "\n") + 1);
									}
									$status = true;
								}
								$modification[$key] = preg_replace($search, $replace, $modification[$key], $limit);
							}

							if (!$status) {
								if ($modListed && $modKey && isset($modErrorMap[$modKey])) {
									$modErrorMap[$modKey]['count']++;
								}

								// Abort applying this modification completely.
								if ($error == 'abort') {
									$modification = $recovery;
									// Log
									$log[] = 'NOT FOUND - ABORTING!';
									break 5;
								} elseif ($error == 'skip') {
									$log[] = 'NOT FOUND - OPERATION SKIPPED!';
									continue;
								} else {
									$log[] = 'NOT FOUND - OPERATIONS ABORTED!';
									break;
								}
							}
						}
					}
				}
			}
			$log[] = $this->makeCenteredLine($modName, 'END');
		}

		// Группировка логов
		$mods = [];
		$current_mod_name = null;
		$current_file = null;
		$current_code = null;
		$total_success = 0;
		$total_errors = 0;

		foreach ($log as $line) {
			$trimmed = trim($line);
			if (strpos($trimmed, 'MOD: ') === 0) {
				$current_mod_name = trim(substr($trimmed, 5));
				if (!isset($mods[$current_mod_name])) {
					$mods[$current_mod_name] = ['mod_line' => $line, 'success' => [], 'error' => []];
				}
				$current_file = null;
				$current_code = null;
				continue;
			}
			if ($current_mod_name === null)
				continue;

			if (strpos($trimmed, 'FILE: ') === 0) {
				$current_file = $line;
				$current_code = null;
				continue;
			}
			if (strpos($trimmed, 'CODE: ') === 0 || strpos($trimmed, 'REGEX: ') === 0) {
				$current_code = $line;
				continue;
			}
			if (strpos($trimmed, 'LINE: ') === 0) {
				$total_success++;
				$entry = array_filter([$current_file, $current_code, $line]);
				$mods[$current_mod_name]['success'][] = $entry;
				continue;
			}
			if (strpos($trimmed, 'NOT FOUND') !== false) {
				$total_errors++;
				$entry = array_filter([$current_file, $current_code, $line]);
				$mods[$current_mod_name]['error'][] = $entry;
				continue;
			}
		}

		$log_success = [];
		$log_error = [];

		foreach ($mods as $modName => $data) {
			if (!empty($data['success'])) {
				$log_success[] = $this->makeCenteredLine($modName, 'START');
				$log_success[] = $data['mod_line'];
				foreach ($data['success'] as $entry) {
					foreach ($entry as $l)
						$log_success[] = $l;
				}
				$log_success[] = $this->makeCenteredLine($modName, 'END');
				$log_success[] = '';
			}
			if (!empty($data['error'])) {
				$log_error[] = $this->makeCenteredLine($modName, 'START');
				$log_error[] = $data['mod_line'];
				foreach ($data['error'] as $entry) {
					foreach ($entry as $l)
						$log_error[] = $l;
				}
				$log_error[] = $this->makeCenteredLine($modName, 'END');
				$log_error[] = '';
			}
		}

		if (!empty($log_success) && end($log_success) === '')
			array_pop($log_success);
		if (!empty($log_error) && end($log_error) === '')
			array_pop($log_error);

		$timestamp = date('Y-m-d H:i:s');
		file_put_contents(DIR_LOGS . 'ocmod.log', "{$timestamp} - Full Log\n" . implode("\n", $log));
		if (!empty($log_success)) {
			file_put_contents(DIR_LOGS . 'ocmod-success.log', "{$timestamp} - Success Log\n" . implode("\n", $log_success));
		}
		if (!empty($log_error)) {
			file_put_contents(DIR_LOGS . 'ocmod-error.log', "{$timestamp} - Error Log ({$total_errors} error)\n" . implode("\n", $log_error));
		}

		file_put_contents(
		DIR_LOGS . 'ocmod-error-map.json',
		json_encode($modErrorMap, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
		);

		// Сохранение изменённых файлов
		foreach ($modification as $key => $value) {
			if ($original[$key] != $value) {
				$dir = dirname(DIR_MODIFICATION . $key);
				if (!is_dir($dir)) {
					mkdir($dir, 0777, true);
				}
				file_put_contents(DIR_MODIFICATION . $key, $value);
			}
		}
		// Maintance mode back to original settings
		$this->model_setting_setting->editSettingValue('config', 'config_maintenance', $maintenance);
		$this->session->data['success'] = sprintf($this->language->get('text_refresh_success'), $total_success, $total_errors);

		$url = $this->buildUrl();
		if (isset($this->request->get['redirect_installer']) && $this->request->get['redirect_installer']) {
			//$this->response->redirect($this->url->link('marketplace/installer', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		//Admin Extensions Installer Refresh Button
		//$this->response->redirect($this->url->link(!empty($data['redirect']) ? $data['redirect'] : 'marketplace/modification', 'user_token=' . $this->session->data['user_token'] . $url, true));

		$this->deleteEmergencyClearToken();

		$success_message = $this->language->get('text_emergency_token_deleted');

		$js = 'console.clear();console.info(' . json_encode(
		$success_message,
		JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
		) . ');';

		$this->document->addScript(
		'data:text/javascript;charset=utf-8,' . rawurlencode($js)
		);

		$this->getList();
	}

	public function clear() {
		$this->load->language('marketplace/modification');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('setting/modification');

		if ($this->validate()) {
			$files = array();
			$path = array(DIR_MODIFICATION . '*');
			while (count($path) != 0) {
				$next = array_shift($path);
				foreach (glob($next) as $file) {
					if (is_dir($file)) {
						$path[] = $file . '/*';
					}
					$files[] = $file;
				}
			}
			rsort($files);
			foreach ($files as $file) {
				if ($file != DIR_MODIFICATION . 'index.html') {
					if (is_file($file)) {
						unlink($file);
					} elseif (is_dir($file)) {
						rmdir($file);
					}
				}
			}
			$this->session->data['success'] = $this->language->get('text_success');
			$url = $this->buildUrl();
			$this->response->redirect($this->url->link('marketplace/modification', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}
		$this->getList();
	}

	public function enable() {
		$this->load->language('marketplace/modification');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('setting/modification');

		if (isset($this->request->get['filename']) && $this->validate()) {
			$filename = basename($this->request->get['filename']);
			if (!preg_match('/^[a-zA-Z0-9._-]+\.ocmod\.xml$/i', $filename)) {
				$this->error['warning'] = $this->language->get('error_warning');
				$this->getList();
				return;
			}

			$original_file = DIR_SYSTEM . $filename;
			// XXX.ocmod.xml -> XXX.ocmod.xm_
			$disabled_file = substr($original_file, 0, -strlen('.ocmod.xml')) . '.ocmod.xm_';

			if (file_exists($disabled_file)) {
				rename($disabled_file, $original_file);
			}

			$this->session->data['success'] = $this->language->get('text_enable');
			$url = $this->buildUrl();
			$this->response->redirect($this->url->link('marketplace/modification', 'user_token=' . $this->session->data['user_token'] . $url, true));
		} elseif (isset($this->request->get['modification_id']) && $this->validate()) {
			$this->model_setting_modification->enableModification($this->request->get['modification_id']);
			$this->session->data['success'] = $this->language->get('text_enable');
			$url = $this->buildUrl();
			$this->response->redirect($this->url->link('marketplace/modification', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}
		$this->getList();
	}

	public function disable() {
		$this->load->language('marketplace/modification');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('setting/modification');

		if (isset($this->request->get['filename']) && $this->validate()) {
			$filename = basename($this->request->get['filename']);
			if (!preg_match('/^[a-zA-Z0-9._-]+\.ocmod\.xml$/i', $filename)) {
				$this->error['warning'] = $this->language->get('error_warning');
				$this->getList();
				return;
			}

			$original_file = DIR_SYSTEM . $filename;
			// XXX.ocmod.xml -> XXX.ocmod.xm_
			$disabled_file = substr($original_file, 0, -strlen('.ocmod.xml')) . '.ocmod.xm_';

			if (file_exists($original_file)) {
				rename($original_file, $disabled_file);
			}

			$this->session->data['success'] = $this->language->get('text_disable');
			$url = $this->buildUrl();
			$this->response->redirect($this->url->link('marketplace/modification', 'user_token=' . $this->session->data['user_token'] . $url, true));
		} elseif (isset($this->request->get['modification_id']) && $this->validate()) {
			$this->model_setting_modification->disableModification($this->request->get['modification_id']);
			$this->session->data['success'] = $this->language->get('text_disable');
			$url = $this->buildUrl();
			$this->response->redirect($this->url->link('marketplace/modification', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}
		$this->getList();
	}

	public function clearlog() {
		$this->load->language('marketplace/modification');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('setting/modification');

		if ($this->validate()) {
			$type = isset($this->request->get['type']) ? $this->request->get['type'] : 'all';
			switch ($type) {
				case 'success':
					@unlink(DIR_LOGS . 'ocmod-success.log');
					break;
				case 'error':
					@unlink(DIR_LOGS . 'ocmod-error.log');
					break;
				case 'all':
				default:
					@unlink(DIR_LOGS . 'ocmod.log');
					@unlink(DIR_LOGS . 'ocmod-success.log');
					@unlink(DIR_LOGS . 'ocmod-error.log');
					@unlink(DIR_LOGS . 'ocmod-error-map.json');
					break;
			}
			$this->session->data['success'] = $this->language->get('text_success_clear_logs');
		}

		$url = $this->buildUrl();
		$this->response->redirect($this->url->link('marketplace/modification', 'user_token=' . $this->session->data['user_token'] . $url, true));
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
		'href' => $this->url->link('marketplace/modification', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['refresh'] = $this->url->link('marketplace/modification/refresh', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['clear'] = $this->url->link('marketplace/modification/clear', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('marketplace/modification/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$error_map = array();
		$error_map_file = DIR_LOGS . 'ocmod-error-map.json';

		if (is_file($error_map_file)) {
			$error_map_json = file_get_contents($error_map_file);
			$error_map_data = json_decode($error_map_json, true);

			if (is_array($error_map_data)) {
				$error_map = $error_map_data;
			}
		}

		$total_error_count = 0;

		foreach ($error_map as $error_item) {
			if (isset($error_item['count'])) {
				$total_error_count += (int)$error_item['count'];
			}
		}

		$data['total_error_count'] = $total_error_count;

		$data['modifications'] = array();

		// Modified Old code (no pagination)
		$filter_data = array(
		'sort'  => $sort,
		'order' => $order
		);

		// Old code total mod
		//$modification_total = $this->model_setting_modification->getTotalModifications();

		// Подсчитываем количество модификаторов из базы данных
		$modification_total_db = $this->model_setting_modification->getTotalModifications();

		$results = $this->model_setting_modification->getModifications($filter_data);

		$statusText = [
			0 => $this->language->get('text_disabled'),
			1 => $this->language->get('text_enabled')
		];

		foreach ($results as $result) {
			$data['modifications'][] = array(
			'modification_id' => $result['modification_id'],
			'name'            => $result['name'],
			'file_url'        => isset($result['extension_install_id']) ? $this->model_setting_modification->getExtensionInstallByExtensionInstallId($result['extension_install_id']) : '',
			'author'          => $result['author'],
			'filename'        => $result['code'].".ocmod.xml",
			'version'         => $result['version'],
			'status'          => (int)$result['status'],
			'status_text'     => $statusText[(int)$result['status']],
			'date_added'      => date($this->language->get('datetime_format'), strtotime($result['date_added'])),
			'link'            => $result['link'],
			'edit'            => $this->url->link('marketplace/modification/edit', 'user_token=' . $this->session->data['user_token'] . '&modification_id=' . $result['modification_id'], true),
			'download'        => $this->url->link('marketplace/modification/download', 'user_token=' . $this->session->data['user_token'] . '&modification_id=' . $result['modification_id'], true),
			'enable'          => $this->url->link('marketplace/modification/enable', 'user_token=' . $this->session->data['user_token'] . '&modification_id=' . $result['modification_id'], true),
			'disable'         => $this->url->link('marketplace/modification/disable', 'user_token=' . $this->session->data['user_token'] . '&modification_id=' . $result['modification_id'], true),
			'source'          => $this->language->get('text_source_db'), // Источник: база данных
			'is_file'         => false,
			'error_count'     => isset($error_map['db:' . (int)$result['modification_id']]) ? (int)$error_map['db:' . (int)$result['modification_id']]['count'] : 0
			);
		}

		// Сканируем папку system/ на наличие файлов .ocmod.xml и .ocmod.xm_
		$files = array_merge(
		glob(DIR_SYSTEM . '*.ocmod.xml'),
		glob(DIR_SYSTEM . '*.ocmod.xm_')
		);

		$modification_total_fs = 0; // кол-во модмфикаций в файловой системе

		foreach ($files as $file) {
			$xml = simplexml_load_file($file);
			if ($xml) {
				$modification_total_fs++;
				$filename = basename($file); // Имя файла

				// отключённый файл: XXX.ocmod.xm_
				$is_disabled = (substr($filename, -strlen('.ocmod.xm_')) === '.ocmod.xm_');

				if ($is_disabled) {
					// XXX.ocmod.xm_ -> XXX.ocmod.xml (то, что показываем в интерфейсе и передаём в enable/disable)
					$original_filename = substr($filename, 0, -strlen('.ocmod.xm_')) . '.ocmod.xml';
					$enabled = false;
				} else {
					$original_filename = $filename; // уже .ocmod.xml
					$enabled = true;
				}

				// Формируем ссылки для кнопок "Включить" и "Отключить"
				$enable_link = '';
				$disable_link = '';

				if (!$enabled) {
					// Если модификация отключена, формируем ссылку для включения
					$enable_link = $this->url->link('marketplace/modification/enable', 'user_token=' . $this->session->data['user_token'] . '&filename=' . $original_filename, true);
				} else {
					// Если модификация включена, формируем ссылку для отключения
					$disable_link = $this->url->link('marketplace/modification/disable', 'user_token=' . $this->session->data['user_token'] . '&filename=' . $original_filename, true);
				}

				// Добавляем данные о модификации в массив
				$data['modifications'][] = array(
				'modification_id' => null, // У файлов нет ID
				'name'            => (string)$xml->name,
				'author'          => (string)$xml->author,
				'filename'        => $original_filename, // Исходное имя файла без суффикса
				'version'         => (string)$xml->version,
				'status'          => $enabled ? 1 : 0,
				'status_text'     => $enabled ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
				'date_added'      => date($this->language->get('datetime_format'), filemtime($file)), // Дата изменения файла
				'link'            => (string)$xml->link,
				'edit'            => '', // Нет возможности редактировать файлы напрямую
				'download'        => '', // Нет возможности скачивать файлы через интерфейс
				'enable'          => $enable_link, // Ссылка для включения
				'disable'         => $disable_link, // Ссылка для отключения
				'enabled'         => $enabled, // Состояние: включена или отключена
				'source'          => $this->language->get('text_source_file'), // Источник: файловая система
				'is_file'         => true,
				'error_count'     => isset($error_map['file:' . $original_filename]) ? (int)$error_map['file:' . $original_filename]['count'] : 0
				);
			}
		}

		// Общее количество модификаторов
		$modification_total = $modification_total_db + $modification_total_fs;

		// Выполняем сортировку
		usort($data['modifications'], function ($a, $b) use ($filter_data) {
			$field = $filter_data['sort'];
			$order = $filter_data['order'];

			$cmp_a = isset($a[$field]) ? $a[$field] : '';
			$cmp_b = isset($b[$field]) ? $b[$field] : '';
			if ($cmp_a == $cmp_b) {
				return 0;
			}

			if ($order === 'ASC') {
				return ($a[$field] < $b[$field]) ? -1 : 1;
			} else {
				return ($a[$field] > $b[$field]) ? -1 : 1;
			}
		});

		// Paginate merged list
		$start = ($page - 1) * $this->config->get('config_limit_admin');
		$limit = $this->config->get('config_limit_admin');
		$data['modifications'] = array_slice($data['modifications'], $start, $limit);


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

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_name'] = $this->url->link('marketplace/modification', 'user_token=' . $this->session->data['user_token'] . '&sort=name' . $url, true);
		$data['sort_author'] = $this->url->link('marketplace/modification', 'user_token=' . $this->session->data['user_token'] . '&sort=author' . $url, true);
		$data['sort_version'] = $this->url->link('marketplace/modification', 'user_token=' . $this->session->data['user_token'] . '&sort=version' . $url, true);
		$data['sort_status'] = $this->url->link('marketplace/modification', 'user_token=' . $this->session->data['user_token'] . '&sort=status' . $url, true);
		$data['sort_date_added'] = $this->url->link('marketplace/modification', 'user_token=' . $this->session->data['user_token'] . '&sort=date_added' . $url, true);
		// Добавлено  ссылки для сортировки по колонке источник
		$data['sort_source'] = $this->url->link('marketplace/modification', 'user_token=' . $this->session->data['user_token'] . '&sort=source' . $url, true);
		$data['sort_error_count'] = $this->url->link('marketplace/modification', 'user_token=' . $this->session->data['user_token'] . '&sort=error_count' . $url, true);

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $modification_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('marketplace/modification', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf(
		$this->language->get('text_pagination'),
		$start + 1,
		min($start + $limit, $modification_total),
		$modification_total,
		ceil($modification_total / $limit)
		);

		$data['sort'] = $sort;
		$data['order'] = $order;

		// Logs
		$logFiles = [
			'log'          => 'ocmod.log',
			'log_success'  => 'ocmod-success.log',
			'log_error'    => 'ocmod-error.log'
		];
		foreach ($logFiles as $key => $filename) {
			$file = DIR_LOGS . $filename;
			if (file_exists($file)) {
				$data[$key] = htmlentities(file_get_contents($file, FILE_USE_INCLUDE_PATH, null), ENT_QUOTES, 'UTF-8');
			} else {
				$data[$key] = '';
			}
		}


		// Флаг наличия ошибок
		$data['has_error_log'] = ($total_error_count > 0);

		$data['clear_log_all']    = $this->url->link('marketplace/modification/clearlog', 'user_token=' . $this->session->data['user_token'] . '&type=all', true);
		$data['clear_log_success'] = $this->url->link('marketplace/modification/clearlog', 'user_token=' . $this->session->data['user_token'] . '&type=success', true);
		$data['clear_log_error']   = $this->url->link('marketplace/modification/clearlog', 'user_token=' . $this->session->data['user_token'] . '&type=error', true);

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('marketplace/modification', $data));
	}

	protected function getForm() {

		$this->load->language('marketplace/modification');

		$this->document->addStyle('view/javascript/codemirror/lib/codemirror.css');
		$this->document->addStyle('view/javascript/codemirror/theme/xq-dark.css');
		$this->document->addScript('view/javascript/codemirror/lib/codemirror.js');
		$this->document->addScript('view/javascript/codemirror/lib/xml.js');
		$this->document->addScript('view/javascript/codemirror/lib/formatting.js');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
		'text' => $this->language->get('text_home'),
		'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
		'text' => $this->language->get('heading_title'),
		'href' => $this->url->link('marketplace/modification', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_form'] = $this->language->get('text_form');
		$data['text_no_results'] = $this->language->get('text_no_results');
		$data['entry_name'] = $this->language->get('entry_name');
		$data['entry_xml'] = $this->language->get('entry_xml');

		$data['column_id'] = $this->language->get('column_id');
		$data['column_code'] = $this->language->get('column_code');
		$data['column_date_added'] = $this->language->get('column_date_added');
		$data['column_restore'] = $this->language->get('column_restore');
		// Добалено: Новая колонка - Источник
		$data['column_source'] = $this->language->get('column_source');

		$data['button_update'] = $this->language->get('button_update');
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');
		$data['button_restore'] = $this->language->get('button_restore');
		$data['button_history'] = $this->language->get('button_history');

		$data['tab_general'] = $this->language->get('tab_general');
		$data['tab_backup'] = $this->language->get('tab_backup');

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

		$url = '';

		if (!isset($this->request->get['modification_id'])) {
			$data['action'] = $this->url->link('marketplace/modification/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		} else {
			$data['action'] = $this->url->link('marketplace/modification/edit', 'user_token=' . $this->session->data['user_token'] . '&modification_id=' . $this->request->get['modification_id'] . $url, true);
		}

		$data['restore'] = $this->url->link('marketplace/modification/restore', 'user_token=' . $this->session->data['user_token'] . '&modification_id=' . $this->request->get['modification_id'] . $url, true);
		$data['history'] = $this->url->link('marketplace/modification/clearhistory', 'user_token=' . $this->session->data['user_token'] . '&modification_id=' . $this->request->get['modification_id'] . $url, true);
		$data['cancel'] = $this->url->link('marketplace/modification', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$this->load->model('setting/modification');

		$backups = $this->model_setting_modification->getModificationBackups($this->request->get['modification_id']);

		$data['backups'] = array();

		if ($backups) {
			foreach ($backups as $backup) {
				$data['backups'][] = array(
				'backup_id'  => $backup['backup_id'],
				'code'       => $backup['code'],
				'date_added' => $backup['date_added'],
				'restore'    => $this->url->link('marketplace/modification/restore', 'user_token=' . $this->session->data['user_token'] . '&modification_id=' . $this->request->get['modification_id'] . '&backup_id=' . $backup['backup_id'] . $url, true)
				);
			}
		}

		$modification = $this->model_setting_modification->getModification($this->request->get['modification_id']);

		if (isset($this->request->post['name'])) {
			$data['name'] = htmlentities(ltrim($this->request->post['name']), ENT_QUOTES, "UTF-8");
		} elseif (isset($modification)) {
			$data['name'] = htmlentities(ltrim($modification['name']), ENT_QUOTES, "UTF-8");
		}

		if (isset($this->request->post['xml'])) {
			$data['xml'] = htmlentities(ltrim($this->request->post['xml'], "﻿"), ENT_QUOTES, "UTF-8");
		} elseif (isset($modification)) {
			$data['xml'] = htmlentities(ltrim($modification['xml'], "﻿"), ENT_QUOTES, "UTF-8");
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('marketplace/modification_form', $data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'marketplace/modification')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['name']) < 2)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'marketplace/modification')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	private function parseMetaFromXml($xml) {
		$meta = array('name' => '', 'code' => '', 'author' => '', 'version' => '', 'link' => '');
		if (!$xml) {
			return $meta;
		}
		libxml_use_internal_errors(true);
		$dom = new DOMDocument('1.0', 'UTF-8');
		if ($dom->loadXML($xml)) {
			$get = function ($tag) use ($dom) {
				$n = $dom->getElementsByTagName($tag)->item(0);
				return $n ? trim($n->nodeValue) : '';
			};
			$meta['name'] = $get('name');
			$meta['code'] = $get('code');
			$meta['author'] = $get('author');
			$meta['version'] = $get('version');
			$meta['link'] = $get('link');
		}
		libxml_clear_errors();
		return $meta;
	}

	private function getEmergencyClearTokenFile() {
		return DIR_STORAGE . 'emergency_clear_token.json';
	}

	private function getEmergencyClearLinkLogFile() {
		return DIR_LOGS . 'emergency-clear-link.log';
	}

	private function createEmergencyClearLink() {
		$token = bin2hex(random_bytes(32));

		$data = array(
		'hash'    => hash('sha256', $token),
		'created' => time(),
		'expires' => time() + 3600
		);

		file_put_contents(
		$this->getEmergencyClearTokenFile(),
		json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
		LOCK_EX
		);

		$link = rtrim(HTTPS_CATALOG, '/') . '/emergency_clear.php?token=' . rawurlencode($token);

		file_put_contents(
		$this->getEmergencyClearLinkLogFile(),
		date('Y-m-d H:i:s') . ' - ' . $link . PHP_EOL,
		LOCK_EX
		);

		return $link;
	}

	private function deleteEmergencyClearToken() {
		$file = $this->getEmergencyClearTokenFile();

		if (is_file($file)) {
			@unlink($file);
		}

		$log_file = $this->getEmergencyClearLinkLogFile();

		if (is_file($log_file)) {
			@unlink($log_file);
		}
	}
}