<?php
namespace Cache;
class UniversalMem {
	private $expire;
	private $driver; // либо \Memcached, либо \Memcache
	private $isMemcached = false;

	public const CACHEDUMP_LIMIT = 9999;

	public function __construct($expire = 3600) {
		$this->expire = $expire;

		if (class_exists('\Memcached')) {
			$this->driver = new \Memcached();
			$this->driver->addServer(CACHE_HOSTNAME, CACHE_PORT);
			if (!$this->driver->getServerList()) {
				throw new \RuntimeException("Memcached connection failed");
			}
			$this->isMemcached = true;
		} elseif (class_exists('\Memcache')) {
			$this->driver = new \Memcache();
			if (!$this->driver->pconnect(CACHE_HOSTNAME, CACHE_PORT)) {
				throw new \RuntimeException("Memcache connection failed");
			}
		} else {
			throw new \RuntimeException("Neither Memcached nor Memcache extension is available");
		}
	}

	public function get($key) {
		return $this->driver->get(CACHE_PREFIX . $key);
	}

	public function set($key, $value, $expire = 0): bool {
		if (!$expire) {
			$expire = $this->expire;
		}

		if ($this->isMemcached) {
			// Memcached API: set(key, value, expire)
			return $this->driver->set(CACHE_PREFIX . $key, $value, $expire);
		} else {
			// Memcache API: set(key, value, flags, expire)
			$flags = defined('MEMCACHE_COMPRESSED') ? MEMCACHE_COMPRESSED : 0;
			return $this->driver->set(CACHE_PREFIX . $key, $value, $flags, $expire);
		}
	}

	public function delete($key): bool {
		return $this->driver->delete(CACHE_PREFIX . $key);
	}

/**
* Очистка всего кэша
*/
	public function flush(): bool {
		return $this->driver->flush();
	}
}
