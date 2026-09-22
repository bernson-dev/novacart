<?php
/**
 * Resolve an optional language prefix before the SEO engine decodes the
 * remaining route. The service is disabled unless explicitly enabled in the
 * store settings, so existing installations keep their current behavior.
 */
class ControllerStartupLanguageUrl extends Controller {
	public function index() {
		$seo_language = new SeoLanguage($this->registry);
		$this->registry->set('seo_language', $seo_language);

		if ($seo_language->isEnabled()) {
			$seo_language->resolve();
		}
	}
}
