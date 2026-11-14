<?php
class ControllerStartupLogin extends Controller {
	public function index() {
		$route = isset($this->request->get['route']) ? $this->request->get['route'] : '';

		$ignore = array(
			'common/login',
			'common/forgotten',
			'common/reset'
		);

		// User
		$this->registry->set('user', new Cart\User($this->registry));

		if (!$this->user->isLogged() && !in_array($route, $ignore)) {
			return new Action('common/login');
		}

		if (isset($this->request->get['route'])) {
			$ignore = array(
				'common/login',
				'common/logout',
				'common/forgotten',
				'common/reset',
				'error/not_found',
				'error/permission'
			);

			if (!in_array($route, $ignore) && (!isset($this->request->get['user_token']) || !isset($this->session->data['user_token']) || ($this->request->get['user_token'] != $this->session->data['user_token']))) {
				return new Action('common/login');
			}
		} else {
			if (!isset($this->request->get['user_token']) || !isset($this->session->data['user_token']) || ($this->request->get['user_token'] != $this->session->data['user_token'])) {
				return new Action('common/login');
			}
		}
        // Personal admin language is resolved after the authenticated user is known.
        // Preferences live in the existing setting table, keyed by user ID.
        if ($this->user->isLogged()) {
            $preference = $this->db->query("SELECT value FROM `" . DB_PREFIX . "setting` WHERE store_id = 0 AND `code` = 'admin_personal_language' AND `key` = 'admin_personal_language_" . (int)$this->user->getId() . "' LIMIT 1");

            if ($preference->num_rows && $preference->row['value'] !== '') {
                $code = (string)$preference->row['value'];
                $available = $this->db->query("SELECT language_id, code FROM `" . DB_PREFIX . "language` WHERE code = '" . $this->db->escape($code) . "' AND status = 1 LIMIT 1");

                if ($available->num_rows && is_file(DIR_LANGUAGE . $code . '/common/header.php')) {
                    $this->config->set('config_admin_language', $code);
                    $this->config->set('config_language_id', (int)$available->row['language_id']);

                    $language = new Language($code);
                    $language->load($code);
                    $this->registry->set('language', $language);
                }
            }
        }
	}
}
