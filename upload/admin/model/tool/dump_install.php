<?php
// admin/model/tool/dump_install.php
class ModelToolDumpInstall extends Model {

	// Белый список таблиц, где можно пропускать PK при экспорте
	private $allowSkipPk = [
		'currency',
		'event',
		'extension',
		'geo_zone',
		'language',
		//'layout',
		'layout_module',
		'layout_route',
		'order_status',
		'return_action',
		'return_reason',
		'return_status',
		'product_image',
		'product_option_value',
		'product_reward',
		'product_special',
		'seo_url',
		'setting',
		'stock_status',
		'tax_class',
		'tax_rate',
		'tax_rule',
		'user_group',
		'zone'
	];

	private $numericTypes = ['tinyint', 'smallint', 'mediumint', 'int', 'bigint'];
	private $floatTypes = ['float', 'double', 'real', 'decimal'];
	// Лимиты: обычный / для установщика / для очень слабого хостинга
	private const LIMIT_DEFAULT = 1000;
	private const LIMIT_INSTALLER = 100;
	private const LIMIT_WEAK = 50;

	public function getTablesByPrefix() {
		$sql = "
			SELECT TABLE_NAME
			FROM information_schema.TABLES
			WHERE TABLE_SCHEMA = DATABASE()
			  AND TABLE_NAME LIKE '" . $this->db->escape(DB_PREFIX) . "%'
			ORDER BY TABLE_NAME
		";

		$query = $this->db->query($sql);
		$tables = [];

		if ($query->rows) {
			foreach ($query->rows as $row) {
				$tables[] = $row['TABLE_NAME'];
			}
		}

		return $tables;
	}

	public function getDatabaseCharset() {
		$row = $this->db->query("SELECT @@character_set_database AS cs")->row;
		return !empty($row['cs']) ? $row['cs'] : 'utf8mb4';
	}

	public function getCreateTableSql($table) {
		$query = $this->db->query("SHOW CREATE TABLE `" . $this->db->escape($table) . "`");

		if (empty($query->row['Create Table'])) {
			return "-- WARNING: cannot read CREATE TABLE for `$table`";
		}

		$sql = $query->row['Create Table'];
		// Убираем AUTO_INCREMENT
		$sql = preg_replace('/\s+AUTO_INCREMENT\s*=\s*\d+\s*/i', ' ', $sql);
		// Убираем висящую запятую
		$sql = preg_replace('/,\s*\)/', "\n)", $sql);

		return $sql . ';';
	}

	private function getPrimaryKey($table) {
		$res = $this->db->query(
		"SHOW KEYS FROM `" . $this->db->escape($table) . "` WHERE Key_name = 'PRIMARY'"
		);

		if (!$res->num_rows) {
			return null;
		}

		$cols = [];
		foreach ($res->rows as $row) {
			$cols[] = $row['Column_name'];
		}

		return (count($cols) === 1) ? $cols[0] : null;
	}

	private function isSafeToSkipPrimaryKey($table) {
		$short = str_replace(DB_PREFIX, '', $table);
		return in_array($short, $this->allowSkipPk, true);
	}

	private function sanitizeSettingRow($table, $row) {
		if ($table !== DB_PREFIX . 'setting') {
			return $row;
		}

		if (!isset($row['code']) || $row['code'] !== 'config') {
			return $row;
		}

		$defaults = [
			'config_mail_engine'        => 'mail',
			'config_mail_parameter'     => '',
			'config_mail_smtp_hostname' => '',
			'config_mail_smtp_username' => '',
			'config_mail_smtp_password' => '',
			'config_mail_smtp_port'     => '25',
			'config_fax'                => '',
			'config_telephone'          => '123456789',
			'config_email'              => '',
			'config_geocode'            => '',
			'config_address'            => 'Адрес',
			'config_owner'              => 'Владелец',
			'config_name'               => 'Мой магазин',
			'config_meta_keyword'       => '',
			'config_meta_description'   => 'Мой магазин',
			'config_meta_title'         => 'Мой магазин',
			'config_encryption'         => ''
		];

		if (isset($row['key']) && array_key_exists($row['key'], $defaults)) {
			$row['value']      = $defaults[$row['key']];
			$row['serialized'] = 0;
		}

		return $row;
	}

/**
* Генерация INSERT-запросов
* @param string $table Имя таблицы
* @param int|null $limit Лимит строк (null = авто-выбор)
* @param bool $installerMode Режим установщика: безопасный лимит + формат
*/
	public function getInsertSql($table, $limit = null, $installerMode = false) {
		// Авто-выбор лимита
		$limit = $limit !== null ? (int)$limit : ($installerMode ? self::LIMIT_INSTALLER : self::LIMIT_DEFAULT);
		$result = [];

		$colsQuery = $this->db->query("SHOW COLUMNS FROM `" . $this->db->escape($table) . "`");
		if (!$colsQuery->num_rows) {
			return ["-- WARNING: table `$table` not found or has no columns"];
		}

		$pk = $this->getPrimaryKey($table);
		$skipPkField = null;

		if ($pk && $this->isSafeToSkipPrimaryKey($table)) {
			$skipPkField = $pk;
		}

		$columns = [];
		$columnInfo = [];
		$sortableColumns = [];

		foreach ($colsQuery->rows as $col) {
			$field = $col['Field'];
			if ($skipPkField === $field) {
				continue;
			}

			$columns[] = '`' . $field . '`';

			$type = strtolower($col['Type']);
			$baseType = preg_replace('/\(.*/', '', $type);
			// Исключаем BLOB/TEXT из ORDER BY для производительности и совместимости MySQL
			$isSortable = (strpos($type, 'blob') === false && strpos($type, 'text') === false);

			$columnInfo[$field] = [
				'type'     => $baseType,
				'nullable' => ($col['Null'] === 'YES')
			];

			if ($isSortable) {
				$sortableColumns[] = '`' . $field . '`';
			}
		}

		if (!$columns) {
			return ["-- WARNING: table `$table` has no insertable columns"];
		}

		$columnSql = implode(', ', $columns);

		// Логика сортировки:
		// 1. Если есть одиночный PK -> сортируем по нему
		// 2. Если PK нет или составной -> сортируем по всем подходящим колонкам
		if ($pk) {
			$orderBySql = " ORDER BY `" . $pk . "`";
		} else {
			$orderBySql = $sortableColumns ? (' ORDER BY ' . implode(', ', $sortableColumns)) : '';
		}

		$offset = 0;
		$lastId = null;
		$lastIdPrev = null;

		while (true) {
			$where = '';
			if ($pk && $lastId !== null) {
				$pkInfo = $columnInfo[$pk] ?? null;
				$isPkNumeric = $pkInfo && in_array($pkInfo['type'], $this->numericTypes, true);

				if ($isPkNumeric) {
					$where = "WHERE `$pk` > " . (int)$lastId;
				} else {
					$where = "WHERE `$pk` > '" . $this->db->escape($lastId) . "'";
				}
			}

			if ($pk) {
				$sql = "SELECT * FROM `" . $this->db->escape($table) . "`
					$where
					$orderBySql
					LIMIT " . (int)$limit;
			} else {
				$sql = "SELECT * FROM `" . $this->db->escape($table) . "`
					$orderBySql
					LIMIT " . (int)$offset . ", " . (int)$limit;
			}

			$query = $this->db->query($sql);
			if (!$query->num_rows) {
				break;
			}

			$rowsSql = [];
			foreach ($query->rows as $row) {
				$row = $this->sanitizeSettingRow($table, $row);
				$values = [];

				foreach ($row as $field => $value) {
					if ($skipPkField === $field) {
						continue;
					}

					$info = $columnInfo[$field] ?? ['type' => 'varchar', 'nullable' => true];
					$type = $info['type'];

					// 1. Настоящий SQL NULL
					if ($value === null) {
						$values[] = 'NULL';
						continue;
					}

					// 2. Пустая строка → оставляем как ''
					if ($value === '') {
						$values[] = "''";
						continue;
					}

					// 3. BLOB/BINARY
					if (strpos($type, 'blob') !== false || strpos($type, 'binary') !== false) {
						$values[] = "0x" . bin2hex($value);
						continue;
					}

					// 4. FLOAT/DECIMAL
					if (in_array($type, $this->floatTypes, true)) {
						$floatVal = (float)$value;
						$values[] = is_nan($floatVal) || is_infinite($floatVal) ? '0' : (string)$floatVal;
						continue;
					}

					// 5. Числовые типы
					if (in_array($type, $this->numericTypes, true)) {
						$values[] = (string)(int)$value;
						continue;
					}

					// 6. Все остальные строки
					$values[] = "'" . $this->db->escape((string)$value) . "'";
				}

				$rowsSql[] = '(' . implode(', ', $values) . ')';

				if ($pk && isset($row[$pk])) {
					$lastIdPrev = $lastId;
					$lastId = $row[$pk];
				}
			}

			if ($rowsSql) {
				$insertHead = "INSERT INTO `" . $table . "` (" . $columnSql . ") VALUES";
				$insertBody = implode(",\n", $rowsSql);
				$result[] = $insertHead . "\n" . $insertBody . ";";
			}

			if ($pk) {
				if ((string)$lastId === (string)$lastIdPrev) {
					break;
				}
			} else {
				$offset += $limit;
			}
		}

		return $result;
	}
}