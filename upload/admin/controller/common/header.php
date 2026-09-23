<?php

class ControllerCommonHeader extends Controller {
	public function index() {
		$data['title'] = $this->document->getTitle();

		if ($this->request->server['HTTPS']) {
			$data['base'] = HTTPS_SERVER;
		} else {
			$data['base'] = HTTP_SERVER;
		}

		$this->load->model('localisation/language');

		$data['default_language_id'] = $this->config->get('config_language_id');
		$data['languages'] = $this->model_localisation_language->getLanguages();

		/*
		 * SEO URL generation is shared by all admin entity forms. Expose the
		 * effective SEO mode and catalog default language per store so the
		 * generator never confuses the admin UI language with the storefront
		 * default language.
		 */
		$language_ids = array();

		foreach ($data['languages'] as $language) {
			$language_ids[(string)$language['code']] = (int)$language['language_id'];
		}

		$data['seo_store_config'] = array();

		$seo_setting_query = $this->db->query("SELECT store_id, code, `key`, `value`
			FROM `" . DB_PREFIX . "setting`
			WHERE (code = 'config' AND `key` IN ('config_language', 'config_seo_url'))
			   OR (code = 'module_seo_language' AND `key` = 'module_seo_language_status')
			   OR (code = 'seo_language' AND `key` = 'seo_language_status')
			ORDER BY store_id, (code = 'module_seo_language') ASC");

		$store_state = array();

		foreach ($seo_setting_query->rows as $row) {
			$store_id = (int)$row['store_id'];

			if (!isset($store_state[$store_id])) {
				$store_state[$store_id] = array(
					'config_language' => '',
					'config_seo_url' => false,
					'seo_language_status' => false
				);
			}

			if ($row['code'] === 'config' && $row['key'] === 'config_language') {
				$store_state[$store_id]['config_language'] = (string)$row['value'];
			} elseif ($row['code'] === 'config' && $row['key'] === 'config_seo_url') {
				$store_state[$store_id]['config_seo_url'] = !empty($row['value']);
			} elseif (
				($row['code'] === 'module_seo_language' && $row['key'] === 'module_seo_language_status')
				|| ($row['code'] === 'seo_language' && $row['key'] === 'seo_language_status')
			) {
				$store_state[$store_id]['seo_language_status'] = !empty($row['value']);
			}
		}

		foreach ($store_state as $store_id => $state) {
			$default_language_id = isset($language_ids[$state['config_language']])
				? $language_ids[$state['config_language']]
				: 0;

			$data['seo_store_config'][$store_id] = array(
				'default_language_id' => $default_language_id,
				'language_scoped' => $state['config_seo_url'] && $state['seo_language_status']
			);
		}

		$data['description'] = $this->document->getDescription();
		$data['keywords'] = $this->document->getKeywords();
		$data['links'] = $this->document->getLinks();
		$data['styles'] = $this->document->getStyles();
		$data['scripts'] = $this->document->getScripts();
		$data['lang'] = $this->language->get('code');
		$data['direction'] = $this->language->get('direction');

		$this->load->language('common/header');

		$data['text_logged'] = sprintf($this->language->get('text_logged'), $this->user->getUserName());

		if (!isset($this->request->get['user_token']) || !isset($this->session->data['user_token']) || ($this->request->get['user_token'] != $this->session->data['user_token'])) {
			$data['logged'] = '';

			$data['home'] = $this->url->link('common/login', '', true);
		} else {
			$data['logged'] = true;

			$data['maintenance'] = (bool)$this->config->get('config_maintenance');
			$data['maintenance_modify'] = $this->user->hasPermission('modify', 'setting/setting');

			$data['maintenance_url'] = $this->url->link(
				'setting/setting/maintenance',
				'user_token=' . $this->session->data['user_token'],
				true
			);

			$data['home'] = $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true);
			$data['logout'] = $this->url->link('common/logout', 'user_token=' . $this->session->data['user_token'], true);
			$data['profile'] = $this->url->link('common/profile', 'user_token=' . $this->session->data['user_token'], true);
			$data['new_category'] = $this->url->link('catalog/category/add', 'user_token=' . $this->session->data['user_token'], true);
			$data['new_customer'] = $this->url->link('user/user/add', 'user_token=' . $this->session->data['user_token'], true);
			$data['new_download'] = $this->url->link('catalog/download/add', 'user_token=' . $this->session->data['user_token'], true);
			$data['new_manufacturer'] = $this->url->link('catalog/manufacturer/add', 'user_token=' . $this->session->data['user_token'], true);
			$data['new_product'] = $this->url->link('catalog/product/add', 'user_token=' . $this->session->data['user_token'], true);

			$this->load->model('user/user');

			$this->load->model('tool/image');

			$user_info = $this->model_user_user->getUser($this->user->getId());

			if ($user_info) {
				$data['firstname'] = $user_info['firstname'];
				$data['lastname'] = $user_info['lastname'];
				$data['username'] = $user_info['username'];
				$data['user_group'] = $user_info['user_group'];

				if (is_file(DIR_IMAGE . html_entity_decode($user_info['image'], ENT_QUOTES, 'UTF-8'))) {
					$data['image'] = $this->model_tool_image->resize(html_entity_decode($user_info['image'], ENT_QUOTES, 'UTF-8'), 45, 45);
				} else {
					$data['image'] = $this->model_tool_image->resize('profile.png', 45, 45);
				}
			} else {
				$data['firstname'] = '';
				$data['lastname'] = '';
				$data['user_group'] = '';
				$data['image'] = '';
			}

			// Online Stores
			$data['stores'] = array();

			$data['stores'][] = array(
				'name' => $this->config->get('config_name'),
				'href' => HTTP_CATALOG
			);

			$this->load->model('setting/store');

			$results = $this->model_setting_store->getStores();

			foreach ($results as $result) {
				$data['stores'][] = array(
					'name' => $result['name'],
					'href' => $result['url']
				);
			}
		}

		$data['search'] = $this->load->controller('search/search');

		return $this->load->view('common/header', $data);
	}
}
