<?php
class ControllerToolSvgPreview extends Controller {

	public function index(): void {
		// Проверка токена
		if (!isset($this->session->data['user_token']) || !isset($this->request->get['user_token'])
		|| $this->request->get['user_token'] !== $this->session->data['user_token']) {
			$this->response->addHeader('HTTP/1.1 403 Forbidden');
			return;
		}

		$path  = $this->request->get['path'] ?? '';
		$color = $this->request->get['color'] ?? '#000000';
		$useOriginal = !empty($this->request->get['original']);

		if (!preg_match('/^#([A-Fa-f0-9]{6})$/', $color)) {
			$color = '#000000';
		}

		// минимальная защита от traversal
		$path = str_replace(['../', '..\\'], '', $path);
		$full = DIR_IMAGE . $path;

		if (!is_file($full) || strtolower(pathinfo($full, PATHINFO_EXTENSION)) !== 'svg') {
			$this->response->addHeader('HTTP/1.1 404 Not Found');
			return;
		}

		$svg = file_get_contents($full);
		if ($svg === false || $svg === '') {
			$this->response->addHeader('HTTP/1.1 500 Internal Server Error');
			return;
		}

		// режим "оригинал"
		if ($useOriginal) {
			$this->response->addHeader('Content-Type: image/svg+xml; charset=utf-8');
			$this->response->addHeader('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
			$this->response->addHeader('Pragma: no-cache');
			$this->response->setOutput($svg);
			return;
		}

		// режим "перекраска"
		$svg = preg_replace_callback('~<svg\b([^>]*)>~i', function ($m) use ($color) {
			$attrs = $m[1];
			if (preg_match('~\sstyle\s*=\s*([\'"])(.*?)\1~i', $attrs)) {
				$attrs = preg_replace('~(\sstyle\s*=\s*)([\'"])(.*?)\2~i', '$1$2$3; color: ' . $color . ';$2', $attrs, 1);
			} else {
				$attrs .= ' style="color: ' . $color . ';"';
			}
			return '<svg' . $attrs . '>';
		}, $svg, 1);

		$svg = preg_replace('~\sfill\s*=\s*([\'"])(?!none\1)(?!url\()([^\'"]+)\1~i', ' fill="currentColor"', $svg);
		$svg = preg_replace('~\sstroke\s*=\s*([\'"])(?!none\1)(?!url\()([^\'"]+)\1~i', ' stroke="currentColor"', $svg);

		// против кеша (для превью в админке)
		$this->response->addHeader('Content-Type: image/svg+xml; charset=utf-8');
		$this->response->addHeader('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
		$this->response->addHeader('Pragma: no-cache');
		$this->response->setOutput($svg);
	}
}