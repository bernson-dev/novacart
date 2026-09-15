<?php
namespace Cart;
class User {
	private $user_id;
	private $user_group_id;
	private $username;
	private $permission = array();
	private $db;
	private $request;
	private $session;

	public function __construct($registry) {
		$this->db = $registry->get('db');
		$this->request = $registry->get('request');
		$this->session = $registry->get('session');

		if (isset($this->session->data['user_id'])) {
			$user_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "user WHERE user_id = '" . (int)$this->session->data['user_id'] . "' AND status = '1'");

			if ($user_query->num_rows) {
				$this->user_id = $user_query->row['user_id'];
				$this->username = $user_query->row['username'];
				$this->user_group_id = $user_query->row['user_group_id'];

				$this->db->query("UPDATE " . DB_PREFIX . "user SET ip = '" . $this->db->escape($this->request->server['REMOTE_ADDR']) . "' WHERE user_id = '" . (int)$this->session->data['user_id'] . "'");

				$user_group_query = $this->db->query("SELECT permission FROM " . DB_PREFIX . "user_group WHERE user_group_id = '" . (int)$user_query->row['user_group_id'] . "'");

				$permissions = json_decode($user_group_query->row['permission'], true);

				if (is_array($permissions)) {
					foreach ($permissions as $key => $value) {
						$this->permission[$key] = $value;
					}
				}
			} else {
				$this->logout();
			}
		}
	}

	public function login($username, $password) {
		$raw_password = $this->request->getRawPost('password', null);

		if ($raw_password !== null) {
			$password = $raw_password;
		}

		$user_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "user WHERE username = '" . $this->db->escape($username) . "' AND status = '1'");

		if ($user_query->num_rows) {
			if (password_verify($password, $user_query->row['password'])) {
				$rehash = password_needs_rehash($user_query->row['password'], PASSWORD_DEFAULT);
			} elseif (isset($user_query->row['salt']) && hash_equals($user_query->row['password'], sha1($user_query->row['salt'] . sha1($user_query->row['salt'] . sha1($password))))) {
				$rehash = true;
			} elseif (hash_equals($user_query->row['password'], md5($password))) {
				$rehash = true;
			} else {
				return false;
			}

			if ($rehash) {
				$this->ensurePasswordColumn();

				$this->db->query("UPDATE `" . DB_PREFIX . "user` SET `password` = '" . $this->db->escape(password_hash($password, PASSWORD_DEFAULT)) . "' WHERE `user_id` = '" . (int)$user_query->row['user_id'] . "'");
			}

			$this->session->data['user_id'] = $user_query->row['user_id'];

			$this->user_id = $user_query->row['user_id'];
			$this->username = $user_query->row['username'];
			$this->user_group_id = $user_query->row['user_group_id'];

			$user_group_query = $this->db->query("SELECT permission FROM " . DB_PREFIX . "user_group WHERE user_group_id = '" . (int)$user_query->row['user_group_id'] . "'");

			$permissions = json_decode($user_group_query->row['permission'], true);

			if (is_array($permissions)) {
				foreach ($permissions as $key => $value) {
					$this->permission[$key] = $value;
				}
			}

			return true;
		} else {
			return false;
		}
	}


	private function ensurePasswordColumn() {
		$query = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "user` LIKE 'password'");

		if ($query->num_rows && isset($query->row['Type']) && preg_match('/^(?:var)?char\((\d+)\)$/i', $query->row['Type'], $matches) && (int)$matches[1] < 255) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "user` MODIFY `password` VARCHAR(255) NOT NULL");
		}
	}

	public function logout() {
		unset($this->session->data['user_id']);

		$this->user_id = '';
		$this->username = '';
	}

	public function hasPermission($key, $value) {
		if (isset($this->permission[$key])) {
			return in_array($value, $this->permission[$key]);
		} else {
			return false;
		}
	}

	public function isLogged() {
		return $this->user_id;
	}

	public function getId() {
		return $this->user_id;
	}

	public function getUserName() {
		return $this->username;
	}

	public function getGroupId() {
		return $this->user_group_id;
	}
}
