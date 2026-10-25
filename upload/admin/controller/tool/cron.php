<?php
class ControllerToolCron extends Controller {
    public function preview() {
        $this->load->language('tool/cron');
        $json = array();
        try {
            if (!$this->user->hasPermission('access', 'tool/cron')) {
                throw new RuntimeException('Access denied');
            }
            require_once(DIR_SYSTEM . 'library/novacart/scheduler.php');
            $scheduler = new NovaCartScheduler($this->registry);
            $json['dates'] = $scheduler->previewCron((string)($this->request->get['expression'] ?? ''), 5);
        } catch (Throwable $e) {
            $json['error'] = $e->getMessage();
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    private function displayRunStatus($status) {
        $status = trim((string)$status);
        $map = array('success'=>'success','ok'=>'success','done'=>'success','error'=>'error','failed'=>'error',
            'warning'=>'warning','running'=>'running','locked'=>'locked','pending'=>'pending','processing'=>'running');
        $key = isset($map[$status]) ? $map[$status] : '';
        return $key !== '' ? $this->language->get('text_run_status_' . $key) : ($status !== '' ? $status : '—');
    }

    private function displayRunMessage($message, $status, $route = '') {
        $message = trim((string)$message);
        if ($message === 'text_success' && $route === 'cron/currency') {
            return $this->language->get('text_currency_refresh_success');
        }
        // Handler text must be preserved; unresolved language keys are not useful messages.
        if ($message === '' || $message === 'OK' || preg_match('/^(?:text|error)_[a-z0-9_]+$/i', $message)) {
            return $this->displayRunStatus($status);
        }
        return $message;
    }

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
        $data['cron_preview_url'] = str_replace('&amp;', '&', $this->url->link('tool/cron/preview', 'user_token=' . $this->session->data['user_token'], true));
        $data['action'] = $this->url->link('tool/cron', 'user_token=' . $this->session->data['user_token'], true);
        $data['can_modify'] = $this->user->hasPermission('modify', 'tool/cron');
        $data['queue_status_labels'] = array(
            'all' => $this->language->get('text_queue_all'),
            'pending' => $this->language->get('text_queue_pending'),
            'processing' => $this->language->get('text_queue_processing'),
            'done' => $this->language->get('text_queue_done'),
            'failed' => $this->language->get('text_queue_failed')
        );
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
        $data['task_history'] = array();
        $data['history_task'] = array();
        $data['edit_task'] = array('cron_id'=>0,'code'=>'','route'=>'','interval_seconds'=>3600,'cron_expression'=>'','args'=>'{}','status'=>1);
        $data['cli_command'] = 'php ' . rtrim(dirname(DIR_APPLICATION), '/\\') . '/cli.php jobs:run';
        $data['http_command'] = '';
        $keyPath = DIR_STORAGE . 'novacart_cron.key';
        if (is_file($keyPath)) {
            $key = trim((string)file_get_contents($keyPath));
            $catalog = defined('HTTPS_CATALOG') ? HTTPS_CATALOG : (defined('HTTP_CATALOG') ? HTTP_CATALOG : '');
            if ($key !== '' && $catalog !== '') {
                $url = rtrim($catalog, '/') . '/cron.php?key=' . rawurlencode($key);
                $data['http_command'] = 'curl.exe -sS -i -L --max-redirs 3 --max-time 120 "' . $url . '"';
            }
        }
        $data['job_runs'] = array();
        $data['run_source_labels'] = array('cli'=>$this->language->get('text_source_cli'), 'scheduled'=>$this->language->get('text_source_scheduled'), 'http'=>$this->language->get('text_source_http'), 'unknown'=>$this->language->get('text_source_unknown'));
        $data['heartbeat_status'] = 'none';
        $data['heartbeat_minutes'] = null;
        foreach (array('text_run_status_success','text_run_status_error','text_run_status_warning','text_run_status_running','text_run_status_locked','text_run_status_pending','text_task_history','text_task_manual','text_task_scheduled','text_task_history_empty','text_schedule_mode','text_schedule_interval','text_schedule_cron','text_cron_expression','text_cron_preview','text_cron_example','text_cron_preview_failed','text_currency_refresh_success','text_interval_years','text_interval_months','text_interval_assumptions','text_interval_quantity','text_interval_unit','text_interval_minutes','text_interval_hours','text_interval_days','text_interval_weeks','text_interval_seconds','text_interval_invalid','text_interval_apply','text_monitor','text_monitor_history','text_monitor_history_hint','text_monitor_empty','text_heartbeat_ok','text_heartbeat_late','text_heartbeat_missing','text_heartbeat_error','text_heartbeat_running','text_action_run','text_action_edit','text_action_delete','text_action_confirm','text_action_add','text_action_code','text_action_route','text_action_interval','text_action_args','text_action_cancel','text_action_save','text_system_task','text_cron_commands','text_cron_help','text_cron_cli','text_cron_web','text_cron_copy','text_cron_curl_help','text_cron_key_rotate','text_cron_key_confirm','text_cron_key_rotated','text_queue_locked','text_queue_cleanup','text_queue_cleanup_confirm','text_queue_run_result','text_queue_run','text_queue_retry','text_queue_confirm_delete','text_queue_panel','text_queue_manage','text_queue_pending','text_queue_processing','text_queue_done','text_queue_failed','text_queue_search','text_queue_filter','text_queue_attempts','text_queue_available','text_queue_error','column_source','column_started','column_finished','column_duration','column_scheduler','column_queue','column_run_status','column_run_error') as $key) {
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
                    if ($operation === 'rotate_http_key') {
                        $keyFile = DIR_STORAGE . 'novacart_cron.key';
                        $newKey = bin2hex(random_bytes(32));
                        $tempFile = tempnam(DIR_STORAGE, 'cronkey_');
                        if ($tempFile === false) {
                            throw new RuntimeException('Unable to create temporary cron key');
                        }
                        try {
                            if (file_put_contents($tempFile, $newKey, LOCK_EX) !== 64) {
                                throw new RuntimeException('Unable to write new cron key');
                            }
                            @chmod($tempFile, 0600);
                            if (!@rename($tempFile, $keyFile)) {
                                throw new RuntimeException('Unable to replace cron key; previous key remains active');
                            }
                        } finally {
                            if (is_file($tempFile)) {
                                @unlink($tempFile);
                            }
                        }
                        $this->session->data['success'] = $this->language->get('text_cron_key_rotated');
                    } elseif ($operation === 'toggle') {
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
                        $processed = isset($result['queue']['processed']) ? (int)$result['queue']['processed'] : 0;
                        $failed = isset($result['queue']['failed']) ? (int)$result['queue']['failed'] : 0;
                        if ($failed > 0) {
                            throw new RuntimeException(sprintf($this->language->get('error_queue_failed'), $processed, $failed));
                        }
                        $this->session->data['success'] = sprintf($this->language->get('text_queue_run_result'), $processed, $failed);
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
                    } elseif ($operation === 'purge_queue_done') {
                        $this->db->query("DELETE FROM `" . DB_PREFIX . "novacart_queue` WHERE status='done' AND date_modified < DATE_SUB(NOW(), INTERVAL 7 DAY)");
                    } elseif ($operation === 'save') {
                        $code = isset($this->request->post['code']) ? trim((string)$this->request->post['code']) : '';
                        $route = isset($this->request->post['route']) ? trim((string)$this->request->post['route']) : '';
                        $expression = isset($this->request->post['schedule_mode']) && $this->request->post['schedule_mode'] === 'cron' ?
                            trim((string)($this->request->post['cron_expression'] ?? '')) : '';
                        if (isset($this->request->post['schedule_mode']) && $this->request->post['schedule_mode'] === 'cron' && $expression === '') {
                            throw new InvalidArgumentException('Cron expression is required');
                        }
                        $scheduler->install();
                        $interval = isset($this->request->post['interval_seconds']) ? (int)$this->request->post['interval_seconds'] : 0;
                        $raw = isset($this->request->post['args']) ? (string)$this->request->post['args'] : '{}';
                        $args = json_decode($raw, true);
                        if (!is_array($args) || json_last_error() !== JSON_ERROR_NONE) {
                            throw new InvalidArgumentException('Invalid JSON arguments');
                        }
                        if ($id > 0) {
                            $scheduler->save($id, $code, $route, $interval, !empty($this->request->post['status']), $args, $expression);
                        } else {
                            if (strpos($code, 'core.') === 0) {
                                throw new InvalidArgumentException('Reserved system task code');
                            }
                            $duplicate = $this->db->query("SELECT cron_id FROM `" . DB_PREFIX .
                                "novacart_cron` WHERE code='" . $this->db->escape($code) . "' LIMIT 1");
                            if ($duplicate->num_rows) {
                                throw new RuntimeException('Scheduler task code already exists');
                            }
                            $scheduler->register($code, $route, $interval, $args, !empty($this->request->post['status']), $expression);
                        }
                    } else {
                        throw new InvalidArgumentException('Unknown scheduler action');
                    }
                    if (!isset($this->session->data['success'])) {
                        $this->session->data['success'] = $this->language->get('text_success');
                    }
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
            $scheduler->install();
            $historyId = isset($this->request->get['history_id']) ? (int)$this->request->get['history_id'] : 0;
            if ($historyId > 0) {
                $data['history_task'] = $scheduler->getTask($historyId);
                if ($data['history_task']) {
                    $scheduler->installHistory();
                    $data['task_history'] = $scheduler->recentHistory($historyId, 30);
                    foreach ($data['task_history'] as &$entry) {
                        $entry['status_display'] = $this->displayRunStatus($entry['status']);
                        $entry['message_display'] = $this->displayRunMessage($entry['message'], $entry['status'], $data['history_task']['route']);
                    }
                    unset($entry);
                }
            }
            $data['tasks'] = $scheduler->listTasks();
            foreach ($data['tasks'] as &$task) {
                $remaining = (int)$task['interval_seconds'];
                $parts = array();
                foreach (array('year'=>31536000,'month'=>2592000,'week'=>604800,'day'=>86400,'hour'=>3600,'minute'=>60,'second'=>1) as $unit => $factor) {
                    $value = (int)floor($remaining / $factor);
                    if ($value > 0) {
                        $parts[] = $value . ' ' . $this->language->get('text_duration_' . $unit);
                        $remaining %= $factor;
                    }
                }
                $task['interval_display'] = !empty($task['cron_expression']) ? $task['cron_expression'] : implode(' ', $parts);
                $task['status_display'] = $this->displayRunStatus($task['last_status']);
                $task['message_display'] = $this->displayRunMessage($task['last_message'], $task['last_status'], $task['route']);
            }
            unset($task);
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
            $data['queue_limit'] = isset($this->request->get['queue_limit']) ? (int)$this->request->get['queue_limit'] : 25;
            if (!in_array($data['queue_limit'], array(10,25,50,100), true)) { $data['queue_limit'] = 25; }
            $data['queue_page'] = isset($this->request->get['queue_page']) ? max(1, (int)$this->request->get['queue_page']) : 1;
            $sortMap = array('queue_id'=>'queue_id','code'=>'code','route'=>'route','status'=>'status',
                'attempts'=>'attempts','available_at'=>'available_at');
            $sort = isset($this->request->get['queue_sort']) ? (string)$this->request->get['queue_sort'] : 'queue_id';
            if (!isset($sortMap[$sort])) { $sort = 'queue_id'; }
            $order = isset($this->request->get['queue_order']) && strtoupper((string)$this->request->get['queue_order']) === 'ASC' ? 'ASC' : 'DESC';
            $data['queue_sort'] = $sort;
            $data['queue_order'] = $order;
            $base = 'user_token=' . $this->session->data['user_token'] . '&queue_search=' .
                urlencode($data['queue_search']) . '&queue_status=' . urlencode($data['queue_status']) .
                '&queue_limit=' . $data['queue_limit'];
            $data['queue_sort_urls'] = array();
            foreach ($sortMap as $key => $column) {
                $nextOrder = $sort === $key && $order === 'ASC' ? 'DESC' : 'ASC';
                $data['queue_sort_urls'][$key] = $this->url->link('tool/cron',
                    $base . '&queue_sort=' . $key . '&queue_order=' . $nextOrder, true);
            }
            $count = (int)$this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX .
                "novacart_queue`" . $where)->row['total'];
            $offset = ($data['queue_page'] - 1) * $data['queue_limit'];
            $data['queue_jobs'] = $this->db->query("SELECT queue_id,code,route,status,attempts,max_attempts,available_at,locked_at,last_error
                FROM `" . DB_PREFIX . "novacart_queue`" . $where . " ORDER BY " .
                $sortMap[$sort] . " " . $order . ", queue_id DESC LIMIT " .
                $offset . "," . $data['queue_limit'])->rows;
            $pagination = new Pagination();
            $pagination->total = $count;
            $pagination->page = $data['queue_page'];
            $pagination->limit = $data['queue_limit'];
            $pagination->url = $this->url->link('tool/cron',
                $base . '&queue_sort=' . $sort . '&queue_order=' . $order . '&queue_page={page}', true);
            $data['queue_pagination'] = $pagination->render();

            if (!empty($this->request->get['edit_id'])) {
                $task = $scheduler->getTask((int)$this->request->get['edit_id']);
                if ($task) { $data['edit_task'] = $task; }
            }
            require_once(DIR_SYSTEM . 'library/novacart/jobs_monitor.php');
            $monitor = new NovaCartJobsMonitor($this->registry);
            $data['job_runs'] = $monitor->recent(30);
            foreach ($data['job_runs'] as &$run) {
                $run['status_display'] = $this->displayRunStatus($run['status']);
                $run['scheduler_status_display'] = $this->displayRunStatus($run['scheduler_status']);
                $run['queue_status_display'] = $this->displayRunStatus($run['queue_status']);
                $run['error_display'] = $this->displayRunMessage($run['error_message'], $run['status']);
            }
            unset($run);
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
