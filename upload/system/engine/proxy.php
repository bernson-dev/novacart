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
* Proxy class
 *
 * @template TWraps of Model
 *
 * @mixin TWraps
*/
class Proxy extends \stdClass {
	/**
	 * @param	string	$key
	 */
	public function __get($key) {
		return $this->{$key};
	}

	/**
	 * @param	string	$key
	 * @param	string	$value
	 */
	public function __set($key, $value) {
		$this->{$key} = $value;
	}

	public function __call($key, $args) {
		if (isset($this->{$key})) {
			// Loader::callback() expects one argument: the complete array
			// of arguments originally passed to the proxied model method.
			return ($this->{$key})($args);
		}

		$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
		$message = 'Undefined method: Proxy::' . $key;

		if (isset($trace[1]['file'], $trace[1]['line'])) {
			$message .= ' in ' . $trace[1]['file'] . ' on line ' . (int)$trace[1]['line'];
		}

		throw new \BadMethodCallException($message);
	}
}
