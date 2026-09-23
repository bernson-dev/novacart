<?php
class ControllerCommonFooter extends Controller {
	public function index() {
		$this->load->language('common/footer');

		$data = array(
			'text_footer'              => $this->language->get('text_footer'),
			'text_version'             => '',
			'text_php'                 => $this->language->get('text_php'),
			'text_ioncube'             => $this->language->get('text_ioncube'),
			'text_https_secure'        => $this->language->get('text_https_secure'),
			'text_https_insecure'      => $this->language->get('text_https_insecure'),
			'text_server_time'         => $this->language->get('text_server_time'),
			'text_display_errors'      => $this->language->get('text_display_errors'),
			'text_display_errors_link' => $this->language->get('text_display_errors_link'),
			'php_version'              => '',
			'ioncube_version'          => '',
			'display_errors'           => false,
			'display_errors_url'       => '',
			'is_https'                 => false,
			'show_https_indicator'     => false,
			'server_time'              => 0,
			'server_timezone'          => ''
		);

		$is_admin = $this->user->isLogged()
			&& isset($this->request->get['user_token'])
			&& isset($this->session->data['user_token'])
			&& $this->request->get['user_token'] === $this->session->data['user_token'];

		if ($is_admin) {
			$data['text_version'] = sprintf($this->language->get('text_version'), VERSION);
			$data['php_version'] = phpversion();
			$data['ioncube_version'] = function_exists('ioncube_loader_version')
				? ioncube_loader_version()
				: $this->language->get('text_not_installed');
			$data['server_time'] = time();
			$data['server_timezone'] = date_default_timezone_get();
			$data['show_https_indicator'] = true;
			$data['is_https'] = !empty($this->request->server['HTTPS'])
				&& strtolower((string)$this->request->server['HTTPS']) !== 'off';

			if ($this->config->get('config_error_display')) {
				$data['display_errors'] = true;

				if (
					$this->user->hasPermission('access', 'setting/setting')
					&& $this->user->hasPermission('modify', 'setting/setting')
				) {
					$data['display_errors_url'] = $this->url->link(
						'setting/setting',
						'user_token=' . $this->session->data['user_token'],
						true
					) . '#tab-server';
				}
			}
		}

		return $this->load->view('common/footer', $data);
	}
}