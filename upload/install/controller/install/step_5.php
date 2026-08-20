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

		unset($this->session->data['install']);
		// Рендерим шаблон
		$this->response->setOutput($this->load->view('install/step_5', $data));
	}

	// Метод для обработки AJAX-запроса на удаление папки install
	public function deleteInstallFolder() {
		$json = [];

		if ($this->request->server['REQUEST_METHOD'] === 'POST') {
			$this->load->language('install/step_5');

			// Определяем путь к папке install
			$install_path = DIR_OPENCART . 'install';

			if (is_dir($install_path)) {
				$result = $this->deleteDirectory($install_path);

				if ($result === true) {
					$json['success'] = $this->language->get('text_success_install_deleted');
				} elseif ($result === 'text_error_dir_delete' && $this->isDirectoryEmpty($install_path)) {
					// На Windows/PHP 8.x текущий запрос может удерживать сам каталог install.
					// Содержимое уже удалено, поэтому откладываем только финальный rmdir().
					if (@file_put_contents(DIR_STORAGE . 'install_cleanup.flag', '1', LOCK_EX) !== false) {
						$json['success'] = $this->language->get('text_success_install_deleted');
					} else {
						$json['error'] = $this->language->get('text_error_dir_delete');
					}
				} else {
					// result содержит ключ ошибки
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

	private function deleteDirectory($dir) {
		if (!is_dir($dir)) {
			return 'text_error_dir_not_found';
		}

		foreach (scandir($dir) as $object) {
			if ($object === '.' || $object === '..')
				continue;

			$path = $dir . '/' . $object;
			if (is_dir($path)) {
				$result = $this->deleteDirectory($path);
				if ($result !== true) {
					return $result;
				}
			} else {
				if (!unlink($path)) {
					return 'text_error_file_delete';
				}
			}
		}

		if (!@rmdir($dir)) {
			return 'text_error_dir_delete';
		}

		return true;
	}

	private function isDirectoryEmpty($dir) {
		if (!is_dir($dir)) {
			return false;
		}

		$files = scandir($dir);

		return $files !== false && count($files) === 2;
	}
}