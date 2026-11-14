<?php
/**
 * Per-user administration interface language preference.
 *
 * Only active, installed language packs may be selected. No OCMOD or schema
 * migration is needed: the standard setting table stores preferences by user ID.
 */
class ControllerCommonPersonalLanguage extends Controller {
    public function index() {
        $this->response->addHeader('Content-Type: application/json; charset=utf-8');
        $json = array();

        if (!$this->user->isLogged()
            || !isset($this->request->get['user_token'], $this->session->data['user_token'])
            || !hash_equals((string)$this->session->data['user_token'], (string)$this->request->get['user_token'])) {
            $this->response->addHeader('HTTP/1.1 403 Forbidden');
            $json['error'] = 'Access denied';
        } elseif ($this->request->server['REQUEST_METHOD'] !== 'POST') {
            $this->response->addHeader('HTTP/1.1 405 Method Not Allowed');
            $json['error'] = 'POST required';
        } else {
            $code = isset($this->request->post['code']) && is_string($this->request->post['code'])
                ? $this->request->post['code'] : '';

            $language = $this->db->query("SELECT code FROM `" . DB_PREFIX . "language` WHERE code = '" . $this->db->escape($code) . "' AND status = 1 LIMIT 1");

            if (!$language->num_rows || !is_file(DIR_LANGUAGE . $code . '/common/header.php')) {
                $json['error'] = 'Language is not installed or is disabled';
            } else {
                $key = 'admin_personal_language_' . (int)$this->user->getId();

                // User ID is server-derived, never accepted from a request.
                $this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE store_id = 0 AND `code` = 'admin_personal_language' AND `key` = '" . $key . "'");
                $this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET store_id = 0, `code` = 'admin_personal_language', `key` = '" . $key . "', value = '" . $this->db->escape($code) . "', serialized = 0");

                $json['success'] = true;
            }
        }

        $this->response->setOutput(json_encode($json));
    }
}
