<?php
class ControllerInstallStep4 extends Controller {
	private $error = array();

	public function index() {
		$this->load->model('install/install');
		$data = $this->load->language('install/step_4');

		$countries = $this->model_install_install->getCountries();

		if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validate($countries)) {
			$selected_countries = array_values(array_unique(array_map('intval', $this->request->post['country'])));
			$default_country = !empty($this->request->post['default_country']) ? (int)$this->request->post['default_country'] : 0;

			try {
				if ($this->request->post['delete_demodata'] === 'yes') {
					$this->deleteDemoImages();
					$this->model_install_install->deleteDemoData();
				}

				$this->model_install_install->enableCountries($selected_countries, $default_country);

				$this->response->redirect($this->url->link('install/step_5'));
				return;
			} catch (\Throwable $e) {
				$this->error['warning'] = $e->getMessage();
			}
		}

		$this->document->setTitle($this->language->get('heading_title'));

		$data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';
		$data['error_delete_demodata'] = isset($this->error['delete_demodata']) ? $this->error['delete_demodata'] : '';
		$data['error_country'] = isset($this->error['country']) ? $this->error['country'] : '';
		$data['error_default_country'] = isset($this->error['default_country']) ? $this->error['default_country'] : '';

		$data['countries'] = $countries;
		$data['delete_demodata'] = isset($this->request->post['delete_demodata']) ? $this->request->post['delete_demodata'] : 'no';

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			$data['country'] = isset($this->request->post['country']) && is_array($this->request->post['country']) ? array_map('intval', $this->request->post['country']) : array();
			$data['default_country'] = isset($this->request->post['default_country']) ? (int)$this->request->post['default_country'] : 0;
		} else {
			$data['country'] = array();
			$data['default_country'] = 0;

			foreach ($countries as $country) {
				if (!empty($country['status'])) {
					$data['country'][] = (int)$country['country_id'];
				}
			}
		}

		$data['action'] = $this->url->link('install/step_4');
		$data['back'] = $this->url->link('install/step_3');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');

		$this->response->setOutput($this->load->view('install/step_4', $data));
	}

	private function validate($countries) {
		$delete_demodata = isset($this->request->post['delete_demodata']) ? $this->request->post['delete_demodata'] : '';

		if (!in_array($delete_demodata, array('yes', 'no'), true)) {
			$this->error['delete_demodata'] = $this->language->get('error_delete_demodata');
		}

		$valid_country_ids = array();
		foreach ($countries as $country) {
			$valid_country_ids[] = (int)$country['country_id'];
		}

		$selected = isset($this->request->post['country']) && is_array($this->request->post['country']) ? array_values(array_unique(array_map('intval', $this->request->post['country']))) : array();
		$selected = array_values(array_intersect($selected, $valid_country_ids));

		if (!$selected) {
			$this->error['country'] = $this->language->get('error_country');
		} elseif (count($selected) !== count(array_values(array_unique(array_map('intval', $this->request->post['country']))))) {
			$this->error['country'] = $this->language->get('error_country');
		}

		$default_country = isset($this->request->post['default_country']) && $this->request->post['default_country'] !== '' ? (int)$this->request->post['default_country'] : 0;

		if ($default_country > 0 && !in_array($default_country, $selected, true)) {
			$this->error['default_country'] = $this->language->get('error_default_country');
		}

		return !$this->error;
	}

	private function deleteDemoImages() {
		$demo_dir = DIR_IMAGE . 'catalog/demo';

		if (!file_exists($demo_dir)) {
			return;
		}

		if (!is_dir($demo_dir) || is_link($demo_dir)) {
			throw new \Exception($this->language->get('error_demo_images'));
		}

		try {
			$iterator = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator($demo_dir, FilesystemIterator::SKIP_DOTS),
				RecursiveIteratorIterator::CHILD_FIRST
			);

			foreach ($iterator as $item) {
				$path = $item->getPathname();

				if ($item->isLink() || $item->isFile()) {
					if (!@unlink($path) && file_exists($path)) {
						throw new \Exception($this->language->get('error_demo_images') . ' ' . $path);
					}
				} elseif ($item->isDir()) {
					if (!@rmdir($path) && is_dir($path)) {
						throw new \Exception($this->language->get('error_demo_images') . ' ' . $path);
					}
				}
			}
		} catch (\UnexpectedValueException $e) {
			throw new \Exception($this->language->get('error_demo_images'));
		}

		if (!@rmdir($demo_dir) && is_dir($demo_dir)) {
			throw new \Exception($this->language->get('error_demo_images') . ' ' . $demo_dir);
		}
	}
}
