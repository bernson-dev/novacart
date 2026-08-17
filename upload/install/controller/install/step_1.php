<?php
class ControllerInstallStep1 extends Controller {
	public function index() {
		$this->load->language('install/step_1');

		$deletion_errors = array();

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			$deletion_errors = $this->clearFolders(array(
				'image/cache',
				'system/storage/cache',
				'system/storage/download',
				'system/storage/logs',
				'system/storage/modification',
				'system/storage/session',
				'system/storage/upload'
			));

			if (!$deletion_errors) {
				$this->response->redirect($this->url->link('install/step_2'));
				return;
			}
		}

		$this->document->setTitle($this->language->get('heading_title'));

		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_step_1'] = $this->language->get('text_step_1');
		$data['text_terms'] = $this->language->get('text_terms');
		$data['button_continue'] = $this->language->get('button_continue');
		$data['action'] = $this->url->link('install/step_1');
		$data['deletion_errors'] = $deletion_errors;

		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');

		$this->response->setOutput($this->load->view('install/step_1', $data));
	}

	private function clearFolders($folders) {
		$errors = array();

		foreach ($folders as $folder) {
			$absolute_path = DIR_OPENCART . $folder;

			if (!is_dir($absolute_path)) {
				continue;
			}

			$errors = array_merge($errors, $this->deleteFolderContents($absolute_path, true));
		}

		return $errors;
	}

	private function deleteFolderContents($folder, $preserve_root_files = false) {
		$errors = array();
		$items = @scandir($folder);

		if ($items === false) {
			$errors[] = $this->language->get('text_notdel_folder') . $this->getRelativePath($folder);
			return $errors;
		}

		foreach ($items as $item) {
			if ($item === '.' || $item === '..') {
				continue;
			}

			if ($preserve_root_files && ($item === 'index.html' || $item === '.htaccess')) {
				continue;
			}

			$path = $folder . DIRECTORY_SEPARATOR . $item;

			if (is_dir($path) && !is_link($path)) {
				$child_errors = $this->deleteFolderContents($path, false);
				$errors = array_merge($errors, $child_errors);

				if (!$child_errors && !@rmdir($path)) {
					$errors[] = $this->language->get('text_notdel_folder') . $this->getRelativePath($path);
				}
			} elseif (!@unlink($path)) {
				$errors[] = $this->language->get('text_notdel_file') . $this->getRelativePath($path);
			}
		}

		return $errors;
	}

	private function getRelativePath($absolute_path) {
		return str_replace(DIR_OPENCART, '', $absolute_path);
	}
}
