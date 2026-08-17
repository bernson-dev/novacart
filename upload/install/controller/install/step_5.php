<?php
class ControllerInstallStep5 extends Controller {
	public function index() {
		$data = $this->load->language('install/step_5');

		$this->document->setTitle($this->language->get('heading_title'));

		if (empty($this->session->data['install']) || $this->session->data['install'] !== 1) {
			$this->response->redirect($this->url->link('install/step_3'));
			return;
		}

		if (empty($this->session->data['install_cleanup_token'])) {
			$this->session->data['install_cleanup_token'] = bin2hex(random_bytes(32));
		}

		$data['cleanup_token'] = $this->session->data['install_cleanup_token'];
		$data['delete_action'] = $this->url->link('install/step_5/deleteInstallFolder');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('install/step_5', $data));
	}

	public function deleteInstallFolder() {
		$this->load->language('install/step_5');

		$json = array();

		if ($this->request->server['REQUEST_METHOD'] !== 'POST') {
			$json['error'] = $this->language->get('text_error_invalid_request');
			return $this->jsonResponse($json);
		}

		$token = isset($this->request->post['token']) ? (string)$this->request->post['token'] : '';
		$session_token = isset($this->session->data['install_cleanup_token']) ? (string)$this->session->data['install_cleanup_token'] : '';

		if (empty($this->session->data['install']) || $this->session->data['install'] !== 1 || $token === '' || $session_token === '' || !hash_equals($session_token, $token)) {
			$json['error'] = $this->language->get('text_error_invalid_session');
			return $this->jsonResponse($json);
		}

		$install_path = rtrim(DIR_OPENCART, '/\\') . DIRECTORY_SEPARATOR . 'install';

		if (is_link($install_path)) {
			$json['error'] = $this->language->get('text_error_install_delete');
			return $this->jsonResponse($json);
		}

		if (!is_dir($install_path)) {
			$json['error'] = $this->language->get('text_error_install_not_found');
			return $this->jsonResponse($json);
		}

		$marker = DIR_STORAGE . 'install_cleanup.flag';
		$payload = json_encode(array(
			'created' => time(),
			'path' => $install_path
		));

		$result = @file_put_contents($marker, $payload, LOCK_EX);

		if ($result === false || $result !== strlen($payload)) {
			$json['error'] = $this->language->get('text_error_install_delete');
			return $this->jsonResponse($json);
		}

		unset($this->session->data['install_cleanup_token']);
		unset($this->session->data['install']);

		$json['success'] = $this->language->get('text_success_install_deleted');

		return $this->jsonResponse($json);
	}

	private function jsonResponse($json) {
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
