<?php
class ModelToolFaviconGenerator extends Model {

/**
* Генерация набора фавиконок из SVG.
*
* @param string      $svg_relative_path относительный путь от image/ (например "catalog/logo.svg")
* @param int         $store_id          ID магазина (0 — основной)
* @param string|null $color             Цвет #RRGGBB
* @param int         $original          1 — использовать оригинальные цвета SVG, 0 — перекрашивать
* @return bool
*/
	public function generateFromSvg($svg_relative_path, $store_id = 0, $color = null, $original = 0) {
		$svg_relative_path = ltrim((string)$svg_relative_path, '/');

		if ($svg_relative_path === '') {
			return false;
		}

		$svg_path = DIR_IMAGE . $svg_relative_path;

		if (!is_file($svg_path) || strtolower(pathinfo($svg_path, PATHINFO_EXTENSION)) !== 'svg') {
			return false;
		}

		$store_id = (int)$store_id;

		if ($store_id < 0) {
			$store_id = 0;
		}

		$original = !empty($original) ? 1 : 0;

		// Цвет
		if ($color === null || $color === '') {
			$color = (string)$this->config->get('config_favicon_color');
		}

		$color = trim((string)$color);

		if (!preg_match('/^#([A-Fa-f0-9]{6})$/', $color)) {
			$color = '#000000';
		}

		$color_lower = strtolower($color);

		// Целевая директория набора
		$favicon_dir_rel = 'favicon/store_' . $store_id . '/';
		$favicon_dir = DIR_IMAGE . $favicon_dir_rel;

		if (!is_dir($favicon_dir)) {
			if (!mkdir($favicon_dir, 0755, true) && !is_dir($favicon_dir)) {
				return false;
			}
		}

		$hash_file = $favicon_dir . 'svg.hash';
		$meta_file = $favicon_dir . '.meta.json';

		// Хэш учитывает SVG, цвет и режим original
		$current_hash = md5_file($svg_path) . '|' . $color_lower . '|original:' . $original;
		$previous_hash = is_file($hash_file) ? trim((string)file_get_contents($hash_file)) : '';

		$needed_sizes = array(16, 32, 48, 96, 180, 192, 512);

		$need_generate = ($current_hash !== $previous_hash);

		// Если hash совпадает — дополнительно проверяем существование файлов
		if (!$need_generate) {
			foreach ($needed_sizes as $s) {
				if (!is_file($favicon_dir . 'favicon-' . $s . 'x' . $s . '.png')) {
					$need_generate = true;
					break;
				}
			}

			if (!is_file($favicon_dir . 'site.webmanifest')) {
				$need_generate = true;
			}

			if (!is_file($favicon_dir . 'favicon.ico')) {
				$need_generate = true;
			}

			if (!is_file($favicon_dir . 'safari-pinned-tab.svg')) {
				$need_generate = true;
			}

			if (!is_file($favicon_dir . 'favicon.svg')) {
				$need_generate = true;
			}

			if (!is_file($meta_file)) {
				$need_generate = true;
			}
		}

		// Imagick обязателен для генерации PNG/ICO
		if (!class_exists('Imagick')) {
			return false;
		}

		if (!$need_generate) {
			return true;
		}

		$svg_raw = (string)file_get_contents($svg_path);

		if ($svg_raw === '') {
			return false;
		}

		// Нормализация: убираем width/height, добавляем viewBox/preserveAspectRatio при необходимости
		$svg_clean = preg_replace('/\s(width|height)="[^"]+"/i', '', $svg_raw);

		if (!preg_match('/viewBox="/i', $svg_clean)) {
			if (preg_match('/<svg[^>]*\swidth="([\d\.]+)"[^>]*\sheight="([\d\.]+)"/i', $svg_raw, $m)) {
				$vw = (int)$m[1];
				$vh = (int)$m[2];

				if ($vw > 0 && $vh > 0) {
					$svg_clean = preg_replace(
					'/<svg\b/i',
					'<svg viewBox="0 0 ' . $vw . ' ' . $vh . '"',
					$svg_clean,
					1
					);
				}
			}
		}

		if (!preg_match('/preserveAspectRatio="/i', $svg_clean)) {
			$svg_clean = preg_replace(
			'/<svg([^>]*)>/i',
			'<svg$1 preserveAspectRatio="xMidYMid meet">',
			$svg_clean,
			1
			);
		}

		// Перекрашиваем только если НЕ original
		if (!$original) {
			$svg_clean = $this->applyColorCurrentColor($svg_clean, $color);
		}

		// Сохраняем финальный SVG
		@file_put_contents($favicon_dir . 'favicon.svg', $svg_clean);
		@file_put_contents($favicon_dir . 'safari-pinned-tab.svg', $svg_clean);

		// PNG рендер
		foreach ($needed_sizes as $size) {
			$this->renderPng($svg_clean, $size, $favicon_dir . 'favicon-' . $size . 'x' . $size . '.png');
		}

		// favicon.ico
		try {
			$ico = new Imagick();
			$ico->setFormat('ico');
			$ico->setBackgroundColor(new ImagickPixel('transparent'));

			foreach (array(16, 32, 48) as $ico_size) {
				$png = $favicon_dir . 'favicon-' . $ico_size . 'x' . $ico_size . '.png';

				if (is_file($png)) {
					$frame = new Imagick($png);
					$frame->setImagePage($frame->getImageWidth(), $frame->getImageHeight(), 0, 0);
					$ico->addImage($frame);
					$frame->clear();
					$frame->destroy();
				}
			}

			$frames = method_exists($ico, 'getNumberImages') ? $ico->getNumberImages() : 0;

			if ($frames > 0) {
				$ico->writeImages($favicon_dir . 'favicon.ico', true);
			}

			$ico->clear();
			$ico->destroy();
		} catch (Exception $e) {
			// игнорируем
		}

		// Название магазина
		$store_name = $this->getStoreName($store_id);
		$short_name = $this->makeShortName($store_name);

		$this->writeManifestAbsolute($favicon_dir, $store_id, $store_name, $short_name, $color, '#ffffff');

		$this->writeMeta($meta_file, array(
		'svg'          => $svg_relative_path,
		'color'        => strtoupper($color),
		'original'     => $original,
		'name'         => $store_name,
		'short_name'   => $short_name,
		'generated_at' => date('c'),
		));

		@file_put_contents($hash_file, $current_hash);

		return true;
	}

/**
* Очистка набора favicon для магазина.
*
* @param int $store_id
* @return void
*/
	public function clearStore($store_id = 0) {
		$store_id = (int)$store_id;

		if ($store_id < 0) {
			$store_id = 0;
		}

		$dir = DIR_IMAGE . 'favicon/store_' . $store_id . '/';

		if (!is_dir($dir)) {
			return;
		}

		foreach (glob($dir . '*') as $file) {
			if (is_file($file)) {
				@unlink($file);
			}
		}

		@unlink($dir . 'svg.hash');
		@unlink($dir . '.meta.json');
		@rmdir($dir);
	}

/* ===================== helpers ===================== */

/**
* Перекраска SVG через currentColor.
*
* @param string $svg
* @param string $color
* @return string
*/
	private function applyColorCurrentColor($svg, $color) {
		$svg = preg_replace_callback(
		'~<svg\b([^>]*)>~i',
		function ($m) use ($color) {
			$attrs = $m[1];

			if (preg_match('~\sstyle\s*=\s*([\'"])(.*?)\1~i', $attrs)) {
				$attrs = preg_replace(
				'~(\sstyle\s*=\s*)([\'"])(.*?)\2~i',
				'$1$2$3; color: ' . $color . ';$2',
				$attrs,
				1
				);
			} else {
				$attrs .= ' style="color: ' . $color . ';"';
			}

			return '<svg' . $attrs . '>';
		},
		$svg,
		1
		);

		// заменяем fill/stroke на currentColor, кроме none и url(...)
		$svg = preg_replace(
		'~\sfill\s*=\s*([\'"])(?!none\1)(?!url\()([^\'"]+)\1~i',
		' fill="currentColor"',
		$svg
		);

		$svg = preg_replace(
		'~\sstroke\s*=\s*([\'"])(?!none\1)(?!url\()([^\'"]+)\1~i',
		' stroke="currentColor"',
		$svg
		);

		return $svg;
	}

/**
* Рендер PNG из SVG.
*
* @param string $svg_content
* @param int    $target
* @param string $out
* @return bool
*/
	private function renderPng($svg_content, $target, $out) {
		try {
			$tmp = preg_replace(
			'/<svg([^>]*)>/i',
			'<svg$1 width="' . (int)$target . '" height="' . (int)$target . '">',
			$svg_content,
			1
			);

			$r = new Imagick();
			$r->setBackgroundColor(new ImagickPixel('transparent'));

			if (method_exists($r, 'setSize')) {
				$r->setSize($target, $target);
			}

			$r->readImageBlob($tmp);
			$r->setImageFormat('png32');
			$r->thumbnailImage($target, $target, true);

			$w = $r->getImageWidth();
			$h = $r->getImageHeight();

			$canvas = new Imagick();
			$canvas->newImage($target, $target, new ImagickPixel('transparent'));
			$canvas->setImageFormat('png32');

			$x = (int)floor(($target - $w) / 2);
			$y = (int)floor(($target - $h) / 2);

			$canvas->compositeImage($r, Imagick::COMPOSITE_DEFAULT, $x, $y);
			$canvas->writeImage($out);

			$r->clear();
			$r->destroy();

			$canvas->clear();
			$canvas->destroy();

			return true;
		} catch (Exception $e) {
			return false;
		}
	}

/**
* Запись manifest.
*
* @param string $favicon_dir
* @param int    $store_id
* @param string $name
* @param string $short_name
* @param string $theme_color
* @param string $background_color
* @return void
*/
	private function writeManifestAbsolute($favicon_dir, $store_id, $name, $short_name, $theme_color, $background_color) {
		$base = '/image/favicon/store_' . (int)$store_id . '/';

		$manifest = array(
		'name'             => $name,
		'short_name'       => $short_name,
		'icons'            => array(
		array(
		'src'   => $base . 'favicon-192x192.png',
		'sizes' => '192x192',
		'type'  => 'image/png'
		),
		array(
		'src'   => $base . 'favicon-512x512.png',
		'sizes' => '512x512',
		'type'  => 'image/png'
		),
		),
		'theme_color'      => $theme_color,
		'background_color' => $background_color,
		'display'          => 'standalone',
		);

		@file_put_contents(
		$favicon_dir . 'site.webmanifest',
		json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
		);
	}

/**
* Запись meta-файла.
*
* @param string $meta_file
* @param array  $meta
* @return void
*/
	private function writeMeta($meta_file, array $meta) {
		@file_put_contents(
		$meta_file,
		json_encode($meta, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
		);
	}

/**
* Получить название магазина.
*
* @param int $store_id
* @return string
*/
	private function getStoreName($store_id) {
		$this->load->model('setting/setting');
		$settings = $this->model_setting_setting->getSetting('config', (int)$store_id);

		$name = '';

		if (is_array($settings) && !empty($settings['config_name'])) {
			$name = (string)$settings['config_name'];
		}

		$name = trim($name);

		return $name !== '' ? $name : 'Site';
	}

/**
* Сокращённое имя для manifest.
*
* @param string $name
* @return string
*/
	private function makeShortName($name) {
		$name = trim((string)$name);

		if ($name === '') {
			return 'Site';
		}

		$limit = 20;

		if (function_exists('mb_strlen') && function_exists('mb_substr')) {
			if (mb_strlen($name, 'UTF-8') > $limit) {
				return mb_substr($name, 0, $limit, 'UTF-8');
			}

			return $name;
		}

		if (strlen($name) > $limit) {
			return substr($name, 0, $limit);
		}

		return $name;
	}
}