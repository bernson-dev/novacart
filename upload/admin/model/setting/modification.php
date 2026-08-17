<?php
class ModelSettingModification extends Model {
	private $column_exists_cache = array();

	private function decodeXml($xml) {
		if ($xml === null) {
			return '';
		}

		$xml = (string)$xml;

		// Если XML пришёл из textarea после экранирования HTML.
		if (strpos($xml, '&lt;') !== false || strpos($xml, '&gt;') !== false || strpos($xml, '&quot;') !== false) {
			$xml = html_entity_decode($xml, ENT_QUOTES, 'UTF-8');
		}

		return $xml;
	}

	private function parseMeta($xml) {
		$meta = array(
		'name'    => '',
		'code'    => '',
		'author'  => '',
		'version' => '',
		'link'    => ''
		);

		$xml = $this->decodeXml($xml);

		if ($xml === '') {
			return $meta;
		}

		$use_errors = libxml_use_internal_errors(true);
		$object = simplexml_load_string($xml);

		if ($object !== false) {
			foreach ($meta as $key => $value) {
				if (isset($object->{$key})) {
					$meta[$key] = trim((string)$object->{$key});
				}
			}

			libxml_clear_errors();
			libxml_use_internal_errors($use_errors);

			return $meta;
		}

		libxml_clear_errors();
		libxml_use_internal_errors($use_errors);

		// Fallback для повреждённого или нестандартного XML.
		foreach (array_keys($meta) as $key) {
			if (preg_match('/<' . preg_quote($key, '/') . '\b[^>]*>(.*?)<\/' . preg_quote($key, '/') . '>/is', $xml, $match)) {
				$value = trim($match[1]);

				if (stripos($value, '<![CDATA[') === 0) {
					$value = preg_replace('/^<!\[CDATA\[(.*)\]\]>$/is', '$1', $value);
				}

				$meta[$key] = trim($value);
			}
		}

		return $meta;
	}

	private function columnExists($table, $column) {
		$key = $table . '.' . $column;

		if (isset($this->column_exists_cache[$key])) {
			return $this->column_exists_cache[$key];
		}

		$query = $this->db->query("SHOW COLUMNS FROM `" . $this->db->escape($table) . "` LIKE '" . $this->db->escape($column) . "'");

		$this->column_exists_cache[$key] = (bool)$query->num_rows;

		return $this->column_exists_cache[$key];
	}

	private function getModificationUpdateSql($modification_id, $data, $keep_status) {
		$current_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "modification` WHERE `modification_id` = '" . (int)$modification_id . "'");

		$current = $current_query->row ? $current_query->row : array();

		$xml = isset($data['xml']) ? $this->decodeXml($data['xml']) : (isset($current['xml']) ? $current['xml'] : '');

		$meta = $this->parseMeta($xml);

		$name = isset($data['name']) ? $data['name'] : ($meta['name'] !== '' ? $meta['name'] : (isset($current['name']) ? $current['name'] : ''));

		$code = isset($data['code']) ? $data['code'] : ($meta['code'] !== '' ? $meta['code'] : (isset($current['code']) ? $current['code'] : ''));

		$author = isset($data['author']) ? $data['author'] : ($meta['author'] !== '' ? $meta['author'] : (isset($current['author']) ? $current['author'] : ''));

		$version = isset($data['version']) ? $data['version'] : ($meta['version'] !== '' ? $meta['version'] : (isset($current['version']) ? $current['version'] : ''));

		$link = isset($data['link']) ? $data['link'] : ($meta['link'] !== '' ? $meta['link'] : (isset($current['link']) ? $current['link'] : ''));

		if ($keep_status) {
			$status = isset($current['status']) ? (int)$current['status'] : 1;
		} else {
			$status = isset($data['status']) ? (int)$data['status'] : (isset($current['status']) ? (int)$current['status'] : 1);
		}

		$sql = "UPDATE `" . DB_PREFIX . "modification` SET
		`name` = '" . $this->db->escape($name) . "',
		`code` = '" . $this->db->escape($code) . "',
		`author` = '" . $this->db->escape($author) . "',
		`version` = '" . $this->db->escape($version) . "',
		`link` = '" . $this->db->escape($link) . "',
		`xml` = '" . $this->db->escape($xml) . "',
		`status` = '" . (int)$status . "'";

		if ($this->columnExists(DB_PREFIX . 'modification', 'date_modified')) {
			$sql .= ", `date_modified` = NOW()";
		}

		$sql .= " WHERE `modification_id` = '" . (int)$modification_id . "'";

		return $sql;
	}

	public function addModification($data) {
		$xml = isset($data['xml']) ? $this->decodeXml($data['xml']) : '';
		$meta = $this->parseMeta($xml);

		$name = $meta['name'] !== '' ? $meta['name'] : (isset($data['name']) ? $data['name'] : '');
		$code = $meta['code'] !== '' ? $meta['code'] : (isset($data['code']) ? $data['code'] : '');
		$author = $meta['author'] !== '' ? $meta['author'] : (isset($data['author']) ? $data['author'] : '');
		$version = $meta['version'] !== '' ? $meta['version'] : (isset($data['version']) ? $data['version'] : '');
		$link = $meta['link'] !== '' ? $meta['link'] : (isset($data['link']) ? $data['link'] : '');
		$status = isset($data['status']) ? (int)$data['status'] : 1;
		$extension_install_id = isset($data['extension_install_id']) ? (int)$data['extension_install_id'] : 0;

		$sql = "INSERT INTO `" . DB_PREFIX . "modification` SET
			`extension_install_id` = '" . (int)$extension_install_id . "',
			`name` = '" . $this->db->escape($name) . "',
			`code` = '" . $this->db->escape($code) . "',
			`author` = '" . $this->db->escape($author) . "',
			`version` = '" . $this->db->escape($version) . "',
			`link` = '" . $this->db->escape($link) . "',
			`xml` = '" . $this->db->escape($xml) . "',
			`status` = '" . (int)$status . "',
			`date_added` = NOW()";

		if ($this->columnExists(DB_PREFIX . 'modification', 'date_modified')) {
			$sql .= ", `date_modified` = NOW()";
		}

		$this->db->query($sql);

		return $this->db->getLastId();
	}

	public function addModificationBackup($modification_id, $data) {
		$xml = isset($data['xml']) ? $this->decodeXml($data['xml']) : '';
		$code = isset($data['code']) ? $data['code'] : '';

		if ($code === '') {
			$meta = $this->parseMeta($xml);
			$code = $meta['code'];
		}

		$this->db->query("INSERT INTO `" . DB_PREFIX . "modification_backup` SET
			`modification_id` = '" . (int)$modification_id . "',
			`code` = '" . $this->db->escape($code) . "',
			`xml` = '" . $this->db->escape($xml) . "',
			`date_added` = NOW()
		");

		return $this->db->getLastId();
	}

	public function editModification($modification_id, $data) {
		$this->db->query($this->getModificationUpdateSql($modification_id, $data, false));
	}

	public function setModificationRestore($modification_id, $xml_raw) {
		$data = array(
		'xml' => $xml_raw
		);

		$this->db->query($this->getModificationUpdateSql($modification_id, $data, true));
	}

	public function deleteModification($modification_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "modification` WHERE `modification_id` = '" . (int)$modification_id . "'");
	}

	public function deleteModificationBackups($modification_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "modification_backup` WHERE `modification_id` = '" . (int)$modification_id . "'");
	}

	public function deleteModificationsByExtensionInstallId($extension_install_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "modification` WHERE `extension_install_id` = '" . (int)$extension_install_id . "'");
	}

	public function enableModification($modification_id) {
		$sql = "UPDATE `" . DB_PREFIX . "modification` SET `status` = '1'";

		if ($this->columnExists(DB_PREFIX . 'modification', 'date_modified')) {
			$sql .= ", `date_modified` = NOW()";
		}

		$sql .= " WHERE `modification_id` = '" . (int)$modification_id . "'";

		$this->db->query($sql);
	}

	public function disableModification($modification_id) {
		$sql = "UPDATE `" . DB_PREFIX . "modification` SET `status` = '0'";

		if ($this->columnExists(DB_PREFIX . 'modification', 'date_modified')) {
			$sql .= ", `date_modified` = NOW()";
		}

		$sql .= " WHERE `modification_id` = '" . (int)$modification_id . "'";

		$this->db->query($sql);
	}

	public function getModification($modification_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "modification` WHERE `modification_id` = '" . (int)$modification_id . "'");

		return $query->row;
	}

	public function getModifications($data = array()) {
		$sql = "SELECT * FROM `" . DB_PREFIX . "modification`";

		$sort_data = array(
			'name',
			'author',
			'version',
			'status',
			'date_added'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY `" . $data['sort'] . "`";
		} else {
			$sql .= " ORDER BY `name`";
		}

		if (isset($data['order']) && $data['order'] == 'DESC') {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			$start = isset($data['start']) ? (int)$data['start'] : 0;
			$limit = isset($data['limit']) ? (int)$data['limit'] : 20;

			if ($start < 0) {
				$start = 0;
			}

			if ($limit < 1) {
				$limit = 20;
			}

			$sql .= " LIMIT " . (int)$start . "," . (int)$limit;
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getModificationBackups($modification_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "modification_backup` WHERE `modification_id` = '" . (int)$modification_id . "' ORDER BY `date_added` DESC, `backup_id` DESC");

		return $query->rows;
	}

	public function getModificationBackup($modification_id, $backup_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "modification_backup` WHERE `modification_id` = '" . (int)$modification_id . "' AND `backup_id` = '" . (int)$backup_id . "' LIMIT 1");

		return $query->row;
	}

	public function getTotalModifications() {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "modification`");

		return (int)$query->row['total'];
	}

	public function getModificationByCode($code) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "modification` WHERE `code` = '" . $this->db->escape($code) . "' LIMIT 1");

		return $query->row;
	}

	public function getExtensionInstallByExtensionInstallId($extension_install_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "extension_install` WHERE `extension_install_id` = '" . (int)$extension_install_id . "' LIMIT 1");

		if ($query->num_rows) {
			return $query->row['filename'];
		}

		return '';
	}
}