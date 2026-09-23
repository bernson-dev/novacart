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
		$this->load->model('setting/setting');
		$this->load->model('setting/store');
		$this->load->model('setting/seo_language');

		$data['default_language_id'] = (int)$this->config->get('config_language_id');
		$data['languages'] = $this->model_localisation_language->getLanguages();

		/*
		 * Build SEO UI state for every storefront explicitly. Do not infer it from
		 * whichever setting rows happen to exist: additional stores may inherit
		 * missing config values from store 0 and still need correct admin behavior.
		 */
		$language_ids = array();
		$language_short_ids = array();
		$language_short_ambiguous = array();

		foreach ($data['languages'] as $language) {
			$code = strtolower(str_replace('_', '-', (string)$language['code']));
			$language_id = (int)$language['language_id'];
			$language_ids[$code] = $language_id;

			$parts = explode('-', $code);
			$short = isset($parts[0]) ? $parts[0] : '';

			if ($short !== '') {
				if (isset($language_short_ids[$short]) && $language_short_ids[$short] !== $language_id) {
					$language_short_ambiguous[$short] = true;
				} else {
					$language_short_ids[$short] = $language_id;
				}
			}
		}

		$default_config = $this->model_setting_setting->getSetting('config', 0);

		if (!isset($default_config['config_language'])) {
			$default_config['config_language'] = (string)$this->config->get('config_language');
		}

		if (!isset($default_config['config_seo_url'])) {
			$default_config['config_seo_url'] = (int)$this->config->get('config_seo_url');
		}

		$store_ids = array(0);

		foreach ($this->model_setting_store->getStores() as $store) {
			$store_ids[] = (int)$store['store_id'];
		}

		$data['seo_store_config'] = array();

		foreach (array_unique($store_ids) as $store_id) {
			$config = $default_config;

			if ($store_id !== 0) {
				$config = array_replace(
					$config,
					$this->model_setting_setting->getSetting('config', $store_id)
				);
			}

			$seo_language = $this->model_setting_seo_language->getSettings($store_id);
			$language_code = isset($config['config_language'])
				? (string)$config['config_language']
				: (string)$default_config['config_language'];
			$normalized_language_code = strtolower(str_replace('_', '-', $language_code));
			$default_language_id = isset($language_ids[$normalized_language_code])
				? (int)$language_ids[$normalized_language_code]
				: 0;

			if ($default_language_id === 0) {
				$parts = explode('-', $normalized_language_code);
				$short = isset($parts[0]) ? $parts[0] : '';

				if (
					$short !== ''
					&& empty($language_short_ambiguous[$short])
					&& isset($language_short_ids[$short])
				) {
					$default_language_id = (int)$language_short_ids[$short];
				}
			}

			$data['seo_store_config'][$store_id] = array(
				'default_language_id' => $default_language_id,
				'language_scoped' => !empty($config['config_seo_url']) && !empty($seo_language['status'])
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
