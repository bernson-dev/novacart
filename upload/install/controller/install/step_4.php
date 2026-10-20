<?php
class ControllerInstallStep4 extends Controller {
	private $error = array();

	public function index() {
		$this->load->model('install/install');

		$data = $this->load->language('install/step_4');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            if (!empty($this->request->post['external_storage'])) {
                try {
                    $migrationFile = DIR_OPENCART . 'system/library/storage_migration.php';
                    if (!is_file($migrationFile)) {
                        throw new RuntimeException('Missing storage migration library: ' . $migrationFile);
                    }
                    require_once $migrationFile;
                    $storagePath = StorageMigration::suggestedPath(DIR_OPENCART);
                    $webRoot = isset($this->request->server['DOCUMENT_ROOT']) ? realpath($this->request->server['DOCUMENT_ROOT']) : false;
                    $destinationParent = realpath(dirname(rtrim($storagePath, '/')));
                    if ($webRoot && $destinationParent) {
                        $webRoot = rtrim(str_replace('\\', '/', $webRoot), '/') . '/';
                        $destination = rtrim(str_replace('\\', '/', $destinationParent), '/') . '/';
                        if (strpos($destination, $webRoot) === 0) {
                            throw new RuntimeException('External storage location is inside web root.');
                        }
                    }
                    $this->session->data['installed_storage_path'] = StorageMigration::move(
                        DIR_OPENCART . 'system/storage', $storagePath,
                        array(DIR_OPENCART . 'config.php', DIR_OPENCART . 'admin/config.php')
                    );
                } catch (Throwable $error) {
                    $this->error['storage'] = $error->getMessage();
                }
            }
            if (!$this->error) {
			if ($this->request->post['delete_demodata'] == 'yes') {
				$demo_images = DIR_IMAGE . 'catalog/demo';
				if (file_exists($demo_images)) {
					foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($demo_images), RecursiveIteratorIterator::CHILD_FIRST) as $file) {
						if ($file->getFilename() === '.' || $file->getFilename() === '..') {
							continue;
						}

						$path = $file->getRealPath();

						if (is_writable($path)) {
							if ($file->isDir()) {
								rmdir($path);
							} else {
								unlink($path);
							}
						}
					}
					if (is_writable($demo_images)) {
						@rmdir($demo_images);
					}
				}

				$this->model_install_install->deleteDemoData();
			}

			$countries = isset($this->request->post['country']) ? $this->request->post['country'] : [];
			$default_country = isset($this->request->post['default_country']) ? (int)$this->request->post['default_country'] : 0;

			// передаём оба параметра в модель
			$this->model_install_install->enableCountries($countries, $default_country);
            $this->session->data['install_result'] = array(
                'country_count' => count($countries),
                'demo_removed' => $this->request->post['delete_demodata'] == 'yes'
            );

			$this->response->redirect($this->url->link('install/step_5'));
            }

		}

		$this->document->setTitle($this->language->get('heading_title'));

		if (isset($this->error['delete_demodata'])) {
			$data['error_delete_demodata'] = $this->error['delete_demodata'];
		} else {
			$data['error_delete_demodata'] = '';
		}

		if (isset($this->error['country'])) {
			$data['error_country'] = $this->error['country'];
		} else {
			$data['error_country'] = '';
		}

		$data['countries'] = $this->model_install_install->getCountries();
        $data['external_storage'] = !empty($this->request->post['external_storage']);
        $data['error_storage'] = isset($this->error['storage']) ? $this->error['storage'] : '';
        $migrationFile = DIR_OPENCART . 'system/library/storage_migration.php';
        if (is_file($migrationFile)) {
            require_once $migrationFile;
            $data['suggested_storage'] = StorageMigration::suggestedPath(DIR_OPENCART);
        } else {
            $data['suggested_storage'] = '';
            $data['error_storage'] = 'Missing storage migration library: ' . $migrationFile;
        }

		if (isset($this->request->post['delete_demodata'])) {
			$data['delete_demodata'] = $this->request->post['delete_demodata'];
		} else {
			$data['delete_demodata'] = 'no';
		}

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			$data['country'] = !empty($this->request->post['country']) ? $this->request->post['country'] : array();
		} else {
			$data['country'] = array();

			foreach ($data['countries'] as $country) {
				if ($country['status']) {
					$data['country'][] = $country['country_id'];
				}
			}
		}

		$data['back'] = $this->url->link('install/step_3');

		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');

		$this->response->setOutput($this->load->view('install/step_4', $data));
	}

	private function validate() {
		if (empty($this->request->post['delete_demodata'])) {
			$this->error['delete_demodata'] = $this->language->get('error_delete_demodata');
		}

		if (empty($this->request->post['country'])) {
			$this->error['country'] = $this->language->get('error_country');
		}

		return !$this->error;
	}
}
