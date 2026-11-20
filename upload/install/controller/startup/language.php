<?php
class ControllerStartupLanguage extends Controller {
	public function index() {
		$directories = glob(DIR_LANGUAGE . '*', GLOB_ONLYDIR);
		$languages   = $directories ? array_map('basename', $directories) : array();

		$code = (string)$this->config->get('language_default');

		if (!in_array($code, $languages, true)) {
			$code = in_array('en-gb', $languages, true) ? 'en-gb' : (string)reset($languages);
		}

		if (isset($this->request->server['HTTP_ACCEPT_LANGUAGE'])) {
			foreach (explode(',', $this->request->server['HTTP_ACCEPT_LANGUAGE']) as $browser_language) {
				$browser_language = strtolower(trim(explode(';', $browser_language)[0]));

				if ($browser_language === '') {
					continue;
				}

				if (in_array($browser_language, $languages, true)) {
					$code = $browser_language;
					break;
				}

				$primary = explode('-', $browser_language)[0];

				foreach ($languages as $available) {
					if (explode('-', $available)[0] === $primary) {
						$code = $available;
						break 2;
					}
				}
			}
		}

		if (!isset($this->session->data['language']) || !in_array(basename((string)$this->session->data['language']), $languages, true)) {
			$this->session->data['language'] = $code;
		}

		$language = new Language($this->session->data['language']);
		$language->load($this->session->data['language']);

		$this->registry->set('language', $language);
	}
}