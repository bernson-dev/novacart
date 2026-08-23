<?php
/**
* Image class (совместим с PHP 7.3+)
*/
class Image {
	private $file;
	private $image;
	private $width;
	private $height;
	private $bits;
	private $mime;

	public function __construct(string $file) {
		if (!extension_loaded('gd')) {
			throw new \RuntimeException('Error: PHP GD is not installed!');
		}

		if (!is_file($file)) {
			error_log('Error: Could not load image ' . $file . '!');
			return;
		}

		$this->file = $file;
		$info = getimagesize($file);

		$this->width  = $info[0];
		$this->height = $info[1];
		$this->bits   = $info['bits'] ?? '';
		$this->mime   = $info['mime'] ?? '';

		switch ($this->mime) {
			case 'image/gif':
				$this->image = imagecreatefromgif($file);
				break;
			case 'image/png':
				$this->image = imagecreatefrompng($file);
				break;
			case 'image/jpeg':
				$this->image = imagecreatefromjpeg($file);
				break;
			case 'image/webp':
				if (function_exists('imagecreatefromwebp')) {
					$this->image = imagecreatefromwebp($file);
				}
				break;
		}
	}

	public function getFile(): ?string {
		return $this->file;
	}
	public function getImage() {
		return $this->image;
	}
	public function getWidth(): int {
		return $this->width;
	}
	public function getHeight(): int {
		return $this->height;
	}
	public function getBits(): string {
		return $this->bits;
	}
	public function getMime(): string {
		return $this->mime;
	}

	public function save(string $file, int $quality = 90): void {
		$info = pathinfo($file);
		$ext = strtolower($info['extension'] ?? '');

		if ($this->image instanceof \GdImage || is_resource($this->image)) {
			switch ($ext) {
				case 'jpeg':
				case 'jpg':
					imagejpeg($this->image, $file, $quality);
					break;
				case 'png':
					imagepng($this->image, $file);
					break;
				case 'gif':
					imagegif($this->image, $file);
					break;
				case 'webp':
					if (function_exists('imagewebp')) {
						imagewebp($this->image, $file);
					}
					break;
			}
		}
	}

	public function resize(int $width = 0, int $height = 0, string $default = ''): void {
		if (!$this->width || !$this->height)
			return;

		$scale_w = $width / $this->width;
		$scale_h = $height / $this->height;
		$scale = ($default === 'w') ? $scale_w : (($default === 'h') ? $scale_h : min($scale_w, $scale_h));

		$new_width  = (int)($this->width * $scale);
		$new_height = (int)($this->height * $scale);
		$xpos = (int)(($width - $new_width) / 2);
		$ypos = (int)(($height - $new_height) / 2);

		$image_old = $this->image;
		$this->image = imagecreatetruecolor($width, $height);

		if (in_array($this->mime, ['image/png', 'image/webp'])) {
			imagealphablending($this->image, false);
			imagesavealpha($this->image, true);
			$background = imagecolorallocatealpha($this->image, 255, 255, 255, 127);
			imagecolortransparent($this->image, $background);
		} else {
			$background = imagecolorallocate($this->image, 255, 255, 255);
		}

		imagefilledrectangle($this->image, 0, 0, $width, $height, $background);
		imagecopyresampled($this->image, $image_old, $xpos, $ypos, 0, 0, $new_width, $new_height, $this->width, $this->height);

		$this->width  = $width;
		$this->height = $height;
	}

	public function crop(int $top_x, int $top_y, int $bottom_x, int $bottom_y): void {
		$image_old = $this->image;
		$this->image = imagecreatetruecolor($bottom_x - $top_x, $bottom_y - $top_y);
		imagecopy($this->image, $image_old, 0, 0, $top_x, $top_y, $this->width, $this->height);
		$this->width  = $bottom_x - $top_x;
		$this->height = $bottom_y - $top_y;
	}

	public function rotate(int $degree, string $color = 'FFFFFF'): void {
		$rgb = $this->html2rgb($color);
		$this->image = imagerotate($this->image, $degree, imagecolorallocate($this->image, $rgb[0], $rgb[1], $rgb[2]));
		$this->width  = imagesx($this->image);
		$this->height = imagesy($this->image);
	}

	public function watermark(Image $watermark, string $position = 'bottomright'): void {
		switch ($position) {
			case 'topleft':       $x = 0; $y = 0; break;
			case 'topcenter':     $x = intval(($this->width - $watermark->getWidth()) / 2); $y = 0; break;
			case 'topright':      $x = $this->width - $watermark->getWidth(); $y = 0; break;
			case 'middleleft':    $x = 0; $y = intval(($this->height - $watermark->getHeight()) / 2); break;
			case 'middlecenter':  $x = intval(($this->width - $watermark->getWidth()) / 2); $y = intval(($this->height - $watermark->getHeight()) / 2); break;
			case 'middleright':   $x = $this->width - $watermark->getWidth(); $y = intval(($this->height - $watermark->getHeight()) / 2); break;
			case 'bottomleft':    $x = 0; $y = $this->height - $watermark->getHeight(); break;
			case 'bottomcenter':  $x = intval(($this->width - $watermark->getWidth()) / 2); $y = $this->height - $watermark->getHeight(); break;
			default:              $x = $this->width - $watermark->getWidth(); $y = $this->height - $watermark->getHeight(); break;
		}

		imagealphablending($this->image, true);
		imagesavealpha($this->image, true);
		imagecopy($this->image, $watermark->getImage(), $x, $y, 0, 0, $watermark->getWidth(), $watermark->getHeight());
	}

	public function text(string $text, int $x = 0, int $y = 0, int $size = 5, string $color = '000000'): void {
		$rgb = $this->html2rgb($color);
		imagestring($this->image, $size, $x, $y, $text, imagecolorallocate($this->image, $rgb[0], $rgb[1], $rgb[2]));
	}

	private function html2rgb(string $color): array {
		if ($color[0] === '#')
			$color = substr($color, 1);
		if (strlen($color) === 6) {
			[$r, $g, $b] = [substr($color,0,2), substr($color,2,2), substr($color,4,2)];
		} elseif (strlen($color) === 3) {
			[$r, $g, $b] = [$color[0].$color[0], $color[1].$color[1], $color[2].$color[2]];
		} else {
			return [0,0,0];
		}
		return [hexdec($r), hexdec($g), hexdec($b)];
	}

	public function destroy(): void {
		if ($this->image instanceof \GdImage || is_resource($this->image)) {
			imagedestroy($this->image);
		}
	}
}
