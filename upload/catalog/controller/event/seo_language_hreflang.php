<?php
/**
 * Inject hreflang links into the rendered common/header without modifying
 * theme templates. This keeps the implementation compatible with default and
 * third-party themes that render their own header.twig.
 */
class ControllerEventSeoLanguageHreflang extends Controller {
	public function index(&$route, &$args, &$output) {
		if (
			!$this->registry->has('seo_language')
			|| !is_string($output)
			|| $output === ''
		) {
			return;
		}

		$seo_language = $this->registry->get('seo_language');

		if (!($seo_language instanceof SeoLanguage) || !$seo_language->isEnabled()) {
			return;
		}

		$current_route = isset($this->request->get['route']) && is_scalar($this->request->get['route'])
			? (string)$this->request->get['route']
			: 'common/home';

		$params = $this->getRouteParams($current_route);

		if ($params === false) {
			return;
		}

		$ssl = !empty($this->request->server['HTTPS'])
			&& strtolower((string)$this->request->server['HTTPS']) !== 'off';

		$links = $seo_language->getAvailableAlternateLinks($current_route, $params, $ssl);

		$language_links = $links;
		unset($language_links['x-default']);

		// One-language stores do not need hreflang markup.
		if (count($language_links) < 2) {
			return;
		}

		$tags = '';

		foreach ($links as $hreflang => $href) {
			$tags .= '<link rel="alternate" hreflang="'
				. htmlspecialchars((string)$hreflang, ENT_QUOTES, 'UTF-8')
				. '" href="'
				. htmlspecialchars(html_entity_decode((string)$href, ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8')
				. '" />' . PHP_EOL;
		}

		$pos = stripos($output, '</head>');

		if ($pos === false) {
			return;
		}

		$output = substr($output, 0, $pos) . $tags . substr($output, $pos);
	}

	/**
	 * Return only parameters that identify the logical page.
	 *
	 * Tracking, sorting, filters and other transient query parameters are
	 * intentionally excluded from hreflang URLs.
	 */
	private function getRouteParams($route) {
		switch ($route) {
			case 'common/home':
			case 'product/manufacturer':
			case 'product/special':
			case 'blog/latest':
				return array();

			case 'product/product':
				return !empty($this->request->get['product_id'])
					? array('product_id' => (int)$this->request->get['product_id'])
					: false;

			case 'product/category':
				return !empty($this->request->get['path'])
					? array('path' => (string)$this->request->get['path'])
					: false;

			case 'product/manufacturer/info':
				return !empty($this->request->get['manufacturer_id'])
					? array('manufacturer_id' => (int)$this->request->get['manufacturer_id'])
					: false;

			case 'information/information':
				return !empty($this->request->get['information_id'])
					? array('information_id' => (int)$this->request->get['information_id'])
					: false;

			case 'blog/article':
				return !empty($this->request->get['article_id'])
					? array('article_id' => (int)$this->request->get['article_id'])
					: false;

			case 'blog/category':
				return !empty($this->request->get['blog_category_id'])
					? array('blog_category_id' => (string)$this->request->get['blog_category_id'])
					: false;
		}

		return false;
	}
}
