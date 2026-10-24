<?php
class ControllerToolCron extends Controller {
    public function index() {
        $this->load->language('tool/cron');
        $this->document->setTitle($this->language->get('heading_title'));
        $data = array();
        $data['heading_title'] = $this->language->get('heading_title');
        foreach (array('text_no_results','text_enabled','text_disabled','text_success','text_cli',
            'column_code','column_route','column_interval','column_status','column_last',
            'column_next','column_result','column_message','column_action','button_enable',
            'button_disable','error_permission') as $key) {
            $data[$key] = $this->language->get($key);
        }
        $data['action'] = $this->url->link('tool/cron', 'user_token=' . $this->session->data['user_token'], true);
        $data['can_modify'] = $this->user->hasPermission('modify', 'tool/cron');
        $data['queue_jobs'] = array();
        $data['queue_stats'] = array('pending'=>0,'processing'=>0,'done'=>0,'failed'=>0);
        $data['queue_status'] = isset($this->request->get['queue_status']) ? (string)$this->request->get['queue_status'] : 'all';
        if (!in_array($data['queue_status'], array('all','pending','processing','done','failed'), true)) {
            $data['queue_status'] = 'all';
        }
        $data['queue_search'] = isset($this->request->get['queue_search']) ? trim((string)$this->request->get['queue_search']) : '';
        $data['queue_search'] = substr($data['queue_search'], 0, 128);
        $data['queue_url'] = $this->url->link('tool/queue', 'user_token=' . $this->session->data['user_token'], true);
        $data['user_token'] = $this->session->data['user_token'];
        $data['tasks'] = array();
        $data['edit_task'] = array('cron_id'=>0,'code'=>'','route'=>'','interval_seconds'=>3600,'args'=>'{}','status'=>1);
        $data['cli_command'] = 'php ' . rtrim(dirname(DIR_APPLICATION), '/\\') . '/cli.php jobs:run';
        $data['http_command'] = '';
        $keyPath = DIR_STORAGE . 'novacart_cron.key';
        if (is_file($keyPath)) {
            $key = trim((string)file_get_contents($keyPath));
            $catalog = defined('HTTPS_CATALOG') ? HTTPS_CATALOG : (defined('HTTP_CATALOG') ? HTTP_CATALOG : '');
            if ($key !== '' && $catalog !== '') {
                $data['http_command'] = 'curl -fsS ' . escapeshellarg(rtrim($catalog, '/') . '/cron.php?key=' . rawurlencode($key));
            }
        }
        $data['job_runs'] = array();
        $data['heartbeat_status'] = 'none';
        $data['heartbeat_minutes'] = null;
        foreach (array('text_monitor','text_monitor_empty','text_heartbeat_ok','text_heartbeat_late','text_heartbeat_missing','text_heartbeat_error','text_heartbeat_running','text_action_run','text_action_edit','text_action_delete','text_action_confirm','text_action_add','text_action_code','text_action_route','text_action_interval','text_action_args','text_action_cancel','text_action_save','text_queue_panel','text_queue_manage','text_queue_pending','text_queue_processing','text_queue_done','text_queue_failed','text_queue_search','text_queue_filter','text_queue_attempts','text_queue_available','text_queue_error','column_started','column_finished','column_duration','column_scheduler','column_queue','column_run_status','column_run_error') as $key) {
            $data[$key] = $this->language->get($key);
        }
        $data['error_warning'] = '';
        $data['success'] = '';
        if ($this->request->server['REQUEST_METHOD'] === 'POST') {
            if (!$data['can_modify']) {
                $this->session->data['error_warning'] = $this->language->get('error_permission');
            } else {
                try {
                    require_once(DIR_SYSTEM . 'library/novacart/scheduler.php');
                    $scheduler = new NovaCartScheduler($this->registry);
                    $operation = isset($this->request->post['scheduler_action']) ? (string)$this->request->post['scheduler_action'] : 'toggle';
                    $id = isset($this->request->post['cron_id']) ? (int)$this->request->post['cron_id'] : 0;
                    if ($operation === 'toggle') {
                        $scheduler->setStatus($id, !empty($this->request->post['status']));
                    } elseif ($operation === 'delete') {
                        $scheduler->deleteTask($id);
                    } elseif ($operation === 'run') {
                        $result = $this->callCatalogCron('scheduler_one', array('scheduler_id'=>$id));
                        if (empty($result['scheduler_one']['success'])) {
                            throw new RuntimeException(isset($result['scheduler_one']['message']) ?
                                (string)$result['scheduler_one']['message'] : 'Scheduler job failed');
                        }
                    } elseif ($operation === 'run_queue') {
                        $result = $this->callCatalogCron('queue');
                        if (empty($result['queue']) || $result['queue']['status'] !== 'ok') {
                            throw new RuntimeException('Queue worker did not complete');
                        }
                    } elseif ($operation === 'run_queue_one') {
                        $queueId = isset($this->request->post['queue_id']) ? (int)$this->request->post['queue_id'] : 0;
                        $retry = !empty($this->request->post['retry']) ? 1 : 0;
                        $result = $this->callCatalogCron('queue_one', array('queue_id'=>$queueId,'retry'=>$retry));
                        if (empty($result['queue_one']['success'])) {
                            throw new RuntimeException(isset($result['queue_one']['message']) ?
                                (string)$result['queue_one']['message'] : 'Queue job failed');
                        }
                    } elseif ($operation === 'delete_queue_one') {
                        require_once(DIR_SYSTEM . 'library/novacart/queue.php');
                        $queueId = isset($this->request->post['queue_id']) ? (int)$this->request->post['queue_id'] : 0;
                        if ($queueId < 1 || !(new NovaCartQueue($this->registry))->deleteById($queueId)) {
                            throw new RuntimeException('Queue job is not removable');
                        }
                    } elseif ($operation === 'save') {
                        $code = isset($this->request->post['code']) ? trim((string)$this->request->post['code']) : '';
                        $route = isset($this->request->post['route']) ? trim((string)$this->request->post['route']) : '';
                        $interval = isset($this->request->post['interval_seconds']) ? (int)$this->request->post['interval_seconds'] : 0;
                        $raw = isset($this->request->post['args']) ? (string)$this->request->post['args'] : '{}';
                        $args = json_decode($raw, true);
                        if (!is_array($args) || json_last_error() !== JSON_ERROR_NONE) {
                            throw new InvalidArgumentException('Invalid JSON arguments');
                        }
                        if ($id > 0) {
                            $scheduler->save($id, $code, $route, $interval, !empty($this->request->post['status']), $args);
                        } else {
                            $scheduler->register($code, $route, $interval, $args, !empty($this->request->post['status']));
                        }
                    } else {
                        throw new InvalidArgumentException('Unknown scheduler action');
                    }
                    $this->session->data['success'] = $this->language->get('text_success');
                } catch (Throwable $e) {
                    $this->session->data['error_warning'] = $e->getMessage();
                }
            }
            $this->response->redirect($data['action']);
            return;
        }
        if (isset($this->session->data['error_warning'])) {
            $data['error_warning'] = $this->session->data['error_warning'];
            unset($this->session->data['error_warning']);
        }
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        }
        try {
            require_once(DIR_SYSTEM . 'library/novacart/scheduler.php');
            $scheduler = new NovaCartScheduler($this->registry);
            $data['tasks'] = $scheduler->listTasks();
            require_once(DIR_SYSTEM . 'library/novacart/queue.php');
            $data['queue_stats'] = (new NovaCartQueue($this->registry))->stats();
            $filterParts = array();
            if ($data['queue_status'] !== 'all') {
                $filterParts[] = "status='" . $this->db->escape($data['queue_status']) . "'";
            }
            if ($data['queue_search'] !== '') {
                $escaped = $this->db->escape($data['queue_search']);
                $filterParts[] = "(code LIKE '%" . $escaped . "%' OR route LIKE '%" . $escaped . "%')";
            }
            $where = $filterParts ? ' WHERE ' . implode(' AND ', $filterParts) : '';
            $data['queue_jobs'] = $this->db->query("SELECT queue_id,code,route,status,attempts,max_attempts,available_at,last_error
                FROM `" . DB_PREFIX . "novacart_queue`" . $where . " ORDER BY queue_id DESC LIMIT 25")->rows;

            if (!empty($this->request->get['edit_id'])) {
                $task = $scheduler->getTask((int)$this->request->get['edit_id']);
                if ($task) { $data['edit_task'] = $task; }
            }
            require_once(DIR_SYSTEM . 'library/novacart/jobs_monitor.php');
            $monitor = new NovaCartJobsMonitor($this->registry);
            $data['job_runs'] = $monitor->recent(30);
            if ($data['job_runs']) {
                $latest = $data['job_runs'][0];
                $age = $this->db->query("SELECT TIMESTAMPDIFF(MINUTE, started_at, NOW()) AS minutes FROM `" . DB_PREFIX . "novacart_jobs_run` WHERE run_id=" . (int)$latest['run_id'])->row;
                $data['heartbeat_minutes'] = max(0, (int)$age['minutes']);
                $data['heartbeat_status'] = $data['heartbeat_minutes'] > 5 ? 'late' :
                    ($latest['status'] === 'ok' ? 'ok' : ($latest['status'] === 'running' ? 'running' : 'error'));
            }
        } catch (Throwable $e) {
            $data['error_warning'] = $e->getMessage();
        }
        $data['breadcrumbs'] = array(
            array('text' => $this->language->get('text_home'), 'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)),
            array('text' => $data['heading_title'], 'href' => $data['action'])
        );
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $this->response->setOutput($this->load->view('tool/cron', $data));
    }
    private function callCatalogCron($mode, $params = array()) {
        $secretPath = DIR_STORAGE . 'novacart_cron.key';
        $key = is_file($secretPath) ? trim((string)file_get_contents($secretPath)) : '';
        if ($key === '' || !function_exists('curl_init')) {
            throw new RuntimeException('HTTP cron key or cURL is not configured');
        }
        $base = defined('HTTPS_CATALOG') ? HTTPS_CATALOG : (defined('HTTP_CATALOG') ? HTTP_CATALOG : '');
        if ($base === '') { throw new RuntimeException('Catalog URL is unavailable'); }
        $url = rtrim($base, '/') . '/cron.php?key=' . rawurlencode($key) . '&mode=' . rawurlencode($mode);
        foreach ($params as $name => $value) {
            $url .= '&' . rawurlencode($name) . '=' . rawurlencode((string)$value);
        }
        $curl = curl_init($url);
        curl_setopt_array($curl, array(CURLOPT_RETURNTRANSFER=>true, CURLOPT_CONNECTTIMEOUT=>5,
            CURLOPT_TIMEOUT=>120, CURLOPT_FOLLOWLOCATION=>false, CURLOPT_SSL_VERIFYPEER=>true,
            CURLOPT_SSL_VERIFYHOST=>2, CURLOPT_HTTPHEADER=>array('Accept: application/json')));
        $body = curl_exec($curl);
        $httpCode = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);
        $decoded = is_string($body) ? json_decode($body, true) : null;
        if ($httpCode !== 200 || !is_array($decoded)) {
            throw new RuntimeException($error !== '' ? $error : 'Scheduler endpoint returned HTTP ' . $httpCode);
        }
        return $decoded;
    }

}
