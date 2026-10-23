<?php
/**
 * Durable database-backed jobs for NovaCart (PHP 7.3+).
 * Queue handlers use the existing catalog controller loader.
 */
class NovaCartQueue {
    private $registry;
    private $db;

    public function __construct($registry) {
        $this->registry = $registry;
        $this->db = $registry->get('db');
    }

    public function install() {
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "novacart_queue` (
            `queue_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `code` VARCHAR(128) NOT NULL,
            `route` VARCHAR(255) NOT NULL,
            `args` MEDIUMTEXT NOT NULL,
            `priority` INT NOT NULL DEFAULT 100,
            `status` VARCHAR(16) NOT NULL DEFAULT 'pending',
            `attempts` INT UNSIGNED NOT NULL DEFAULT 0,
            `max_attempts` INT UNSIGNED NOT NULL DEFAULT 3,
            `available_at` DATETIME NOT NULL,
            `locked_at` DATETIME NULL,
            `locked_by` VARCHAR(96) NULL,
            `last_error` TEXT NULL,
            `date_added` DATETIME NOT NULL,
            `date_modified` DATETIME NOT NULL,
            PRIMARY KEY (`queue_id`),
            KEY `ready` (`status`, `available_at`, `priority`, `queue_id`),
            KEY `worker` (`status`, `locked_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    public function enqueue($code, $route, $args = array(), $priority = 100, $maxAttempts = 3) {
        if (!is_string($code) || !preg_match('/^[a-z0-9_.:-]{2,128}$/i', $code) ||
            !is_string($route) || !preg_match('#^cron/[a-z0-9_/]+$#i', $route)) {
            throw new InvalidArgumentException('Invalid queue code or route');
        }
        $json = json_encode($args, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (!is_array($args) || $json === false) {
            throw new InvalidArgumentException('Invalid queue arguments');
        }
        $this->db->query("INSERT INTO `" . DB_PREFIX . "novacart_queue` SET code='" . $this->db->escape($code) .
            "', route='" . $this->db->escape($route) . "', args='" . $this->db->escape($json) .
            "', priority=" . (int)$priority . ", max_attempts=" . max(1, min(20, (int)$maxAttempts)) .
            ", status='pending', attempts=0, available_at=NOW(), date_added=NOW(), date_modified=NOW()");
        return (int)$this->db->getLastId();
    }

    public function stats() {
        $stats = array('pending'=>0, 'processing'=>0, 'done'=>0, 'failed'=>0);
        $query = $this->db->query("SELECT status, COUNT(*) total FROM `" . DB_PREFIX . "novacart_queue` GROUP BY status");
        foreach ($query->rows as $row) {
            $stats[$row['status']] = (int)$row['total'];
        }
        return $stats;
    }

    public function run($limit = 25) {
        $limit = max(1, min(100, (int)$limit));
        $processed = 0;
        $failed = 0;
        $this->recoverStale();
        for ($i = 0; $i < $limit; $i++) {
            $job = $this->claim();
            if (!$job) {
                break;
            }
            $processed++;
            if (!$this->execute($job)) {
                $failed++;
            }
        }
        return array('status'=>'ok', 'processed'=>$processed, 'failed'=>$failed);
    }

    private function claim() {
        $worker = substr(bin2hex(random_bytes(16)), 0, 96);
        // One atomic update claims a single available row, even with multiple workers.
        $this->db->query("UPDATE `" . DB_PREFIX . "novacart_queue` SET status='processing', attempts=attempts+1,
            locked_at=NOW(), locked_by='" . $this->db->escape($worker) . "', date_modified=NOW()
            WHERE status='pending' AND available_at<=NOW()
            ORDER BY priority ASC, queue_id ASC LIMIT 1");
        if ($this->db->countAffected() < 1) {
            return array();
        }
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "novacart_queue`
            WHERE status='processing' AND locked_by='" . $this->db->escape($worker) . "' LIMIT 1");
        return $query->num_rows ? $query->row : array();
    }

    private function execute($job) {
        $success = true;
        $message = '';
        $id = (int)$job['queue_id'];
        $lock = 'ncq_' . substr(hash('sha256', DB_DATABASE . ':' . DB_PREFIX), 0, 16) . '_' . $id;
        $locked = false;
        try {
            $result = $this->db->query("SELECT GET_LOCK('" . $this->db->escape($lock) . "', 0) acquired");
            if (empty($result->row['acquired'])) {
                throw new RuntimeException('Job advisory lock unavailable');
            }
            $locked = true;
            $route = (string)$job['route'];
            if (!preg_match('#^cron/[a-z0-9_/]+$#i', $route)) {
                throw new RuntimeException('Invalid queue route');
            }
            $args = json_decode((string)$job['args'], true);
            if (!is_array($args)) {
                throw new RuntimeException('Invalid queue payload');
            }
            $response = $this->registry->get('load')->controller($route, $args);
            if ($response === false || (is_array($response) && array_key_exists('success', $response) && !$response['success'])) {
                throw new RuntimeException(is_array($response) && isset($response['message']) ? (string)$response['message'] : 'Handler returned failure');
            }
        } catch (Throwable $e) {
            $success = false;
            $message = substr(get_class($e) . ': ' . $e->getMessage(), 0, 2000);
            $log = $this->registry->get('log');
            if ($log) {
                $log->write('NovaCart queue #' . $id . ': ' . $message);
            }
        } finally {
            if ($locked) {
                $this->db->query("SELECT RELEASE_LOCK('" . $this->db->escape($lock) . "')");
            }
        }
        if ($success) {
            $this->db->query("UPDATE `" . DB_PREFIX . "novacart_queue` SET status='done', locked_at=NULL,
                locked_by=NULL, last_error=NULL, date_modified=NOW() WHERE queue_id=" . $id);
        } else {
            $final = (int)$job['attempts'] >= (int)$job['max_attempts'];
            $delay = min(3600, 30 * (2 ** max(0, (int)$job['attempts'] - 1)));
            $this->db->query("UPDATE `" . DB_PREFIX . "novacart_queue` SET status='" . ($final ? 'failed' : 'pending') .
                "', available_at=DATE_ADD(NOW(), INTERVAL " . (int)$delay . " SECOND), locked_at=NULL,
                locked_by=NULL, last_error='" . $this->db->escape($message) . "', date_modified=NOW()
                WHERE queue_id=" . $id);
        }
        return $success;
    }

    private function recoverStale() {
        // Claim that appears abandoned is recovered only if its advisory lock is no longer held.
        $rows = $this->db->query("SELECT queue_id, attempts, max_attempts FROM `" . DB_PREFIX .
            "novacart_queue` WHERE status='processing' AND locked_at < DATE_SUB(NOW(), INTERVAL 15 MINUTE)
            ORDER BY queue_id LIMIT 100")->rows;
        foreach ($rows as $row) {
            $id = (int)$row['queue_id'];
            $lock = 'ncq_' . substr(hash('sha256', DB_DATABASE . ':' . DB_PREFIX), 0, 16) . '_' . $id;
            try {
                $q = $this->db->query("SELECT IS_USED_LOCK('" . $this->db->escape($lock) . "') owner");
                if (!array_key_exists('owner', $q->row) || $q->row['owner'] !== null) {
                    continue;
                }
                $status = (int)$row['attempts'] >= (int)$row['max_attempts'] ? 'failed' : 'pending';
                $this->db->query("UPDATE `" . DB_PREFIX . "novacart_queue` SET status='" . $status .
                    "', locked_at=NULL, locked_by=NULL, available_at=NOW(), date_modified=NOW()
                    WHERE queue_id=" . $id . " AND status='processing'");
            } catch (Throwable $e) {
                $log = $this->registry->get('log');
                if ($log) { $log->write('NovaCart queue recovery: ' . $e->getMessage()); }
            }
        }
    }
}
