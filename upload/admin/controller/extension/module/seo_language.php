<?php
class ControllerExtensionModuleSeoLanguage extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/seo_language');
		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');
		$this->load->model('localisation/language');

		$store_id = isset($this->request->get['store_id']) ? (int)$this->request->get['store_id'] : 0;

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate($store_id)) {
			$this->request->post['seo_language_prefix'] = $this->normalizePrefixes(
				isset($this->request->post['seo_language_prefix'])
					? $this->request->post['seo_language_prefix']
					: array()
			);

			$this->model_setting_setting->editSetting('seo_language', $this->request->post, $store_id);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = 'user_token=' . $this->session->data['user_token'];

			if ($store_id) {
				$url .= '&store_id=' . $store_id;
			}

			$this->response->redirect($this->url->link('extension/module/seo_language', $url, true));
		}

		$data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';
		$data['error_prefix'] = isset($this->error['prefix']) ? $this->error['prefix'] : array();

		$data['breadcrumbs'] = array();
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		$url = 'user_token=' . $this->session->data['user_token'];

		if ($store_id) {
			$url .= '&store_id=' . $store_id;
		}

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/seo_language', $url, true)
		);

		$data['action'] = $this->url->link('extension/module/seo_language', $url, true);
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		$settings = $this->model_setting_setting->getSetting('seo_language', $store_id);
		$store_settings = $this->model_setting_setting->getSetting('config', $store_id);

		if (isset($this->request->post['seo_language_status'])) {
			$data['seo_language_status'] = (int)$this->request->post['seo_language_status'];
		} else {
			$data['seo_language_status'] = isset($settings['seo_language_status'])
				? (int)$settings['seo_language_status']
				: 0;
		}

		if (isset($this->request->post['seo_language_prefix'])) {
			$prefixes = $this->request->post['seo_language_prefix'];
		} elseif (isset($settings['seo_language_prefix']) && is_array($settings['seo_language_prefix'])) {
			$prefixes = $settings['seo_language_prefix'];
		} else {
			$prefixes = array();
		}

		$languages = $this->model_localisation_language->getLanguages();
		$data['languages'] = array();
		$data['default_language'] = isset($store_settings['config_language'])
			? (string)$store_settings['config_language']
			: (string)$this->config->get('config_language');
		$has_saved_prefixes = isset($settings['seo_language_prefix'])
			&& is_array($settings['seo_language_prefix']);

		foreach ($languages as $language) {
			if (empty($language['status'])) {
				continue;
			}

			$code = (string)$language['code'];
			$prefix = isset($prefixes[$code]) ? trim((string)$prefixes[$code], " /\\") : '';

			if ($prefix === '' && !$has_saved_prefixes) {
				$legacy = $this->db->query("SELECT keyword FROM " . DB_PREFIX . "seo_url
					WHERE query = 'common/home'
					AND store_id = '" . $store_id . "'
					AND language_id = '" . (int)$language['language_id'] . "'
					LIMIT 1");

				if ($legacy->num_rows && trim((string)$legacy->row['keyword']) !== '') {
					$prefix = trim((string)$legacy->row['keyword'], " /\\");
				} elseif ($code !== $data['default_language']) {
					$code_parts = preg_split('/[-_]/', strtolower($code));
					$prefix = !empty($code_parts[0]) ? $code_parts[0] : '';
				}
			}

			$data['languages'][] = array(
				'language_id' => (int)$language['language_id'],
				'name'        => (string)$language['name'],
				'code'        => $code,
				'prefix'      => strtolower($prefix),
				'is_default'  => ($code === $data['default_language'])
			);
		}

		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/seo_language', $data));
	}

	public function install() {
		$this->load->model('user/user_group');

		$this->model_user_user_group->addPermission(
			$this->user->getGroupId(),
			'access',
			'extension/module/seo_language'
		);
		$this->model_user_user_group->addPermission(
			$this->user->getGroupId(),
			'modify',
			'extension/module/seo_language'
		);
	}

	private function validate($store_id) {
		if (!$this->user->hasPermission('modify', 'extension/module/seo_language')) {
			$this->error['warning'] = $this->language->get('error_permission');
			return false;
		}

		$this->load->model('localisation/language');

		$languages = $this->model_localisation_language->getLanguages();
		$store_settings = $this->model_setting_setting->getSetting('config', $store_id);
		$default_code = isset($store_settings['config_language'])
			? (string)$store_settings['config_language']
			: (string)$this->config->get('config_language');
		$status = !empty($this->request->post['seo_language_status']);
		$prefixes = $this->normalizePrefixes(
			isset($this->request->post['seo_language_prefix'])
				? $this->request->post['seo_language_prefix']
				: array()
		);
		$used = array();

		foreach ($languages as $language) {
			if (empty($language['status'])) {
				continue;
			}

			$code = (string)$language['code'];
			$prefix = isset($prefixes[$code]) ? $prefixes[$code] : '';

			if ($status && $code !== $default_code && $prefix === '') {
				$this->error['prefix'][$code] = $this->language->get('error_prefix_required');
				continue;
			}

			if ($prefix === '') {
				continue;
			}

			if (!preg_match('/^[a-z0-9][a-z0-9_-]{0,31}$/', $prefix)) {
				$this->error['prefix'][$code] = $this->language->get('error_prefix_format');
				continue;
			}

			if (isset($used[$prefix]) && $used[$prefix] !== $code) {
				$this->error['prefix'][$code] = $this->language->get('error_prefix_duplicate');
				continue;
			}

			$used[$prefix] = $code;

			$collision = $this->db->query("SELECT seo_url_id FROM " . DB_PREFIX . "seo_url
				WHERE store_id = '" . (int)$store_id . "'
				AND keyword = '" . $this->db->escape($prefix) . "'
				AND query <> 'common/home'
				LIMIT 1");

			if ($collision->num_rows) {
				$this->error['prefix'][$code] = $this->language->get('error_prefix_collision');
			}
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}

	private function normalizePrefixes($prefixes) {
		$result = array();

		if (!is_array($prefixes)) {
			return $result;
		}

		foreach ($prefixes as $code => $prefix) {
			if (!is_scalar($prefix)) {
				continue;
			}

			$prefix = strtolower(trim((string)$prefix, " /\\"));

			if ($prefix !== '') {
				$result[(string)$code] = $prefix;
			}
		}

		return $result;
	}
}
