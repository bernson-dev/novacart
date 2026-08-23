<?php
namespace Cache;

class Redis {
	private $expire;
	private $cache;

	public function __construct($expire = 3600) {
		if (!extension_loaded('redis')) {
			throw new \RuntimeException('The server does not support redis extension!');
		}
		$this->expire = $expire;

		$this->cache = new \Redis();
		if (!$this->cache->pconnect(CACHE_HOSTNAME, CACHE_PORT)) {
			throw new \RuntimeException('Redis connection failed!');
		}

		if (!empty(CACHE_PASSWORD)) {
			if (!$this->cache->auth((string)CACHE_PASSWORD)) {
				throw new \RuntimeException('Redis: wrong password!');
			}
		}
	}

	public function get($key) {
		$data = $this->cache->get(CACHE_PREFIX . $key);
		if ($data === false || $data === null) {
			return null;
		}

		try {
			return json_decode($data, true, 512, JSON_THROW_ON_ERROR);
		} catch (\JsonException $e) {
			return null;
		}
	}

	public function set($key, $value, $expire = 0): bool {
		if (!$expire) {
			$expire = $this->expire;
		}

		try {
			$json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
		} catch (\JsonException $e) {
			return false;
		}

		return $this->cache->set(CACHE_PREFIX . $key, $json, $expire);
	}

	public function delete($key): bool {
		$pattern = CACHE_PREFIX . $key . '*';
		$iterator = null;
		$deleted = 0;
		do {
			$keys = $this->cache->scan($iterator, $pattern, 1000);
			if ($keys !== false && !empty($keys)) {
				foreach ($keys as $matched_key) {
					$deleted += $this->cache->del($matched_key);
				}
			}
		} while ($iterator > 0);
		return $deleted > 0;
	}

	public function flush(): bool {
		return $this->cache->flushDB();
	}
}
