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
$_['text_information']     = 'Information<p><strong>SEO URL (keyword)</strong> must be unique within the URL namespace where an actual routing conflict can occur. With multilingual SEO URLs enabled, the same <strong>keyword</strong> in different languages is valid because language is resolved by a separate prefix. Duplicates within the same store and language remain invalid.</p><p>Use Latin letters, numbers, hyphens (-) and underscores (_). Language prefixes are reserved and must not be used as normal SEO URLs.</p>';

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
$_['text_audit_summary']       = 'total rows: %d; rows with issues: %d; conflicting keyword groups: %d; duplicate query groups: %d; language-prefix conflicts: %d; cross-language matches: %d; orphaned rows: %d.';
$_['text_issue_all']           = 'All issues';
$_['text_issue_keyword']       = 'SEO URL conflict';
$_['text_issue_query']         = 'Duplicate query';
$_['text_issue_prefix']        = 'Language prefix conflict';
$_['text_issue_keyword_short'] = 'SEO URL';
$_['text_issue_query_short']   = 'Query';
$_['text_issue_prefix_short']  = 'Prefix';
$_['column_issue']             = 'Issue';
$_['entry_issue']              = 'Issue';
$_['help_issue_keyword']       = 'The same keyword conflicts in the store\'s current routing mode. With multilingual SEO URLs, identical keywords in different languages are valid.';
$_['help_issue_query']         = 'The same query is duplicated within one store and language.';
$_['help_issue_prefix']        = 'The keyword collides with a reserved language prefix.';

$_['text_issue_shared_language']       = 'Same across languages';
$_['text_issue_orphan']                = 'Orphaned SEO URL';
$_['text_issue_shared_language_short'] = 'Across languages';
$_['text_issue_orphan_short']          = 'Orphaned';
$_['help_issue_shared_language']       = 'The same keyword is used in different languages of one store. This is valid with multilingual SEO URLs enabled, but becomes a conflict if language prefixes are disabled.';
$_['help_issue_orphan']                = 'The SEO URL points to an entity that no longer exists in the database.';

$_['help_issue_all']          = 'Current conflicts that can already affect routing. Cross-language matches are excluded while language prefixes are enabled.';
$_['button_generate']         = 'Generate from name';
$_['button_inline_save']      = 'Save SEO URL';
$_['text_inline_saved']       = 'SEO URL saved';
$_['text_saving']             = 'Saving...';
$_['text_open_source']        = 'Open source entity';
$_['error_request_method']    = 'Invalid request method.';
$_['error_not_found']         = 'SEO URL record was not found.';
$_['error_ajax']              = 'Could not save the SEO URL. Check the error log and try again.';

$_['entry_search']            = 'Quick search';
$_['help_search']             = 'Search by any fragment of an SEO URL, query, or related product, category, manufacturer, page, or article name.';
$_['text_group_records']       = 'records in group';
