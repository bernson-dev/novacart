<?php
// admin/controller/setting/setting.php
class ControllerSettingSetting extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('setting/setting');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		$url = '';

		if (isset($this->request->get['store_id'])) {
			$url .= '&store_id=' . (int)$this->request->get['store_id'];
		}

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('config', $this->request->post);

			// Start Add svg Favicon generator
			$store_id = (int)($this->request->get['store_id'] ?? 0);

			$svg      = $this->request->post['config_svg_icon'] ?? '';
			$color    = $this->request->post['config_favicon_color'] ?? '#000000';
			$original = !empty($this->request->post['config_favicon_original']) ? 1 : 0;

			$this->load->model('tool/favicon_generator');

			if ($svg) {
				if (!class_exists('Imagick')) {
					$this->session->data['warning'] = $this->language->get('warning_imagick_missing');
				} else {
					$result = $this->model_tool_favicon_generator->generateFromSvg($svg, $store_id, $color, $original);

					if (!$result) {
						$this->session->data['warning'] = $this->language->get('warning_favicon_generate');
					}
				}
			} else {
				// SVG не выбран — удаляем старый набор, чтобы has_favicon не “залипал”
				$this->model_tool_favicon_generator->clearStore($store_id);
			}
			// End Add svg Favicon generator

			if ($this->config->get('config_currency_auto')) {
				$this->load->model('localisation/currency');
				$this->load->controller('extension/currency/' . $this->config->get('config_currency_engine')."/currency", $this->config->get('config_currency'));
			}

			$this->session->data['success'] = $this->language->get('text_success');

			if (isset($this->request->post['apply']) && $this->request->post['apply'] == 1) {
				$this->response->redirect($this->url->link('setting/setting', 'user_token=' . $this->session->data['user_token'] . $url, true));
			} else {
				$this->response->redirect($this->url->link('setting/store', 'user_token=' . $this->session->data['user_token'], true));
			}
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}

		if (isset($this->error['owner'])) {
			$data['error_owner'] = $this->error['owner'];
		} else {
			$data['error_owner'] = '';
		}

		if (isset($this->error['address'])) {
			$data['error_address'] = $this->error['address'];
		} else {
			$data['error_address'] = '';
		}

		if (isset($this->error['email'])) {
			$data['error_email'] = $this->error['email'];
		} else {
			$data['error_email'] = '';
		}

		if (isset($this->error['telephone'])) {
			$data['error_telephone'] = $this->error['telephone'];
		} else {
			$data['error_telephone'] = '';
		}

		if (isset($this->error['meta_title'])) {
			$data['error_meta_title'] = $this->error['meta_title'];
		} else {
			$data['error_meta_title'] = '';
		}

		if (isset($this->error['country'])) {
			$data['error_country'] = $this->error['country'];
		} else {
			$data['error_country'] = '';
		}

		if (isset($this->error['zone'])) {
			$data['error_zone'] = $this->error['zone'];
		} else {
			$data['error_zone'] = '';
		}

		if (isset($this->error['customer_group_display'])) {
			$data['error_customer_group_display'] = $this->error['customer_group_display'];
		} else {
			$data['error_customer_group_display'] = '';
		}

		if (isset($this->error['login_attempts'])) {
			$data['error_login_attempts'] = $this->error['login_attempts'];
		} else {
			$data['error_login_attempts'] = '';
		}

		if (isset($this->error['voucher_min'])) {
			$data['error_voucher_min'] = $this->error['voucher_min'];
		} else {
			$data['error_voucher_min'] = '';
		}

		if (isset($this->error['voucher_max'])) {
			$data['error_voucher_max'] = $this->error['voucher_max'];
		} else {
			$data['error_voucher_max'] = '';
		}

		if (isset($this->error['processing_status'])) {
			$data['error_processing_status'] = $this->error['processing_status'];
		} else {
			$data['error_processing_status'] = '';
		}

		if (isset($this->error['complete_status'])) {
			$data['error_complete_status'] = $this->error['complete_status'];
		} else {
			$data['error_complete_status'] = '';
		}

		if (isset($this->error['log'])) {
			$data['error_log'] = $this->error['log'];
		} else {
			$data['error_log'] = '';
		}

		if (isset($this->error['limit_admin'])) {
			$data['error_limit_admin'] = $this->error['limit_admin'];
		} else {
			$data['error_limit_admin'] = '';
		}

		if (isset($this->error['limit_autocomplete'])) {
			$data['error_limit_autocomplete'] = $this->error['limit_autocomplete'];
		} else {
			$data['error_limit_autocomplete'] = '';
		}

		if (isset($this->error['limit_filemanager'])) {
			$data['error_limit_filemanager'] = $this->error['limit_filemanager'];
		} else {
			$data['error_limit_filemanager'] = '';
		}

		if (isset($this->error['encryption'])) {
			$data['error_encryption'] = $this->error['encryption'];
		} else {
			$data['error_encryption'] = '';
		}

		if (isset($this->error['file_max_size'])) {
			$data['error_file_max_size'] = $this->error['file_max_size'];
		} else {
			$data['error_file_max_size'] = '';
		}

		$data['error_mail_smtp_hostname'] = isset($this->error['mail_smtp_hostname']) ? $this->error['mail_smtp_hostname'] : '';
		$data['error_mail_smtp_port'] = isset($this->error['mail_smtp_port']) ? $this->error['mail_smtp_port'] : '';
		$data['error_mail_smtp_timeout'] = isset($this->error['mail_smtp_timeout']) ? $this->error['mail_smtp_timeout'] : '';
		$data['error_mail_smtp_credentials'] = isset($this->error['mail_smtp_credentials']) ? $this->error['mail_smtp_credentials'] : '';

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_stores'),
			'href' => $this->url->link('setting/store', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('setting/setting', 'user_token=' . $this->session->data['user_token'], true)
		);

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->session->data['warning'])) {
			$data['warning'] = $this->session->data['warning'];

			unset($this->session->data['warning']);
		} else {
			$data['warning'] = '';
		}

		if (!class_exists('Imagick')) {
			$imagick_warning = $this->language->get('warning_imagick_missing');

			if ($data['warning']) {
				if (strpos($data['warning'], $imagick_warning) === false) {
					$data['warning'] .= ' ' . $imagick_warning;
				}
			} else {
				$data['warning'] = $imagick_warning;
			}
		}

		$data['action'] = $this->url->link('setting/setting', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('setting/store', 'user_token=' . $this->session->data['user_token'], true);

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->post['config_meta_title'])) {
			$data['config_meta_title'] = $this->request->post['config_meta_title'];
		} else {
			$data['config_meta_title'] = $this->config->get('config_meta_title');
		}

		if (isset($this->request->post['config_meta_description'])) {
			$data['config_meta_description'] = $this->request->post['config_meta_description'];
		} else {
			$data['config_meta_description'] = $this->config->get('config_meta_description');
		}

		if (isset($this->request->post['config_meta_keyword'])) {
			$data['config_meta_keyword'] = $this->request->post['config_meta_keyword'];
		} else {
			$data['config_meta_keyword'] = $this->config->get('config_meta_keyword');
		}

		if (isset($this->request->post['config_theme'])) {
			$data['config_theme'] = $this->request->post['config_theme'];
		} else {
			$data['config_theme'] = $this->config->get('config_theme');
		}

		if ($this->request->server['HTTPS']) {
			$data['store_url'] = HTTPS_CATALOG;
		} else {
			$data['store_url'] = HTTP_CATALOG;
		}

		$data['themes'] = array();

		$this->load->model('setting/extension');

		$extensions = $this->model_setting_extension->getInstalled('theme');

		foreach ($extensions as $code) {
			if ($this->config->get('theme_' . $code . '_status')) {
				$this->load->language('extension/theme/' . $code, 'extension');

				$data['themes'][] = array(
					'text'  => $this->language->get('extension')->get('heading_title'),
					'value' => $code
				);
			}
		}

		if (isset($this->request->post['config_layout_id'])) {
			$data['config_layout_id'] = (int)$this->request->post['config_layout_id'];
		} else {
			$data['config_layout_id'] = $this->config->get('config_layout_id');
		}

		$this->load->model('design/layout');

		$data['layouts'] = $this->model_design_layout->getLayouts();

		if (isset($this->request->post['config_name'])) {
			$data['config_name'] = $this->request->post['config_name'];
		} else {
			$data['config_name'] = $this->config->get('config_name');
		}

		if (isset($this->request->post['config_owner'])) {
			$data['config_owner'] = $this->request->post['config_owner'];
		} else {
			$data['config_owner'] = $this->config->get('config_owner');
		}

		if (isset($this->request->post['config_address'])) {
			$data['config_address'] = $this->request->post['config_address'];
		} else {
			$data['config_address'] = $this->config->get('config_address');
		}

		if (isset($this->request->post['config_geocode'])) {
			$data['config_geocode'] = $this->request->post['config_geocode'];
		} else {
			$data['config_geocode'] = $this->config->get('config_geocode');
		}

		if (isset($this->request->post['config_email'])) {
			$data['config_email'] = $this->request->post['config_email'];
		} else {
			$data['config_email'] = $this->config->get('config_email');
		}

		if (isset($this->request->post['config_telephone'])) {
			$data['config_telephone'] = $this->request->post['config_telephone'];
		} else {
			$data['config_telephone'] = $this->config->get('config_telephone');
		}

		if (isset($this->request->post['config_fax'])) {
			$data['config_fax'] = $this->request->post['config_fax'];
		} else {
			$data['config_fax'] = $this->config->get('config_fax');
		}

		if (isset($this->request->post['config_image'])) {
			$data['config_image'] = $this->request->post['config_image'];
		} else {
			$data['config_image'] = $this->config->get('config_image');
		}

		$this->load->model('tool/image');

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		if (isset($this->request->post['config_image']) && is_file(DIR_IMAGE . $this->request->post['config_image'])) {
			$data['thumb'] = $this->model_tool_image->resize($this->request->post['config_image'], 100, 100);
		} elseif ($this->config->get('config_image') && is_file(DIR_IMAGE . $this->config->get('config_image'))) {
			$data['thumb'] = $this->model_tool_image->resize($this->config->get('config_image'), 100, 100);
		} else {
			$data['thumb'] = $data['placeholder'];
		}

		// --- SVG icon + color for favicon ---
		
		if (isset($this->request->post['config_favicon_original'])) {
			$data['config_favicon_original'] = $this->request->post['config_favicon_original'];
		} else {
			$data['config_favicon_original'] = $this->config->get('config_favicon_original');
		}

		if (isset($this->request->post['config_svg_icon'])) {
			$data['config_svg_icon'] = $this->request->post['config_svg_icon'];
		} else {
			$data['config_svg_icon'] = $this->config->get('config_svg_icon');
		}

		if (isset($this->request->post['config_favicon_color'])) {
			$data['config_favicon_color'] = $this->request->post['config_favicon_color'];
		} else {
			$data['config_favicon_color'] = $this->config->get('config_favicon_color') ?: '#ff0000';
		}

		// URL предпросмотра SVG с цветом
		$data['svg_preview_url'] = $this->url->link('setting/setting/svgPreview', 'user_token=' . $this->session->data['user_token'], true);


		// Картинка превью: если svg — отдаём через svgPreview, иначе обычный resize
		$data['svg_icon'] = $data['placeholder'];

		if (!empty($data['config_svg_icon']) && is_file(DIR_IMAGE . $data['config_svg_icon'])) {
			$ext = strtolower(pathinfo($data['config_svg_icon'], PATHINFO_EXTENSION));

			if ($ext === 'svg') {
				$data['svg_icon'] = $data['svg_preview_url']
				. '&path=' . urlencode($data['config_svg_icon']);

				if (!empty($data['config_favicon_original'])) {
					$data['svg_icon'] .= '&original=1';
				} else {
					$data['svg_icon'] .= '&color=' . urlencode($data['config_favicon_color']);
				}

				$data['svg_icon'] .= '&_=' . time();
			} else {
				$data['svg_icon'] = $this->model_tool_image->resize($data['config_svg_icon'], 100, 100);
			}
		}

		$data['error_svg_icon'] = $this->error['svg_icon'] ?? '';
		$data['error_favicon_color'] = $this->error['favicon_color'] ?? '';

		if (isset($this->request->post['config_open'])) {
			$data['config_open'] = $this->request->post['config_open'];
		} else {
			$data['config_open'] = $this->config->get('config_open');
		}

		if (isset($this->request->post['config_comment'])) {
			$data['config_comment'] = $this->request->post['config_comment'];
		} else {
			$data['config_comment'] = $this->config->get('config_comment');
		}

		$this->load->model('localisation/location');

		$data['locations'] = $this->model_localisation_location->getLocations();

		if (isset($this->request->post['config_location'])) {
			$data['config_location'] = $this->request->post['config_location'];
		} elseif ($this->config->get('config_location')) {
			$data['config_location'] = $this->config->get('config_location');
		} else {
			$data['config_location'] = array();
		}

		if (isset($this->request->post['config_country_id'])) {
			$data['config_country_id'] = (int)$this->request->post['config_country_id'];
		} else {
			$data['config_country_id'] = $this->config->get('config_country_id');
		}

		$this->load->model('localisation/country');

		$data['countries'] = $this->model_localisation_country->getCountries();

		if (isset($this->request->post['config_zone_id'])) {
			$data['config_zone_id'] = (int)$this->request->post['config_zone_id'];
		} else {
			$data['config_zone_id'] = (int)$this->config->get('config_zone_id');
		}

		if (isset($this->request->post['config_timezone'])) {
			$data['config_timezone'] = $this->request->post['config_timezone'];
		} elseif ($this->config->has('config_timezone')) {
			$data['config_timezone'] = $this->config->get('config_timezone');
		} else {
			$data['config_timezone'] = 'UTC';
		}

		// Set Time Zone
		$data['timezones'] = array();

		$timestamp = date_create('now');

		$timezones = timezone_identifiers_list();

		foreach ($timezones as $timezone) {
			date_timezone_set($timestamp, timezone_open($timezone));

			$hour = ' (' . date_format($timestamp, 'P') . ')';

			$data['timezones'][] = array(
				'text'  => $timezone . $hour,
				'value' => $timezone
			);
		}

		if (isset($this->request->post['config_language'])) {
			$data['config_language'] = $this->request->post['config_language'];
		} else {
			$data['config_language'] = $this->config->get('config_language');
		}

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->post['config_admin_language'])) {
			$data['config_admin_language'] = $this->request->post['config_admin_language'];
		} else {
			$data['config_admin_language'] = $this->config->get('config_admin_language');
		}

		if (isset($this->request->post['config_frontend_currency'])) {
			$data['config_frontend_currency'] = $this->request->post['config_frontend_currency'];
		} else {
			$data['config_frontend_currency'] = $this->config->get('config_frontend_currency');
		}

		if (isset($this->request->post['config_currency'])) {
			$data['config_currency'] = $this->request->post['config_currency'];
		} else {
			$data['config_currency'] = $this->config->get('config_currency');
		}

		if (isset($this->request->post['config_currency_auto'])) {
			$data['config_currency_auto'] = $this->request->post['config_currency_auto'];
		} else {
			$data['config_currency_auto'] = $this->config->get('config_currency_auto');
		}

		if (isset($this->request->post['config_currency_engine'])) {
			$data['config_currency_engine'] = $this->request->post['config_currency_engine'];
		} else {
			$data['config_currency_engine'] = $this->config->get('config_currency_engine');
		}

		$data['currency_engine_extensions'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=currency', true);

		$this->load->model('localisation/currency');

		$data['currencies'] = $this->model_localisation_currency->getCurrencies();

		$data['currency_engines'] = array();

		$extension_codes = $this->model_setting_extension->getInstalled('currency');

		foreach ($extension_codes as $extension_code) {
			if ($this->config->get('currency_' . $extension_code . '_status')) {
				$this->load->language('extension/currency/' . $extension_code, 'currency_engine');

				$data['currency_engines'][] = array(
					'text'  => $this->language->get('currency_engine')->get('heading_title'),
					'value' => $extension_code
				);
			}
		}

		// процент отклонения курса
		if (isset($this->request->post['config_delta_threshold'])) {
			$data['config_delta_threshold'] = (float)$this->request->post['config_delta_threshold'];
		} elseif ($this->config->has('config_delta_threshold')) {
			$data['config_delta_threshold'] = (float)$this->config->get('config_delta_threshold');
		} else {
			$data['config_delta_threshold'] = 1.0; // значение по умолчанию
		}

		// условие показа предупреждения
		if (isset($this->request->post['config_alert_mode'])) {
			$data['config_alert_mode'] = $this->request->post['config_alert_mode'];
		} elseif ($this->config->has('config_alert_mode')) {
			$data['config_alert_mode'] = $this->config->get('config_alert_mode');
		} else {
			$data['config_alert_mode'] = 'any'; // значение по умолчанию
		}

		if (isset($this->request->post['config_symbol_left_space'])) {
			$data['config_symbol_left_space'] = $this->request->post['config_symbol_left_space'];
		} else {
			$data['config_symbol_left_space'] = $this->config->get('config_symbol_left_space');
		}
		if (isset($this->request->post['config_symbol_right_space'])) {
			$data['config_symbol_right_space'] = $this->request->post['config_symbol_right_space'];
		} else {
			$data['config_symbol_right_space'] = $this->config->get('config_symbol_right_space');
		}

		if (isset($this->request->post['config_length_class_id'])) {
			$data['config_length_class_id'] = (int)$this->request->post['config_length_class_id'];
		} else {
			$data['config_length_class_id'] = $this->config->get('config_length_class_id');
		}

		$this->load->model('localisation/length_class');

		$data['length_classes'] = $this->model_localisation_length_class->getLengthClasses();

		if (isset($this->request->post['config_weight_class_id'])) {
			$data['config_weight_class_id'] = (int)$this->request->post['config_weight_class_id'];
		} else {
			$data['config_weight_class_id'] = $this->config->get('config_weight_class_id');
		}

		$this->load->model('localisation/weight_class');

		$data['weight_classes'] = $this->model_localisation_weight_class->getWeightClasses();

		if (isset($this->request->post['config_limit_admin'])) {
			$data['config_limit_admin'] = $this->request->post['config_limit_admin'];
		} else {
			$data['config_limit_admin'] = $this->config->get('config_limit_admin');
		}

		if (isset($this->request->post['config_limit_autocomplete'])) {
			$data['config_limit_autocomplete'] = $this->request->post['config_limit_autocomplete'];
		} elseif ($this->config->get('config_limit_autocomplete')) {
			$data['config_limit_autocomplete'] = $this->config->get('config_limit_autocomplete');
		} else {
			$data['config_limit_autocomplete'] = 5;
		}

		if (isset($this->request->post['config_limit_filemanager'])) {
			$data['config_limit_filemanager'] = $this->request->post['config_limit_filemanager'];
		} elseif ($this->config->get('config_limit_filemanager')) {
			$data['config_limit_filemanager'] = $this->config->get('config_limit_filemanager');
		} else {
			$data['config_limit_filemanager'] = 16;
		}

		if (isset($this->request->post['config_product_count'])) {
			$data['config_product_count'] = (int)$this->request->post['config_product_count'];
		} else {
			$data['config_product_count'] = $this->config->get('config_product_count');
		}

		if (isset($this->request->post['config_review_status'])) {
			$data['config_review_status'] = (int)$this->request->post['config_review_status'];
		} else {
			$data['config_review_status'] = $this->config->get('config_review_status');
		}

		if (isset($this->request->post['config_review_guest'])) {
			$data['config_review_guest'] = (int)$this->request->post['config_review_guest'];
		} else {
			$data['config_review_guest'] = $this->config->get('config_review_guest');
		}

		if (isset($this->request->post['config_voucher_min'])) {
			$data['config_voucher_min'] = $this->request->post['config_voucher_min'];
		} else {
			$data['config_voucher_min'] = $this->config->get('config_voucher_min');
		}

		if (isset($this->request->post['config_voucher_max'])) {
			$data['config_voucher_max'] = $this->request->post['config_voucher_max'];
		} else {
			$data['config_voucher_max'] = $this->config->get('config_voucher_max');
		}

		if (isset($this->request->post['config_tax'])) {
			$data['config_tax'] = (int)$this->request->post['config_tax'];
		} else {
			$data['config_tax'] = $this->config->get('config_tax');
		}

		if (isset($this->request->post['config_tax_default'])) {
			$data['config_tax_default'] = $this->request->post['config_tax_default'];
		} else {
			$data['config_tax_default'] = $this->config->get('config_tax_default');
		}

		if (isset($this->request->post['config_tax_customer'])) {
			$data['config_tax_customer'] = $this->request->post['config_tax_customer'];
		} else {
			$data['config_tax_customer'] = $this->config->get('config_tax_customer');
		}

		if (isset($this->request->post['config_customer_online'])) {
			$data['config_customer_online'] = (int)$this->request->post['config_customer_online'];
		} else {
			$data['config_customer_online'] = $this->config->get('config_customer_online');
		}

		if (isset($this->request->post['config_customer_activity'])) {
			$data['config_customer_activity'] = (int)$this->request->post['config_customer_activity'];
		} else {
			$data['config_customer_activity'] = $this->config->get('config_customer_activity');
		}

		if (isset($this->request->post['config_customer_search'])) {
			$data['config_customer_search'] = (int)$this->request->post['config_customer_search'];
		} else {
			$data['config_customer_search'] = $this->config->get('config_customer_search');
		}

		if (isset($this->request->post['config_customer_group_id'])) {
			$data['config_customer_group_id'] = (int)$this->request->post['config_customer_group_id'];
		} else {
			$data['config_customer_group_id'] = $this->config->get('config_customer_group_id');
		}

		$this->load->model('customer/customer_group');

		$data['customer_groups'] = $this->model_customer_customer_group->getCustomerGroups();

		if (isset($this->request->post['config_customer_group_display'])) {
			$data['config_customer_group_display'] = $this->request->post['config_customer_group_display'];
		} elseif ($this->config->get('config_customer_group_display')) {
			$data['config_customer_group_display'] = $this->config->get('config_customer_group_display');
		} else {
			$data['config_customer_group_display'] = array();
		}

		if (isset($this->request->post['config_customer_price'])) {
			$data['config_customer_price'] = (int)$this->request->post['config_customer_price'];
		} else {
			$data['config_customer_price'] = $this->config->get('config_customer_price');
		}

		if (isset($this->request->post['config_login_attempts'])) {
			$data['config_login_attempts'] = (int)$this->request->post['config_login_attempts'];
		} elseif ($this->config->has('config_login_attempts')) {
			$data['config_login_attempts'] = $this->config->get('config_login_attempts');
		} else {
			$data['config_login_attempts'] = 5;
		}

		if (isset($this->request->post['config_account_id'])) {
			$data['config_account_id'] = (int)$this->request->post['config_account_id'];
		} else {
			$data['config_account_id'] = $this->config->get('config_account_id');
		}

		if (isset($this->request->post['config_cookie_id'])) {
			$data['config_cookie_id'] = $this->request->post['config_cookie_id'];
		} else {
			$data['config_cookie_id'] = $this->config->get('config_cookie_id');
		}

		$this->load->model('catalog/information');

		$data['informations'] = $this->model_catalog_information->getInformations();

		if (isset($this->request->post['config_cart_weight'])) {
			$data['config_cart_weight'] = (int)$this->request->post['config_cart_weight'];
		} else {
			$data['config_cart_weight'] = $this->config->get('config_cart_weight');
		}

		if (isset($this->request->post['config_checkout_guest'])) {
			$data['config_checkout_guest'] = (int)$this->request->post['config_checkout_guest'];
		} else {
			$data['config_checkout_guest'] = $this->config->get('config_checkout_guest');
		}

		if (isset($this->request->post['config_checkout_id'])) {
			$data['config_checkout_id'] = (int)$this->request->post['config_checkout_id'];
		} else {
			$data['config_checkout_id'] = $this->config->get('config_checkout_id');
		}

		if (isset($this->request->post['config_invoice_prefix'])) {
			$data['config_invoice_prefix'] = $this->request->post['config_invoice_prefix'];
		} elseif ($this->config->get('config_invoice_prefix')) {
			$data['config_invoice_prefix'] = $this->config->get('config_invoice_prefix');
		} else {
			$data['config_invoice_prefix'] = 'INV-' . date('Y') . '-00';
		}

		if (isset($this->request->post['config_order_status_id'])) {
			$data['config_order_status_id'] = (int)$this->request->post['config_order_status_id'];
		} else {
			$data['config_order_status_id'] = $this->config->get('config_order_status_id');
		}

		if (isset($this->request->post['config_processing_status'])) {
			$data['config_processing_status'] = $this->request->post['config_processing_status'];
		} elseif ($this->config->get('config_processing_status')) {
			$data['config_processing_status'] = $this->config->get('config_processing_status');
		} else {
			$data['config_processing_status'] = array();
		}

		if (isset($this->request->post['config_complete_status'])) {
			$data['config_complete_status'] = $this->request->post['config_complete_status'];
		} elseif ($this->config->get('config_complete_status')) {
			$data['config_complete_status'] = $this->config->get('config_complete_status');
		} else {
			$data['config_complete_status'] = array();
		}

		if (isset($this->request->post['config_fraud_status_id'])) {
			$data['config_fraud_status_id'] = (int)$this->request->post['config_fraud_status_id'];
		} else {
			$data['config_fraud_status_id'] = $this->config->get('config_fraud_status_id');
		}

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->request->post['config_api_id'])) {
			$data['config_api_id'] = (int)$this->request->post['config_api_id'];
		} else {
			$data['config_api_id'] = $this->config->get('config_api_id');
		}

		$this->load->model('user/api');

		$data['apis'] = $this->model_user_api->getApis();

		if (isset($this->request->post['config_stock_display'])) {
			$data['config_stock_display'] = (int)$this->request->post['config_stock_display'];
		} else {
			$data['config_stock_display'] = $this->config->get('config_stock_display');
		}

		if (isset($this->request->post['config_stock_warning'])) {
			$data['config_stock_warning'] = (int)$this->request->post['config_stock_warning'];
		} else {
			$data['config_stock_warning'] = $this->config->get('config_stock_warning');
		}

		if (isset($this->request->post['config_stock_checkout'])) {
			$data['config_stock_checkout'] = (int)$this->request->post['config_stock_checkout'];
		} else {
			$data['config_stock_checkout'] = $this->config->get('config_stock_checkout');
		}

		if (isset($this->request->post['config_stock_popup_status'])) {
			$data['config_stock_popup_status'] = (int)$this->request->post['config_stock_popup_status'];
		} else {
			$data['config_stock_popup_status'] = (int)$this->config->get('config_stock_popup_status');
		}

		if (isset($this->request->post['config_stock_popup_mode'])) {
			$data['config_stock_popup_mode'] = (string)$this->request->post['config_stock_popup_mode'];
		} elseif ($this->config->has('config_stock_popup_mode')) {
			$data['config_stock_popup_mode'] = (string)$this->config->get('config_stock_popup_mode');
		} else {
			$data['config_stock_popup_mode'] = 'checkout';
		}

		if (isset($this->request->post['config_stock_popup_routes'])) {
			$data['config_stock_popup_routes'] = (string)$this->request->post['config_stock_popup_routes'];
		} elseif ($this->config->has('config_stock_popup_routes')) {
			$data['config_stock_popup_routes'] = (string)$this->config->get('config_stock_popup_routes');
		} else {
			$data['config_stock_popup_routes'] = '';
		}

		if (isset($this->request->post['config_stock_popup_title']) && is_array($this->request->post['config_stock_popup_title'])) {
			$data['config_stock_popup_title'] = $this->request->post['config_stock_popup_title'];
		} else {
			$data['config_stock_popup_title'] = (array)$this->config->get('config_stock_popup_title');
		}

		if (isset($this->request->post['config_stock_popup_message']) && is_array($this->request->post['config_stock_popup_message'])) {
			$data['config_stock_popup_message'] = $this->request->post['config_stock_popup_message'];
		} else {
			$data['config_stock_popup_message'] = (array)$this->config->get('config_stock_popup_message');
		}

		if (isset($this->request->post['config_stock_popup_show_image'])) {
			$data['config_stock_popup_show_image'] = (int)$this->request->post['config_stock_popup_show_image'];
		} elseif ($this->config->has('config_stock_popup_show_image')) {
			$data['config_stock_popup_show_image'] = (int)$this->config->get('config_stock_popup_show_image');
		} else {
			$data['config_stock_popup_show_image'] = 1;
		}

		if (isset($this->request->post['config_stock_popup_show_model'])) {
			$data['config_stock_popup_show_model'] = (int)$this->request->post['config_stock_popup_show_model'];
		} elseif ($this->config->has('config_stock_popup_show_model')) {
			$data['config_stock_popup_show_model'] = (int)$this->config->get('config_stock_popup_show_model');
		} else {
			$data['config_stock_popup_show_model'] = 1;
		}

		if (isset($this->request->post['config_stock_popup_show_quantity'])) {
			$data['config_stock_popup_show_quantity'] = (int)$this->request->post['config_stock_popup_show_quantity'];
		} elseif ($this->config->has('config_stock_popup_show_quantity')) {
			$data['config_stock_popup_show_quantity'] = (int)$this->config->get('config_stock_popup_show_quantity');
		} else {
			$data['config_stock_popup_show_quantity'] = 1;
		}

		if (isset($this->request->post['config_affiliate_group_id'])) {
			$data['config_affiliate_group_id'] = (int)$this->request->post['config_affiliate_group_id'];
		} else {
			$data['config_affiliate_group_id'] = $this->config->get('config_affiliate_group_id');
		}

		if (isset($this->request->post['config_affiliate_approval'])) {
			$data['config_affiliate_approval'] = (int)$this->request->post['config_affiliate_approval'];
		} elseif ($this->config->has('config_affiliate_approval')) {
			$data['config_affiliate_approval'] = $this->config->get('config_affiliate_approval');
		} else {
			$data['config_affiliate_approval'] = '';
		}

		if (isset($this->request->post['config_affiliate_auto'])) {
			$data['config_affiliate_auto'] = (int)$this->request->post['config_affiliate_auto'];
		} elseif ($this->config->has('config_affiliate_auto')) {
			$data['config_affiliate_auto'] = $this->config->get('config_affiliate_auto');
		} else {
			$data['config_affiliate_auto'] = '';
		}

		if (isset($this->request->post['config_affiliate_commission'])) {
			$data['config_affiliate_commission'] = $this->request->post['config_affiliate_commission'];
		} elseif ($this->config->has('config_affiliate_commission')) {
			$data['config_affiliate_commission'] = $this->config->get('config_affiliate_commission');
		} else {
			$data['config_affiliate_commission'] = '5.00';
		}

		if (isset($this->request->post['config_affiliate_id'])) {
			$data['config_affiliate_id'] = (int)$this->request->post['config_affiliate_id'];
		} else {
			$data['config_affiliate_id'] = $this->config->get('config_affiliate_id');
		}

		if (isset($this->request->post['config_return_id'])) {
			$data['config_return_id'] = (int)$this->request->post['config_return_id'];
		} else {
			$data['config_return_id'] = $this->config->get('config_return_id');
		}

		if (isset($this->request->post['config_return_status_id'])) {
			$data['config_return_status_id'] = (int)$this->request->post['config_return_status_id'];
		} else {
			$data['config_return_status_id'] = $this->config->get('config_return_status_id');
		}

		$this->load->model('localisation/return_status');

		$data['return_statuses'] = $this->model_localisation_return_status->getReturnStatuses();

		if (isset($this->request->post['config_captcha'])) {
			$data['config_captcha'] = $this->request->post['config_captcha'];
		} else {
			$data['config_captcha'] = $this->config->get('config_captcha');
		}

		$this->load->model('setting/extension');

		$data['captchas'] = array();

		// Get a list of installed captchas
		$extensions = $this->model_setting_extension->getInstalled('captcha');

		foreach ($extensions as $code) {
			$this->load->language('extension/captcha/' . $code, 'extension');

			if ($this->config->get('captcha_' . $code . '_status')) {
				$data['captchas'][] = array(
					'text'  => $this->language->get('extension')->get('heading_title'),
					'value' => $code
				);
			}
		}

		if (isset($this->request->post['config_captcha_page'])) {
			$data['config_captcha_page'] = $this->request->post['config_captcha_page'];
		} elseif ($this->config->has('config_captcha_page')) {
			$data['config_captcha_page'] = $this->config->get('config_captcha_page');
		} else {
			$data['config_captcha_page'] = array();
		}

		$data['captcha_pages'] = array();

		$data['captcha_pages'][] = array(
			'text'  => $this->language->get('text_register'),
			'value' => 'register'
		);

		$data['captcha_pages'][] = array(
			'text'  => $this->language->get('text_guest'),
			'value' => 'guest'
		);

		$data['captcha_pages'][] = array(
			'text'  => $this->language->get('text_review'),
			'value' => 'review'
		);

		$data['captcha_pages'][] = array(
			'text'  => $this->language->get('text_return'),
			'value' => 'return'
		);

		$data['captcha_pages'][] = array(
			'text'  => $this->language->get('text_contact'),
			'value' => 'contact'
		);

		// Images
		if (isset($this->request->post['config_logo'])) {
			$data['config_logo'] = $this->request->post['config_logo'];
		} else {
			$data['config_logo'] = $this->config->get('config_logo');
		}

		if (isset($this->request->post['config_logo']) && is_file(DIR_IMAGE . $this->request->post['config_logo'])) {
			$data['logo'] = $this->model_tool_image->resize($this->request->post['config_logo'], 100, 100);
		} elseif ($this->config->get('config_logo') && is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
			$data['logo'] = $this->model_tool_image->resize($this->config->get('config_logo'), 100, 100);
		} else {
			$data['logo'] = $data['placeholder'];
		}

		// Загружаем значение для формы
		if (isset($this->request->post['config_logo_fallback_mode'])) {
			$data['config_logo_fallback_mode'] = $this->request->post['config_logo_fallback_mode'];
		} else {
			$data['config_logo_fallback_mode'] = $this->config->get('config_logo_fallback_mode');
		}

		if (isset($this->request->post['config_icon'])) {
			$data['config_icon'] = $this->request->post['config_icon'];
		} else {
			$data['config_icon'] = $this->config->get('config_icon');
		}

		if (isset($this->request->post['config_icon']) && is_file(DIR_IMAGE . $this->request->post['config_icon'])) {
			$data['icon'] = $this->model_tool_image->resize($this->request->post['config_icon'], 100, 100);
		} elseif ($this->config->get('config_icon') && is_file(DIR_IMAGE . $this->config->get('config_icon'))) {
			$data['icon'] = $this->model_tool_image->resize($this->config->get('config_icon'), 100, 100);
		} else {
			$data['icon'] = $data['placeholder'];
		}

		if (isset($this->request->post['config_mail_engine'])) {
			$data['config_mail_engine'] = $this->request->post['config_mail_engine'];
		} else {
			$data['config_mail_engine'] = $this->config->get('config_mail_engine');
		}

		if (isset($this->request->post['config_mail_parameter'])) {
			$data['config_mail_parameter'] = $this->request->post['config_mail_parameter'];
		} else {
			$data['config_mail_parameter'] = $this->config->get('config_mail_parameter');
		}

		if (isset($this->request->post['config_mail_smtp_hostname'])) {
			$data['config_mail_smtp_hostname'] = $this->request->post['config_mail_smtp_hostname'];
		} else {
			$data['config_mail_smtp_hostname'] = $this->config->get('config_mail_smtp_hostname');
		}

		if (isset($this->request->post['config_mail_smtp_username'])) {
			$data['config_mail_smtp_username'] = $this->request->post['config_mail_smtp_username'];
		} else {
			$data['config_mail_smtp_username'] = $this->config->get('config_mail_smtp_username');
		}

		if (isset($this->request->post['config_mail_smtp_password'])) {
			$data['config_mail_smtp_password'] = $this->request->post['config_mail_smtp_password'];
		} else {
			$data['config_mail_smtp_password'] = $this->config->get('config_mail_smtp_password');
		}

		if (isset($this->request->post['config_mail_smtp_port'])) {
			$data['config_mail_smtp_port'] = (int)$this->request->post['config_mail_smtp_port'];
		} elseif ($this->config->has('config_mail_smtp_port')) {
			$data['config_mail_smtp_port'] = $this->config->get('config_mail_smtp_port');
		} else {
			$data['config_mail_smtp_port'] = 25;
		}

		if (isset($this->request->post['config_mail_smtp_timeout'])) {
			$data['config_mail_smtp_timeout'] = (int)$this->request->post['config_mail_smtp_timeout'];
		} elseif ($this->config->has('config_mail_smtp_timeout')) {
			$data['config_mail_smtp_timeout'] = $this->config->get('config_mail_smtp_timeout');
		} else {
			$data['config_mail_smtp_timeout'] = 5;
		}

		if (isset($this->request->post['config_mail_alert'])) {
			$data['config_mail_alert'] = $this->request->post['config_mail_alert'];
		} elseif ($this->config->has('config_mail_alert')) {
			$data['config_mail_alert'] = $this->config->get('config_mail_alert');
		} else {
			$data['config_mail_alert'] = array();
		}

		$data['mail_alerts'] = array();

		$data['mail_alerts'][] = array(
			'text'  => $this->language->get('text_mail_account'),
			'value' => 'account'
		);

		$data['mail_alerts'][] = array(
			'text'  => $this->language->get('text_mail_affiliate'),
			'value' => 'affiliate'
		);

		$data['mail_alerts'][] = array(
			'text'  => $this->language->get('text_mail_order'),
			'value' => 'order'
		);

		$data['mail_alerts'][] = array(
			'text'  => $this->language->get('text_mail_review'),
			'value' => 'review'
		);

		if (isset($this->request->post['config_mail_alert_email'])) {
			$data['config_mail_alert_email'] = $this->request->post['config_mail_alert_email'];
		} else {
			$data['config_mail_alert_email'] = $this->config->get('config_mail_alert_email');
		}

		if (isset($this->request->post['config_secure'])) {
			$data['config_secure'] = (int)$this->request->post['config_secure'];
		} else {
			$data['config_secure'] = $this->config->get('config_secure');
		}

		if (isset($this->request->post['config_shared'])) {
			$data['config_shared'] = (int)$this->request->post['config_shared'];
		} else {
			$data['config_shared'] = $this->config->get('config_shared');
		}

		if (isset($this->request->post['config_robots'])) {
			$data['config_robots'] = $this->request->post['config_robots'];
		} else {
			$data['config_robots'] = $this->config->get('config_robots');
		}

		if (isset($this->request->post['config_seo_url'])) {
			$data['config_seo_url'] = $this->request->post['config_seo_url'];
		} else {
			$data['config_seo_url'] = $this->config->get('config_seo_url');
		}

		if (isset($this->request->post['config_canonical_method'])) {
			$data['config_canonical_method'] = $this->request->post['config_canonical_method'];
		} else {
			$data['config_canonical_method'] = $this->config->get('config_canonical_method');
		}

		if (isset($this->request->post['config_canonical_self'])) {
			$data['config_canonical_self'] = $this->request->post['config_canonical_self'];
		} else {
			$data['config_canonical_self'] = $this->config->get('config_canonical_self');
		}

		if (isset($this->request->post['config_add_prevnext'])) {
			$data['config_add_prevnext'] = $this->request->post['config_add_prevnext'];
		} else {
			$data['config_add_prevnext'] = $this->config->get('config_add_prevnext');
		}

		if (isset($this->request->post['config_noindex_status'])) {
			$data['config_noindex_status'] = $this->request->post['config_noindex_status'];
		} else {
			$data['config_noindex_status'] = $this->config->get('config_noindex_status');
		}

		if (isset($this->request->post['config_noindex_disallow_params'])) {
			$data['config_noindex_disallow_params'] = $this->request->post['config_noindex_disallow_params'];
		} elseif ($this->config->get('config_noindex_disallow_params')) {
			$data['config_noindex_disallow_params'] = $this->config->get('config_noindex_disallow_params');
		} else {
			$data['config_noindex_disallow_params'] = '';
		}

		// Server
		if (isset($this->request->post['config_file_max_size'])) {
			$data['config_file_max_size'] = $this->request->post['config_file_max_size'];
		} elseif ($this->config->get('config_file_max_size')) {
			$data['config_file_max_size'] = $this->config->get('config_file_max_size');
		} else {
			$data['config_file_max_size'] = 20;
		}

		if (isset($this->request->post['config_file_ext_allowed'])) {
			$data['config_file_ext_allowed'] = $this->request->post['config_file_ext_allowed'];
		} else {
			$data['config_file_ext_allowed'] = $this->config->get('config_file_ext_allowed');
		}

		if (isset($this->request->post['config_file_mime_allowed'])) {
			$data['config_file_mime_allowed'] = $this->request->post['config_file_mime_allowed'];
		} else {
			$data['config_file_mime_allowed'] = $this->config->get('config_file_mime_allowed');
		}

		if (isset($this->request->post['config_maintenance'])) {
			$data['config_maintenance'] = (int)$this->request->post['config_maintenance'];
		} else {
			$data['config_maintenance'] = $this->config->get('config_maintenance');
		}

		if (isset($this->request->post['config_password'])) {
			$data['config_password'] = $this->request->post['config_password'];
		} else {
			$data['config_password'] = $this->config->get('config_password');
		}

		if (isset($this->request->post['config_encryption'])) {
			$data['config_encryption'] = $this->request->post['config_encryption'];
		} else {
			$data['config_encryption'] = $this->config->get('config_encryption');
		}

		if (isset($this->request->post['config_compression'])) {
			$data['config_compression'] = (int)$this->request->post['config_compression'];
		} else {
			$data['config_compression'] = $this->config->get('config_compression');
		}

		if (isset($this->request->post['config_editor_default'])) {
			$data['config_editor_default'] = $this->request->post['config_editor_default'];
		} else {
			$data['config_editor_default'] = $this->config->get('config_editor_default');
		}

		if (isset($this->request->post['config_error_display'])) {
			$data['config_error_display'] = (int)$this->request->post['config_error_display'];
		} else {
			$data['config_error_display'] = $this->config->get('config_error_display');
		}

		if (isset($this->request->post['config_error_log'])) {
			$data['config_error_log'] = (int)$this->request->post['config_error_log'];
		} else {
			$data['config_error_log'] = $this->config->get('config_error_log');
		}

		if (isset($this->request->post['config_error_filename'])) {
			$data['config_error_filename'] = $this->request->post['config_error_filename'];
		} else {
			$data['config_error_filename'] = $this->config->get('config_error_filename');
		}

		if (isset($this->request->post['config_seo_pro'])) {
			$data['config_seo_pro'] = $this->request->post['config_seo_pro'];
		} else {
			$data['config_seo_pro'] = $this->config->get('config_seo_pro');
		}

		if (isset($this->request->post['config_seo_url_include_path'])) {
			$data['config_seo_url_include_path'] = $this->request->post['config_seo_url_include_path'];
		} else {
			$data['config_seo_url_include_path'] = $this->config->get('config_seo_url_include_path');
		}

		if (isset($this->request->post['config_seo_url_cache'])) {
			$data['config_seo_url_cache'] = $this->request->post['config_seo_url_cache'];
		} else {
			$data['config_seo_url_cache'] = $this->config->get('config_seo_url_cache');
		}

		if (isset($this->request->post['config_page_postfix'])) {
			$data['config_page_postfix'] = $this->request->post['config_page_postfix'];
		} else {
			$data['config_page_postfix'] = $this->config->get('config_page_postfix');
		}

		if (isset($this->request->post['config_seopro_addslash'])) {
			$data['config_seopro_addslash'] = $this->request->post['config_seopro_addslash'];
		} elseif ($this->config->has('config_seopro_addslash')) {
			$data['config_seopro_addslash'] = $this->config->get('config_seopro_addslash');
		}

		if (isset($this->request->post['config_seopro_lowercase'])) {
			$data['config_seopro_lowercase'] = $this->request->post['config_seopro_lowercase'];
		} elseif ($this->config->has('config_seopro_lowercase')) {
			$data['config_seopro_lowercase'] = $this->config->get('config_seopro_lowercase');
		}

		if (isset($this->request->post['config_valide_params'])) {
			$data['config_valide_params'] = $this->request->post['config_valide_params'];
		} elseif ($this->config->get('config_valide_params')) {
			$data['config_valide_params'] = $this->config->get('config_valide_params');
		} else {
			$data['config_valide_params'] = "block\r\nfrommarket\r\ngclid\r\nfbclid\r\nttclid\r\ngad_source\r\nsrsltid\r\nmsclkid\r\nkeyword\r\nlist_type\r\nopenstat\r\nopenstat_service\r\nopenstat_campaign\r\nopenstat_ad\r\nopenstat_source\r\nposition\r\nsource\r\ntracking\r\ntype\r\nyclid\r\nymclid\r\nuri\r\nurltype\r\nutm_source\r\nutm_medium\r\nutm_campaign\r\nutm_term\r\nutm_content\r\nutm_referrer";
		}

		$data['is_in_subfolder'] = false;

		// Проверка установки в подпапку
		// Получаем корневой путь
		$base_path = str_replace('\\', '/', dirname(DIR_APPLICATION));
		$root_path = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
		$subfolder_path = ltrim(substr($base_path, strlen($root_path)), '/');

		if (!empty($subfolder_path)) {
			$data['is_in_subfolder'] = true;
			$data['text_information'] = sprintf($this->language->get('text_information'), $subfolder_path, $subfolder_path);
		}
		// Конец Проверка установки в подпапку

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('setting/setting', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'setting/setting')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['config_meta_title']) {
			$this->error['meta_title'] = $this->language->get('error_meta_title');
		}

		if (!$this->request->post['config_name']) {
			$this->error['name'] = $this->language->get('error_name');
		}

		if ((utf8_strlen($this->request->post['config_owner']) < 3) || (utf8_strlen($this->request->post['config_owner']) > 64)) {
			$this->error['owner'] = $this->language->get('error_owner');
		}

		if ((utf8_strlen($this->request->post['config_address']) < 3) || (utf8_strlen($this->request->post['config_address']) > 256)) {
			$this->error['address'] = $this->language->get('error_address');
		}

		if ((utf8_strlen($this->request->post['config_email']) > 96) || !filter_var($this->request->post['config_email'], FILTER_VALIDATE_EMAIL)) {
			$this->error['email'] = $this->language->get('error_email');
		}

		if ((utf8_strlen($this->request->post['config_telephone']) < 3) || (utf8_strlen($this->request->post['config_telephone']) > 32)) {
			$this->error['telephone'] = $this->language->get('error_telephone');
		}

		if (!empty($this->request->post['config_customer_group_display']) && !in_array($this->request->post['config_customer_group_id'], $this->request->post['config_customer_group_display'])) {
			$this->error['customer_group_display'] = $this->language->get('error_customer_group_display');
		}

		if (!$this->request->post['config_limit_admin']) {
			$this->error['limit_admin'] = $this->language->get('error_limit');
		}

		if (!$this->request->post['config_limit_autocomplete']) {
			$this->error['limit_autocomplete'] = $this->language->get('error_limit');
		}

		if (!$this->request->post['config_limit_filemanager']) {
			$this->error['limit_filemanager'] = $this->language->get('error_limit');
		}

		if ($this->request->post['config_login_attempts'] < 1) {
			$this->error['login_attempts'] = $this->language->get('error_login_attempts');
		}

		if (!$this->request->post['config_voucher_min']) {
			$this->error['voucher_min'] = $this->language->get('error_voucher_min');
		}

		if (!$this->request->post['config_voucher_max']) {
			$this->error['voucher_max'] = $this->language->get('error_voucher_max');
		}

		if (!isset($this->request->post['config_processing_status'])) {
			$this->error['processing_status'] = $this->language->get('error_processing_status');
		}

		if (!isset($this->request->post['config_complete_status'])) {
			$this->error['complete_status'] = $this->language->get('error_complete_status');
		}

		if (!$this->request->post['config_error_filename']) {
			$this->error['log'] = $this->language->get('error_log_required');
		} elseif (preg_match('/\.\.[\/\\\]?/', $this->request->post['config_error_filename'])) {
			$this->error['log'] = $this->language->get('error_log_invalid');
		} elseif (substr($this->request->post['config_error_filename'], strrpos($this->request->post['config_error_filename'], '.')) != '.log') {
			$this->error['log'] = $this->language->get('error_log_extension');
		}

		if ((utf8_strlen($this->request->post['config_encryption']) < 32) || (utf8_strlen($this->request->post['config_encryption']) > 1024)) {
			$this->error['encryption'] = $this->language->get('error_encryption');
		}

		if (isset($this->request->post['config_mail_engine']) && $this->request->post['config_mail_engine'] === 'smtp') {
			$hostname = isset($this->request->post['config_mail_smtp_hostname']) ? trim((string)$this->request->post['config_mail_smtp_hostname']) : '';
			$username = isset($this->request->post['config_mail_smtp_username']) ? trim((string)$this->request->post['config_mail_smtp_username']) : '';
			$password = isset($this->request->post['config_mail_smtp_password']) ? (string)$this->request->post['config_mail_smtp_password'] : '';
			$port = isset($this->request->post['config_mail_smtp_port']) ? (int)$this->request->post['config_mail_smtp_port'] : 0;
			$timeout = isset($this->request->post['config_mail_smtp_timeout']) ? (int)$this->request->post['config_mail_smtp_timeout'] : 0;

			if ($hostname === '') {
				$this->error['mail_smtp_hostname'] = $this->language->get('error_smtp_test_hostname');
			}

			if ($port < 1 || $port > 65535) {
				$this->error['mail_smtp_port'] = $this->language->get('error_smtp_test_port');
			}

			if ($timeout < 1 || $timeout > 300) {
				$this->error['mail_smtp_timeout'] = $this->language->get('error_smtp_test_timeout');
			}

			if (($username === '') xor ($password === '')) {
				$this->error['mail_smtp_credentials'] = $this->language->get('error_smtp_test_credentials');
			}
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		$original = !empty($this->request->post['config_favicon_original']);

		if (!$original && !empty($this->request->post['config_favicon_color'])) {
			if (!preg_match('/^#([A-Fa-f0-9]{6})$/', $this->request->post['config_favicon_color'])) {
				$this->error['favicon_color'] = $this->language->get('error_favicon_color');
			}
		}

		if (!empty($this->request->post['config_svg_icon'])) {
			$path = $this->request->post['config_svg_icon'];
			$full = DIR_IMAGE . $path;

			if (!is_file($full)) {
				$this->error['svg_icon'] = $this->language->get('error_svg_icon');
			} else {
				$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

				// Генератор favicon работает только с SVG
				if ($ext !== 'svg') {
					$this->error['svg_icon'] = $this->language->get('error_svg_icon');
				}
			}
		}

		if (empty($this->request->post['config_file_max_size']) || (int)$this->request->post['config_file_max_size'] < 1) {
			$this->error['file_max_size'] = $this->language->get('error_file_max_size');
		} else {
			$config_file_max_size = (int)$this->request->post['config_file_max_size'];
			$server_max_size = $this->getServerUploadMaxBytes();

			if ($server_max_size > 0 && ($config_file_max_size * 1024 * 1024) > $server_max_size) {
				$this->error['file_max_size'] = sprintf(
					$this->language->get('error_upload_size'),
					$this->formatBytesAsMegabytes($server_max_size)
				);
			}
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}

	public function svgPreview() {
		if (
			!isset($this->session->data['user_token']) ||
			!isset($this->request->get['user_token']) ||
			$this->request->get['user_token'] !== $this->session->data['user_token']
		) {
			$this->response->addHeader('HTTP/1.1 403 Forbidden');
			return;
		}

		$path = isset($this->request->get['path']) ? (string)$this->request->get['path'] : '';
		$color = isset($this->request->get['color']) ? (string)$this->request->get['color'] : '#000000';
		$original = !empty($this->request->get['original']);

		$this->load->library('svg_preview');

		$svg = $this->svg_preview->render($path, $color, $original);

		if ($svg === false) {
			$this->response->addHeader('HTTP/1.1 404 Not Found');
			return;
		}

		$this->response->addHeader('Content-Type: image/svg+xml; charset=utf-8');
		$this->response->addHeader('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
		$this->response->addHeader('Pragma: no-cache');
		$this->response->setOutput($svg);
	}

	public function theme() {
		if ($this->request->server['HTTPS']) {
			$server = HTTPS_CATALOG;
		} else {
			$server = HTTP_CATALOG;
		}

		// This is only here for compatibility with old themes.
		if ($this->request->get['theme'] == 'theme_default') {
			$theme = $this->config->get('theme_default_directory');
		} else {
			$theme = basename($this->request->get['theme']);
		}

		if (is_file(DIR_CATALOG . 'view/theme/' . $theme . '/image/' . $theme . '.png')) {
			$this->response->setOutput($server . 'catalog/view/theme/' . $theme . '/image/' . $theme . '.png');
		} else {
			$this->response->setOutput($server . 'image/no_image.png');
		}
	}


	public function testSmtp() {
		$this->load->language('setting/setting');

		$json = array();

		if (!$this->user->hasPermission('modify', 'setting/setting')) {
			$json['error'] = $this->language->get('error_permission');
		} elseif ($this->request->server['REQUEST_METHOD'] != 'POST') {
			$json['error'] = $this->language->get('error_smtp_test_method');
		} else {
			$hostname = isset($this->request->post['config_mail_smtp_hostname']) ? trim((string)$this->request->post['config_mail_smtp_hostname']) : '';
			$username = isset($this->request->post['config_mail_smtp_username']) ? trim((string)$this->request->post['config_mail_smtp_username']) : '';
			$password = isset($this->request->post['config_mail_smtp_password']) ? (string)$this->request->post['config_mail_smtp_password'] : '';
			$port = isset($this->request->post['config_mail_smtp_port']) ? (int)$this->request->post['config_mail_smtp_port'] : 25;
			$timeout = isset($this->request->post['config_mail_smtp_timeout']) ? (int)$this->request->post['config_mail_smtp_timeout'] : 5;
			$email = isset($this->request->post['config_email']) ? trim((string)$this->request->post['config_email']) : (string)$this->config->get('config_email');
			$store_name = isset($this->request->post['config_name']) ? trim((string)$this->request->post['config_name']) : (string)$this->config->get('config_name');

			if ($hostname === '') {
				$json['error'] = $this->language->get('error_smtp_test_hostname');
			} elseif ($port < 1 || $port > 65535) {
				$json['error'] = $this->language->get('error_smtp_test_port');
			} elseif ($timeout < 1 || $timeout > 300) {
				$json['error'] = $this->language->get('error_smtp_test_timeout');
			} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$json['error'] = $this->language->get('error_smtp_test_email');
			} elseif (($username === '') xor ($password === '')) {
				$json['error'] = $this->language->get('error_smtp_test_credentials');
			} else {
				try {
					$mail = new \Mail('smtp');
					$mail->smtp_hostname = $hostname;
					$mail->smtp_username = $username;
					$mail->smtp_password = html_entity_decode($password, ENT_QUOTES, 'UTF-8');
					$mail->smtp_port = $port;
					$mail->smtp_timeout = $timeout;

					$mail->setTo($email);
					$mail->setFrom($email);
					$mail->setSender($store_name !== '' ? html_entity_decode($store_name, ENT_QUOTES, 'UTF-8') : $email);
					$mail->setSubject($this->language->get('text_smtp_test_subject'));
					$mail->setText(sprintf($this->language->get('text_smtp_test_message'), $hostname, $port));

					$result = $mail->test(true);

					$json['success'] = sprintf($this->language->get('text_smtp_test_success'), $email);
					$json['details'] = array(
						'hostname' => $result['hostname'],
						'port'     => $result['port'],
						'tls'      => !empty($result['tls']),
						'steps'    => $result['steps']
					);
				} catch (\Exception $e) {
					$json['error'] = sprintf($this->language->get('error_smtp_test_failed'), $e->getMessage());
				}
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function maintenance() {
		$json = array();

		$this->load->language('setting/setting');

		if (!$this->user->hasPermission('modify', 'setting/setting')) {
			$json['error'] = $this->language->get('error_permission');
		} elseif ($this->request->server['REQUEST_METHOD'] != 'POST') {
			$json['error'] = 'Invalid request method';
		} elseif (!isset($this->request->post['maintenance'])) {
			$json['error'] = 'Invalid maintenance state';
		} else {
			$maintenance = (int)$this->request->post['maintenance'];

			if ($maintenance !== 0 && $maintenance !== 1) {
				$json['error'] = 'Invalid maintenance state';
			} else {
				$this->load->model('setting/setting');

				$this->model_setting_setting->editSettingValue(
					'config',
					'config_maintenance',
					$maintenance
				);

				$json['success'] = true;
				$json['maintenance'] = $maintenance;
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}


	/**
	 * Convert a PHP shorthand size (for example 512K, 128M or 2G) to bytes.
	 * A value of 0 means that the corresponding PHP limit is disabled/unlimited.
	 */
	private function iniSizeToBytes($value) {
		$value = trim((string)$value);

		if ($value === '') {
			return 0;
		}

		$number = (float)$value;
		$unit = strtolower(substr($value, -1));

		switch ($unit) {
			case 'g':
				$number *= 1024;
				// no break
			case 'm':
				$number *= 1024;
				// no break
			case 'k':
				$number *= 1024;
		}

		return (int)round($number);
	}

	/**
	 * Return the smallest active PHP request/file upload limit.
	 */
	private function getServerUploadMaxBytes() {
		$limits = array();

		$upload_max_filesize = $this->iniSizeToBytes(ini_get('upload_max_filesize'));
		$post_max_size = $this->iniSizeToBytes(ini_get('post_max_size'));

		if ($upload_max_filesize > 0) {
			$limits[] = $upload_max_filesize;
		}

		if ($post_max_size > 0) {
			$limits[] = $post_max_size;
		}

		return $limits ? min($limits) : 0;
	}

	private function formatBytesAsMegabytes($bytes) {
		$megabytes = $bytes / 1024 / 1024;

		if ((int)$megabytes == $megabytes) {
			return (string)(int)$megabytes;
		}

		return rtrim(rtrim(number_format($megabytes, 2, '.', ''), '0'), '.');
	}

}
