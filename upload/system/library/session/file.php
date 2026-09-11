<?php
namespace Session;

class File {
	private $directory;

	public function read($session_id) {
		$file = DIR_SESSION . 'sess_' . basename($session_id);

		if (!is_file($file)) {
			return array();
		}

		$handle = fopen($file, 'r');

		if (!$handle) {
			return array();
		}

		flock($handle, LOCK_SH);

		$size = filesize($file);
		$data = $size > 0 ? fread($handle, $size) : '';

		flock($handle, LOCK_UN);
		fclose($handle);

		if ($data === '') {
			return array();
		}

		$result = @unserialize($data);

		return is_array($result) ? $result : array();
	}

	public function write($session_id, $data) {
		if (!$session_id) {
			return false;
		}

		$file = DIR_SESSION . 'sess_' . basename($session_id);
		$handle = fopen($file, 'w');

		if (!$handle) {
			return false;
		}

		flock($handle, LOCK_EX);

		$result = fwrite($handle, serialize($data)) !== false;

		fflush($handle);
		flock($handle, LOCK_UN);
		fclose($handle);

		return $result;
	}

	public function destroy($session_id) {
		$file = DIR_SESSION . 'sess_' . basename($session_id);

		if (is_file($file)) {
			return unlink($file);
		}

		return true;
	}

	public function gc($session_id = '') {
		$gc_divisor = (int)ini_get('session.gc_divisor');
		$gc_probability = (int)ini_get('session.gc_probability');
		$gc_maxlifetime = (int)ini_get('session.gc_maxlifetime');

		if ($gc_divisor < 1) {
			$gc_divisor = 100;
		}

		if ($gc_probability < 0) {
			$gc_probability = 0;
		}

		if ($gc_maxlifetime < 1) {
			$gc_maxlifetime = 1440;
		}

		if ($gc_probability === 0 || mt_rand(1, $gc_divisor) > $gc_probability) {
			return true;
		}

		$expire = time() - $gc_maxlifetime;
		$files = glob(DIR_SESSION . 'sess_*');

		if ($files === false) {
			return false;
		}

		foreach ($files as $file) {
			if (is_file($file) && filemtime($file) < $expire) {
				@unlink($file);
			}
		}

		return true;
	}
}
