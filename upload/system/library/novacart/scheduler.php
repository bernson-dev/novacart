<?php
/**
 * NovaCart periodic task scheduler. PHP 7.3+.
 * The database schema is installed explicitly by cron:install.
 */
class NovaCartScheduler {
    private $registry;
    private $db;
    private $historyReady = false;

    public function __construct($registry) {
        $this->registry = $registry;
        $this->db = $registry->get('db');
    }

    public function install() {
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "novacart_cron` (
            `cron_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `code` VARCHAR(128) NOT NULL,
            `route` VARCHAR(255) NOT NULL,
            `args` TEXT NOT NULL,
            `interval_seconds` INT UNSIGNED NOT NULL DEFAULT 3600,
            `cron_expression` VARCHAR(100) NULL,
            `misfire_policy` VARCHAR(16) NOT NULL DEFAULT 'run_once',
            `status` TINYINT(1) NOT NULL DEFAULT 1,
            `date_next` DATETIME NOT NULL,
            `date_last` DATETIME NULL,
            `last_status` VARCHAR(16) NULL,
            `last_message` TEXT NULL,
            `last_duration_ms` INT UNSIGNED NULL,
            `date_added` DATETIME NOT NULL,
            `date_modified` DATETIME NOT NULL,
            PRIMARY KEY (`cron_id`),
            UNIQUE KEY `code` (`code`),
            KEY `due` (`status`, `date_next`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $columns = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "novacart_cron` LIKE 'cron_expression'");
        if (!$columns->num_rows) {
            $this->db->query("ALTER TABLE `" . DB_PREFIX . "novacart_cron` ADD `cron_expression` VARCHAR(100) NULL AFTER `interval_seconds`");
        }
        $policyColumn = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "novacart_cron` LIKE 'misfire_policy'");
        if (!$policyColumn->num_rows) {
            $this->db->query("ALTER TABLE `" . DB_PREFIX . "novacart_cron` ADD `misfire_policy` VARCHAR(16) NOT NULL DEFAULT 'run_once' AFTER `cron_expression`");
        }
        $this->installHistory();
    }

    private function nextCron($expression) {
        require_once(DIR_SYSTEM . 'library/novacart/cron_expression.php');
        $now = $this->db->query("SELECT NOW() AS cron_now")->row['cron_now'];
        return NovaCartCronExpression::next($expression, $now, 1)[0];
    }

    public function previewCron($expression, $count = 5) {
        require_once(DIR_SYSTEM . 'library/novacart/cron_expression.php');
        $now = $this->db->query("SELECT NOW() AS cron_now")->row['cron_now'];
        return NovaCartCronExpression::next($expression, $now, $count);
    }

    public function installHistory() {
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "novacart_cron_history` (
            `history_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `cron_id` INT UNSIGNED NOT NULL,
            `task_code` VARCHAR(128) NOT NULL,
            `source` VARCHAR(16) NOT NULL,
            `started_at` DATETIME NOT NULL,
            `status` VARCHAR(16) NOT NULL,
            `duration_ms` INT UNSIGNED NOT NULL DEFAULT 0,
            `message` TEXT NULL,
            PRIMARY KEY (`history_id`),
            KEY `task_recent` (`cron_id`, `history_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $this->historyReady = true;
    }

    public function recentHistory($cronId, $limit = 30) {
        $limit = max(1, min(100, (int)$limit));
        return $this->db->query("SELECT * FROM `" . DB_PREFIX . "novacart_cron_history`
            WHERE cron_id=" . (int)$cronId . " ORDER BY history_id DESC LIMIT " . $limit)->rows;
    }

    private function recordHistory($job, $source, $ok, $duration, $message) {
        try {
            if (!$this->historyReady) { $this->installHistory(); }
            $this->db->query("INSERT INTO `" . DB_PREFIX . "novacart_cron_history`
                SET cron_id=" . (int)$job['cron_id'] . ",
                    task_code='" . $this->db->escape((string)$job['code']) . "',
                    source='" . $this->db->escape($source) . "',
                    started_at=NOW(),
                    status='" . ($ok ? 'success' : 'error') . "',
                    duration_ms=" . max(0, min(4294967295, (int)$duration)) . ",
                    message='" . $this->db->escape(substr((string)$message, 0, 2000)) . "'");
            $this->db->query("DELETE FROM `" . DB_PREFIX . "novacart_cron_history`
                WHERE cron_id=" . (int)$job['cron_id'] . "
                  AND history_id NOT IN (
                    SELECT history_id FROM (
                        SELECT history_id FROM `" . DB_PREFIX . "novacart_cron_history`
                        WHERE cron_id=" . (int)$job['cron_id'] . "
                        ORDER BY history_id DESC LIMIT 100
                    ) AS recent
                  )");
        } catch (Throwable $e) {
            $log = $this->registry->get('log');
            if ($log) { $log->write('NovaCart cron history: ' . $e->getMessage()); }
        }
    }

    public function register($code, $route, $interval, $args = array(), $enabled = true, $expression = '', $policy = 'run_once') {
        if (!preg_match('/^[a-z0-9_.:-]{2,128}$/i', $code)
            || !preg_match('#^cron/[a-z0-9_/]+$#i', $route)
            || $interval < 60 || $interval > 31536000) {
            throw new InvalidArgumentException('Invalid cron task definition');
        }
        $json = json_encode($args, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new InvalidArgumentException('Invalid cron task parameters');
        }
        if (!in_array($policy, array('run_once', 'skip'), true)) { throw new InvalidArgumentException('Invalid misfire policy'); }
        $expression = trim((string)$expression);
        $next = $expression !== '' ? $this->nextCron($expression) : null;
        // Existing schedules are retained on registration (including user status).
        $this->db->query("INSERT IGNORE INTO `" . DB_PREFIX . "novacart_cron` SET code='" .
            $this->db->escape($code) . "', route='" . $this->db->escape($route) .
            "', args='" . $this->db->escape($json) . "', interval_seconds=" .
            (int)$interval . ", cron_expression=" .
            ($expression === '' ? "NULL" : "'" . $this->db->escape($expression) . "'") .
            ", misfire_policy='" . $this->db->escape($policy) . "', status=" . (int)(bool)$enabled . ", date_next=" .
            ($next === null ? "NOW()" : "'" . $this->db->escape($next) . "'") .
            ", date_added=NOW(), date_modified=NOW()");
    }

    // CodeCart-compatible administration API, backed by the existing NovaCart table.
    public function getTask($id) {
        $q = $this->db->query("SELECT * FROM `" . DB_PREFIX . "novacart_cron` WHERE cron_id=" . (int)$id . " LIMIT 1");
        return $q->num_rows ? $q->row : array();
    }

    public function save($id, $code, $route, $interval, $status, $args = array(), $expression = '', $policy = 'run_once') {
        $existing = $this->getTask($id);
        if (!$existing) { throw new RuntimeException('Scheduler task not found'); }
        if (!preg_match('/^[a-z0-9_.:-]{2,128}$/i', $code)
            || !preg_match('#^cron/[a-z0-9_/]+$#i', $route)
            || $interval < 60 || $interval > 31536000 || !is_array($args)) {
            throw new InvalidArgumentException('Invalid scheduler task');
        }
        if (strpos($existing['code'], 'core.') === 0 && $existing['code'] !== $code) {
            throw new RuntimeException('Cannot rename a system task');
        }
        $duplicate = $this->db->query("SELECT cron_id FROM `" . DB_PREFIX . "novacart_cron` WHERE code='" .
            $this->db->escape($code) . "' AND cron_id!=" . (int)$id . " LIMIT 1");
        if ($duplicate->num_rows) { throw new RuntimeException('Scheduler code already exists'); }
        if (!in_array($policy, array('run_once', 'skip'), true)) { throw new InvalidArgumentException('Invalid misfire policy'); }
        $expression = trim((string)$expression);
        $next = $expression !== '' ? $this->nextCron($expression) : null;
        $json = json_encode($args, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) { throw new InvalidArgumentException('Invalid JSON arguments'); }
        $this->db->query("UPDATE `" . DB_PREFIX . "novacart_cron` SET code='" . $this->db->escape($code) .
            "', route='" . $this->db->escape($route) . "', args='" . $this->db->escape($json) .
            "', interval_seconds=" . (int)$interval . ", cron_expression=" .
            ($expression === '' ? "NULL" : "'" . $this->db->escape($expression) . "'") .
            ", misfire_policy='" . $this->db->escape($policy) . "', status=" . (int)(bool)$status .
            ", date_next=" . ($next !== null ? "'" . $this->db->escape($next) . "'" :
                (!$existing['status'] && $status ? "NOW()" : "DATE_ADD(NOW(), INTERVAL " . (int)$interval . " SECOND)")) .
            ", date_modified=NOW() WHERE cron_id=" . (int)$id);
    }

    public function setStatus($id, $enabled) {
        $task = $this->getTask($id);
        if (!$task) { throw new RuntimeException('Scheduler task not found'); }
        $next = $enabled && !empty($task['cron_expression']) ?
            "'" . $this->db->escape($this->nextCron($task['cron_expression'])) . "'" : 'NOW()';
        $this->db->query("UPDATE `" . DB_PREFIX . "novacart_cron` SET status=" . (int)(bool)$enabled .
            ($enabled ? ", date_next=" . $next : "") . ", date_modified=NOW() WHERE cron_id=" . (int)$id);
    }

    public function deleteTask($id) {
        $job = $this->getTask($id);
        if (!$job) { return; }
        if (strpos($job['code'], 'core.') === 0) { throw new RuntimeException('System tasks cannot be deleted'); }
        $this->db->query("DELETE FROM `" . DB_PREFIX . "novacart_cron` WHERE cron_id=" . (int)$id);
    }

    public function runOne($id) {
        $job = $this->getTask($id);
        if (!$job) { throw new RuntimeException('Scheduler task not found'); }
        $lock = 'nc_cron_' . substr(hash('sha256', DB_DATABASE . ':' . DB_PREFIX), 0, 30);
        $q = $this->db->query("SELECT GET_LOCK('" . $this->db->escape($lock) . "', 0) acquired");
        if (empty($q->row['acquired'])) {
            return array('success'=>false, 'status'=>'locked', 'duration_ms'=>0, 'message'=>'Scheduler is busy');
        }
        try {
            $ok = $this->execute($job, 'manual');
            $fresh = $this->getTask($id);
            return array('success'=>$ok, 'status'=>$ok ? 'success' : 'error',
                'duration_ms'=>(int)$fresh['last_duration_ms'], 'message'=>(string)$fresh['last_message']);
        } finally {
            $this->db->query("SELECT RELEASE_LOCK('" . $this->db->escape($lock) . "')");
        }
    }

    public function listTasks() {
        return $this->db->query("SELECT * FROM `" . DB_PREFIX . "novacart_cron` ORDER BY code")->rows;
    }

    public function runDue($limit = 20) {
        $limit = max(1, min(100, (int)$limit));
        // This initial implementation serializes scheduler runs across all workers.
        $lock = 'nc_cron_' . substr(hash('sha256', DB_DATABASE . ':' . DB_PREFIX), 0, 30);
        $query = $this->db->query("SELECT GET_LOCK('" . $this->db->escape($lock) . "', 0) AS acquired");
        if (empty($query->row['acquired'])) {
            return array('status' => 'locked', 'processed' => 0, 'failed' => 0);
        }
        $processed = 0;
        $failed = 0;
        try {
            $jobs = $this->db->query("SELECT * FROM `" . DB_PREFIX .
                "novacart_cron` WHERE status=1 AND date_next<=NOW() ORDER BY date_next, cron_id LIMIT " . $limit);
            foreach ($jobs->rows as $job) {
                // A delayed worker executes an overdue task once by default.
                // Optional skip policy ignores stale runs (more than two minutes late).
                if (isset($job['misfire_policy']) && $job['misfire_policy'] === 'skip') {
                    $late = $this->db->query("SELECT TIMESTAMPDIFF(SECOND, '" .
                        $this->db->escape($job['date_next']) . "', NOW()) AS seconds_late")->row;
                    if ((int)$late['seconds_late'] > 120) {
                        $nextSql = !empty($job['cron_expression']) ?
                            "'" . $this->db->escape($this->nextCron($job['cron_expression'])) . "'" :
                            "DATE_ADD(NOW(), INTERVAL " . max(60, min(31536000, (int)$job['interval_seconds'])) . " SECOND)";
                        $this->db->query("UPDATE `" . DB_PREFIX . "novacart_cron` SET date_next=" .
                            $nextSql . ", last_status='skipped', last_message='Missed execution skipped', " .
                            "date_modified=NOW() WHERE cron_id=" . (int)$job['cron_id']);
                        continue;
                    }
                }
                $processed++;
                if (!$this->execute($job, 'scheduled')) {
                    $failed++;
                }
            }
        } finally {
            $this->db->query("SELECT RELEASE_LOCK('" . $this->db->escape($lock) . "')");
        }
        return array('status' => 'ok', 'processed' => $processed, 'failed' => $failed);
    }

    private function execute($job, $source) {
        $ok = true;
        $message = 'OK';
        $start = microtime(true);
        try {
            $route = (string)$job['route'];
            if (!preg_match('#^cron/[a-z0-9_/]+$#i', $route)) {
                throw new RuntimeException('Invalid cron route');
            }
            $args = json_decode((string)$job['args'], true);
            if (!is_array($args)) {
                throw new RuntimeException('Invalid cron parameters');
            }
            $result = $this->registry->get('load')->controller($route, $args);
            if (is_array($result) && isset($result['message'])) {
                $message = (string)$result['message'];
            }
            if ($result === false || (is_array($result) && array_key_exists('success', $result) && !$result['success'])) {
                $ok = false;
                if (!is_array($result) || !isset($result['message'])) {
                    $message = 'Handler returned failure';
                }
            }
        } catch (Throwable $e) {
            $ok = false;
            $message = get_class($e) . ': ' . $e->getMessage();
            $log = $this->registry->get('log');
            if ($log) {
                $log->write('NovaCart cron [' . $job['code'] . ']: ' . $message);
            }
        }
        $duration = (int)round((microtime(true) - $start) * 1000);
        // Schedule relative to current database time; avoid PHP/MySQL timezone drift.
        $nextSql = !empty($job['cron_expression']) ?
            "'" . $this->db->escape($this->nextCron($job['cron_expression'])) . "'" :
            "DATE_ADD(NOW(), INTERVAL " . max(60, min(31536000, (int)$job['interval_seconds'])) . " SECOND)";
        $this->db->query("UPDATE `" . DB_PREFIX . "novacart_cron` SET date_last=NOW(), date_next=" .
            $nextSql . ", last_status='" .
            ($ok ? 'success' : 'error') . "', last_message='" .
            $this->db->escape(substr($message, 0, 1000)) . "', last_duration_ms=" .
            $duration . ", date_modified=NOW() WHERE cron_id=" . (int)$job['cron_id']);
        $this->recordHistory($job, $source, $ok, $duration, $message);
        return $ok;
    }
}
