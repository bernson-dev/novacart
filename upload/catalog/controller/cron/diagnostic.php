<?php
/**
 * Read-only scheduler diagnostic. No catalog/database writes or external calls.
 * Reachable only through the authenticated cron entry point.
 */
class ControllerCronDiagnostic extends Controller {
    public function index($args = array()) {
        if (!defined('NOVACART_CRON_ENTRY')) {
            return array('success' => false, 'message' => 'Cron entry required');
        }

        if (is_array($args) && !empty($args['probe_fail'])) {
            return array('success' => false, 'message' => 'Queue diagnostic expected failure');
        }

        return array(
            'success' => true,
            'message' => 'Scheduler diagnostic OK'
        );
    }
}
