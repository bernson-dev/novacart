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
                require_once(DIR_SYSTEM . 'library/novacart/jobs_monitor.php');
                (new NovaCartJobsMonitor($this->registry))->install();
                // Provision the HTTP cron secret once; do not rotate an existing key.
                $secretPath = DIR_STORAGE . 'novacart_cron.key';
                if (!is_file($secretPath)) {
                    $handle = @fopen($secretPath, 'x');
                    if ($handle !== false) {
                        try {
                            if (fwrite($handle, bin2hex(random_bytes(32))) !== 64) {
                                throw new RuntimeException('Could not write the cron key');
                            }
                        } finally {
                            fclose($handle);
                        }
                        @chmod($secretPath, 0600);
                    }
                }
                if (!is_file($secretPath) || trim((string)file_get_contents($secretPath)) === '') {
                    throw new RuntimeException('HTTP cron key is missing or empty');
                }
                $result = array('status' => 'ok', 'message' => 'Cron schema installed');
            } elseif (!$isCli && isset($this->request->get['mode']) && $this->request->get['mode'] === 'scheduler_one') {
                $id = isset($this->request->get['scheduler_id']) ? (int)$this->request->get['scheduler_id'] : 0;
                $result = array('scheduler_one' => $scheduler->runOne($id));
            } elseif (!$isCli && isset($this->request->get['mode']) && $this->request->get['mode'] === 'queue_one') {
                $id = isset($this->request->get['queue_id']) ? (int)$this->request->get['queue_id'] : 0;
                $retry = !empty($this->request->get['retry']);
                require_once(DIR_SYSTEM . 'library/novacart/queue.php');
                $result = array('queue_one' => (new NovaCartQueue($this->registry))->runOne($id, $retry));
            } elseif (!$isCli && isset($this->request->get['mode']) && $this->request->get['mode'] === 'queue') {
                require_once(DIR_SYSTEM . 'library/novacart/queue.php');
                $result = array('queue' => (new NovaCartQueue($this->registry))->run(25));
            } elseif ($command === 'list' && $isCli) {
                $result = array('status' => 'ok', 'tasks' => $scheduler->listTasks());
            } elseif ($command === 'jobs:run' && $isCli) {
                require_once(DIR_SYSTEM . 'library/novacart/jobs_monitor.php');
                $monitor = new NovaCartJobsMonitor($this->registry);
                $started = microtime(true);
                $runId = $monitor->begin();
                $cronResult = array('status' => 'error', 'processed' => 0, 'failed' => 0);
                $queueResult = array('status' => 'error', 'processed' => 0, 'failed' => 0);
                $runErrors = array();
                try {
                    $cronResult = $scheduler->runDue(20);
                } catch (Throwable $e) {
                    $runErrors[] = 'Scheduler: ' . get_class($e) . ': ' . $e->getMessage();
                }
                try {
                    require_once(DIR_SYSTEM . 'library/novacart/queue.php');
                    $queueResult = (new NovaCartQueue($this->registry))->run(25);
                } catch (Throwable $e) {
                    $runErrors[] = 'Queue: ' . get_class($e) . ': ' . $e->getMessage();
                }
                $runError = implode('; ', $runErrors);
                if ($runError !== '') {
                    $this->log->write('NovaCart jobs run: ' . $runError);
                }
                $monitor->finish($runId, $cronResult, $queueResult, (int)round((microtime(true) - $started) * 1000), $runError);
                $healthy = $runError === '' && $cronResult['status'] === 'ok' && $queueResult['status'] === 'ok'
                    && empty($cronResult['failed']) && empty($queueResult['failed']);
                $result = array('status' => $healthy ? 'ok' : 'error', 'scheduler' => $cronResult, 'queue' => $queueResult);
                if ($runError !== '') {
                    $result['message'] = $runError;
                }
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
        if ($isCli) {
            $GLOBALS['NOVACART_CLI_EXIT_STATUS'] = $payload !== false && isset($result['status']) && $result['status'] === 'ok' ? 0 : 1;
        }
    }
}
