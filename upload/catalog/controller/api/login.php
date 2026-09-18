<?php
class ControllerApiLogin extends Controller {
	public function index() {
		$this->load->language('api/login');

		$json = $api_info = array();
		$remote_addr = isset($this->request->server['REMOTE_ADDR']) ? (string)$this->request->server['REMOTE_ADDR'] : '';

		$this->load->model('account/api');

		// Login with API Key
		$username = isset($this->request->post['username']) && is_scalar($this->request->post['username']) ? (string)$this->request->post['username'] : 'Default';
		$key = isset($this->request->post['key']) && is_scalar($this->request->post['key']) ? (string)$this->request->post['key'] : '';

		if ($key !== '') {
			$api_info = $this->model_account_api->login($username, $key);
		}

		if ($api_info) {
			// Check if IP is allowed
			$ip_data = array();

			$results = $this->model_account_api->getApiIps($api_info['api_id']);

			foreach ($results as $result) {
				$ip_data[] = trim($result['ip']);
			}

			if (!in_array($remote_addr, $ip_data)) {
				$json['error']['ip'] = sprintf($this->language->get('error_ip'), $remote_addr);
			}

			if (!$json) {
				$json['success'] = $this->language->get('text_success');

				$session = new Session($this->config->get('session_engine'), $this->registry);

				$session->start();

				$this->model_account_api->addApiSession($api_info['api_id'], $session->getId(), $remote_addr);

				$session->data['api_id'] = $api_info['api_id'];

				// Create Token
				$json['api_token'] = $session->getId();
			} else {
				$json['error']['key'] = $this->language->get('error_key');
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
