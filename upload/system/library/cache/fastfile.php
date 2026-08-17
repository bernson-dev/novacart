<?php
namespace Cache;

/**
 * Fast file cache
 *
 * Main ideas:
 * - TTL is stored in file's mtime (touch(file, time() + $expire))
 * - Files are distributed across subdirectories based on cache key parts
 * - Arrays are stored as JSON, other values as TXT
 * - set() removes files of the same key in all supported formats
 * - Writes are atomic: temporary file + rename
 * - get() uses only lightweight filesystem operations
 *
 * Compatible with PHP 7.3+
 */
class FastFile
{
    private $expire;
    private $defaultFormat;
    private $baseDir;
    private $formats;

    /**
     * Cached resolved paths by key|format.
     *
     * @var array
     */
    private static $pathCache = array();

    /**
     * Directories already confirmed/created during current request.
     *
     * @var array
     */
    private static $createdDirs = array();

    /**
     * @param int|null $expire Default cache lifetime in seconds.
     */
    public function __construct($expire = null)
    {
        $this->expire = ((int)$expire > 0) ? (int)$expire : 3600;

        $base = defined('DIR_CACHE')
            ? rtrim(DIR_CACHE, "/\\")
            : rtrim(sys_get_temp_dir(), "/\\");

        $this->baseDir = $base . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;

        $this->defaultFormat = 'json';
        $this->formats = array($this->defaultFormat);

        if ($this->defaultFormat !== 'txt') {
            $this->formats[] = 'txt';
        }
    }

    /**
     * Read cache value.
     *
     * Returns false when:
     * - cache file does not exist;
     * - cache is expired;
     * - file cannot be read;
     * - JSON data is invalid.
     *
     * @param string      $key
     * @param string|null $format
     *
     * @return mixed|false
     */
    public function get(string $key, ?string $format = null)
    {
        $format = $format !== null ? $format : $this->defaultFormat;

        if (!in_array($format, $this->formats, true)) {
            return false;
        }

        $path = $this->getCachedPath($key, $format);

        if (!is_file($path)) {
            return false;
        }

        $mtime = @filemtime($path);

        if ($mtime === false || $mtime < time()) {
            @unlink($path);
            return false;
        }

        $data = @file_get_contents($path);

        if ($data === false) {
            @unlink($path);
            return false;
        }

        if ($format === 'json') {
            $decoded = json_decode($data, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                @unlink($path);
                return false;
            }

            return $decoded;
        }

        return $data;
    }

    /**
     * Write cache value.
     *
     * Arrays are stored as JSON; all other values are stored as TXT.
     *
     * @param string       $key
     * @param array|string $data
     * @param int          $expire
     *
     * @return bool
     */
    public function set(string $key, $data, int $expire = 0): bool
    {
        $expire = $expire > 0 ? $expire : $this->expire;
        $format = is_array($data) ? 'json' : 'txt';

        $this->delete($key);

        $path = $this->getCachedPath($key, $format);
        $dir = dirname($path);

        if (!$this->ensureDirectory($dir)) {
            return false;
        }

        if ($format === 'json') {
            $payload = json_encode(
                $data,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );

            if ($payload === false) {
                return false;
            }
        } else {
            $payload = (string)$data;
        }

        $tmp = $dir . DIRECTORY_SEPARATOR . uniqid('tmp_', true) . '.tmp';

        $written = @file_put_contents($tmp, $payload, LOCK_EX);

        if ($written === false) {
            @unlink($tmp);
            return false;
        }

        /*
         * On some platforms rename() cannot overwrite an existing file.
         * First try atomic rename; if it fails, remove destination and retry.
         */
        if (!@rename($tmp, $path)) {
            @unlink($path);

            if (!@rename($tmp, $path)) {
                @unlink($tmp);
                return false;
            }
        }

        if (!@touch($path, time() + $expire)) {
            @unlink($path);
            return false;
        }

        return true;
    }

    /**
     * Delete cache files for one key in all supported formats.
     *
     * @param string $key
     *
     * @return void
     */
    public function delete(string $key): void
    {
        foreach ($this->formats as $format) {
            $path = $this->getCachedPath($key, $format);

            if (is_file($path)) {
                @unlink($path);
            }
        }

        // Empty directory cleanup is intentionally not done here.
    }

    /**
     * Delete all expired cache files.
     *
     * Expensive operation: intended for cron/admin usage.
     *
     * @return void
     */
    public function flush(): void
    {
        $now = time();

        foreach ($this->getCacheFiles() as $file) {
            $mtime = @filemtime($file);

            if ($mtime === false || $mtime < $now) {
                @unlink($file);
            }
        }

        $this->cleanupEmptyDirs($this->baseDir);
    }

    /**
     * Delete all cache files.
     *
     * Expensive operation: intended for admin usage.
     *
     * @return void
     */
    public function clear(): void
    {
        foreach ($this->getCacheFiles() as $file) {
            @unlink($file);
        }

        $this->cleanupEmptyDirs($this->baseDir);
    }

    /**
     * Iterate over cache files without building an array of all paths.
     *
     * This keeps memory usage nearly constant even with very large caches.
     *
     * @return \Generator
     */
    private function getCacheFiles(): \Generator
    {
        if (!is_dir($this->baseDir)) {
            return;
        }

        try {
            $directory = new \RecursiveDirectoryIterator(
                $this->baseDir,
                \FilesystemIterator::SKIP_DOTS
            );

            $iterator = new \RecursiveIteratorIterator(
                $directory,
                \RecursiveIteratorIterator::LEAVES_ONLY
            );
        } catch (\UnexpectedValueException $e) {
            return;
        }

        foreach ($iterator as $fileInfo) {
            if (!$fileInfo->isFile()) {
                continue;
            }

            $name = $fileInfo->getFilename();

            if (strpos($name, 'cache.') !== 0) {
                continue;
            }

            yield $fileInfo->getPathname();
        }
    }

    /**
     * Resolve and cache file path for key + format.
     *
     * @param string $key
     * @param string $format
     *
     * @return string
     */
    private function getCachedPath(string $key, string $format): string
    {
        $cacheKey = $key . '|' . $format;

        if (isset(self::$pathCache[$cacheKey])) {
            return self::$pathCache[$cacheKey];
        }

        $path = $this->buildFilePath($key, $format);
        self::$pathCache[$cacheKey] = $path;

        return $path;
    }

    /**
     * Build cache path from key.
     *
     * Parts before the final dot become subdirectories;
     * the final part becomes the filename suffix.
     *
     * @param string $key
     * @param string $format
     *
     * @return string
     */
    private function buildFilePath(string $key, string $format): string
    {
        $sanitized = preg_replace('/[^A-Z0-9\._-]/i', '', $key);

        if ($sanitized === null || $sanitized === '') {
            $sanitized = 'key';
        }

        $parts = array_filter(
            explode('.', $sanitized),
            function ($part) {
                return $part !== '' && $part !== '.' && $part !== '..';
            }
        );

        $basename = array_pop($parts);

        if (!$basename) {
            $basename = 'k';
        }

        $dir = rtrim($this->baseDir, "/\\");

        if (!empty($parts)) {
            $dir .= DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $parts);
        }

        $filename = 'cache.' . $basename . '.' . $format;

        return $dir . DIRECTORY_SEPARATOR . $filename;
    }

    /**
     * Ensure directory exists.
     *
     * @param string $dir
     *
     * @return bool
     */
    private function ensureDirectory(string $dir): bool
    {
        if (isset(self::$createdDirs[$dir])) {
            return true;
        }

        if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
            return false;
        }

        self::$createdDirs[$dir] = true;

        return true;
    }

    /**
     * Remove empty subdirectories under cache base directory.
     *
     * The base directory itself is preserved.
     *
     * @param string $dir
     *
     * @return void
     */
    private function cleanupEmptyDirs(string $dir): void
    {
        $baseDir = rtrim($this->baseDir, "/\\");
        $dir = rtrim($dir, "/\\");

        if (!is_dir($dir)) {
            return;
        }

        try {
            $iterator = new \FilesystemIterator(
                $dir,
                \FilesystemIterator::SKIP_DOTS
            );
        } catch (\UnexpectedValueException $e) {
            return;
        }

        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isDir() && !$fileInfo->isLink()) {
                $this->cleanupEmptyDirs($fileInfo->getPathname());
            }
        }

        if ($dir === $baseDir || !is_dir($dir)) {
            return;
        }

        try {
            $check = new \FilesystemIterator(
                $dir,
                \FilesystemIterator::SKIP_DOTS
            );

            if (!$check->valid()) {
                @rmdir($dir);
                unset(self::$createdDirs[$dir]);
            }
        } catch (\UnexpectedValueException $e) {
            // Directory may have disappeared concurrently.
        }
    }
}
