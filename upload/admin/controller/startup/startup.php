<?php
class ControllerStartupStartup extends Controller {
	public function index() {
		// Settings
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE store_id = '0'");

		foreach ($query->rows as $setting) {
			if (!$setting['serialized']) {
				$this->config->set($setting['key'], $setting['value']);
			} else {
				$this->config->set($setting['key'], json_decode($setting['value'], true));
			}
		}

		// Apply the store timezone after its settings have been loaded.
		$timezone = frameworkNormalizeTimezone($this->config->get('config_timezone'));
		date_default_timezone_set($timezone);

		// Use a numeric offset so MySQL/MariaDB timezone tables are not required.
		$this->db->query("SET time_zone = '" . $this->db->escape(date('P')) . "'");

		// Theme
		$this->config->set('template_cache', $this->config->get('developer_theme'));

		// Admin language
		$admin_language = (string)$this->config->get('config_admin_language');

		if ($admin_language === '') {
			$admin_language = 'en-gb';
		}

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "language` WHERE code = '" . $this->db->escape($admin_language) . "'");

		if ($query->num_rows) {
			$this->config->set('config_language_id', $query->row['language_id']);
		}

		// Language
		$language = new Language($admin_language);
		$language->load($admin_language);
		$this->registry->set('language', $language);

		// Customer
		$this->registry->set('customer', new Cart\Customer($this->registry));

		// Currency
		$this->registry->set('currency', new Cart\Currency($this->registry));

		// Tax
		$this->registry->set('tax', new Cart\Tax($this->registry));

		if ($this->config->get('config_tax_default') === 'shipping') {
			$this->tax->setShippingAddress($this->config->get('config_country_id'), (int)$this->config->get('config_zone_id'));
		}

		if ($this->config->get('config_tax_default') === 'payment') {
			$this->tax->setPaymentAddress($this->config->get('config_country_id'), (int)$this->config->get('config_zone_id'));
		}

		$this->tax->setStoreAddress($this->config->get('config_country_id'), (int)$this->config->get('config_zone_id'));

		// Weight
		$this->registry->set('weight', new Cart\Weight($this->registry));

		// Length
		$this->registry->set('length', new Cart\Length($this->registry));

		// Cart
		$this->registry->set('cart', new Cart\Cart($this->registry));

		// Encryption
		$this->registry->set('encryption', new Encryption());
	}
}
