<?php
class ControllerCronRun extends Controller {
    public function index() {
        // Never allow the route through the public catalog front controller.
        if (!defined('NOVACART_CRON_ENTRY')) {
            $this->response->setStatusCode(403);
            $this->response->setOutput('Forbidden');
            return;
        }
        $isCli = PHP_SAPI === 'cli';
        if (!$isCli) {
            $secretFile = DIR_STORAGE . 'novacart_cron.key';
            $secret = is_file($secretFile) ? trim((string)file_get_contents($secretFile)) : '';
            $provided = isset($this->request->get['key']) && is_string($this->request->get['key']) ? $this->request->get['key'] : '';
            if ($secret === '' || !hash_equals($secret, $provided)) {
                $this->response->setStatusCode(403);
                $this->response->setOutput('Forbidden');
                return;
            }
        }

        require_once(DIR_SYSTEM . 'library/novacart/scheduler.php');
        $scheduler = new NovaCartScheduler($this->registry);
        $command = $isCli && isset($this->request->get['command']) ? $this->request->get['command'] : 'run';
        try {
            if ($command === 'install' && $isCli) {
                $scheduler->install();
                require_once(DIR_SYSTEM . 'library/novacart/queue.php');
                (new NovaCartQueue($this->registry))->install();
                $result = array('status' => 'ok', 'message' => 'Cron schema installed'); } elseif ($command === 'list' && $isCli) {
                $result = array('status' => 'ok', 'tasks' => $scheduler->listTasks());
            } elseif ($isCli && $command === 'queue:test:seed') {
                require_once(DIR_SYSTEM . 'library/novacart/queue.php');
                $queue = new NovaCartQueue($this->registry);
                $ids = array(
                    'success' => $queue->enqueue('test.queue.success', 'cron/queue_probe', array('mode'=>'success'), 100, 1),
                    'failure' => $queue->enqueue('test.queue.failure', 'cron/queue_probe', array('mode'=>'failure'), 100, 2),
                    'slow' => $queue->enqueue('test.queue.slow', 'cron/queue_probe', array('mode'=>'slow'), 100, 1)
                );
                $result = array('status'=>'ok', 'ids'=>$ids);
            } elseif ($isCli && $command === 'queue:test:list') {
                $rows = $this->db->query("SELECT queue_id, code, status, attempts, max_attempts, available_at, last_error
                    FROM `" . DB_PREFIX . "novacart_queue` WHERE code LIKE 'test.queue.%' ORDER BY queue_id DESC LIMIT 30")->rows;
                $result = array('status'=>'ok', 'tasks'=>$rows);
            } elseif ($command === 'queue:run' || $command === 'queue:stats') {
                require_once(DIR_SYSTEM . 'library/novacart/queue.php');
                $queue = new NovaCartQueue($this->registry);
                $result = $command === 'queue:stats' ? array('status'=>'ok', 'queue'=>$queue->stats()) : $queue->run(25);
            } else {
                $result = $scheduler->runDue(20);
            }
        } catch (Throwable $e) {
            $this->response->setStatusCode(500);
            $result = array('status' => 'error', 'message' => $e->getMessage());
            $this->log->write('NovaCart cron: ' . $e->getMessage());
        }
        $this->response->addHeader('Content-Type: application/json; charset=utf-8');
        $payload = json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $this->response->setOutput($payload !== false ? $payload : '{"status":"error"}');
    }
}
