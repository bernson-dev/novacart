<?php

/**
* Log class
*/
class Log {
/** @var resource|false */
	private $handle;

/** @var string */
	private $filename;

/** @var int Максимальный размер файла лога в байтах (по умолчанию 5 МБ) */
	private $max_size = 5242880;

/** @var int Количество файлов ротации */
	private $max_files = 5;

/**
* Constructor
*
* @param string $filename
* @param int $max_size Максимальный размер файла в байтах (0 = без ограничений)
* @param int $max_files Количество файлов ротации
*/
	public function __construct(string $filename, int $max_size = 5242880, int $max_files = 5) {
		$filepath = DIR_LOGS . $filename;

		if (!is_dir(DIR_LOGS)) {
			throw new \RuntimeException("Log directory does not exist: " . DIR_LOGS);
		}

		$this->filename = $filename;
		$this->max_size = $max_size;
		$this->max_files = $max_files;

		// Проверка необходимости ротации перед открытием файла
		if ($max_size > 0 && file_exists($filepath)) {
			$this->rotate($filepath);
		}

		$this->handle = @fopen($filepath, 'ab');

		if ($this->handle === false) {
			throw new \RuntimeException("Failed to open log file: $filepath");
		}
	}

/**
* Ротация лог-файлов
*
* @param string $filepath Путь к файлу
* @return void
*/
	private function rotate(string $filepath): void {
		if (!file_exists($filepath)) {
			return;
		}

		$filesize = filesize($filepath);

		if ($filesize < $this->max_size) {
			return;
		}

		// Удаляем самый старый файл ротации
		$oldest_file = $filepath . '.' . $this->max_files;
		if (file_exists($oldest_file)) {
			@unlink($oldest_file);
		}

		// Сдвигаем файлы: .4 -> .5, .3 -> .4 и т.д.
		for ($i = $this->max_files - 1; $i >= 1; $i--) {
			$old_file = $filepath . '.' . $i;
			$new_file = $filepath . '.' . ($i + 1);

			if (file_exists($old_file)) {
				@rename($old_file, $new_file);
			}
		}

		// Переименовываем текущий файл в .1
		@rename($filepath, $filepath . '.1');
	}

/**
* Write message to log
*
* @param string|array|object $message
* @return void
*/
	public function write($message): void {
		if (!$this->handle) {
			return;
		}

		$timestamp = date('Y-m-d H:i:s');
		$output = $timestamp . ' - ' . print_r($message, true) . PHP_EOL;

		// Используем эксклюзивную блокировку для предотвращения состояния гонки
		// при одновременной записи из разных процессов (admin/catalog)
		if (flock($this->handle, LOCK_EX)) {
			fwrite($this->handle, $output);
			fflush($this->handle); // Принудительная запись на диск
			flock($this->handle, LOCK_UN);
		} else {
			// Если не удалось получить блокировку, пробуем записать без неё (fallback)
			// Это может привести к частичной потере данных при высокой нагрузке,
			// но предотвратит полную остановку логирования
			fwrite($this->handle, $output);
		}

		// Проверка размера после записи для следующей ротации
		if ($this->max_size > 0) {
			$filepath = DIR_LOGS . $this->filename;
			if (file_exists($filepath) && filesize($filepath) >= $this->max_size) {
				// Закрываем текущий хендл, выполняем ротацию и открываем новый
				fclose($this->handle);
				$this->rotate($filepath);
				$this->handle = @fopen($filepath, 'ab');
			}
		}
	}

/**
* Destructor
*/
	public function __destruct() {
		if (is_resource($this->handle)) {
			flock($this->handle, LOCK_UN); // Снимаем блокировку если есть
			fclose($this->handle);
		}
	}
}
