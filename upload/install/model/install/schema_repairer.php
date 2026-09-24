<?php

class SchemaRepairer {

    /**
     * Быстрое восстановление структуры БД по эталонному SQL-файлу.
     *
     * Создаёт отсутствующие таблицы, колонки, индексы и ОБНОВЛЯЕТ типы/длину существующих полей.
     * Существующие данные не удаляет.
     *
     * @param DB     $db
     * @param string $prefix
     * @return void
     * @throws Exception
     */
    public function repairSchemaFromFile($db, $prefix) {
        /*
         * Use the canonical installer dump as the schema source.
         *
         * opencart.sql keeps PRIMARY KEY, indexes and AUTO_INCREMENT inside
         * CREATE TABLE definitions, which is exactly what parseCreateTables()
         * understands and avoids maintaining a second divergent schema file.
         */
        $schema_file = DIR_APPLICATION . 'opencart.sql';

        if (!file_exists($schema_file)) {
            throw new \Exception('Could not load schema file: ' . $schema_file);
        }

        $sql = file_get_contents($schema_file);

        if ($sql === false || trim($sql) === '') {
            throw new \Exception('Schema file is empty: ' . $schema_file);
        }

        // Очищаем SQL от комментариев для надежности парсинга
        $sql = $this->cleanSqlComments($sql);

        $schema_tables = $this->parseCreateTables($sql);

        if (!$schema_tables) {
            throw new \Exception('No CREATE TABLE statements found in schema file: ' . $schema_file);
        }

        $existing_tables = $this->getExistingTables($db);

        foreach ($schema_tables as $table_name => $table_data) {
            $target_table = $prefix . $table_name;

            // Если таблицы вообще нет — создаем целиком
            if (!isset($existing_tables[$target_table])) {
                $create_sql = str_replace('`oc_', '`' . $prefix, $table_data['create']);
                $db->query($create_sql);
                $existing_tables[$target_table] = true;
                continue;
            }

            // Таблица существует — проверяем поля и индексы
            $existing_columns = $this->getExistingColumns($db, $target_table);
            $existing_indexes = $this->getExistingIndexes($db, $target_table);

            // Ремонтируем/обновляем колонки и индексы ОДНИМ пакетным запросом на таблицу
            $this->repairTableBatch($db, $target_table, $table_data, $existing_columns, $existing_indexes);
        }
    }

    /**
     * Проверяет, требуется ли выбранному дампу восстановление структуры
     * относительно эталонного install/opencart.sql.
     *
     * Сравниваются только те признаки, которые умеет исправлять
     * repairSchemaFromFile(): отсутствующие таблицы/колонки/индексы,
     * тип колонки, NULL/NOT NULL и AUTO_INCREMENT.
     *
     * @param string $dump_file
     * @return bool
     * @throws Exception
     */
    public function dumpNeedsRepair($dump_file) {
        $schema_file = DIR_APPLICATION . 'opencart.sql';

        if (!is_file($schema_file)) {
            throw new \\Exception('Could not load schema file: ' . $schema_file);
        }

        if (!is_file($dump_file)) {
            throw new \\Exception('Could not load dump file: ' . $dump_file);
        }

        $schema_real = realpath($schema_file);
        $dump_real = realpath($dump_file);

        // Сам эталон никогда не нуждается в восстановлении по самому себе.
        if ($schema_real !== false && $dump_real !== false && $schema_real === $dump_real) {
            return false;
        }

        $schema_sql = file_get_contents($schema_file);
        $dump_sql = file_get_contents($dump_file);

        if ($schema_sql === false || trim($schema_sql) === '') {
            throw new \\Exception('Schema file is empty: ' . $schema_file);
        }

        if ($dump_sql === false || trim($dump_sql) === '') {
            throw new \\Exception('Dump file is empty: ' . $dump_file);
        }

        $schema_tables = $this->parseCreateTables($this->cleanSqlComments($schema_sql));
        $dump_tables = $this->parseCreateTables($this->cleanSqlComments($dump_sql));

        if (!$schema_tables) {
            throw new \\Exception('No CREATE TABLE statements found in schema file: ' . $schema_file);
        }

        if (!$dump_tables) {
            throw new \\Exception('No compatible CREATE TABLE statements found in dump file: ' . $dump_file);
        }

        foreach ($schema_tables as $table_name => $schema_table) {
            if (!isset($dump_tables[$table_name])) {
                return true;
            }

            $dump_table = $dump_tables[$table_name];

            foreach ($schema_table['columns'] as $column_name => $schema_column) {
                if (!isset($dump_table['columns'][$column_name])) {
                    return true;
                }

                if ($this->dumpColumnNeedsRepair(
                    $schema_column['definition'],
                    $dump_table['columns'][$column_name]['definition']
                )) {
                    return true;
                }
            }

            // Текущий repairSchemaFromFile() восстанавливает отсутствующие
            // индексы по имени, поэтому анализ использует тот же критерий.
            foreach ($schema_table['indexes'] as $index_name => $schema_index) {
                if (!isset($dump_table['indexes'][$index_name])) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Сравнение определений колонок двух SQL-дампов по тем признакам,
     * которые реально исправляет SchemaRepairer.
     */
    private function dumpColumnNeedsRepair($schema_definition, $dump_definition) {
        $schema = $this->getDumpColumnSignature($schema_definition);
        $dump = $this->getDumpColumnSignature($dump_definition);

        return $schema['type'] !== $dump['type']
            || $schema['not_null'] !== $dump['not_null']
            || $schema['auto_increment'] !== $dump['auto_increment'];
    }

    /**
     * Нормализованная сигнатура колонки из CREATE TABLE.
     */
    private function getDumpColumnSignature($definition) {
        $definition = strtolower(trim(preg_replace('/\\s+/', ' ', (string)$definition)));
        $type = '';

        if (preg_match('/^([a-z]+(?:\\s*\\([^)]*\\))?(?:\\s+unsigned)?(?:\\s+zerofill)?)/i', $definition, $match)) {
            $type = strtolower($match[1]);
            $type = preg_replace('/\\s+/', ' ', $type);
            $type = preg_replace('/\\s*\\(\\s*/', '(', $type);
            $type = preg_replace('/\\s*\\)/', ')', $type);
            $type = preg_replace('/\\s*,\\s*/', ',', $type);
        }

        return array(
            'type' => trim($type),
            'not_null' => strpos($definition, 'not null') !== false,
            'auto_increment' => strpos($definition, 'auto_increment') !== false
        );
    }

    /**
     * Удаляет однострочные и многострочные комментарии из SQL.
     *
     * @param string $sql
     * @return string
     */
    private function cleanSqlComments($sql) {
        // Удаляем многострочные комментарии /* ... */
        $sql = preg_replace('!/\*.*?\*/!s', '', $sql);
        // Удаляем однострочные комментарии -- ...
        $sql = preg_replace('/^--.*$/m', '', $sql);
        return $sql;
    }

    /**
     * Парсинг CREATE TABLE из SQL-файла.
     *
     * @param string $sql
     * @return array
     */
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
                $line = trim($line);
                $line = rtrim($line, ',');

                if ($line === '') {
                    continue;
                }

                if (preg_match('/^`([^`]+)`\s+(.+)$/s', $line, $column_match)) {
                    $column_name = $column_match[1];

                    $columns[$column_name] = array(
                        'name' => $column_name,
                        'definition' => $column_match[2], // Полная спецификация (тип, NULL, default и т.д.)
                        'sql'  => $line,
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
                'create'  => $create_sql,
                'columns' => $columns,
                'indexes' => $indexes
            );
        }

        return $tables;
    }

    /**
     * Разделение тела CREATE TABLE с учетом вложенных скобок и кавычек.
     *
     * @param string $body
     * @return array
     */
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

    /**
     * Парсинг индексов.
     *
     * @param string $line
     * @return array|false
     */
    private function parseIndexDefinition($line) {
        $line = trim(rtrim($line, ','));

        if (preg_match('/^PRIMARY\s+KEY\s+(.+)$/is', $line, $match)) {
            return array('name' => 'PRIMARY', 'sql' => 'PRIMARY KEY ' . trim($match[1]));
        }

        if (preg_match('/^(UNIQUE|FULLTEXT)?\s*KEY\s+`([^`]+)`\s+(.+)$/is', $line, $match)) {
            $type = strtoupper($match[1]);
            $name = $match[2];
            $prefix = $type ? $type . ' KEY' : 'KEY';
            return array('name' => $name, 'sql' => $prefix . ' `' . $name . '` ' . trim($match[3]));
        }

        return false;
    }

    /**
     * Получение существующих таблиц.
     */
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

    /**
     * Получение детальной структуры колонок (тип, NULL, default и т.д.).
     */
    private function getExistingColumns($db, $table) {
        $columns = array();
        $query = $db->query("SHOW COLUMNS FROM `" . $table . "`");

        foreach ($query->rows as $row) {
            // Собираем типы в нижнем регистре для корректного сравнения
            $columns[$row['Field']] = array(
                'Type'    => strtolower($row['Type']),
                'Null'    => strtolower($row['Null']),
                'Default' => $row['Default'],
                'Extra'   => strtolower($row['Extra'])
            );
        }

        return $columns;
    }

    /**
     * Получение существующих индексов.
     */
    private function getExistingIndexes($db, $table) {
        $indexes = array();
        $query = $db->query("SHOW INDEX FROM `" . $table . "`");

        foreach ($query->rows as $row) {
            $indexes[$row['Key_name']] = true;
        }

        return $indexes;
    }

    /**
     * Всплывающая проверка на различие типов/длины колонок.
     */
    private function isColumnDefinitionChanged($schema_def, $existing_col) {
        $def = strtolower($schema_def);
        $type = $existing_col['Type'];

        // Сравниваем тип данных с длиной (например "varchar(255)")
        if (strpos($def, $type) === false) {
            return true;
        }

        // Проверяем NULL / NOT NULL
        if (strpos($def, 'not null') !== false && $existing_col['Null'] === 'yes') {
            return true;
        }

        // AUTO_INCREMENT является частью определения колонки. Без этой
        // проверки repairSchemaFromFile() не замечает потерянный AUTO_INCREMENT,
        // если тип и NOT NULL остались прежними (например cart.cart_id).
        $schema_auto_increment = strpos($def, 'auto_increment') !== false;
        $existing_auto_increment = strpos($existing_col['Extra'], 'auto_increment') !== false;

        if ($schema_auto_increment !== $existing_auto_increment) {
            return true;
        }

        return false;
    }

    /**
     * Пакетный ремонт таблицы (колонки + индексы в одном ALTER TABLE).
     */
    private function repairTableBatch($db, $table, $table_data, $existing_columns, $existing_indexes) {
        $alter_clauses = array();

        // 1. Проверяем колонки
        foreach ($table_data['columns'] as $column_name => $column) {
            if (!isset($existing_columns[$column_name])) {
                // Добавление новой колонки
                $clause = "ADD " . $column['sql'];
                if ($column['after'] !== '' && isset($existing_columns[$column['after']])) {
                    $clause .= " AFTER `" . $column['after'] . "`";
                } else {
                    $clause .= " FIRST";
                }
                $alter_clauses[] = $clause;
            } elseif ($this->isColumnDefinitionChanged($column['definition'], $existing_columns[$column_name])) {
                // Изменение существующей колонки (тип, длина, null)
                $alter_clauses[] = "MODIFY " . $column['sql'];
            }
        }

        // 2. Проверяем индексы
        foreach ($table_data['indexes'] as $index_name => $index) {
            if (!isset($existing_indexes[$index_name])) {
                $alter_clauses[] = "ADD " . $index['sql'];
            }
        }

        // Если есть изменения — выполняем ВСЕ ОДНИМ ЗАПРОСОМ
        if (!empty($alter_clauses)) {
            $sql = "ALTER TABLE `" . $table . "` " . implode(', ', $alter_clauses);
            $db->query($sql);
        }
    }
}
