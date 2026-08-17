<?php
/**
* @package     OpenCart
* @author      Daniel Kerr
* @copyright   Copyright (c) 2005 - 2017, OpenCart, Ltd. (https://www.opencart.com/)
* @license     https://opensource.org/licenses/GPL-3.0
* @link        https://www.opencart.com
*/

/**
* Log class
*/
class Log {
	/** @var resource|false */
	private $handle;

	/**
	* Constructor
	*
	* @param string $filename
	*/
	public function __construct(string $filename) {
		$filepath = DIR_LOGS . $filename;

		if (!is_dir(DIR_LOGS)) {
			throw new \RuntimeException("Log directory does not exist: " . DIR_LOGS);
		}

		$this->handle = @fopen($filepath, 'ab');

		if ($this->handle === false) {
			throw new \RuntimeException("Failed to open log file: $filepath");
		}
	}

	/**
	* Write message to log
	*
	* @param string|array|object $message
	* @return void
	*/
	public function write($message): void {
		if (!$this->handle) {
			return;
		}

		$timestamp = date('Y-m-d H:i:s');
		$output = $timestamp . ' - ' . print_r($message, true) . PHP_EOL;

		fwrite($this->handle, $output);
	}

	/**
	* Destructor
	*/
	public function __destruct() {
		if (is_resource($this->handle)) {
			fclose($this->handle);
		}
	}
}
