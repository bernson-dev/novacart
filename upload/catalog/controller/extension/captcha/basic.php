<?php
class ControllerExtensionCaptchaBasic extends Controller {
	public function index($error = array()) {
		$this->load->language('extension/captcha/basic');

		$data['error_captcha'] = isset($error['captcha']) ? $error['captcha'] : '';
		$data['route'] = $this->request->get['route'];

		// Генерация кода капчи при загрузке формы
		$this->session->data['captcha'] = $this->generateCode();

		return $this->load->view('extension/captcha/basic', $data);
	}

	public function validate() {
		$this->load->language('extension/captcha/basic');

		if (empty($this->session->data['captcha']) || ($this->session->data['captcha'] != $this->request->post['captcha'])) {
			return $this->language->get('error_captcha');
		}
	}

	public function captcha() {
		$image  = imagecreatetruecolor(150, 35);

		$width  = imagesx($image);
		$height = imagesy($image);

		$black  = imagecolorallocate($image, 0, 0, 0);
		$white  = imagecolorallocate($image, 255, 255, 255);
		$red    = imagecolorallocatealpha($image, 255, 0, 0, 75);
		$green  = imagecolorallocatealpha($image, 0, 255, 0, 75);
		$blue   = imagecolorallocatealpha($image, 0, 0, 255, 75);

		imagefilledrectangle($image, 0, 0, $width, $height, $white);
		imagefilledellipse($image, mt_rand(5, 145), mt_rand(0, 35), 30, 30, $red);
		imagefilledellipse($image, mt_rand(5, 145), mt_rand(0, 35), 30, 30, $green);
		imagefilledellipse($image, mt_rand(5, 145), mt_rand(0, 35), 30, 30, $blue);

		// рамка
		imagerectangle($image, 0, 0, $width - 1, $height - 1, $black);

		// текст капчи
		imagestring(
		$image,
		5,
		(int)(($width - (strlen($this->session->data['captcha']) * 9)) / 2),
		(int)(($height - 15) / 2),
		$this->session->data['captcha'],
		$black
		);

		header('Content-type: image/jpeg');
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

		imagejpeg($image);

		// безопасное освобождение
		$this->safeImageDestroy($image);

		exit();
	}

	// Приватный метод генерации кода
	private function generateCode($length = 6) {
		return substr(sha1(mt_rand()), 17, $length);
	}

	// Универсальное освобождение GD-изображения
	private function safeImageDestroy(&$image) {
		if (is_resource($image) && get_resource_type($image) === 'gd') {
			imagedestroy($image);
		} elseif ($image instanceof GdImage) {
			unset($image);
		}
	}
}
