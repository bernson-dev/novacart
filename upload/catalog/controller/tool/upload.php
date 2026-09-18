<?php
class ControllerToolUpload extends Controller {
	public function index() {
		$this->load->language('tool/upload');

		$json = array();

		if (!empty($this->request->files['file']['name']) && is_file($this->request->files['file']['tmp_name'])) {
			// Sanitize the filename
			$filename = basename(preg_replace('/[^a-zA-Z0-9\.\-\s+]/', '', html_entity_decode($this->request->files['file']['name'], ENT_QUOTES, 'UTF-8')));

			// Validate the filename length
			if ((utf8_strlen($filename) < 3) || (utf8_strlen($filename) > 64)) {
				$json['error'] = $this->language->get('error_filename');
			}

			// Allowed file extension types
			$allowed = array();

			$extension_allowed = preg_replace('~\r?\n~', "\n", $this->config->get('config_file_ext_allowed'));

			$filetypes = explode("\n", $extension_allowed);

			foreach ($filetypes as $filetype) {
				$filetype = strtolower(trim($filetype));

				if ($filetype !== '') {
					$allowed[] = $filetype;
				}
			}

			$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

			if (!$extension || !in_array($extension, $allowed, true)) {
				$json['error'] = $this->language->get('error_filetype');
			}

			// Allowed file mime types
			$allowed = array();

			$mime_allowed = preg_replace('~\r?\n~', "\n", $this->config->get('config_file_mime_allowed'));

			$filetypes = explode("\n", $mime_allowed);

			foreach ($filetypes as $filetype) {
				$filetype = strtolower(trim($filetype));

				if ($filetype !== '') {
					$allowed[] = $filetype;
				}
			}

			$mime = '';

			if (function_exists('finfo_open')) {
				$finfo = finfo_open(FILEINFO_MIME_TYPE);

				if ($finfo) {
					$mime = finfo_file($finfo, $this->request->files['file']['tmp_name']);
					finfo_close($finfo);
				}
			} elseif (function_exists('mime_content_type')) {
				$mime = mime_content_type($this->request->files['file']['tmp_name']);
			}

			$mime = strtolower((string)$mime);

			if (!$mime || !in_array($mime, $allowed, true)) {
				$json['error'] = $this->language->get('error_filetype');
			}

			// Check to see if any PHP files are trying to be uploaded
			$content = file_get_contents($this->request->files['file']['tmp_name']);

			if (preg_match('/\<\?php/i', $content)) {
				$json['error'] = $this->language->get('error_filetype');
			}

			// Return any upload error
			if ($this->request->files['file']['error'] != UPLOAD_ERR_OK) {
				$json['error'] = $this->language->get('error_upload_' . $this->request->files['file']['error']);
			}
		} else {
			$json['error'] = $this->language->get('error_upload');
		}

		if (!$json) {
			$file = $filename . '.' . token(32);

			if (!move_uploaded_file($this->request->files['file']['tmp_name'], DIR_UPLOAD . $file)) {
				$json['error'] = $this->language->get('error_upload');
			} else {
				// Hide the uploaded file name so people can not link to it directly.
				$this->load->model('tool/upload');

				$json['code'] = $this->model_tool_upload->addUpload($filename, $file);
				$json['success'] = $this->language->get('text_upload');
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
