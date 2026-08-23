<?php
namespace Session;
class DB {
	public $maxlifetime;
	public $db;

	public function __construct($registry) {
		$this->db = $registry->get('db');

		$this->maxlifetime = ini_get('session.gc_maxlifetime') !== null ? (int)ini_get('session.gc_maxlifetime') : 1440;

		$this->gc();
	}

	public function read($session_id) {
		$query = $this->db->query("SELECT `data` FROM `" . DB_PREFIX . "session`
        WHERE `session_id` = '" . $this->db->escape($session_id) . "'
        AND `expire` > '" . $this->db->escape(gmdate('Y-m-d H:i:s')) . "'");

		if ($query->num_rows) {
			try {
				return json_decode($query->row['data'], true, 512, JSON_THROW_ON_ERROR);
			} catch (\JsonException $e) {
				return [];
			}
		}
		return [];
	}

	public function write($session_id, $data) {
		if ($session_id) {
			$json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
			$expire = gmdate('Y-m-d H:i:s', time() + $this->maxlifetime);
			return $this->db->query("REPLACE INTO `" . DB_PREFIX . "session`
            SET `session_id` = '" . $this->db->escape($session_id) . "',
                `data` = '" . $this->db->escape($json) . "',
                `expire` = '" . $this->db->escape($expire) . "'");
		}
		return false;
	}

	public function destroy($session_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "session` WHERE `session_id` = '" . $this->db->escape($session_id) . "'");

		return true;
	}

	public function gc() {
		if (ini_get('session.gc_divisor') && $gc_divisor = (int)ini_get('session.gc_divisor')) {
			$gc_divisor = $gc_divisor === 0 ? 100 : $gc_divisor;
		} else {
			$gc_divisor = 100;
		}

		if (ini_get('session.gc_probability')) {
			$gc_probability = (int)ini_get('session.gc_probability');
		} else {
			$gc_probability = 1;
		}

		if (mt_rand() / mt_getrandmax() < $gc_probability / $gc_divisor) {
			$this->db->query("DELETE FROM `" . DB_PREFIX . "session` WHERE `expire` < '" . $this->db->escape(gmdate('Y-m-d H:i:s', time())) . "'");
			//$this->db->query("OPTIMIZE TABLE `" . DB_PREFIX . "session`");

			return true;
		}
	}
}
