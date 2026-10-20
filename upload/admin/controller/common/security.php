<?php
class ControllerCommonSecurity extends Controller {
	public function index() {
		$this->load->language('common/security');

		$data['text_instruction'] = $this->language->get('text_instruction');

		$data['user_token'] = $this->session->data['user_token'];

		$data['storage'] = DIR_SYSTEM . 'storage/';

		$path = '';

		$data['paths'] = array();

		$parts = explode('/', str_replace('\\', '/', rtrim(DIR_SYSTEM, '/')));

		foreach ($parts as $part) {
			$path .= $part . '/';

			$data['paths'][] = $path;
		}

		rsort($data['paths']);

		$data['document_root'] = str_replace('\\', '/', realpath($this->request->server['DOCUMENT_ROOT'] . '/../') . '/');

		return $this->load->view('common/security', $data);
	}

    public function move() {
        $this->load->language('common/security');
        $json = array();
        if (!$this->user->hasPermission('modify', 'common/security')) {
            $json['error'] = $this->language->get('error_permission');
        } elseif (DIR_STORAGE != DIR_SYSTEM . 'storage/') {
            $json['error'] = $this->language->get('error_path');
        } else {
            $path = isset($this->request->post['path']) ? (string)$this->request->post['path'] : '';
            $directory = isset($this->request->post['directory']) ? (string)$this->request->post['directory'] : '';
            $pathReal = realpath($path);
            $systemReal = realpath(DIR_SYSTEM);
            if (!$pathReal || !$systemReal || strpos(str_replace('\\', '/', $systemReal) . '/', rtrim(str_replace('\\', '/', $pathReal), '/') . '/') !== 0) {
                $json['error'] = $this->language->get('error_path');
            } elseif (!preg_match('/^[a-zA-Z0-9_-]+$/', $directory)) {
                $json['error'] = $this->language->get('error_directory');
            } else {
                try {
                    require_once DIR_SYSTEM . 'library/storage_migration.php';
                    StorageMigration::move(DIR_SYSTEM . 'storage', $pathReal . '/' . $directory, array(
                        DIR_APPLICATION . 'config.php',
                        dirname(rtrim(DIR_APPLICATION, '/\\')) . '/config.php'
                    ));
                    $json['success'] = $this->language->get('text_success');
                } catch (Throwable $error) {
                    $json['error'] = $error->getMessage();
                }
            }
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
