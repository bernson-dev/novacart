<?php
$_['heading_title'] = 'Task Scheduler';
$_['text_home'] = 'Home'; $_['text_no_results'] = 'No scheduled tasks';
$_['text_enabled'] = 'Enabled'; $_['text_disabled'] = 'Disabled';
$_['text_success'] = 'Task status updated';
$_['text_cli'] = 'Configure a system cron with: php cli.php jobs:run. This page reports task status but does not launch the scheduler.';
$_['column_code'] = 'Code'; $_['column_route'] = 'Route'; $_['column_interval'] = 'Interval';
$_['column_status'] = 'Status'; $_['column_last'] = 'Last run';
$_['column_next'] = 'Next run'; $_['column_result'] = 'Result';
$_['column_message'] = 'Message'; $_['column_action'] = 'Action';
$_['button_enable'] = 'Enable'; $_['button_disable'] = 'Disable';
$_['error_permission'] = 'You do not have permission to modify scheduled tasks';
$_['error_action'] = 'Invalid action parameters';

$_['text_monitor'] = 'Run monitoring';
$_['text_monitor_empty'] = 'No recorded runs';
$_['text_heartbeat_ok'] = 'Automatic runner is active';
$_['text_heartbeat_late'] = 'No runs in over five minutes';
$_['text_heartbeat_missing'] = 'No runs have been recorded';
$_['column_started'] = 'Started';
$_['column_finished'] = 'Finished';
$_['column_duration'] = 'Duration';
$_['column_scheduler'] = 'Scheduler (processed/failed)';
$_['column_queue'] = 'Queue (processed/failed)';
$_['column_run_status'] = 'Result';
$_['column_run_error'] = 'Error';

$_['text_heartbeat_error'] = 'Last run finished with errors';
$_['text_heartbeat_running'] = 'Job runner is in progress';

$_['text_action_run'] = 'Run now';
$_['text_action_edit'] = 'Edit';
$_['text_action_delete'] = 'Delete';
$_['text_action_confirm'] = 'Delete task?';
$_['text_action_add'] = 'Add task';
$_['text_action_code'] = 'Task code';
$_['text_action_route'] = 'Internal route';
$_['text_action_interval'] = 'Interval, seconds';
$_['text_action_args'] = 'JSON arguments';
$_['text_action_cancel'] = 'Cancel';
$_['text_action_save'] = 'Save';

$_['text_queue_panel'] = 'Background Queue';
$_['text_queue_manage'] = 'Manage Queue';
$_['text_queue_pending'] = 'Pending';
$_['text_queue_processing'] = 'Processing';
$_['text_queue_done'] = 'Done';
$_['text_queue_failed'] = 'Failed';
$_['text_queue_search'] = 'Search code or route';
$_['text_queue_filter'] = 'Filter';
$_['text_queue_attempts'] = 'Attempts';
$_['text_queue_available'] = 'Available at';
$_['text_queue_error'] = 'Last error';

$_['text_cron_commands'] = 'CRON commands';
$_['text_cron_help'] = 'CLI is recommended for automatic runs. The HTTPS command contains a secret key; do not share it.';
$_['text_cron_cli'] = 'CLI command';
$_['text_cron_web'] = 'HTTPS Web Cron command';
$_['text_cron_copy'] = 'Copy';

$_['text_queue_run'] = 'Run Queue Now';
$_['text_queue_retry'] = 'Retry';
$_['text_queue_confirm_delete'] = 'Delete queued job?';

$_['text_system_task'] = 'System';

$_['text_queue_all'] = 'All statuses';


$_['text_queue_run_result'] = 'Queue: processed %d, failed %d';
$_['error_queue_failed'] = 'Queue finished with failures: processed %d, failed %d';

$_['column_source'] = 'Source';
$_['text_source_cli'] = 'CLI (manual/unmarked)';
$_['text_source_scheduled'] = 'CLI (scheduled)';
$_['text_source_http'] = 'HTTPS Web Cron';
$_['text_source_unknown'] = 'Unknown';

$_['text_queue_locked'] = 'Locked at';
$_['text_queue_cleanup'] = 'Clear completed older than 7 days';
$_['text_queue_cleanup_confirm'] = 'Delete completed jobs older than 7 days?';

$_['text_monitor_history'] = 'Run history';
