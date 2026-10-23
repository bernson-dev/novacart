<?php
/**
 * NovaCart periodic task scheduler. PHP 7.3+.
 * The database schema is installed explicitly by cron:install.
 */
class NovaCartScheduler {
    private $registry;
    private $db;

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
    }

    public function register($code, $route, $interval, $args = array(), $enabled = true) {
        if (!preg_match('/^[a-z0-9_.:-]{2,128}$/i', $code)
            || !preg_match('#^cron/[a-z0-9_/]+$#i', $route)
            || $interval < 60 || $interval > 31536000) {
            throw new InvalidArgumentException('Invalid cron task definition');
        }
        $json = json_encode($args, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new InvalidArgumentException('Invalid cron task parameters');
        }
        // Existing schedules are retained on registration (including user status).
        $this->db->query("INSERT IGNORE INTO `" . DB_PREFIX . "novacart_cron` SET code='" .
            $this->db->escape($code) . "', route='" . $this->db->escape($route) .
            "', args='" . $this->db->escape($json) . "', interval_seconds=" .
            (int)$interval . ", status=" . (int)(bool)$enabled .
            ", date_next=NOW(), date_added=NOW(), date_modified=NOW()");
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
                $processed++;
                if (!$this->execute($job)) {
                    $failed++;
                }
            }
        } finally {
            $this->db->query("SELECT RELEASE_LOCK('" . $this->db->escape($lock) . "')");
        }
        return array('status' => 'ok', 'processed' => $processed, 'failed' => $failed);
    }

    private function execute($job) {
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
            if ($result === false || (is_array($result) && array_key_exists('success', $result) && !$result['success'])) {
                $ok = false;
                $message = is_array($result) && isset($result['message']) ? (string)$result['message'] : 'Handler returned failure';
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
        $this->db->query("UPDATE `" . DB_PREFIX . "novacart_cron` SET date_last=NOW(), date_next=DATE_ADD(NOW(), INTERVAL " .
            max(60, min(31536000, (int)$job['interval_seconds'])) . " SECOND), last_status='" .
            ($ok ? 'success' : 'error') . "', last_message='" .
            $this->db->escape(substr($message, 0, 1000)) . "', last_duration_ms=" .
            $duration . ", date_modified=NOW() WHERE cron_id=" . (int)$job['cron_id']);
        return $ok;
    }
}
