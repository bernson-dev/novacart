<?php
/**
 * Bounded execution history for the unified CLI jobs runner.
 * The most recent row is also the heartbeat; no external service is needed.
 */
class NovaCartJobsMonitor {
    private $db;

    public function __construct($registry) {
        $this->db = $registry->get('db');
    }

    public function install() {
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "novacart_jobs_run` (
            `run_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `started_at` DATETIME NOT NULL,
            `source` VARCHAR(24) NOT NULL DEFAULT 'unknown',
            `finished_at` DATETIME NULL,
            `status` VARCHAR(16) NOT NULL DEFAULT 'running',
            `scheduler_status` VARCHAR(16) NULL,
            `scheduler_processed` INT UNSIGNED NOT NULL DEFAULT 0,
            `scheduler_failed` INT UNSIGNED NOT NULL DEFAULT 0,
            `queue_status` VARCHAR(16) NULL,
            `queue_processed` INT UNSIGNED NOT NULL DEFAULT 0,
            `queue_failed` INT UNSIGNED NOT NULL DEFAULT 0,
            `duration_ms` INT UNSIGNED NULL,
            `error_message` TEXT NULL,
            PRIMARY KEY (`run_id`),
            KEY `recent` (`started_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $columns = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "novacart_jobs_run` LIKE 'source'");
        if (!$columns->num_rows) {
            $this->db->query("ALTER TABLE `" . DB_PREFIX . "novacart_jobs_run` ADD `source` VARCHAR(24) NOT NULL DEFAULT 'unknown' AFTER `started_at`");
        }
    }

    public function begin($source = 'unknown') {
        if (!in_array($source, array('cli', 'scheduled', 'http'), true)) { $source = 'unknown'; }
        $this->db->query("INSERT INTO `" . DB_PREFIX . "novacart_jobs_run` SET started_at=NOW(), source='" . $this->db->escape($source) . "', status='running'");
        return (int)$this->db->getLastId();
    }

    public function finish($id, $scheduler, $queue, $durationMs, $error = '') {
        $scheduler = is_array($scheduler) ? $scheduler : array();
        $queue = is_array($queue) ? $queue : array();
        $schedulerStatus = isset($scheduler['status']) ? (string)$scheduler['status'] : 'error';
        $queueStatus = isset($queue['status']) ? (string)$queue['status'] : 'error';
        $status = $error !== '' ? 'error' :
            (($schedulerStatus === 'ok' && $queueStatus === 'ok' &&
              empty($scheduler['failed']) && empty($queue['failed'])) ? 'ok' : 'warning');
        $this->db->query("UPDATE `" . DB_PREFIX . "novacart_jobs_run` SET finished_at=NOW(),
            status='" . $status . "', scheduler_status='" . $this->db->escape($schedulerStatus) . "',
            scheduler_processed=" . max(0, (int)(isset($scheduler['processed']) ? $scheduler['processed'] : 0)) . ",
            scheduler_failed=" . max(0, (int)(isset($scheduler['failed']) ? $scheduler['failed'] : 0)) . ",
            queue_status='" . $this->db->escape($queueStatus) . "',
            queue_processed=" . max(0, (int)(isset($queue['processed']) ? $queue['processed'] : 0)) . ",
            queue_failed=" . max(0, (int)(isset($queue['failed']) ? $queue['failed'] : 0)) . ",
            duration_ms=" . max(0, min(4294967295, (int)$durationMs)) . ",
            error_message='" . $this->db->escape(substr((string)$error, 0, 2000)) . "'
            WHERE run_id=" . (int)$id . " AND status='running'");
        // Retain the latest 500 runs plus recent failures/incomplete runs.
        // The seven-day limit bounds extra rows even if every minute fails.
        $this->db->query("DELETE FROM `" . DB_PREFIX . "novacart_jobs_run`
            WHERE run_id NOT IN (
                SELECT run_id FROM (
                    SELECT run_id FROM `" . DB_PREFIX . "novacart_jobs_run`
                    ORDER BY run_id DESC LIMIT 500
                ) AS recent_runs
            )
            AND NOT (status IN ('error', 'warning', 'running')
                AND started_at >= DATE_SUB(NOW(), INTERVAL 7 DAY))");
    }

    public function recent($limit = 30) {
        $limit = max(1, min(100, (int)$limit));
        return $this->db->query("SELECT * FROM `" . DB_PREFIX .
            "novacart_jobs_run` ORDER BY run_id DESC LIMIT " . $limit)->rows;
    }
}
