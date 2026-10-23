<?php
/**
 * Harmless scheduler integration probe.
 * It never writes to the catalog, its database, or external services.
 */
class ControllerCronTest extends Controller {
    public function index($args = array()) {
        if (!defined('NOVACART_CRON_ENTRY')) {
            $this->response->setStatusCode(403);
            return array('success' => false, 'message' => 'Forbidden');
        }

        return array(
            'success' => true,
            'message' => 'NovaCart cron integration test OK'
        );
    }
}
