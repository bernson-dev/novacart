<?php
namespace Cache;
class File {
	private $expire;
	private $gc_probability = 5;
	private $cacheDir;

	public function __construct($expire = 3600) {
		$this->expire = $expire;
		$this->cacheDir = rtrim(DIR_CACHE, '/\\') . DIRECTORY_SEPARATOR;

		if (mt_rand(1, 100) <= $this->gc_probability) {
			$this->gc();
		}
	}

	private function getFilePath($key, $expire = null): string {
		$safeKey = preg_replace('/[^A-Z0-9._\-]/i', '', $key);
		$ttl = $expire ?? (time() + $this->expire);
		return $this->cacheDir . 'cache.' . $safeKey . '.' . $ttl;
	}

	private function findFile($key): ?string {
		$safeKey = preg_replace('/[^A-Z0-9._\-]/i', '', $key);
		$pattern = $this->cacheDir . 'cache.' . $safeKey . '.*';
		$files = glob($pattern);
		if (!$files) {
			return null;
		}
		// Берём самый свежий (если вдруг несколько)
		return array_reduce($files, function($carry, $item) {
			return (!$carry || filemtime($item) > filemtime($carry)) ? $item : $carry;
		});
	}

	public function get($key) {
		$file = $this->findFile($key);
		if (!$file || !is_file($file)) {
			return false;
		}

		// TTL из имени
		$parts = explode('.', $file);
		$expire = (int)end($parts);

		if (time() > $expire) {
			@unlink($file);
			return false;
		}

		$data = @file_get_contents($file);
		if ($data === false) {
			return false;
		}

		try {
			return json_decode($data, true, 512, JSON_THROW_ON_ERROR);
		} catch (\JsonException $e) {
			return false;
		}
	}

	public function set($key, $value, $expire = null): bool {
		$expire = time() + ($expire ?? $this->expire);
		$file = $this->getFilePath($key, $expire);
		$temp = $file . '.' . uniqid('tmp', true) . '.' . getmypid();

		try {
			$json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
		} catch (\JsonException $e) {
			return false;
		}

		if (file_put_contents($temp, $json, LOCK_EX) !== false) {
			return rename($temp, $file);
		}

		return false;
	}

	public function delete($key): void {
		if (strpos($key, '*') !== false) {
			$safePattern = preg_replace('/[^A-Z0-9._\-*]/i', '', $key);
			$pattern = $this->cacheDir . 'cache.' . $safePattern . '.*';
			$files = glob($pattern);
			if ($files) {
				foreach ($files as $file) {
					@unlink($file);
				}
			}
			return;
		}

		$file = $this->findFile($key);
		if ($file && is_file($file)) {
			@unlink($file);
		}
	}

	public function gc(): void {
		$files = glob($this->cacheDir . 'cache.*.*');
		if (!$files) {
			return;
		}

		$now = time();
		foreach ($files as $file) {
			$parts = explode('.', $file);
			$expire = (int)end($parts);
			if ($expire > 0 && $expire < $now) {
				@unlink($file);
			}
		}
	}
}
