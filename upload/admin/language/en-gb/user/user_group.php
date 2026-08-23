<?php
// Heading
$_['heading_title']                       = 'User Groups';

// Text
$_['text_success']                        = 'Settings successfully changed!';
$_['text_list']                           = 'User Group List';
$_['text_add']                            = 'Add';
$_['text_edit']                           = 'Edit';
$_['text_admin_permissions_restored']     = 'Critical permissions for the Administrator group were automatically restored.';
$_['text_permission_search_placeholder']  = 'Quick action search, for example: catalog/product';
$_['text_permission_full_access']         = 'Full access';
$_['text_permission_readonly']            = 'View only';
$_['text_permission_no_rights']           = 'No rights';
$_['text_permission_empty']               = 'Nothing found.';
$_['text_permission_critical_missing']    = 'Critical rights removed: %s';
$_['text_permission_filter_all']          = 'All';
$_['text_permission_filter_no_access']    = 'No viewing';
$_['text_permission_filter_no_modify']    = 'No editing';
$_['text_permission_filter_hidden']       = 'Hidden';
$_['text_permission_filter_inconsistent'] = 'Conflicting';

// Entry
$_['entry_name']                          = 'User group name';
$_['entry_access']                        = 'View';
$_['entry_modify']                        = 'Edit';
$_['entry_hide']                          = 'Hide';
$_['entry_permission']                    = 'Permissions';

// Column
$_['column_name']                         = 'User Group Name';
$_['column_action']                       = 'Action';
$_['column_permission_route']             = 'Action / route';

// Button
$_['button_permission_search_clear']      = 'Clear search';
$_['button_permission_reset_filters']     = 'Reset filters';

// Help
$_['help_access']                         = 'Allows viewing and using the add-on, but not modification.';
$_['help_modify']                         = 'Grants permission to change settings or uninstall the add-on.';
$_['help_no_rights']                      = 'Viewing and editing prohibited';
$_['help_hide']                           = 'The selected add-ons will not be displayed on the modules, payments, and delivery pages.';
$_['help_permission_modify_access_logic'] = 'Permission synchronization (editing is not possible without viewing): if you allow editing, viewing will be enabled automatically. If you deny viewing, editing for this action will be disabled.';

// Error
$_['error_permission']                    = 'You do not have permission to make changes!';
$_['error_name']                          = 'The name must be between 3 and 64 characters long!';
$_['error_user']                          = 'This user group cannot be deleted because it contains %s users!';

// Foolproof protection
$_['error_admin_empty_permissions']       = 'You cannot remove all permissions from the Administrator group - access to the admin panel will be lost.';
$_['error_admin_required_access']         = 'The Administrator group must have access to: %s';
$_['error_admin_required_modify']         = 'The Administrator group must have modify access to: %s';
