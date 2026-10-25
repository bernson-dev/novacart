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

$_['text_monitor_history_hint'] = 'Click to expand or collapse';

$_['text_currency_refresh_success'] = 'Currency rates updated successfully';
$_['text_interval_quantity'] = 'Quantity';
$_['text_interval_unit'] = 'Time unit';
$_['text_interval_minutes'] = 'Minutes';
$_['text_interval_hours'] = 'Hours';
$_['text_interval_days'] = 'Days';
$_['text_interval_weeks'] = 'Weeks';
$_['text_interval_seconds'] = 'sec.';
$_['text_interval_invalid'] = 'Invalid interval (60–31536000 sec.)';
$_['text_interval_apply'] = 'Insert into field';

$_['text_interval_years'] = 'Years';
$_['text_interval_months'] = 'Months';
$_['text_interval_assumptions'] = '1 year = 365 days; 1 month = 30 days.';

$_['text_duration_year'] = 'y';
$_['text_duration_month'] = 'mo';
$_['text_duration_week'] = 'w';
$_['text_duration_day'] = 'd';
$_['text_duration_hour'] = 'h';
$_['text_duration_minute'] = 'min';
$_['text_duration_second'] = 's';
$_['text_cron_curl_help'] = 'curl flags: -sS shows errors; -i includes HTTP headers; -L follows up to 3 redirects; --max-time 120 limits the request to 120 seconds. The response body remains visible on HTTP errors.';

$_['text_cron_key_rotate'] = 'Generate new secret key';
$_['text_cron_key_confirm'] = 'Replace the HTTPS Web Cron secret? Previously copied URLs and commands will stop working.';
$_['text_cron_key_rotated'] = 'HTTPS Web Cron secret replaced. Copy the updated command.';

$_['text_schedule_mode'] = 'Schedule mode';
$_['text_schedule_interval'] = 'Interval';
$_['text_schedule_cron'] = 'Cron expression';
$_['text_cron_expression'] = 'Cron schedule (5 fields)';
$_['text_cron_preview'] = 'Preview next runs';
$_['text_cron_example'] = 'Example: 0 3 * * * — daily at 03:00 database local time.';
