<?php
/**
* Генерация SVG-логотипа
*
* @param string $store_name   Название магазина
* @param string $mode         'initials' или 'name'
* @param array  $options      Доп. параметры: width, height, palette
* @return string              data:image/svg+xml URI
*/
function generateStoreLogo(string $store_name, string $mode = 'initials', array $options = []): string {
	$store_name_raw = trim($store_name);
	if ($store_name_raw === '') {
		$store_name_raw = 'Store';
	}

	$store_name = html_entity_decode($store_name_raw, ENT_QUOTES, 'UTF-8');
	$safe_title = htmlspecialchars($store_name, ENT_QUOTES, 'UTF-8');

	// Настройки по умолчанию
	$width  = $options['width']  ?? ($mode === 'name' ? 220 : 64);
	$height = $options['height'] ?? ($mode === 'name' ? 60  : 64);
	$grad_start = $options['grad_start'] ?? '#1E88E5';
	$grad_end   = $options['grad_end']   ?? '#42A5F5';

	if ($mode === 'name') {
		$safe_text = htmlspecialchars($store_name, ENT_QUOTES, 'UTF-8');
		$length = mb_strlen($store_name, 'UTF-8');

		if ($length > 18) {
			$font_size = 14;
		} elseif ($length > 12) {
			$font_size = 18;
		} elseif ($length > 8) {
			$font_size = 22;
		} else {
			$font_size = 26;
		}

		// Вычисляем размеры для прямоугольников
		$rect_width1  = $width - 4;
		$rect_height1 = $height - 4;
		$rect_width2  = $width - 5;
		$rect_height2 = $height - 5;

		$svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="$width" height="$height" viewBox="0 0 $width $height" aria-label="$safe_title" role="img">
<defs>
<linearGradient id="logoGrad" x1="0" y1="0" x2="1" y2="1">
<stop offset="0%" stop-color="$grad_start"/>
<stop offset="100%" stop-color="$grad_end"/>
</linearGradient>
<filter id="shadow" x="-20%" y="-20%" width="140%" height="140%">
<feDropShadow dx="0" dy="1.5" stdDeviation="1.5" flood-color="#000000" flood-opacity="0.18"/>
</filter>
</defs>
<rect x="2" y="2" width="$rect_width1" height="$rect_height1" rx="10" ry="10" fill="url(#logoGrad)" filter="url(#shadow)"/>
<rect x="2.5" y="2.5" width="$rect_width2" height="$rect_height2" rx="10" ry="10" fill="none" stroke="rgba(255,255,255,0.22)" stroke-width="1"/>
<text x="50%" y="50%" text-anchor="middle" dominant-baseline="central"
font-family="system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif"
font-size="$font_size" font-weight="700" letter-spacing="0.3" fill="#ffffff">$safe_text</text>
</svg>
SVG;
	} else {
		// initials
		$words = preg_split('/[\s\-_]+/u', $store_name, -1, PREG_SPLIT_NO_EMPTY);
		$initials = '';
		if (!empty($words)) {
			$initials .= mb_substr($words[0], 0, 1, 'UTF-8');
			if (count($words) >= 2) {
				$initials .= mb_substr($words[1], 0, 1, 'UTF-8');
			}
		}
		$initials = mb_strtoupper($initials ?: 'S', 'UTF-8');
		$safe_initials = htmlspecialchars($initials, ENT_QUOTES, 'UTF-8');

		// Вычисляем радиусы для кругов
		$circle_r1 = ($width / 2) - 2;
		$circle_r2 = ($width / 2) - 3;
		$cx = $width / 2;
		$cy = $height / 2;

		$svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="$width" height="$height" viewBox="0 0 $width $height" aria-label="$safe_title" role="img">
<defs>
<linearGradient id="logoGrad" x1="0" y1="0" x2="1" y2="1">
<stop offset="0%" stop-color="$grad_start"/>
<stop offset="100%" stop-color="$grad_end"/>
</linearGradient>
<filter id="shadow" x="-20%" y="-20%" width="140%" height="140%">
<feDropShadow dx="0" dy="1.5" stdDeviation="1.5" flood-color="#000000" flood-opacity="0.18"/>
</filter>
</defs>
<circle cx="$cx" cy="$cy" r="$circle_r1" fill="url(#logoGrad)" filter="url(#shadow)"/>
<circle cx="$cx" cy="$cy" r="$circle_r2" fill="none" stroke="rgba(255,255,255,0.22)" stroke-width="1"/>
<text x="50%" y="50%" text-anchor="middle" dominant-baseline="central"
font-family="system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif"
font-size="24" font-weight="700" letter-spacing="0.5" fill="#ffffff">$safe_initials</text>
</svg>
SVG;
	}

	return 'data:image/svg+xml;charset=UTF-8,' . rawurlencode($svg);
}