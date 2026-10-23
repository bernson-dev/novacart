<?php
/** Temporary queue integration probe. Remove before merge. */
class ControllerCronQueueProbe extends Controller {
    public function index($args = array()) {
        if (!defined('NOVACART_CRON_ENTRY') || PHP_SAPI !== 'cli') {
            throw new RuntimeException('Probe is CLI-only');
        }
        $mode = isset($args['mode']) ? $args['mode'] : 'success';
        if ($mode === 'failure') {
            throw new RuntimeException('Expected queue probe failure');
        }
        if ($mode === 'slow') {
            sleep(8);
        }
        if ($mode !== 'success' && $mode !== 'slow') {
            throw new InvalidArgumentException('Unknown probe mode');
        }
        return array('success' => true, 'message' => 'Queue probe OK');
    }
}
