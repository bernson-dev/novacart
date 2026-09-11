<?php
class ImageAttributes {
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

		if (!preg_match('/\bsrc\s*=\s*(["\'])(.*?)\1/i', $tag, $src_match)) {
			return self::ensureAlt($tag);
		}

		$src = html_entity_decode($src_match[2], ENT_QUOTES, 'UTF-8');
		$src_path = parse_url($src, PHP_URL_PATH);

		if (!is_string($src_path) || stripos($src_path, '/image/cache/') === false) {
			return self::ensureAlt($tag);
		}

		$has_width = preg_match('/\bwidth\s*=\s*(["\']).*?\1/i', $tag);
		$has_height = preg_match('/\bheight\s*=\s*(["\']).*?\1/i', $tag);

		if (!$has_width && !$has_height && preg_match('/-(\d+)x(\d+)\.[a-z0-9]+$/i', $src_path, $size_match)) {
			$width = (int)$size_match[1];
			$height = (int)$size_match[2];

			if ($width > 0 && $height > 0) {
				$tag = self::appendAttributes($tag, ' width="' . $width . '" height="' . $height . '"');
			}
		}

		return self::ensureAlt($tag);
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
