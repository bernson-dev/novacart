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
                $result = array('status' => 'ok', 'message' => 'Cron schema installed');
             } elseif ($command === 'test:register' && $isCli) {
                $scheduler->register('test.integration', 'cron/test', 3600, array());
                $result = array('status' => 'ok', 'message' => 'Test task registered');
            } elseif ($command === 'list' && $isCli) {
                $result = array('status' => 'ok', 'tasks' => $scheduler->listTasks());
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
