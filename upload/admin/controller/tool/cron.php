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
        $data['tasks'] = array();
        $data['job_runs'] = array();
        $data['heartbeat_status'] = 'none';
        $data['heartbeat_minutes'] = null;
        foreach (array('text_monitor','text_monitor_empty','text_heartbeat_ok','text_heartbeat_late','text_heartbeat_missing','column_started','column_finished','column_duration','column_scheduler','column_queue','column_run_status','column_run_error') as $key) {
            $data[$key] = $this->language->get($key);
        }
        $data['error_warning'] = '';
        $data['success'] = '';
        if ($this->request->server['REQUEST_METHOD'] === 'POST') {
            if (!$data['can_modify']) {
                $this->session->data['error_warning'] = $this->language->get('error_permission');
            } else {
                $id = isset($this->request->post['cron_id']) ? (int)$this->request->post['cron_id'] : 0;
                $status = isset($this->request->post['status']) ? (int)$this->request->post['status'] : -1;
                if ($id > 0 && ($status === 0 || $status === 1)) {
                    try {
                        $this->db->query("UPDATE `" . DB_PREFIX . "novacart_cron` SET status=" . $status .
                            ", date_modified=NOW() WHERE cron_id=" . $id);
                        $this->session->data['success'] = $this->language->get('text_success');
                    } catch (Throwable $e) {
                        $this->session->data['error_warning'] = $e->getMessage();
                    }
                } else {
                    $this->session->data['error_warning'] = $this->language->get('error_action');
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
            require_once(DIR_SYSTEM . 'library/novacart/jobs_monitor.php');
            $monitor = new NovaCartJobsMonitor($this->registry);
            $data['job_runs'] = $monitor->recent(30);
            if ($data['job_runs']) {
                $latest = $data['job_runs'][0];
                $age = $this->db->query("SELECT TIMESTAMPDIFF(MINUTE, started_at, NOW()) AS minutes FROM `" . DB_PREFIX . "novacart_jobs_run` WHERE run_id=" . (int)$latest['run_id'])->row;
                $data['heartbeat_minutes'] = max(0, (int)$age['minutes']);
                $data['heartbeat_status'] = $data['heartbeat_minutes'] > 5 ? 'late' : 'ok';
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
}
