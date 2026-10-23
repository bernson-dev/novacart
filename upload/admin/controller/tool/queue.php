<?php
class ControllerToolQueue extends Controller {
    public function index() {
        $this->load->language('tool/queue');
        $this->document->setTitle($this->language->get('heading_title'));
        $statuses = array('all', 'pending', 'processing', 'done', 'failed');
        $status = isset($this->request->get['filter_status']) ? (string)$this->request->get['filter_status'] : 'all';
        if (!in_array($status, $statuses, true)) { $status = 'all'; }
        $page = isset($this->request->get['page']) ? max(1, (int)$this->request->get['page']) : 1;
        $base = 'user_token=' . $this->session->data['user_token'];
        $filter = '&filter_status=' . urlencode($status);
        $url = $this->url->link('tool/queue', $base . $filter . '&page=' . $page, true);
        $canModify = $this->user->hasPermission('modify', 'tool/queue');
        if ($this->request->server['REQUEST_METHOD'] === 'POST') {
            $this->log->write('Queue admin POST: action=' . (isset($this->request->post['queue_action']) ? $this->request->post['queue_action'] : '[missing]') . ', id=' . (isset($this->request->post['queue_id']) ? (int)$this->request->post['queue_id'] : 0) . ', modify=' . (int)$canModify);
            if (!$canModify) {
                $this->session->data['queue_error'] = $this->language->get('error_permission');
            } else {
                $action = isset($this->request->post['queue_action']) ? (string)$this->request->post['queue_action'] : '';
                $id = isset($this->request->post['queue_id']) ? (int)$this->request->post['queue_id'] : 0;
                try {
                    if ($action === 'retry' && $id > 0) {
                        $this->db->query("UPDATE `" . DB_PREFIX . "novacart_queue` SET status='pending', attempts=0, available_at=NOW(), locked_at=NULL, locked_by=NULL, last_error=NULL, date_modified=NOW() WHERE queue_id=" . $id . " AND status='failed'");
                    } elseif ($action === 'delete_done' && $id > 0) {
                        $this->db->query("DELETE FROM `" . DB_PREFIX . "novacart_queue` WHERE queue_id=" . $id . " AND status='done'");
                    } elseif ($action === 'purge_done') {
                        $this->db->query("DELETE FROM `" . DB_PREFIX . "novacart_queue` WHERE status='done' AND date_modified < DATE_SUB(NOW(), INTERVAL 7 DAY)");
                    } else {
                        throw new InvalidArgumentException($this->language->get('error_action'));
                    }
                    $affected = $this->db->countAffected();
                    $this->log->write('Queue admin result: action=' . $action . ', id=' . $id . ', affected=' . $affected);
                    if ($affected < 1) {
                        throw new RuntimeException($this->language->get('error_no_changes'));
                    }
                    $this->session->data['queue_success'] = $this->language->get('text_success');
                    if ($action === 'retry') {
                        $url = $this->url->link('tool/queue', $base . '&filter_status=pending', true);
                    }
                } catch (Throwable $e) {
                    $this->log->write('Queue admin error: ' . $e->getMessage());
                    $this->session->data['queue_error'] = $e->getMessage();
                }
            }
            $this->response->redirect($url);
            return;
        }

        $data = array();
        foreach (array('heading_title','text_home','text_no_results','text_success','text_all','text_pending',
            'text_processing','text_done','text_failed','text_filter','text_cleanup',
            'column_id','column_code','column_route','column_status','column_attempts',
            'column_available','column_locked','column_error','column_action','button_retry',
            'button_delete','button_cleanup','text_confirm_retry','text_confirm_delete',
            'text_confirm_cleanup','text_queue_cli') as $key) {
            $data[$key] = $this->language->get($key);
        }
        $data['can_modify'] = $canModify;
        $data['action'] = $url;
        $data['filter_status'] = $status;
        $data['filter_url'] = $this->url->link('tool/queue', $base, true);
        $data['error_warning'] = isset($this->session->data['queue_error']) ? $this->session->data['queue_error'] : '';
        $data['success'] = isset($this->session->data['queue_success']) ? $this->session->data['queue_success'] : '';
        unset($this->session->data['queue_error'], $this->session->data['queue_success']);
        $data['stats'] = array('pending'=>0, 'processing'=>0, 'done'=>0, 'failed'=>0);
        $data['jobs'] = array();
        $data['pagination'] = '';
        $data['results'] = '';
        try {
            require_once(DIR_SYSTEM . 'library/novacart/queue.php');
            $queue = new NovaCartQueue($this->registry);
            $data['stats'] = $queue->stats();
            $where = $status === 'all' ? '' : " WHERE status='" . $this->db->escape($status) . "'";
            $table = "`" . DB_PREFIX . "novacart_queue`";
            $count = (int)$this->db->query("SELECT COUNT(*) AS total FROM " . $table . $where)->row['total'];
            $offset = ($page - 1) * 25;
            $data['jobs'] = $this->db->query("SELECT queue_id, code, route, status, attempts, max_attempts, available_at, locked_at, last_error FROM " . $table . $where . " ORDER BY queue_id DESC LIMIT " . $offset . ",25")->rows;
            $pagination = new Pagination();
            $pagination->total = $count;
            $pagination->page = $page;
            $pagination->limit = 25;
            $pagination->url = $this->url->link('tool/queue', $base . $filter . '&page={page}', true);
            $data['pagination'] = $pagination->render();
            $data['results'] = sprintf($this->language->get('text_pagination'), $count ? $offset + 1 : 0, min($offset + 25, $count), $count, max(1, (int)ceil($count / 25)));
        } catch (Throwable $e) {
            $data['error_warning'] = $e->getMessage();
        }
        $data['breadcrumbs'] = array(
            array('text'=>$data['text_home'], 'href'=>$this->url->link('common/dashboard', $base, true)),
            array('text'=>$data['heading_title'], 'href'=>$data['filter_url'])
        );
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $this->response->setOutput($this->load->view('tool/queue', $data));
    }
}
