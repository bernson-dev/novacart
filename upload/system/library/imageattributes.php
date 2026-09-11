<?php
class ImageAttributes {
	private static $sizeCache = array();

	public static function enhance($html) {
		if (!is_string($html) || $html === '') {
			return $html;
		}

		if (!preg_match('/^\s*(?:<!doctype\s+html\b|<html\b)/i', $html)) {
			return $html;
		}

		return preg_replace_callback('/<img\b[^>]*>/i', array(__CLASS__, 'enhanceTag'), $html);
	}

	private static function enhanceTag($matches) {
		$tag = $matches[0];
		$has_width = preg_match('/\bwidth\s*=\s*(["\']).*?\1/i', $tag);
		$has_height = preg_match('/\bheight\s*=\s*(["\']).*?\1/i', $tag);

		if (!$has_width && !$has_height) {
			$sources = self::getTagSources($tag);

			foreach ($sources as $src) {
				$size = self::getSize($src);

				if ($size) {
					$tag = self::appendAttributes($tag, ' width="' . $size[0] . '" height="' . $size[1] . '"');
					break;
				}
			}
		}

		return self::ensureAlt($tag);
	}

	private static function getTagSources($tag) {
		$sources = array();
		$lazy_attributes = array('data-src', 'data-original', 'data-lazy-src');

		foreach ($lazy_attributes as $attribute) {
			if (preg_match('/\b' . preg_quote($attribute, '/') . '\s*=\s*(["\'])(.*?)\1/i', $tag, $match)) {
				$src = html_entity_decode($match[2], ENT_QUOTES, 'UTF-8');

				if ($src !== '' && !in_array($src, $sources, true)) {
					$sources[] = $src;
				}
			}
		}

		// Lazy-load placeholders are often 1x1 images and must not define
		// the intrinsic dimensions of the real image.
		if ($sources) {
			return $sources;
		}

		if (preg_match('/\bsrc\s*=\s*(["\'])(.*?)\1/i', $tag, $match)) {
			$src = html_entity_decode($match[2], ENT_QUOTES, 'UTF-8');

			if ($src !== '') {
				$sources[] = $src;
			}
		}

		return $sources;
	}

	private static function getSize($src) {
		if (array_key_exists($src, self::$sizeCache)) {
			return self::$sizeCache[$src];
		}

		$size = self::detectSize($src);
		self::$sizeCache[$src] = $size;

		return $size;
	}

	private static function detectSize($src) {
		if (stripos($src, 'data:image/svg+xml') === 0) {
			return self::getSvgDataSize($src);
		}

		if (!self::isLocalUrl($src)) {
			return false;
		}

		$src_path = parse_url($src, PHP_URL_PATH);

		if (!is_string($src_path) || $src_path === '') {
			return false;
		}

		$src_path = rawurldecode(str_replace('\\', '/', $src_path));

		// Standard OpenCart resized images already contain the requested size.
		if (stripos($src_path, '/image/cache/') !== false && preg_match('/-(\d+)x(\d+)\.[a-z0-9]+$/i', $src_path, $size_match)) {
			$width = (int)$size_match[1];
			$height = (int)$size_match[2];

			if ($width > 0 && $height > 0) {
				return array($width, $height);
			}
		}

		$file = self::resolveLocalFile($src_path);

		if (!$file) {
			return false;
		}

		$extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

		if ($extension === 'svg') {
			return self::getSvgFileSize($file);
		}

		if ($extension === 'svgz') {
			$content = file_get_contents($file);

			if ($content !== false && function_exists('gzdecode')) {
				$content = gzdecode($content);
				return is_string($content) ? self::getSvgSizeFromContent($content) : false;
			}

			return false;
		}

		$size = @getimagesize($file);

		if ($size && !empty($size[0]) && !empty($size[1])) {
			return array((int)$size[0], (int)$size[1]);
		}

		return false;
	}

	private static function isLocalUrl($src) {
		$parts = parse_url($src);

		if ($parts === false) {
			return false;
		}

		$scheme = isset($parts['scheme']) ? strtolower($parts['scheme']) : '';
		$host = isset($parts['host']) ? strtolower($parts['host']) : '';

		if ($host === '') {
			return $scheme === '';
		}

		if ($scheme !== '' && $scheme !== 'http' && $scheme !== 'https') {
			return false;
		}

		$request_hosts = array();

		foreach (array('HTTP_HOST', 'SERVER_NAME') as $server_key) {
			if (!empty($_SERVER[$server_key])) {
				$request_host = parse_url('http://' . ltrim((string)$_SERVER[$server_key], '/'), PHP_URL_HOST);

				if (is_string($request_host) && $request_host !== '') {
					$request_host = strtolower($request_host);

					if (!in_array($request_host, $request_hosts, true)) {
						$request_hosts[] = $request_host;
					}
				}
			}
		}

		return in_array($host, $request_hosts, true);
	}

	private static function resolveLocalFile($src_path) {
		$path = '/' . ltrim($src_path, '/');
		$candidates = array();

		if (defined('DIR_IMAGE')) {
			$pos = stripos($path, '/image/');

			if ($pos !== false) {
				$relative = substr($path, $pos + 7);
				$candidates[] = array(DIR_IMAGE, $relative);
			}
		}

		if (defined('DIR_APPLICATION')) {
			$pos = stripos($path, '/catalog/language/');

			if ($pos !== false) {
				$relative = substr($path, $pos + 18);
				$candidates[] = array(DIR_APPLICATION . 'language/', $relative);
			}

			// Admin language flags use language/<code>/<code>.png.
			if (strpos($path, '/language/') === 0) {
				$candidates[] = array(DIR_APPLICATION . 'language/', substr($path, 10));
			}
		}

		foreach ($candidates as $candidate) {
			$base = realpath($candidate[0]);
			$file = realpath($candidate[0] . str_replace('/', DIRECTORY_SEPARATOR, $candidate[1]));

			if ($base && $file) {
				$base = rtrim(str_replace('\\', '/', $base), '/') . '/';
				$normalized_file = str_replace('\\', '/', $file);

				if (strpos($normalized_file, $base) === 0 && is_file($file)) {
					return $file;
				}
			}
		}

		return false;
	}

	private static function getSvgFileSize($file) {
		$content = file_get_contents($file, false, null, 0, 16384);

		return is_string($content) ? self::getSvgSizeFromContent($content) : false;
	}

	private static function getSvgDataSize($src) {
		$comma = strpos($src, ',');

		if ($comma === false) {
			return false;
		}

		$meta = substr($src, 0, $comma);
		$data = substr($src, $comma + 1);
		$content = stripos($meta, ';base64') !== false ? base64_decode($data, true) : rawurldecode($data);

		return is_string($content) ? self::getSvgSizeFromContent($content) : false;
	}

	private static function getSvgSizeFromContent($content) {
		if (!preg_match('/<svg\b([^>]*)>/i', $content, $svg_match)) {
			return false;
		}

		$attributes = $svg_match[1];
		$width = self::getSvgNumericAttribute($attributes, 'width');
		$height = self::getSvgNumericAttribute($attributes, 'height');

		if ($width > 0 && $height > 0) {
			return array((int)round($width), (int)round($height));
		}

		if (preg_match('/\bviewBox\s*=\s*(["\'])\s*[-+0-9.eE]+[\s,]+[-+0-9.eE]+[\s,]+([-+0-9.eE]+)[\s,]+([-+0-9.eE]+)\s*\1/i', $attributes, $viewbox_match)) {
			$width = (float)$viewbox_match[2];
			$height = (float)$viewbox_match[3];

			if ($width > 0 && $height > 0) {
				return array((int)round($width), (int)round($height));
			}
		}

		return false;
	}

	private static function getSvgNumericAttribute($attributes, $name) {
		if (preg_match('/\b' . preg_quote($name, '/') . '\s*=\s*(["\'])\s*([0-9]+(?:\.[0-9]+)?)\s*(?:px)?\s*\1/i', $attributes, $match)) {
			return (float)$match[2];
		}

		return 0;
	}

	private static function ensureAlt($tag) {
		if (!preg_match('/\balt\s*=\s*(["\']).*?\1/i', $tag)) {
			$tag = self::appendAttributes($tag, ' alt=""');
		}

		return $tag;
	}

	private static function appendAttributes($tag, $attributes) {
		if (substr($tag, -2) === '/>') {
			return substr($tag, 0, -2) . $attributes . ' />';
		}

		return substr($tag, 0, -1) . $attributes . '>';
	}
}
