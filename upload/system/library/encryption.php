<?php
class Encryption {
	private $cipher = 'aes-256-ctr';
	private $digest = 'sha256';

	public function encrypt(string $key, string $value): string {
		$key       = openssl_digest($key, $this->digest, true);
		$iv_length = openssl_cipher_iv_length($this->cipher);
		$iv        = random_bytes($iv_length);

		$encrypted = openssl_encrypt($value, $this->cipher, $key, OPENSSL_RAW_DATA, $iv);
		if ($encrypted === false) {
			throw new \RuntimeException('Encryption failed');
		}

		return base64_encode($iv . $encrypted);
	}

	public function decrypt(string $key, string $value): ?string {
		$key       = openssl_digest($key, $this->digest, true);
		$iv_length = openssl_cipher_iv_length($this->cipher);
		$value     = base64_decode($value, true);

		if ($value === false) {
			return null;
		}

		$iv    = substr($value, 0, $iv_length);
		$value = substr($value, $iv_length);

		if (strlen($iv) !== $iv_length) {
			return null;
		}

		$decrypted = openssl_decrypt($value, $this->cipher, $key, OPENSSL_RAW_DATA, $iv);
		return $decrypted === false ? null : $decrypted;
	}
}
