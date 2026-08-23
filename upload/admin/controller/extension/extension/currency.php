<?php
class ControllerExtensionExtensionCurrency extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/extension/currency');

		$this->load->model('setting/extension');

		$this->getList();
	}

	public function install() {
		$this->load->language('extension/extension/currency');

		$this->load->model('setting/extension');

		if ($this->validate()) {
			$this->model_setting_extension->install('currency', $this->request->get['extension']);

			$this->load->model('user/user_group');

			$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/currency/' . $this->request->get['extension']);
			$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/currency/' . $this->request->get['extension']);

			// Call install method if it exists
			$this->load->controller('extension/currency/' . $this->request->get['extension'] . '/install');

			$this->session->data['success'] = $this->language->get('text_success');
		}

		$this->getList();
	}

	public function uninstall() {
		$this->load->language('extension/extension/currency');

		$this->load->model('setting/extension');

		if ($this->validate()) {
			$this->model_setting_extension->uninstall('currency', $this->request->get['extension']);

			// Call uninstall method if it exists
			$this->load->controller('extension/currency/' . $this->request->get['extension'] . '/uninstall');

			$this->load->model('user/user_group');

			$this->model_user_user_group->removePermissions('extension/currency/' . $this->request->get['extension']);

			$this->session->data['success'] = $this->language->get('text_success');
		}

		$this->getList();
	}

	protected function getList() {
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

		$extensions = $this->model_setting_extension->getInstalled('currency');

		foreach ($extensions as $key => $value) {
			if (!is_file(DIR_APPLICATION . 'controller/extension/currency/' . $value . '.php') && !is_file(DIR_APPLICATION . 'controller/currency/' . $value . '.php')) {
				$this->model_setting_extension->uninstall('currency', $value);

				unset($extensions[$key]);
			}
		}

		$data['extensions'] = array();

		// Compatibility code for old extension folders
		$files = glob(DIR_APPLICATION . 'controller/extension/currency/*.php');

		if ($files) {
			foreach ($files as $file) {
				$extension = basename($file, '.php');

				$this->load->language('extension/currency/' . $extension, 'extension');
				$enabled = (bool)$this->config->get('currency_' . $extension . '_status');

				$data['extensions'][] = array(
					'name'      => $this->language->get('extension')->get('heading_title'),
					'status'    => $enabled ? '<span class="label label-success">' . $this->language->get('text_enabled') . '</span>' : '<span class="label label-danger">' . $this->language->get('text_disabled') . '</span>',
					'enabled'   => $enabled, // логическое значение для сортировки
					'install'   => $this->url->link('extension/extension/currency/install', 'user_token=' . $this->session->data['user_token'] . '&extension=' . $extension, true),
					'uninstall' => $this->url->link('extension/extension/currency/uninstall', 'user_token=' . $this->session->data['user_token'] . '&extension=' . $extension, true),
					'installed' => in_array($extension, $extensions),
					'edit'      => $this->url->link('extension/currency/' . $extension, 'user_token=' . $this->session->data['user_token'], true)
				);
			}
		}

		// Сортировка: включённые по алфавиту, затем выключенные по алфавиту
		usort($data['extensions'], function ($a, $b) {
			if ($a['enabled'] != $b['enabled']) {
				return $a['enabled'] ? -1 : 1; // true перед false
			}

			$nameA = ltrim(strip_tags($a['name']));
			$nameB = ltrim(strip_tags($b['name']));
			return strcasecmp($nameA, $nameB); // регистронезависимая сортировка
		});

		$this->response->setOutput($this->load->view('extension/extension/currency', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/extension/currency')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}
