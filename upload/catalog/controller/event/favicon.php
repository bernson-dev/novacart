<?php
/**
 * Built-in NovaCart favicon output event.
 *
 * Injects the generated favicon set into the rendered storefront header.
 * This intentionally uses a view/after event instead of OCMOD so favicon
 * changes do not depend on the modification cache or a specific theme file.
 */
class ControllerEventFavicon extends Controller {
	public function index(&$route, &$args, &$output) {
		if (!is_string($output) || $output === '' || stripos($output, '</head>') === false) {
			return;
		}

		// Avoid duplicates while an old cached OCMOD version is still active.
		if (strpos($output, '<!-- FAVICON_SET -->') !== false) {
			return;
		}

		$store_id = (int)$this->config->get('config_store_id');

		if ($store_id < 0) {
			$store_id = 0;
		}

		$relative_dir = 'favicon/store_' . $store_id . '/';
		$absolute_dir = DIR_IMAGE . $relative_dir;

		$required_files = array(
			'favicon.ico',
			'favicon-32x32.png',
			'favicon-96x96.png',
			'favicon-180x180.png',
			'site.webmanifest',
			'safari-pinned-tab.svg',
			'svg.hash',
			'.meta.json'
		);

		$has_favicon_set = true;

		foreach ($required_files as $file) {
			if (!is_file($absolute_dir . $file)) {
				$has_favicon_set = false;
				break;
			}
		}

		if (!empty($this->request->server['HTTPS']) && strtolower((string)$this->request->server['HTTPS']) !== 'off') {
			$server = (string)$this->config->get('config_ssl');
		} else {
			$server = (string)$this->config->get('config_url');
		}

		$server = rtrim($server, '/') . '/';
		$tags = '';

		if ($has_favicon_set) {
			$hash = md5_file($absolute_dir . 'svg.hash');
			$version = $hash !== false ? substr($hash, 0, 12) : '';
			$query = $version !== '' ? '?v=' . rawurlencode($version) : '';
			$base = $server . 'image/' . $relative_dir;

			$color = trim((string)$this->config->get('config_favicon_color'));

			if (!preg_match('/^#[A-Fa-f0-9]{6}$/', $color)) {
				$color = '#000000';
			}

			$tags .= '<!-- FAVICON_SET -->' . PHP_EOL;
			$tags .= '<link rel="icon" href="' . $this->escape($base . 'favicon.ico' . $query) . '">' . PHP_EOL;
			$tags .= '<link rel="icon" type="image/png" sizes="32x32" href="' . $this->escape($base . 'favicon-32x32.png' . $query) . '">' . PHP_EOL;
			$tags .= '<link rel="icon" type="image/png" sizes="96x96" href="' . $this->escape($base . 'favicon-96x96.png' . $query) . '">' . PHP_EOL;
			$tags .= '<link rel="apple-touch-icon" sizes="180x180" href="' . $this->escape($base . 'favicon-180x180.png' . $query) . '">' . PHP_EOL;
			$tags .= '<link rel="manifest" href="' . $this->escape($base . 'site.webmanifest' . $query) . '">' . PHP_EOL;
			$tags .= '<link rel="mask-icon" href="' . $this->escape($base . 'safari-pinned-tab.svg' . $query) . '" color="' . $this->escape($color) . '">' . PHP_EOL;
		} else {
			// Preserve standard OpenCart fallback when no generated set exists.
			$config_icon = ltrim((string)$this->config->get('config_icon'), '/');

			if ($config_icon !== '' && is_file(DIR_IMAGE . $config_icon)) {
				$tags .= '<link rel="icon" href="' . $this->escape($server . 'image/' . $config_icon) . '">' . PHP_EOL;
			}
		}

		if ($tags === '') {
			return;
		}

		$position = stripos($output, '</head>');

		if ($position === false) {
			return;
		}

		$output = substr($output, 0, $position) . $tags . substr($output, $position);
	}

	private function escape($value) {
		return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
	}
}
