<?php
namespace Mail;

class Smtp extends \stdClass {
	public $smtp_hostname;
	public $smtp_username;
	public $smtp_password;
	public $smtp_port = 25;
	public $smtp_timeout = 5;
	public $max_attempts = 3;
	public $verp = false;
	public $debug = false; // По умолчанию логирование отключено

	public function send() {
		if (is_array($this->to)) {
			$to = implode(',', $this->to);
		} else {
			$to = $this->to;
		}

		$eol = "\r\n";
		$boundary = '----=_NextPart_' . md5(time());

		// --- Сборка заголовков ---
		$header = 'MIME-Version: 1.0' . $eol;
		$header .= 'To: <' . $to . '>' . $eol;
		$header .= 'Subject: =?UTF-8?B?' . base64_encode($this->subject) . '?=' . $eol;
		$header .= 'Date: ' . date('D, d M Y H:i:s O') . $eol;
		$header .= 'From: =?UTF-8?B?' . base64_encode($this->sender) . '?= <' . $this->from . '>' . $eol;
		$header .= 'Reply-To: =?UTF-8?B?' . base64_encode($this->reply_to ?: $this->sender) . '?= <' . ($this->reply_to ?: $this->from) . '>' . $eol;
		$header .= 'Message-ID: <' . md5(microtime()) . substr($this->from, strrpos($this->from, '@')) . '>' . $eol;
		$header .= 'Return-Path: ' . $this->from . $eol;
		$header .= 'X-Mailer: PHP/' . PHP_VERSION . $eol;
		$header .= 'Content-Type: multipart/mixed; boundary="' . $boundary . '"' . $eol . $eol;

		// --- Сборка тела сообщения ---
		$message = '';
		if (!$this->html) {
			$message .= '--' . $boundary . $eol;
			$message .= 'Content-Type: text/plain; charset="utf-8"' . $eol;
			$message .= 'Content-Transfer-Encoding: base64' . $eol . $eol;
			$message .= chunk_split(base64_encode($this->text)) . $eol;
		} else {
			$message .= '--' . $boundary . $eol;
			$message .= 'Content-Type: multipart/alternative; boundary="' . $boundary . '_alt"' . $eol . $eol;
			$message .= '--' . $boundary . '_alt' . $eol;
			$message .= 'Content-Type: text/plain; charset="utf-8"' . $eol;
			$message .= 'Content-Transfer-Encoding: base64' . $eol . $eol;
			$message .= chunk_split(base64_encode($this->text ?: 'This is a HTML email.')) . $eol;
			$message .= '--' . $boundary . '_alt' . $eol;
			$message .= 'Content-Type: text/html; charset="utf-8"' . $eol;
			$message .= 'Content-Transfer-Encoding: base64' . $eol . $eol;
			$message .= chunk_split(base64_encode($this->html)) . $eol;
			$message .= '--' . $boundary . '_alt--' . $eol;
		}

		// --- Обработка вложений ---
		if (!empty($this->attachments) && is_array($this->attachments)) {
			foreach ($this->attachments as $attachment) {
				if (file_exists($attachment)) {
					$handle = fopen($attachment, 'r');
					$content = fread($handle, filesize($attachment));
					fclose($handle);

					$message .= '--' . $boundary . $eol;
					$message .= 'Content-Type: application/octet-stream; name="' . basename($attachment) . '"' . $eol;
					$message .= 'Content-Transfer-Encoding: base64' . $eol;
					$message .= 'Content-Disposition: attachment; filename="' . basename($attachment) . '"' . $eol;
					$message .= 'Content-ID: <' . urlencode(basename($attachment)) . '>' . $eol . $eol;
					$message .= chunk_split(base64_encode($content));
				}
			}
		}

		$message .= '--' . $boundary . '--' . $eol;

		// --- Подключение ---
		if (strpos($this->smtp_hostname, 'tls://') === 0) {
			$hostname = substr($this->smtp_hostname, 6);
		} else {
			$hostname = $this->smtp_hostname;
		}

		$handle = fsockopen($hostname, $this->smtp_port, $errno, $errstr, $this->smtp_timeout);

		if (!$handle) {
			throw new \Exception('Error: ' . $errstr . ' (' . $errno . ')');
		}

		$this->handleReply($handle, 220, 'Connection Start');

		$server_name = !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';

		fputs($handle, 'EHLO ' . $server_name . $eol);
		$this->handleReply($handle, 250, 'EHLO Command');

		if (strpos($this->smtp_hostname, 'tls://') === 0) {
			fputs($handle, 'STARTTLS' . $eol);
			$this->handleReply($handle, 220, 'STARTTLS Command');

			$crypto_method = STREAM_CRYPTO_METHOD_TLS_CLIENT;
			if (defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')) {
				$crypto_method |= STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
			}

			if (!stream_socket_enable_crypto($handle, true, $crypto_method)) {
				throw new \Exception('Error: TLS Connection Failed');
			}

			fputs($handle, 'EHLO ' . $server_name . $eol);
			$this->handleReply($handle, 250, 'EHLO after TLS');
		}

		if (!empty($this->smtp_username) && !empty($this->smtp_password)) {
			fputs($handle, 'AUTH LOGIN' . $eol);
			$this->handleReply($handle, 334, 'AUTH LOGIN Command');
			fputs($handle, base64_encode($this->smtp_username) . $eol);
			$this->handleReply($handle, 334, 'SMTP Username');
			fputs($handle, base64_encode($this->smtp_password) . $eol);
			$this->handleReply($handle, 235, 'SMTP Password');
		}

		fputs($handle, 'MAIL FROM: <' . $this->from . '>' . ($this->verp ? ' XVERP' : '') . $eol);
		$this->handleReply($handle, 250, 'MAIL FROM Command');

		$recipients = is_array($this->to) ? $this->to : [$this->to];
		foreach ($recipients as $recipient) {
			fputs($handle, 'RCPT TO: <' . $recipient . '>' . $eol);
			$this->handleReply($handle, 250, 'RCPT TO Command');
		}

		fputs($handle, 'DATA' . $eol);
		$this->handleReply($handle, 354, 'DATA Command');

		$data = str_replace(["\r\n", "\r"], "\n", $header . $message);
		$lines = explode("\n", $data);

		foreach ($lines as $line) {
			if (isset($line[0]) && $line[0] === '.') {
				$line = '.' . $line;
			}
			$chunks = ($line === '') ? [''] : str_split($line, 998);
			foreach ($chunks as $chunk) {
				fputs($handle, $chunk . $eol);
			}
		}

		fputs($handle, '.' . $eol);
		$this->handleReply($handle, 250, 'DATA End');

		fputs($handle, 'QUIT' . $eol);
		$this->handleReply($handle, 221, 'QUIT Command');

		fclose($handle);
	}

	private function handleReply($handle, $status_code = false, $error_label = '', $counter = 0) {
		$reply = '';

		while ($line = fgets($handle, 515)) {
			$reply .= $line;
			if (substr($line, 3, 1) == ' ') break;
		}

		// Логируем ответ ТОЛЬКО если включен режим debug
		if ($this->debug) {
			$this->logResponse($error_label, $reply);
		}

		if (!$line && empty($reply) && $counter < $this->max_attempts) {
			sleep(1);
			return $this->handleReply($handle, $status_code, $error_label, ++$counter);
		}

		if ($status_code && substr($reply, 0, 3) != $status_code) {
			throw new \Exception('SMTP Error [' . $error_label . ']: ' . $reply);
		}

		return $reply;
	}

	private function logResponse($stage, $reply) {
		$log_dir = defined('DIR_LOGS') ? DIR_LOGS : (defined('DIR_STORAGE') ? DIR_STORAGE . 'logs/' : '');
		if ($log_dir) {
			$file = $log_dir . 'smtp_debug.log';
			$content = "[" . date('Y-m-d H:i:s') . "] STAGE: $stage | REPLY: " . trim($reply) . PHP_EOL;
			file_put_contents($file, $content, FILE_APPEND);
		}
	}
}
