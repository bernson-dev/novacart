<?php
/**
 * @package     OpenCart
 * @author      Daniel Kerr
 * @copyright   Copyright (c) 2005 - 2017, OpenCart, Ltd.
 * @license     https://opensource.org/licenses/GPL-3.0
 * @link        https://www.opencart.com
 */

/**
 * Event class
 *
 * Optimized for OpenCart 3.x
 *
 * Optimizations:
 * - cache matched handlers per event within current request;
 * - avoid repeated preg_quote()/str_replace() calls;
 * - use strpos() for triggers without wildcards;
 * - use preg_match() only for wildcard triggers;
 * - preserve original OpenCart return semantics.
 */
class Event {
	protected $registry;
	protected $data = array();

	/**
	 * Cache of matched actions for concrete events.
	 *
	 * Example:
	 * $processed['catalog/model/catalog/product/getProduct/before']
	 *
	 * @var array
	 */
	protected $processed = array();

	/**
	 * Constructor
	 *
	 * @param object $registry
	 */
	public function __construct($registry) {
		$this->registry = $registry;
	}

	/**
	 * Register event handler
	 *
	 * @param string $trigger
	 * @param Action $action
	 * @param int    $priority
	 *
	 * @return void
	 */
	public function register($trigger, Action $action, $priority = 0) {
		$regex = null;

		/*
		 * Build regular expression only once when registering
		 * triggers containing wildcards.
		 */
		if (strpos($trigger, '*') !== false || strpos($trigger, '?') !== false) {
			$regex = '/^' . str_replace(
				array('\*', '\?'),
				array('.*', '.'),
				preg_quote($trigger, '/')
			) . '/';
		}

		$this->data[] = array(
			'trigger'  => $trigger,
			'action'   => $action,
			'priority' => $priority,
			'regex'    => $regex
		);

		/*
		 * Preserve original OpenCart priority behavior.
		 */
		$sort_order = array();

		foreach ($this->data as $key => $value) {
			$sort_order[$key] = $value['priority'];
		}

		array_multisort($sort_order, SORT_ASC, $this->data);

		/*
		 * Registered handlers or their order changed.
		 * Previously cached matches are no longer valid.
		 */
		$this->processed = array();
	}

	/**
	 * Trigger event
	 *
	 * @param string $event
	 * @param array  $args
	 *
	 * @return mixed
	 */
	public function trigger($event, array $args = array()) {
		/*
		 * Resolve matching handlers only once for each unique
		 * event name within the current PHP request.
		 */
		if (!isset($this->processed[$event])) {
			$this->processed[$event] = array();

			foreach ($this->data as $value) {
				if ($value['regex'] === null) {
					/*
					 * Original OpenCart regexp is anchored only
					 * at the beginning:
					 *
					 * /^trigger/
					 *
					 * Therefore prefix comparison preserves
					 * original behavior.
					 */
					if (strpos($event, $value['trigger']) === 0) {
						$this->processed[$event][] = $value['action'];
					}
				} else {
					/*
					 * preg_match() is required only for
					 * triggers containing * or ?.
					 *
					 * The regexp itself was already built
					 * during register().
					 */
					if (preg_match($value['regex'], $event)) {
						$this->processed[$event][] = $value['action'];
					}
				}
			}
		}

		/*
		 * Preserve original OpenCart event execution semantics.
		 */
		foreach ($this->processed[$event] as $action) {
			$result = $action->execute($this->registry, $args);

			if ($result !== null && !($result instanceof Exception)) {
				return $result;
			}
		}
	}

	/**
	 * Unregister event handler
	 *
	 * @param string $trigger
	 * @param string $route
	 *
	 * @return void
	 */
	public function unregister($trigger, $route) {
		$changed = false;

		foreach ($this->data as $key => $value) {
			if (
				$trigger == $value['trigger'] &&
				$value['action']->getId() == $route
			) {
				unset($this->data[$key]);
				$changed = true;
			}
		}

		if ($changed) {
			/*
			 * Keep array numerically indexed after unset().
			 */
			$this->data = array_values($this->data);

			/*
			 * Handler list changed.
			 */
			$this->processed = array();
		}
	}

	/**
	 * Clear all handlers registered for trigger
	 *
	 * @param string $trigger
	 *
	 * @return void
	 */
	public function clear($trigger) {
		$changed = false;

		foreach ($this->data as $key => $value) {
			if ($trigger == $value['trigger']) {
				unset($this->data[$key]);
				$changed = true;
			}
		}

		if ($changed) {
			/*
			 * Keep array numerically indexed after unset().
			 */
			$this->data = array_values($this->data);

			/*
			 * Handler list changed.
			 */
			$this->processed = array();
		}
	}
}
