<?php
/**
 * @package		OpenCart
 *
 * @author		Daniel Kerr
 * @copyright	Copyright (c) 2005 - 2017, OpenCart, Ltd. (https://www.opencart.com/)
 * @license		https://opensource.org/licenses/GPL-3.0
 *
 * @see		https://www.opencart.com
*/

/**
* Request class
*/
class Request {
	public $get = array();
	public $post = array();
	public $request = array();
	public $cookie = array();
	public $files = array();
	public $server = array();

	/**
	 * Constructor
	*/
	public function __construct() {
		$this->get = $this->clean($_GET);
		$this->post = $this->clean($_POST);
		$this->request = $this->clean($_REQUEST);
		$this->cookie = $this->clean($_COOKIE);
		$this->files = $this->clean($_FILES);
		$this->server = $this->clean($_SERVER);
	}


	/**
	 * Return an original scalar POST value without trim() or HTML encoding.
	 *
	 * Intended for passwords and other opaque credentials only.
	 * Ordinary request data must continue to use $this->post.
	 *
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	public function getRawPost($key, $default = '') {
		if (!array_key_exists($key, $_POST) || !is_string($_POST[$key])) {
			return $default;
		}

		return $_POST[$key];
	}

	/**
	 * @param	array	$data
	 *
	 * @return	array
	 */
	public function clean($data) {
		if (is_array($data)) {
			foreach ($data as $key => $value) {
				unset($data[$key]);

				$data[(string) $this->clean($key)] = $this->clean($value);
			}
		} else {
			$data = trim(htmlspecialchars($data ?? '', ENT_COMPAT, 'UTF-8'));
		}

		return $data;
	}
}
