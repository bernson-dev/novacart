<?php
class ModelSettingExtension extends Model {
	public function getExtensions($type) {
		$query = $this->db->query("
            SELECT extension_id, type, code
            FROM `" . DB_PREFIX . "extension`
            WHERE `type` = '" . $this->db->escape($type) . "'
        ");
		return $query->rows;
	}

	public function getExtensionByCode($type, $code) {
		$query = $this->db->query("
            SELECT extension_id, type, code
            FROM `" . DB_PREFIX . "extension`
            WHERE `type` = '" . $this->db->escape($type) . "'
              AND `code` = '" . $this->db->escape($code) . "'
        ");
		return $query->row;
	}
}
