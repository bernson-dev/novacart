<?php
class ControllerInstallStep1 extends Controller {
	public function index() {
		$this->load->language('install/step_1');

		$deletionLog = []; // Лог для информации об удалении
		// Очистка папок
		$deletionLog = $this->clearFolders([
			'image/cache',
			'system/storage/cache',
			'system/storage/download',
			'system/storage/logs',
			'system/storage/modification',
			'system/storage/session',
			'system/storage/upload',
		]);

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			$this->response->redirect($this->url->link('install/step_2'));
		}

		$this->document->setTitle($this->language->get('heading_title'));

		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_step_1'] = $this->language->get('text_step_1');
		$data['text_terms'] = $this->language->get('text_terms');
		$data['button_continue'] = $this->language->get('button_continue');
		$data['action'] = $this->url->link('install/step_1');
		$data['text_collapse'] = $this->language->get('text_collapse');

		// Передаем лог об удалении в шаблон
		$data['deletion_log'] = $deletionLog;

		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');

		$this->response->setOutput($this->load->view('install/step_1', $data));
	}

	private function clearFolders($folders) {
		$log = []; // Лог для записи операций

		foreach ($folders as $folder) {
			// Используем абсолютные пути на основе констант
			$absolutePath = constant('DIR_OPENCART') . $folder;
			$log[$folder] = $this->deleteFolderContents($absolutePath);
		}

		return $log;
	}

	private function deleteFolderContents($folder) {
		if (!is_dir($folder)) {
			// Если папка не существует
			return [$this->language->get('text_notfound_folder')  . $this->getRelativePath($folder)];
		}

		$log = [];
		$files = array_diff(scandir($folder), ['.', '..', 'index.html', '.htaccess']);

		foreach ($files as $file) {
			$path = $folder . DIRECTORY_SEPARATOR . $file;
			if (is_dir($path)) {
				$log = array_merge($log, $this->deleteFolderContents($path));
				if (rmdir($path)) {
					$log[] = $this->language->get('text_del_folder') . $this->getRelativePath($path);
				} else {
					$log[] = $this->language->get('text_notdel_folder') . $this->getRelativePath($path);
				}
			} else {
				if (unlink($path)) {
					$log[] = $this->language->get('text_del_file')  . $this->getRelativePath($path);
				} else {
					$log[] = $this->language->get('text_notdel_file') . $this->getRelativePath($path);
				}
			}
		}

		return $log;
	}

	private function getRelativePath($absolutePath) {
		// Преобразуем абсолютный путь в относительный
		return str_replace(DIR_OPENCART, '', $absolutePath);
	}
}
