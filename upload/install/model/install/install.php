<?php
require_once(DIR_APPLICATION . 'model/install/schema_repairer.php');

class ModelInstallInstall extends Model {
	protected $db;

	public function __construct($registry) {
		parent::__construct($registry);

		if (
		defined('DB_DRIVER') &&
		defined('DB_HOSTNAME') &&
		defined('DB_USERNAME') &&
		defined('DB_PASSWORD') &&
		defined('DB_DATABASE') &&
		defined('DB_PORT')
		) {
			$this->db = new DB(
			DB_DRIVER,
			html_entity_decode(DB_HOSTNAME, ENT_QUOTES, 'UTF-8'),
			html_entity_decode(DB_USERNAME, ENT_QUOTES, 'UTF-8'),
			html_entity_decode(DB_PASSWORD, ENT_QUOTES, 'UTF-8'),
			html_entity_decode(DB_DATABASE, ENT_QUOTES, 'UTF-8'),
			DB_PORT
			);
		}
	}

/**
* Установка базы данных из SQL-файла
*
* @param array $data Данные для подключения и установки
* @return void
*/
	public function database($data) {
		// Создаём подключение к базе данных
		$db = new DB(
		$data['db_driver'],
		html_entity_decode($data['db_hostname'], ENT_QUOTES, 'UTF-8'),
		html_entity_decode($data['db_username'], ENT_QUOTES, 'UTF-8'),
		html_entity_decode($data['db_password'], ENT_QUOTES, 'UTF-8'),
		html_entity_decode($data['db_database'], ENT_QUOTES, 'UTF-8'),
		$data['db_port']
		);

		// Проверяем наличие SQL-файла
		$file = DIR_APPLICATION . $data['sql_dump'];
		if (!file_exists($file)) {
			throw new \Exception('Could not load sql file: ' . $file);
		}

		// Устанавливаем кодировку
		$res = $db->query("SELECT VERSION() as version");
		$version = isset($res->row['version']) ? $res->row['version'] : '0';

		// Пробуем вытащить числовую часть (на случай MariaDB "10.x.x-MariaDB")
		if (preg_match('/^\d+(\.\d+){1,3}/', $version, $m)) {
			$version_num = $m[0];
		} else {
			$version_num = $version;
		}

		// utf8mb4 доступен с 5.5.3
		$charset = (version_compare($version_num, '5.5.3', '>=') ? 'utf8mb4' : 'utf8');

		$db->query("SET NAMES '" . $charset . "'");
		$db->query("SET CHARACTER SET " . $charset);
		$db->query("SET @@session.sql_mode = ''");

		// Загружаем SQL-файл потоково в память)
		$handle = fopen($file, 'r');
		if (!$handle) {
			throw new \Exception('Could not open sql file: ' . $file);
		}

		$sql = '';
		$inBlockComment = false;

		while (($line = fgets($handle)) !== false) {
			$line = trim($line);

			if ($line === '') {
				continue;
			}

			// Обработка блочных комментариев /* ... */
			if ($inBlockComment) {
				if (strpos($line, '*/') !== false) {
					$inBlockComment = false;
				}
				continue;
			}
			if (strpos($line, '/*') === 0) {
				if (strpos($line, '*/') === false) {
					$inBlockComment = true;
				}
				continue;
			}

			// Пропускаем однострочные комментарии
			if (strpos($line, '--') === 0 || strpos($line, '#') === 0) {
				continue;
			}

			$sql .= $line . "\n";

			// Запрос завершён ;
			if (substr(rtrim($line), -1) === ';') {
				// Замена префикса
				// Было: `oc_...` -> `myprefix...`
				$sql_exec = str_replace('`oc_', '`' . $data['db_prefix'], $sql);

				$db->query($sql_exec);
				$sql = '';
			}
		}

		fclose($handle);

		// Дополнительно: если что-то накопилось без ;
		$sql = trim($sql);
		if ($sql !== '') {
			$sql_exec = str_replace('`oc_', '`' . $data['db_prefix'], $sql);
			$db->query($sql_exec);
		}

		// Восстановление/синхронизация схемы
		//$this->repairSchemaFromFile($db, $data['db_prefix']);

		// Опциональное восстановление структуры после импорта дампа.
		// Нужно для старых/неполных дампов: добавляет отсутствующие таблицы, колонки и индексы.
		if (!empty($data['repair_schema'])) {
			$repairer = new SchemaRepairer();
			$repairer->repairSchemaFromFile($db, $data['db_prefix']);
		}

		// Управление пользователем
		$db->query(
		"DELETE FROM `" . $data['db_prefix'] . "user` WHERE `user_id` = '1'"
		);

		$password_hash = password_hash(html_entity_decode($data['password'], ENT_QUOTES, 'UTF-8'), PASSWORD_DEFAULT);

		$db->query(
		"INSERT INTO `" . $data['db_prefix'] . "user` SET
			user_id = '1',
			user_group_id = '1',
			username = '" . $db->escape($data['username']) . "',
			password = '" . $db->escape($password_hash) . "',
			firstname = 'Super',
			lastname = 'Admin',
			email = '" . $db->escape($data['email']) . "',
			status = '1',
			date_added = NOW()"
		);

		// Обновляем настройки
		$db->query(
		"DELETE FROM `" . $data['db_prefix'] . "setting` WHERE `key` = 'config_email'"
		);

		$db->query(
		"INSERT INTO `" . $data['db_prefix'] . "setting` SET
			`code` = 'config',
			`key` = 'config_email',
			`value` = '" . $db->escape($data['email']) . "'"
		);

		$db->query(
		"DELETE FROM `" . $data['db_prefix'] . "setting` WHERE `key` = 'config_encryption'"
		);

		$db->query(
		"INSERT INTO `" . $data['db_prefix'] . "setting` SET
			`code` = 'config',
			`key` = 'config_encryption',
			`value` = '" . $db->escape(token(1024)) . "'"
		);

		// Сбрасываем счётчики просмотров
		$db->query(
		"UPDATE `" . $data['db_prefix'] . "product` SET `viewed` = '0'"
		);

		// Создаём API-пользователя
		$db->query("DELETE FROM `" . $data['db_prefix'] . "api`");
		$db->query(
		"INSERT INTO `" . $data['db_prefix'] . "api` SET
			username = 'Default',
			`key` = '" . $db->escape(token(256)) . "',
			status = 1,
			date_added = NOW(),
			date_modified = NOW()"
		);
		$api_id = $db->getLastId();

		// Обновляем настройки API
		$db->query(
		"DELETE FROM `" . $data['db_prefix'] . "setting` WHERE `key` = 'config_api_id'"
		);

		$db->query(
		"INSERT INTO `" . $data['db_prefix'] . "setting` SET
			`code` = 'config',
			`key` = 'config_api_id',
			`value` = '" . (int)$api_id . "'"
		);

		// Устанавливаем префикс счёта
		$db->query(
		"UPDATE `" . $data['db_prefix'] . "setting`
			SET `value` = 'INV-" . date('Y') . "-00'
			WHERE `key` = 'config_invoice_prefix'"
		);

		// --- исправление максимального размера загружаемого файла ---
		$upload_max_filesize = ini_get('upload_max_filesize'); // например "64M"
		$post_max_size       = ini_get('post_max_size');       // например "64M"

		// Функция перевода php.ini значений в мегабайты
		function parseSizeToMb($size) {
			$unit  = strtolower(substr($size, -1));
			$value = (int)$size;
			switch ($unit) {
				case 'g': return $value * 1024;
				case 'm': return $value;
				case 'k': return $value / 1024;
				default:  return $value;
			}
		}

		$max_upload_mb = min(parseSizeToMb($upload_max_filesize), parseSizeToMb($post_max_size));

		// Целевое значение — 20 МБ
		$target = 20;

		// Если PHP позволяет ≥ 20 МБ — ставим 20, иначе максимально доступное
		$value = ($max_upload_mb >= $target) ? $target : $max_upload_mb;

		// Обновляем настройку в таблице setting
		$db->query("UPDATE `" . $data['db_prefix'] . "setting`
				SET `value` = '" . (int)$value . "'
				WHERE `key` = 'config_file_max_size'");


		// Универсальная коррекция таймзоны для совместимости
		$timezone_query = $db->query(
		"SELECT `value` FROM `" . $data['db_prefix'] . "setting`
			WHERE `key` = 'config_timezone' LIMIT 1"
		);

		if ($timezone_query->num_rows) {
			$stored_timezone = $timezone_query->row['value'];
			$supported_timezones = timezone_identifiers_list();

			$timezone_aliases = array(
			'Europe/Kiev' => 'Europe/Kyiv',
			'Asia/Calcutta' => 'Asia/Kolkata',
			'Asia/Rangoon' => 'Asia/Yangon',
			'America/Montreal' => 'America/Toronto',
			'US/Eastern' => 'America/New_York',
			'US/Central' => 'America/Chicago',
			'US/Mountain' => 'America/Denver',
			'US/Pacific' => 'America/Los_Angeles',
			'Canada/Eastern' => 'America/Toronto',
			'Canada/Central' => 'America/Winnipeg',
			'Canada/Mountain' => 'America/Edmonton',
			'Canada/Pacific' => 'America/Vancouver'
			);

			if (!in_array($stored_timezone, $supported_timezones, true)) {
				if (isset($timezone_aliases[$stored_timezone])) {
					$stored_timezone = $timezone_aliases[$stored_timezone];
				} else {
					$reverse_aliases = array_flip($timezone_aliases);
					if (isset($reverse_aliases[$stored_timezone])) {
						$stored_timezone = $reverse_aliases[$stored_timezone];
					} else {
						$stored_timezone = 'UTC';
					}
				}

				$db->query(
				"UPDATE `" . $data['db_prefix'] . "setting`
					SET `value` = '" . $db->escape($stored_timezone) . "'
					WHERE `key` = 'config_timezone'"
				);
			}
		}

		// Очищаем таблицы модификаторов, чтобы избежать конфликтов при установке из дампа
//		$db->query("TRUNCATE TABLE `" . $data['db_prefix'] . "extension_install`");
//		$db->query("TRUNCATE TABLE `" . $data['db_prefix'] . "extension_path`");
//		$db->query("TRUNCATE TABLE `" . $data['db_prefix'] . "modification`");
//		$db->query("TRUNCATE TABLE `" . $data['db_prefix'] . "modification_backup`");

		// Список таблиц для очистки
		$tables = [
			$data['db_prefix'] . "extension_install",
			$data['db_prefix'] . "extension_path",
			$data['db_prefix'] . "modification",
			$data['db_prefix'] . "modification_backup"
		];

		foreach ($tables as $table) {
			$query = $db->query("SHOW TABLES LIKE '" . $db->escape($table) . "'");
			if ($query->num_rows) {
				$db->query("TRUNCATE TABLE `" . $table . "`");
			}
		}
	}

/**
* Удаление демонстрационных данных
*
* @return void
*/
	public function deleteDemoData() {
		$tablePatterns = array(
		'article*',
		'attribute*',
		'*banner*',
		'*blog*',
		'category*',
		'filter*',
		'manufacturer*',
		'option*',
		'product*',
		'review',
		'module',
		'voucher*'
		);

		$tablesToClear = array();

		foreach ($tablePatterns as $pattern) {
			$query = $this->db->query(
			"SHOW TABLES LIKE '" . DB_PREFIX . str_replace('*', '%', $pattern) . "'"
			);
			foreach ($query->rows as $row) {
				$tablesToClear[] = reset($row);
			}
		}

		$tablesToClear = array_values(array_unique($tablesToClear));

		$this->db->query("SET FOREIGN_KEY_CHECKS = 0");

		foreach ($tablesToClear as $table) {
			// Валидация идентификатора таблицы)
			if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
				continue;
			}

			// TRUNCATE быстрее, сбрасывает AUTO_INCREMENT
			$this->db->query("TRUNCATE TABLE `" . $table . "`");
		}

		// Дополнительные таблицы
		$this->db->query(
		"DELETE FROM `" . DB_PREFIX . "setting` WHERE `code` = 'module_filter'"
		);
		$this->db->query(
		"DELETE FROM `" . DB_PREFIX . "extension`
			WHERE `type` = 'module'
				AND `code` IN ('banner', 'carousel', 'featured', 'slideshow', 'filter')"
		);

		// Очищаем "висячие" SeoUrl
		$this->db->query(
		"DELETE FROM `" . DB_PREFIX . "seo_url`
			WHERE
			(query LIKE 'product_id=%' AND SUBSTRING_INDEX(query, '=', -1) NOT IN (
				SELECT product_id FROM `" . DB_PREFIX . "product`
			))
			OR (query LIKE 'information_id=%' AND SUBSTRING_INDEX(query, '=', -1) NOT IN (
				SELECT information_id FROM `" . DB_PREFIX . "information`
			))
			OR (query LIKE 'manufacturer_id=%' AND SUBSTRING_INDEX(query, '=', -1) NOT IN (
				SELECT manufacturer_id FROM `" . DB_PREFIX . "manufacturer`
			))
			OR (query LIKE 'category_id=%' AND SUBSTRING_INDEX(query, '=', -1) NOT IN (
				SELECT category_id FROM `" . DB_PREFIX . "category`
		))"
		);

		$this->db->query(
		"DELETE FROM `" . DB_PREFIX . "layout_module` WHERE `layout_module_id` > '20'"
		);

		$this->db->query("SET FOREIGN_KEY_CHECKS = 1");
	}

/**
* Получение списка стран
*
* @return array
*/
	public function getCountries() {
		$query = $this->db->query(
		"SELECT country_id, name, status
			FROM `" . DB_PREFIX . "country`
			ORDER BY status DESC, LCASE(name)"
		);

		return $query->rows;
	}

/**
* Включение выбранных стран
*
* @param array $countries
* @param int   $default_country
* @return void
*/
	public function enableCountries($countries, $default_country = 0) {
		// 1) Выключаем все страны
		$this->db->query("UPDATE `" . DB_PREFIX . "country` SET status = '0'");

		// 2) Фильтруем входящий массив
		$countries_filtered = array_filter(array_map('intval', (array)$countries));

		if (!empty($countries_filtered)) {
			$in_clause = implode(',', $countries_filtered);

			// Включаем отмеченные
			$this->db->query(
			"UPDATE `" . DB_PREFIX . "country`
				SET status = '1'
				WHERE country_id IN (" . $in_clause . ")"
			);

			$default_country_id = (int)$default_country;

			if ($default_country_id > 0 && !in_array($default_country_id, $countries_filtered, true)) {
				$default_country_id = 0;
			}

			// 3) Определяем default_country_id:
			if ($default_country_id === 0) {
				if (in_array(220, $countries_filtered, true)) {
					$default_country_id = 220; // Украина
				} elseif (in_array(176, $countries_filtered, true)) {
					$default_country_id = 176; // Россия
				} else {
					$default_country_id = (int)reset($countries_filtered);
				}
			}

			// 4) Сохраняем в настройках
			$this->db->query(
			"UPDATE `" . DB_PREFIX . "setting`
				SET `value` = '" . (int)$default_country_id . "'
				WHERE `key` = 'config_country_id'"
			);
		}
	}
}
