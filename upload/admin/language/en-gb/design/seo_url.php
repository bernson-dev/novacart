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
$_['text_information']     = 'Information<p><strong>SEO URL (keyword)</strong> is a unique identifier for URLs. It must be unique for each object (product, category, manufacturer) within the same store and language. Use only lowercase Latin letters, numbers, and hyphens (-). For example: <code>my-product-1</code>.</p><p>From an SEO perspective, it\'s better to use unique <strong>keywords</strong> for each language to improve the site\'s ranking and visibility.</p><p>Using the same <strong>keyword</strong> for different languages is not recommended, as it can create duplicate content for search engines, especially if they index both language versions.</p>';

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
$_['text_audit_summary']       = 'total rows: %d; rows with issues: %d; conflicting keyword groups: %d; duplicate query groups: %d; language-prefix conflicts: %d.';
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
