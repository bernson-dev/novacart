<?php
namespace DB;

class MySQLi {
/** @var \mysqli|null */
	private $connection;

/**
* Constructor
*
* @param string $hostname
* @param string $username
* @param string $password
* @param string $database
* @param int $port
* @throws \Exception
*/
	public function __construct(string $hostname, string $username, string $password, string $database, int $port = 3306) {
		try {
			$this->connection = @new \mysqli($hostname, $username, $password, $database, $port);
			if ($this->connection->connect_error) {
				throw new \mysqli_sql_exception($this->connection->connect_error, $this->connection->connect_errno);
			}

			// Пытаемся установить utf8mb4, иначе используем utf8
			if (!$this->connection->set_charset('utf8mb4')) {
				$this->connection->set_charset('utf8');
			}

			$this->connection->query("SET SESSION sql_mode = 'NO_ZERO_IN_DATE,NO_ENGINE_SUBSTITUTION'");
			$this->connection->query("SET FOREIGN_KEY_CHECKS = 0");
		} catch (\mysqli_sql_exception $e) {
			throw new \Exception('Error: Could not make a database link using ' . $username . '@' . $hostname . '! ' . $e->getMessage());
		}
	}


/**
* Execute a raw SQL query.
*
* @param string $sql The SQL query string.
* @return object|bool
* @throws \Exception
*/
	public function query(string $sql) {
		if (!$this->connection) {
			throw new \Exception('Error: No database connection established.');
		}

		$query = $this->connection->query($sql);

		if ($this->connection->errno) {
			throw new \Exception('Error: ' . $this->connection->error . ' (' . $this->connection->errno . ') ' . $sql);
		}

/**
*
* PHP stack trace
* Если нужно видеть контроллер/модель/файл, который вызвал ошибочный запрос
*
*/
		/*
		if ($this->connection->errno) {
			// Формируем читаемый стек вызовов
			$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10); // ограничим глубину до 10
			$trace = '';
			foreach ($backtrace as $bt) {
				$file = $bt['file'] ?? '[internal]';
				$line = $bt['line'] ?? '';
				$func = $bt['function'] ?? '';
				$trace .= $file . ':' . $line . ' -> ' . $func . "()\n";
			}

			throw new \Exception(
			"Error: " . $this->connection->error . " (" . $this->connection->errno . ")\n" .
			"SQL: " . $sql . "\n" .
			"Backtrace:\n" . $trace
			);
		}
		*/

		if ($query instanceof \mysqli_result) {

			$data = [];

			while ($row = $query->fetch_assoc()) {
				$data[] = $row;
			}

			$result = new \stdClass();
			$result->num_rows = $query->num_rows;
			$result->row = $data[0] ?? [];
			$result->rows = $data;

			$query->close();

			return $result;
		}

		return true;
	}

/**
* Execute a prepared statement with parameter binding.
*
* @param string $sql The SQL query with '?' placeholders.
* @param array $params An array of parameters to bind.
* @return object|bool
* @throws \Exception
*/
	public function preparedQuery(string $sql, array $params = []) {
		if (!$this->connection) {
			throw new \Exception('Error: No database connection established.');
		}

		// Prepare statement
		$stmt = $this->connection->prepare($sql);
		if ($stmt === false) {
			throw new \Exception('Error preparing statement: ' . $this->connection->error . ' (' . $this->connection->errno . ') ' . $sql);
		}

		// Bind parameters
		if (!empty($params)) {
			// Determine the type string for parameters
			$types = str_repeat('s', count($params)); // 's' for string is a safe default
			$stmt->bind_param($types, ...$params);
		}

		// Execute statement
		if (!$stmt->execute()) {
			throw new \Exception('Error executing statement: ' . $stmt->error . ' (' . $stmt->errno . ') ' . $sql);
		}

		// Get result set if there is one
		$result = $stmt->get_result();

		if ($result instanceof \mysqli_result) {
			$data = [];
			while ($row = $result->fetch_assoc()) {
				$data[] = $row;
			}

			$output = new \stdClass();
			$output->num_rows = $result->num_rows;
			$output->row = $data[0] ?? [];
			$output->rows = $data;

			$result->close();
			$stmt->close();

			return $output;
		}

		$stmt->close();
		return true;
	}

/**
* Escapes a string for use in an SQL statement.
*
* @param mixed $value
* @return string
*/
	public function escape($value) {
		if (!$this->connection) {
			return (string)$value;
		}
		return $this->connection->real_escape_string((string)$value);
	}

/**
* Get the number of affected rows.
*
* @return int
*/
	public function countAffected(): int {
		return $this->connection ? $this->connection->affected_rows : 0;
	}

/**
* Get the last inserted ID.
*
* @return int
*/
	public function getLastId(): int {
		return $this->connection ? $this->connection->insert_id : 0;
	}

/**
* Checks if the database connection is active.
*
* @return bool
*/
	public function isConnected(): bool {
		return $this->connection && $this->connection->ping();
	}

/**
* Closes the DB connection when this object is destroyed.
*/
	public function __destruct() {
		if ($this->connection) {
			$this->connection->close();
			$this->connection = null;
		}
	}
}
