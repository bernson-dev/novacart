<?php
class ControllerCommonFooter extends Controller {
	public function index() {
		$this->load->language('common/footer');

		$data = [
			'text_footer'          => $this->language->get('text_footer'),
			'text_version'         => '',
			'extra_version'        => '',
			'display_errors'       => '',
			'is_https'             => false,
			'show_https_indicator' => false,
			'server_time'          => '',
			'server_timezone'      => ''
		];

		$is_admin = $this->user->isLogged()
		&& isset($this->request->get['user_token'])
		&& $this->request->get['user_token'] === $this->session->data['user_token'];

		if ($is_admin) {
			$data['text_version'] = sprintf($this->language->get('text_version'), VERSION);

			$php_version     = phpversion();
			$ioncube_version = function_exists('ioncube_loader_version') ? ioncube_loader_version() : 'Not installed';

			$data['extra_version']   = " | PHP {$php_version} | IonCube {$ioncube_version}";
			$data['server_time']     = time();
			$data['server_timezone'] = date_default_timezone_get();

			if ($this->config->get('config_error_display')) {
				$data['display_errors'] = $this->language->get('text_display_errors');
			}

			$data['show_https_indicator'] = true;
			$data['is_https'] = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
		}

		return $this->load->view('common/footer', $data);
	}
}