<?php

class SvgPreview {
	public function __construct($registry) {
	}

	public function render($path, $color = '#000000', $use_original = false) {
		$path = ltrim(str_replace('\\', '/', (string)$path), '/');

		if ($path === '') {
			return false;
		}

		$base = realpath(DIR_IMAGE);

		if ($base === false) {
			return false;
		}

		$full = realpath(DIR_IMAGE . $path);

		if ($full === false || !is_file($full)) {
			return false;
		}

		$base = rtrim(str_replace('\\', '/', $base), '/') . '/';
		$full_normalized = str_replace('\\', '/', $full);

		// Файл должен находиться только внутри DIR_IMAGE.
		if (strpos($full_normalized, $base) !== 0) {
			return false;
		}

		if (strtolower(pathinfo($full, PATHINFO_EXTENSION)) !== 'svg') {
			return false;
		}

		$svg = file_get_contents($full);

		if ($svg === false || $svg === '') {
			return false;
		}

		if ($use_original) {
			return $svg;
		}

		if (!preg_match('/^#[A-Fa-f0-9]{6}$/', $color)) {
			$color = '#000000';
		}

		$svg = preg_replace_callback(
			'~<svg\\b([^>]*)>~i',
			function ($match) use ($color) {
				$attrs = $match[1];

				if (preg_match('~\\sstyle\\s*=\\s*([\'\"])(.*?)\\1~i', $attrs)) {
					$attrs = preg_replace(
						'~(\\sstyle\\s*=\\s*)([\'\"])(.*?)\\2~i',
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

		$svg = preg_replace(
			'~\\sfill\\s*=\\s*([\'\"])(?!none\\1)(?!url\\()([^\'\"]+)\\1~i',
			' fill="currentColor"',
			$svg
		);

		$svg = preg_replace(
			'~\\sstroke\\s*=\\s*([\'\"])(?!none\\1)(?!url\\()([^\'\"]+)\\1~i',
			' stroke="currentColor"',
			$svg
		);

		return $svg;
	}
}
