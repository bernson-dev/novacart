<?php
/**
 * Language-aware URL generator.
 *
 * Generates a URL for another language without changing the user's session.
 * The normal OpenCart rewrite stack (standard seo_url or SeoPro) remains the
 * single source of truth for the actual SEO URL.
 */
class LanguageUrl {
	private $registry;
	private $config;
	private $request;
	private $url;

	public function __construct($registry) {
		$this->registry = $registry;
		$this->config = $registry->get('config');
		$this->request = $registry->get('request');
		$this->url = $registry->get('url');
	}

	/**
	 * Generate a URL using the requested language context.
	 *
	 * Only config_language_id is changed temporarily because rewrite handlers
	 * use it to select the language-specific seo_url row. Session, cookies and
	 * the active Language object are never touched.
	 *
	 * @param string       $route
	 * @param string|array $args
	 * @param int          $language_id
	 * @param bool         $secure
	 *
	 * @return string
	 */
	public function link($route, $args = '', $language_id = 0, $secure = false) {
		$route = (string)$route;
		$language_id = (int)$language_id;
		$current_language_id = (int)$this->config->get('config_language_id');

		if ($language_id < 1 || $language_id === $current_language_id) {
			return $this->url->link($route, $args, $secure);
		}

		$this->config->set('config_language_id', $language_id);

		try {
			return $this->url->link($route, $args, $secure);
		} finally {
			$this->config->set('config_language_id', $current_language_id);
		}
	}

	/**
	 * Generate the current page URL for another language.
	 *
	 * @param int  $language_id
	 * @param bool $secure
	 *
	 * @return string
	 */
	public function current($language_id, $secure = false) {
		$target = $this->getCurrentTarget();

		return $this->link(
			$target['route'],
			$target['args'],
			$language_id,
			$secure
		);
	}

	/**
	 * Return the current OpenCart route and non-routing GET parameters.
	 *
	 * _route_ is deliberately excluded: it represents the current SEO path and
	 * must be regenerated for the target language.
	 *
	 * @return array
	 */
	public function getCurrentTarget() {
		$route = 'common/home';
		$args = array();

		if (isset($this->request->get['route']) && is_scalar($this->request->get['route'])) {
			$candidate = (string)$this->request->get['route'];

			if (preg_match('/^[a-zA-Z0-9_\/]+$/', $candidate)) {
				$route = $candidate;
			}
		}

		foreach ($this->request->get as $key => $value) {
			if ($key === '_route_' || $key === 'route') {
				continue;
			}

			$args[$key] = $value;
		}

		return array(
			'route' => $route,
			'args'  => $args
		);
	}
}
