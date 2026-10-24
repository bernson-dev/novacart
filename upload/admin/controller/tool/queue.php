<?php
class ControllerToolQueue extends Controller {
    public function index() {
        if (!$this->user->hasPermission('access', 'tool/cron')) {
            $this->response->redirect($this->url->link('error/permission', 'user_token=' . $this->session->data['user_token'], true));
            return;
        }
        $params = 'user_token=' . $this->session->data['user_token'];
        if (isset($this->request->get['filter_status'])) {
            $status = (string)$this->request->get['filter_status'];
            if (in_array($status, array('pending','processing','done','failed'), true)) {
                $params .= '&queue_status=' . $status;
            }
        }
        $this->response->redirect($this->url->link('tool/cron', $params, true) . '#queue-panel');
    }
}
