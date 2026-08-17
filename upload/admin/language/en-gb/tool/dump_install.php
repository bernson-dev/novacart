<?php
// admin/language/en-gb/tool/dump_install.php

// Heading
$_['heading_title']                        = 'DB Dump for Installation';

// Text
$_['text_list']                            = 'Export SQL Dump';

$_['text_help']                            = 'The tool generates an SQL dump for store installation. The table structure is always exported, but data is only exported for the tables that are marked.';

$_['text_quick_help_title']                = 'What will be included in the dump';
$_['text_quick_help_structure']            = 'The CREATE TABLE table structure is exported for all tables.';
$_['text_quick_help_data']                 = 'INSERT data is only exported for the tables that are marked.';
$_['text_quick_help_safe']                 = 'By default, live and service data are excluded: orders, customers, users, sessions, downloads, and temporary tables.';

$_['text_installer_mode']                  = 'Safe export mode for the installer';
$_['text_installer_mode_help']             = 'Reduces the size of INSERT blocks to improve dump import stability via the installer and on hosting with restrictions.';
$_['text_installer_auto_hint']             = 'Mode enabled automatically';
$_['text_installer_auto_reason']           = 'Hosting restrictions were detected that may prevent the export or import of a large dump.';
$_['text_installer_mode_notice']           = 'You can uncheck this box manually, but on slow hosting, it\'s safer to leave this mode enabled.';

$_['text_table_filter_help']               = 'The filter only changes the list display. Tables that are already selected but hidden by the filter are not reset.';

$_['text_visible_tables']                  = 'Showed';
$_['text_selected_tables']                 = 'Selected with data';
$_['text_default_excluded_tables']         = 'Excluded by default';

$_['text_data_include_notice']             = 'Check the selected tables before exporting. The structure will be included in the dump in any case.';

$_['text_hosting_limit_details_title']     = 'Details of detected restrictions';
$_['text_hosting_limit_memory']            = 'memory_limit: %s. For large dumps, 256M or higher is recommended.';
$_['text_hosting_limit_execution_time']    = 'max_execution_time: %s sec. For large databases, 120 sec or higher is recommended.';
$_['text_hosting_limit_packet']            = 'mysqli.max_allowed_packet: %s. For large INSERT queries, 8M or higher is recommended.';
$_['text_hosting_limit_set_time_missing']  = 'The set_time_limit() function is unavailable. The script will not be able to increase the execution time automatically.';
$_['text_hosting_limit_set_time_disabled'] = 'The set_time_limit() function is disabled in disable_functions. The script will not be able to increase execution time on its own.';

$_['text_export_processing']               = 'Generating dump...';

$_['text_default_store']                   = 'Primary store';
$_['text_comment_store_none']              = 'Do not add store name';

// Entry
$_['entry_comment']                        = 'Dump comment';
$_['entry_comment_help']                   = 'The comment will be added to the beginning of the SQL file. Use it to mark the version, date, or purpose of the dump.';

$_['entry_installer_mode']                 = 'Export mode';

$_['entry_include_drop']                   = 'Add DROP TABLE IF EXISTS';
$_['entry_include_drop_help']              = 'A DROP TABLE IF EXISTS command will be added before CREATE TABLE. This is useful for a clean installation or a complete structure update.';

$_['entry_table_filter']                   = 'Quick table search';
$_['entry_table_filter_placeholder']       = 'For example: product, setting, order';

$_['entry_data_include']                   = 'Tables for data export';
$_['entry_data_include_help']              = 'Checked tables will be exported with both structure and data. Unchecked tables will be dumped with only the structure.';

$_['entry_comment_store']                  = 'Store in the comment';
$_['entry_comment_store_help']             = 'If you select a store, its name will be added to the SQL file comment. Leave blank to omit the store name.';

// Button
$_['button_export']                        = 'Download dump';
$_['button_sel_all']                       = 'Select visible';
$_['button_sel_none']                      = 'Unselect visible';
$_['button_default']                       = 'Reset to default selection';
$_['button_clear_filter']                  = 'Clear';

// Error
$_['error_permission']                     = 'You do not have permission to access this section!';
