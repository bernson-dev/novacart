<?php

class SchemaRepairer {
	/**
	 * Восстанавливает отсутствующие таблицы, колонки и индексы по эталонной схеме.
	 * Для существующих колонок синхронизирует тип, NULL, DEFAULT и AUTO_INCREMENT.
	 * Существующие данные не удаляет.
	 *
	 * @param DB     $db
	 * @param string $prefix
	 * @return void
	 * @throws Exception
	 */
	public function repairSchemaFromFile($db, $prefix) {
		$schema_file = DIR_APPLICATION . 'model/install/opencart_schema.sql';

		if (!is_file($schema_file)) {
			throw new \Exception('Could not load schema file: ' . $schema_file);
		}

		$sql = file_get_contents($schema_file);

		if ($sql === false || trim($sql) === '') {
			throw new \Exception('Schema file is empty: ' . $schema_file);
		}

		$sql = $this->cleanSqlComments($sql);
		$schema_tables = $this->parseCreateTables($sql);

		if (!$schema_tables) {
			throw new \Exception('No CREATE TABLE statements found in schema file: ' . $schema_file);
		}

		$existing_tables = $this->getExistingTables($db);

		foreach ($schema_tables as $table_name => $table_data) {
			$target_table = $prefix . $table_name;

			if (!isset($existing_tables[$target_table])) {
				$create_sql = str_replace('`oc_', '`' . $prefix, $table_data['create']);
				$db->query($create_sql);
				$existing_tables[$target_table] = true;
				continue;
			}

			$existing_columns = $this->getExistingColumns($db, $target_table);
			$existing_indexes = $this->getExistingIndexes($db, $target_table);

			$this->repairTableBatch($db, $target_table, $table_data, $existing_columns, $existing_indexes);
		}
	}

	private function cleanSqlComments($sql) {
		$sql = preg_replace('!/\*.*?\*/!s', '', $sql);
		$sql = preg_replace('/^\s*--.*$/m', '', $sql);
		$sql = preg_replace('/^\s*#.*$/m', '', $sql);

		return $sql;
	}

	private function parseCreateTables($sql) {
		$tables = array();

		if (!preg_match_all('/CREATE\s+TABLE\s+`oc_([^`]+)`\s*\((.*?)\)\s*ENGINE\s*=\s*([^\s;]+)(.*?)\s*;/is', $sql, $matches, PREG_SET_ORDER)) {
			return $tables;
		}

		foreach ($matches as $match) {
			$table_name = $match[1];
			$body = trim($match[2]);
			$engine = trim($match[3]);
			$options = trim($match[4]);

			$create_sql = 'CREATE TABLE `oc_' . $table_name . "` (\n" . $body . "\n) ENGINE=" . $engine;
			if ($options !== '') {
				$create_sql .= ' ' . $options;
			}
			$create_sql .= ';';

			$lines = $this->splitCreateTableBody($body);
			$columns = array();
			$indexes = array();
			$previous_column = '';

			foreach ($lines as $line) {
				$line = rtrim(trim($line), ',');

				if ($line === '') {
					continue;
				}

				if (preg_match('/^`([^`]+)`\s+(.+)$/s', $line, $column_match)) {
					$column_name = $column_match[1];
					$columns[$column_name] = array(
						'name' => $column_name,
						'definition' => $column_match[2],
						'sql' => $line,
						'after' => $previous_column
					);
					$previous_column = $column_name;
					continue;
				}

				$index = $this->parseIndexDefinition($line);
				if ($index) {
					$indexes[$index['name']] = $index;
				}
			}

			$tables[$table_name] = array(
				'create' => $create_sql,
				'columns' => $columns,
				'indexes' => $indexes
			);
		}

		return $tables;
	}

	private function splitCreateTableBody($body) {
		$items = array();
		$current = '';
		$level = 0;
		$length = strlen($body);
		$in_quote = false;
		$quote_char = '';

		for ($i = 0; $i < $length; $i++) {
			$char = $body[$i];

			if (($char === "'" || $char === '"') && ($i === 0 || $body[$i - 1] !== '\\')) {
				if (!$in_quote) {
					$in_quote = true;
					$quote_char = $char;
				} elseif ($quote_char === $char) {
					$in_quote = false;
					$quote_char = '';
				}
			}

			if (!$in_quote) {
				if ($char === '(') {
					$level++;
				} elseif ($char === ')') {
					$level--;
				}

				if ($char === ',' && $level === 0) {
					$items[] = trim($current);
					$current = '';
					continue;
				}
			}

			$current .= $char;
		}

		$current = trim($current);
		if ($current !== '') {
			$items[] = $current;
		}

		return $items;
	}

	private function parseIndexDefinition($line) {
		$line = trim(rtrim($line, ','));

		if (preg_match('/^PRIMARY\s+KEY\s+(.+)$/is', $line, $match)) {
			return array('name' => 'PRIMARY', 'sql' => 'PRIMARY KEY ' . trim($match[1]));
		}

		if (preg_match('/^(UNIQUE|FULLTEXT)?\s*KEY\s+`([^`]+)`\s+(.+)$/is', $line, $match)) {
			$type = strtoupper($match[1]);
			$name = $match[2];
			$index_prefix = $type ? $type . ' KEY' : 'KEY';

			return array('name' => $name, 'sql' => $index_prefix . ' `' . $name . '` ' . trim($match[3]));
		}

		return false;
	}

	private function getExistingTables($db) {
		$tables = array();
		$query = $db->query("SHOW FULL TABLES");

		foreach ($query->rows as $row) {
			$table = reset($row);
			if ($table !== false && $table !== null) {
				$tables[$table] = true;
			}
		}

		return $tables;
	}

	private function getExistingColumns($db, $table) {
		$columns = array();
		$query = $db->query("SHOW COLUMNS FROM `" . $table . "`");

		foreach ($query->rows as $row) {
			$columns[$row['Field']] = array(
				'Type' => strtolower($row['Type']),
				'Null' => strtolower($row['Null']),
				'Default' => $row['Default'],
				'Extra' => strtolower($row['Extra'])
			);
		}

		return $columns;
	}

	private function getExistingIndexes($db, $table) {
		$indexes = array();
		$query = $db->query("SHOW INDEX FROM `" . $table . "`");

		foreach ($query->rows as $row) {
			$indexes[$row['Key_name']] = true;
		}

		return $indexes;
	}

	private function isColumnDefinitionChanged($schema_def, $existing_col) {
		$schema = $this->parseColumnDefinition($schema_def);

		if ($schema['type'] !== $this->normalizeSql($existing_col['Type'])) {
			return true;
		}

		$existing_nullable = ($existing_col['Null'] === 'yes');
		if ($schema['nullable'] !== $existing_nullable) {
			return true;
		}

		if ($schema['has_default']) {
			if (!$this->defaultsEqual($schema['default'], $existing_col['Default'])) {
				return true;
			}
		} elseif ($existing_col['Default'] !== null) {
			return true;
		}

		$existing_auto_increment = (strpos($existing_col['Extra'], 'auto_increment') !== false);
		if ($schema['auto_increment'] !== $existing_auto_increment) {
			return true;
		}

		return false;
	}

	private function parseColumnDefinition($definition) {
		$definition = trim($definition);
		$normalized = $this->normalizeSql($definition);

		$stop = preg_split('/\s+(?:not\s+null|null|default|auto_increment|comment|collate|character\s+set|on\s+update)\b/i', $definition, 2);
		$type = $this->normalizeSql($stop[0]);

		$nullable = (stripos($normalized, ' not null') === false);
		$auto_increment = (stripos($normalized, ' auto_increment') !== false);
		$has_default = false;
		$default = null;

		if (preg_match('/\bDEFAULT\s+(NULL|CURRENT_TIMESTAMP(?:\(\))?|[-+]?[0-9]+(?:\.[0-9]+)?|\'(?:\\.|[^\'])*\'|"(?:\\.|[^"])*")/i', $definition, $match)) {
			$has_default = true;
			$default = $this->normalizeDefault($match[1]);
		}

		return array(
			'type' => $type,
			'nullable' => $nullable,
			'has_default' => $has_default,
			'default' => $default,
			'auto_increment' => $auto_increment
		);
	}

	private function normalizeSql($value) {
		$value = strtolower(trim((string)$value));
		$value = preg_replace('/\s+/', ' ', $value);
		$value = preg_replace('/\s*,\s*/', ',', $value);

		return $value;
	}

	private function normalizeDefault($value) {
		$value = trim($value);

		if (strcasecmp($value, 'NULL') === 0) {
			return null;
		}

		if ((substr($value, 0, 1) === "'" && substr($value, -1) === "'") || (substr($value, 0, 1) === '"' && substr($value, -1) === '"')) {
			$value = substr($value, 1, -1);
			$value = stripcslashes($value);
		}

		if (strcasecmp($value, 'CURRENT_TIMESTAMP()') === 0) {
			return 'CURRENT_TIMESTAMP';
		}

		return $value;
	}

	private function defaultsEqual($schema_default, $existing_default) {
		if ($schema_default === null || $existing_default === null) {
			return $schema_default === $existing_default;
		}

		$schema_default = $this->normalizeDefault((string)$schema_default);
		$existing_default = $this->normalizeDefault((string)$existing_default);

		return strcasecmp((string)$schema_default, (string)$existing_default) === 0;
	}

	private function repairTableBatch($db, $table, $table_data, $existing_columns, $existing_indexes) {
		$alter_clauses = array();
		$known_columns = $existing_columns;

		foreach ($table_data['columns'] as $column_name => $column) {
			if (!isset($known_columns[$column_name])) {
				$clause = 'ADD ' . $column['sql'];

				if ($column['after'] !== '' && isset($known_columns[$column['after']])) {
					$clause .= ' AFTER `' . $column['after'] . '`';
				} else {
					$clause .= ' FIRST';
				}

				$alter_clauses[] = $clause;
				$known_columns[$column_name] = array();
			} elseif (isset($existing_columns[$column_name]) && $this->isColumnDefinitionChanged($column['definition'], $existing_columns[$column_name])) {
				$alter_clauses[] = 'MODIFY ' . $column['sql'];
			}
		}

		foreach ($table_data['indexes'] as $index_name => $index) {
			if (!isset($existing_indexes[$index_name])) {
				$alter_clauses[] = 'ADD ' . $index['sql'];
			}
		}

		if ($alter_clauses) {
			$db->query('ALTER TABLE `' . $table . '` ' . implode(', ', $alter_clauses));
		}
	}
}
