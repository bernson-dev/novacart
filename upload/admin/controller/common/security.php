<?php
class ControllerCommonSecurity extends Controller {
	public function index() {
		$this->load->language('common/security');

		$data['text_instruction'] = $this->language->get('text_instruction');
		$data['user_token'] = $this->session->data['user_token'];
		$data['storage'] = DIR_SYSTEM . 'storage/';

		$path = '';
		$data['paths'] = array();

		$parts = explode('/', str_replace('\\\\', '/', rtrim(DIR_SYSTEM, '/')));

		foreach ($parts as $part) {
			$path .= $part . '/';
			$data['paths'][] = $path;
		}

		rsort($data['paths']);

		$site_root = realpath(DIR_APPLICATION . '../');
		$site_root = $site_root ? rtrim(str_replace('\\\\', '/', $site_root), '/') : rtrim(str_replace('\\\\', '/', DIR_APPLICATION . '../'), '/');
		$storage_root = str_replace('\\\\', '/', dirname($site_root)) . '/storage/';
		$site_name = preg_replace('/[^A-Za-z0-9._-]/', '-', basename($site_root));
		$site_name = trim($site_name, '.-');

		$data['document_root'] = $storage_root;
		$data['suggested_directory'] = $site_name . '-' . substr(hash('sha256', $site_root), 0, 8);

		return $this->load->view('common/security', $data);
	}

	public function move() {
		$this->load->language('common/security');

		$json = array();

		$path = isset($this->request->post['path']) ? trim((string)$this->request->post['path']) : '';
		$directory = isset($this->request->post['directory']) ? trim((string)$this->request->post['directory']) : '';

		if (!$this->user->hasPermission('modify', 'common/security')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json && DIR_STORAGE != DIR_SYSTEM . 'storage/') {
			$json['error'] = $this->language->get('error_path');
		}

		if (!$json && (!$directory || !preg_match('/^[A-Za-z0-9._-]+$/', $directory))) {
			$json['error'] = $this->language->get('error_directory');
		}

		$site_root = realpath(DIR_APPLICATION . '../');
		$site_root = $site_root ? rtrim(str_replace('\\\\', '/', $site_root), '/') : '';

		if (!$json) {
			$path = rtrim(str_replace('\\\\', '/', $path), '/') . '/';

			// CodeCart-style default: one shared storage container, one deterministic
			// subdirectory per site. Create only the shared container automatically.
			$default_root = $site_root ? str_replace('\\\\', '/', dirname($site_root)) . '/storage/' : '';

			if ($path === $default_root && !is_dir($path)) {
				$parent = realpath(dirname(rtrim($path, '/')));

				if (!$parent || !is_writable($parent) || (!@mkdir($path, 0750, true) && !is_dir($path))) {
					$json['error'] = $this->language->get('error_path');
				}
			}
		}

		if (!$json) {
			$real_path = realpath($path);

			if (!$real_path || !is_dir($real_path) || !is_writable($real_path)) {
				$json['error'] = $this->language->get('error_path');
			} else {
				$path = rtrim(str_replace('\\\\', '/', $real_path), '/') . '/';
				$target = $path . $directory;
				$target_normalized = rtrim(str_replace('\\\\', '/', $target), '/') . '/';

				if (!$site_root || strpos($target_normalized, $site_root . '/') === 0) {
					$json['error'] = $this->language->get('error_path');
				} elseif (is_link($target) || (file_exists($target) && !is_dir($target))) {
					$json['error'] = $this->language->get('error_directory');
				} elseif (is_dir($target) && !is_writable($target)) {
					$json['error'] = $this->language->get('error_writable');
				}
			}
		}

		$config_files = array(
			realpath(DIR_APPLICATION . '../') . '/config.php',
			DIR_APPLICATION . 'config.php'
		);

		if (!$json && (!is_writable($config_files[0]) || !is_writable($config_files[1]))) {
			$json['error'] = $this->language->get('error_writable');
		}

		if (!$json) {
			$source = rtrim(DIR_SYSTEM . 'storage/', '/\\\\') . DIRECTORY_SEPARATOR;
			$target = rtrim($path . $directory, '/\\\\') . DIRECTORY_SEPARATOR;

			if (!is_dir($target) && !@mkdir($target, 0750, true) && !is_dir($target)) {
				$json['error'] = $this->language->get('error_directory');
			} else {
				$iterator = new RecursiveIteratorIterator(
					new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS),
					RecursiveIteratorIterator::SELF_FIRST
				);

				foreach ($iterator as $item) {
					if ($item->isLink()) {
						continue;
					}

					$relative = substr($item->getPathname(), strlen($source));
					$destination = $target . $relative;

					if ($item->isDir()) {
						if (!is_dir($destination) && !@mkdir($destination, 0750, true) && !is_dir($destination)) {
							$json['error'] = $this->language->get('error_directory');
							break;
						}
					} else {
						$destination_dir = dirname($destination);

						if ((!is_dir($destination_dir) && !@mkdir($destination_dir, 0750, true) && !is_dir($destination_dir)) || !@copy($item->getPathname(), $destination)) {
							$json['error'] = $this->language->get('error_writable');
							break;
						}
					}
				}
			}

			if (!$json) {
				$storage_path = str_replace('\\', '/', rtrim($target, '/\\')) . '/';

				foreach ($config_files as $config_file) {
					$output = '';
					$lines = file($config_file);

					foreach ($lines as $line) {
						if (strpos($line, "define('DIR_STORAGE'") !== false) {
							$output .= "define('DIR_STORAGE', '" . $storage_path . "');" . "\n";
						} else {
							$output .= $line;
						}
					}

					$file = fopen($config_file, 'w');
					fwrite($file, $output);
					fclose($file);
				}

				$json['success'] = $this->language->get('text_success');
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
