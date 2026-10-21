<?php
/**
 * @package		OpenCart
 * @author		Daniel Kerr
 * @copyright	Copyright (c) 2005 - 2017, OpenCart, Ltd. (https://www.opencart.com/)
 * @license		https://opensource.org/licenses/GPL-3.0
 * @link		https://www.opencart.com
*/

/**
* Router class
*/
final class Router {
	private $registry;
	private $pre_action = array();
	private $error;
	
	/**
	 * Constructor
	 *
	 * @param	object	$route
	*/
	public function __construct($registry) {
		$this->registry = $registry;
	}

	/**
	 *
	 *
	 * @param	object	$pre_action
	*/
	public function addPreAction(Action $pre_action) {
		$this->pre_action[] = $pre_action;
	}

	/**
	 *
	 *
	 * @param	object	$action
	 * @param	object	$error
	*/
	public function dispatch(Action $action, Action $error) {
		$this->error = $error;

		foreach ($this->pre_action as $pre_action_index => $pre_action) {
			$profile_start = class_exists('NovaCartAdminProfiler', false) ? microtime(true) : null;
			$result = $this->execute($pre_action);
			if ($profile_start !== null) {
				$elapsed = microtime(true) - $profile_start;
				NovaCartAdminProfiler::add('router_pre_action', $elapsed);
				NovaCartAdminProfiler::add('router_pre_action_' . ($pre_action_index + 1), $elapsed);
			}

			if ($result instanceof Action) {
				$action = $result;

				break;
			}
		}

		while ($action instanceof Action) {
			$profile_start = class_exists('NovaCartAdminProfiler', false) ? microtime(true) : null;
			$action = $this->execute($action);
			if ($profile_start !== null) {
				NovaCartAdminProfiler::add('router_main_action', microtime(true) - $profile_start);
			}
		}
	}

	/**
	 * @param object $action
	 *
	 * @return	object
	*/
	private function execute(Action $action) {
		$result = $action->execute($this->registry);

		if ($result instanceof Action) {
			return $result;
		}

		if ($result instanceof Exception) {
			$action = $this->error;

			$this->error = null;

			return $action;
		}

		return null;
	}
}
