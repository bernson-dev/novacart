<?php
class ControllerInstallStep5 extends Controller {
	public function index() {
		// Загружаем языковой файл
		$data = $this->load->language('install/step_5');

		// Устанавливаем заголовок страницы
		$this->document->setTitle($this->language->get('heading_title'));

		// Загружаем header и footer
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		
        $data['install_summary'] = $this->getInstallSummary();
        $storagePath = isset($this->session->data['installed_storage_path']) ? $this->session->data['installed_storage_path'] : DIR_OPENCART . 'system/storage/';
        $data['install_summary'][] = array('label' => $this->language->get('summary_storage_path'), 'value' => $storagePath);
        $data['install_summary'][] = array('label' => $this->language->get('summary_installer_language'), 'value' => isset($this->session->data['language']) ? $this->session->data['language'] : '—');
        $data['install_summary'][] = array('label' => $this->language->get('summary_administrator'), 'value' => isset($this->session->data['install_admin_user']) ? $this->session->data['install_admin_user'] : '—');
        $result = isset($this->session->data['install_result']) ? $this->session->data['install_result'] : array();
        if (isset($result['country_count'])) {
            $data['install_summary'][] = array('label' => $this->language->get('summary_countries'), 'value' => (string)$result['country_count']);
        }
        if (isset($result['demo_removed'])) {
            $data['install_summary'][] = array('label' => $this->language->get('summary_demo'), 'value' => $result['demo_removed'] ? $this->language->get('summary_demo_removed') : $this->language->get('summary_demo_kept'));
        }
        $data['column_left'] = $this->load->controller('common/column_left');
        // Keep installation session metadata until the finish page has been prepared.
        unset($this->session->data['install'], $this->session->data['install_db']);
		// Рендерим шаблон
		$this->response->setOutput($this->load->view('install/step_5', $data));
	}


    private function getInstalledVersion() {
        if (defined('VERSION')) {
            return VERSION;
        }

        $entry = DIR_OPENCART . 'index.php';
        if (is_file($entry)) {
            $contents = @file_get_contents($entry);
            if ($contents !== false && preg_match("/define\\s*\\(\\s*['\\\"]VERSION['\\\"]\\s*,\\s*['\\\"]([^'\\\"]+)['\\\"]/", $contents, $matches)) {
                return $matches[1];
            }
        }

        return '—';
    }

    private function getInstallSummary() {
        $rows = array(
            array('label' => 'NovaCart', 'value' => $this->getInstalledVersion()),
            array('label' => 'PHP', 'value' => PHP_VERSION)
        );

        $db = null;
        try {
            $db = $this->registry->get('db');
            if ($db) {
                $query = $db->query('SELECT VERSION() AS version, @@character_set_database AS charset');
                $version = isset($query->row['version']) ? $query->row['version'] : '';
                $charset = isset($query->row['charset']) ? $query->row['charset'] : '';
                $rows[] = array('label' => 'База данных / Database', 'value' => trim($version . ' · ' . $charset, ' ·'));
            }
        } catch (\Throwable $e) {
            $rows[] = array('label' => 'База данных / Database', 'value' => 'Configured');
        }

        $storage = defined('DIR_STORAGE') ? realpath(DIR_STORAGE) : false;
        $public = defined('DIR_SYSTEM') ? realpath(DIR_SYSTEM . 'storage') : false;
        $outside = !empty($this->session->data['installed_storage_path']);
        $rows[] = array('label' => 'Storage', 'value' => $outside ? 'External' : 'Default');
        return $rows;
    }

	// Метод для обработки AJAX-запроса на удаление папки install
	public function deleteInstallFolder() {
		$json = [];

		if ($this->request->server['REQUEST_METHOD'] === 'POST') {
			$this->load->language('install/step_5');

			$install_path = DIR_OPENCART . 'install';

			if (is_dir($install_path)) {
				$result = $this->deleteDirectory($install_path);

				if ($result === true) {
					$json['success'] = $this->language->get('text_success_install_deleted');
				} elseif ($result === 'text_error_dir_delete') {
					// Папка пустая, но rmdir() не сработал — создаём флаг для отложенного удаления
					$flag = DIR_STORAGE . 'install_cleanup.flag';
					if (@file_put_contents($flag, '1', LOCK_EX) !== false) {
						$json['success'] = $this->language->get('text_success_install_deleted');
					} else {
						$json['error'] = $this->language->get('text_error_dir_delete');
					}
				} else {
					$json['error'] = $this->language->get($result);
				}
			} else {
				$json['error'] = $this->language->get('text_error_install_not_found');
			}
		} else {
			$json['error'] = $this->language->get('text_error_invalid_request');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	* Вспомогательный метод для рекурсивного удаления директории и ее содержимого.
	*
	* @param string $dir Путь к удаляемой папке
	* @return bool|string Возвращает true при успехе или ключ языковой переменной с ошибкой
	*/
	private function deleteDirectory($dir) {
		if (!is_dir($dir)) {
			return true; // Папки уже нет, считаем это успехом
		}

		// Убеждаемся, что у нас есть права на запись в корень удаляемой папки
		if (!is_writable($dir)) {
			@chmod($dir, 0777);
		}

		try {
			$files = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS | RecursiveDirectoryIterator::UNIX_PATHS),
			RecursiveIteratorIterator::CHILD_FIRST
			);

			foreach ($files as $fileinfo) {
				$path = $fileinfo->getRealPath();
				if (!$path) {
					continue;
				}

				if ($fileinfo->isDir()) {
					// Попытка удаления, при неудаче — принудительное изменение прав и повторная попытка
					if (!@rmdir($path)) {
						@chmod($path, 0777);
						@rmdir($path);
					}
				} else {
					if (!@unlink($path)) {
						@chmod($path, 0777);
						@unlink($path);
					}
				}
			}
		} catch (Throwable $e) {
			return 'text_error_dir_delete';
		}

		clearstatcache(true, $dir);

		// Финальная попытка удалить саму папку
		if (is_dir($dir)) {
			if (!@rmdir($dir)) {
				@chmod($dir, 0777);
				@rmdir($dir);
			}
		}

		// Финальная проверка
		return is_dir($dir) ? 'text_error_dir_delete' : true;
	}

}