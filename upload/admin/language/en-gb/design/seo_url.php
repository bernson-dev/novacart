<?php
// Heading
$_['heading_title']        = 'SEO URL';

// Text
$_['text_success']         = 'Success: You have modified SEO URL!';
$_['text_list']            = 'SEO URL List';
$_['text_add']             = 'Add SEO URL';
$_['text_edit']            = 'Edit SEO URL';
$_['text_filter']          = 'Filter';
$_['text_default']         = 'Default';
$_['text_information']     = 'Information<p>With language prefixes enabled, the public <strong>SEO URL</strong> is taken from the catalog default language and reused for every language; only the language prefix changes. Other language SEO URLs remain stored as fallbacks and become active again if prefixes are disabled.</p><p>Keywords remain unique within a store. Use Latin letters, numbers, hyphens (-) and underscores (_). Language prefixes are reserved.</p>';

// Column
$_['column_query']         = 'Query';
$_['column_keyword']       = 'Keyword';
$_['column_store']         = 'Store';
$_['column_language']      = 'Language';
$_['column_action']        = 'Action';

// Entry
$_['entry_query']          = 'Query';
$_['entry_keyword']        = 'Keyword';
$_['entry_store']          = 'Store';
$_['entry_language']       = 'Language';

// Help
$_['help_keyword']         = 'Make sure you only use characters in the a-z or 0-9 use and - or _ for spaces. Leave blank if you just want to remove the parameter.';
$_['help_query']           = 'The URL query to replace.';

// Error
$_['error_permission']     = 'Warning: You do not have permission to modify SEO URL!';
$_['error_keyword']        = 'Keyword must be between 3 and 64 characters!';
$_['error_keyword_exists'] = 'Keyword already in use!';
$_['error_query']          = 'Query must be between 3 and 255 characters!';
$_['error_query_exists']   = 'Query already in use!';

$_['text_all']                 = 'All';
$_['text_audit']               = 'SEO URL audit:';
$_['text_audit_stats']          = 'Overall statistics';
$_['text_stat_total']           = 'Total rows';
$_['text_stat_issues']          = 'Rows with issues';
$_['text_stat_keyword_groups']  = 'SEO URL conflict groups';
$_['text_stat_query_groups']    = 'Duplicate query groups';
$_['text_issue_all']           = 'All issues';
$_['text_issue_keyword']       = 'SEO URL conflict';
$_['text_issue_query']         = 'Duplicate query';
$_['text_issue_prefix']        = 'Language prefix conflict';
$_['text_issue_keyword_short'] = 'SEO URL';
$_['text_issue_query_short']   = 'Query';
$_['text_issue_prefix_short']  = 'Prefix';
$_['column_issue']             = 'Issue';
$_['entry_issue']              = 'Issue';
$_['help_issue_keyword']       = 'The same keyword is already used in this store. Language fallback SEO URLs must also remain unique so prefix mode can be disabled safely.';
$_['help_issue_query']         = 'The same query is duplicated within one store and language.';
$_['help_issue_prefix']        = 'The keyword collides with a reserved language prefix.';

$_['text_issue_shared_language']       = 'Same across languages';
$_['text_issue_orphan']                = 'Orphaned SEO URL';
$_['text_issue_shared_language_short'] = 'Across languages';
$_['text_issue_orphan_short']          = 'Orphaned';
$_['help_issue_shared_language']       = 'The same keyword appears in different languages of one store. These rows need review because language fallback SEO URLs must remain unique.';
$_['help_issue_orphan']                = 'The SEO URL points to an entity that no longer exists in the database.';

$_['help_issue_all']          = 'All detected SEO URL issues that can affect routing or safe switching of the language URL mode.';
$_['button_generate']         = 'Generate from name';
$_['button_inline_save']      = 'Save SEO URL';
$_['text_inline_saved']       = 'SEO URL saved';
$_['text_saving']             = 'Saving...';
$_['text_open_source']        = 'Open source entity';
$_['text_open_storefront'] = 'Open storefront';
$_['error_request_method']    = 'Invalid request method.';
$_['error_not_found']         = 'SEO URL record was not found.';
$_['error_ajax']              = 'Could not save the SEO URL. Check the error log and try again.';

$_['entry_search']            = 'Quick search';
$_['help_search']             = 'Search by any fragment of an SEO URL, query, or related product, category, manufacturer, page, or article name.';
$_['text_group_records']       = 'records in group';

$_['text_history'] = 'SEO URL history';
$_['text_history_migration'] = 'History table is missing. Apply docs/migrations/seo-url-history.sql with your database prefix.';
$_['text_history_help'] = 'History stores previous keywords, not complete URLs. Active keywords take precedence. A current keyword does not guarantee page availability.';
$_['text_history_search'] = 'Old keyword or entity identifier';
$_['column_history_old'] = 'Old keyword';
$_['column_history_current'] = 'Current keyword';
$_['column_history_date'] = 'Date changed';
$_['text_history_shadowed'] = 'In use';
$_['text_history_missing'] = 'No current keyword';

$_['text_redirects'] = 'Redirects';
$_['text_redirect_migration'] = 'Table missing. Apply docs/migrations/seo-redirect.sql.';
$_['text_redirect_help'] = 'Enter internal paths starting with /, including the language prefix and store subdirectory. No domain or query string. A 410 needs no target. Active SEO URLs take priority.';
$_['entry_redirect_source'] = 'Source path';
$_['entry_redirect_target'] = 'Target path';
$_['entry_redirect_status'] = 'Status';
$_['error_redirect_scope'] = 'Invalid store or language.';
$_['error_redirect_rule'] = 'Invalid rule.';
$_['error_redirect_path'] = 'Invalid path. Only internal store paths are allowed.';
$_['error_redirect_conflict'] = 'Source is an active SEO URL or matches the target.';
$_['error_redirect_duplicate'] = 'This rule already exists.';
$_['error_redirect_chain'] = 'This rule creates a redirect chain or loop.';

$_['text_not_found_log'] = '404 log';
$_['text_not_found_migration'] = 'Log table missing. Apply docs/migrations/seo-not-found.sql.';
$_['text_not_found_help'] = 'The log aggregates missing SEO paths by store, language and path. Query parameters are not stored. Create a redirect from a log entry to fix the error.';
$_['column_not_found_hits'] = 'Hits';
$_['column_not_found_first'] = 'First seen';
$_['column_not_found_last'] = 'Last seen';
$_['button_not_found_redirect'] = 'Create redirect';
$_['button_not_found_prune'] = 'Delete older than %d days';
$_['text_not_found_prune_confirm'] = 'Delete entries not seen for over %d days?';
$_['entry_not_found_status'] = '404 logging';
$_['entry_not_found_retention'] = 'Retention (days)';
$_['text_not_found_settings_help'] = 'Global settings for all stores. The scheduler cleans the journal daily even when logging is disabled. Run php cli.php cron:install to register the task.';
$_['error_not_found_settings'] = 'Enter a retention period from 1 to 3650 days and a valid status.';
$_['text_missing_keywords'] = 'Missing SEO URLs';
$_['text_missing_help'] = 'Find entities without SEO URLs and preview suggestions. Disabled entities are included.';
$_['text_missing_shared'] = 'Shared keyword mode: only the store default language is checked.';
$_['text_missing_type'] = 'Entity type';
$_['text_missing_total'] = 'Total missing';
$_['text_missing_preview'] = 'Preview';
$_['text_missing_name'] = 'Name';
$_['text_missing_no_keyword'] = 'No keyword could be generated';
$_['text_missing_no_languages'] = 'No eligible language to check.';
$_['text_missing_product'] = 'Products';
$_['text_missing_category'] = 'Categories';
$_['text_missing_manufacturer'] = 'Manufacturers';
$_['text_missing_information'] = 'Information pages';
$_['text_missing_article'] = 'Blog articles';
$_['text_missing_blog_category'] = 'Blog categories';
$_['text_missing_created'] = 'Created';
$_['text_missing_skipped'] = 'Skipped';
$_['text_missing_errors'] = 'Errors';
$_['text_missing_confirm'] = 'Create SEO URLs for selected entities?';
$_['button_missing_generate'] = 'Create selected SEO URLs';
$_['error_missing_selection'] = 'Select 1 to 50 entities in a valid store and language.';
$_['button_missing_all'] = 'Create all missing SEO URLs';
$_['text_missing_all_confirm'] = 'Create all missing SEO URLs for the selected store, all eligible languages and entity types?';
$_['button_missing_stop'] = 'Stop';
$_['button_missing_refresh'] = 'Refresh results';
$_['error_missing_batch'] = 'Batch generation interrupted. Refresh results and retry.';
$_['text_diagnostics'] = 'SEO diagnostics';
$_['text_diag_readonly'] = 'Diagnostics changes nothing until selected fixes are explicitly confirmed.';
$_['text_diag_urls'] = 'SEO URL and redirect issues';
$_['text_diag_global'] = 'SEO URL and redirect counts across all stores and languages.';
$_['text_diag_empty'] = 'Empty SEO URLs';
$_['text_diag_invalid'] = 'Invalid SEO URL characters';
$_['text_diag_redirect'] = 'Manual redirect chains and loops';
$_['text_diag_unavailable'] = 'Table unavailable';
$_['text_diag_meta'] = 'Metadata';
$_['text_diag_meta_help'] = 'Active entities in the selected store and language. Duplicate titles are checked separately per entity type.';
$_['text_diag_missing_title'] = 'Missing Meta Title';
$_['text_diag_missing_description'] = 'Missing Meta Description';
$_['text_diag_duplicate_title'] = 'Duplicate Meta Title';
$_['text_diag_source'] = 'Source path';
$_['text_diag_target'] = 'Target path';
$_['text_diag_updated'] = 'Updated';
$_['text_diag_skipped'] = 'Skipped';
$_['text_diag_errors'] = 'Errors';
$_['text_diag_shared_meta'] = 'Review suggested values and select records. Metadata for an entity and language is shared across its stores.';
$_['text_diag_orphan_help'] = 'Select orphan URLs to delete. Entity existence will be checked again.';
$_['text_diag_select_page'] = 'Select current page';
$_['text_diag_proposal'] = 'Suggested value';
$_['text_diag_manual'] = 'Manual review required';
$_['text_diag_confirm'] = 'Apply changes to selected records?';
$_['text_diag_apply'] = 'Apply selected';
$_['text_csv_title'] = 'Redirect CSV';
$_['text_csv_help'] = 'UTF-8, semicolon or comma separator. Up to 1000 rows and 2 MB. Import only adds rules; existing rules are unchanged. Conflicts between file rows are rechecked on save.';
$_['text_csv_export'] = 'Export CSV';
$_['text_csv_preview'] = 'Preview';
$_['text_csv_created'] = 'Created';
$_['text_csv_ready'] = 'Ready for validation';
$_['text_csv_result'] = 'Result';
$_['text_csv_confirm'] = 'Import eligible rows?';
$_['text_csv_apply'] = 'Import';
$_['error_csv_format'] = 'Invalid CSV or processing error';
$_['error_csv_header'] = 'Invalid CSV header';
$_['error_csv_limit'] = 'CSV limit exceeded: 1000 rows or 2 MB';
$_['error_csv_empty'] = 'CSV contains no records';
$_['error_csv_expired'] = 'Preview expired';
