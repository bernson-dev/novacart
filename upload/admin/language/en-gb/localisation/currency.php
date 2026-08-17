<?php
// Heading
$_['heading_title']                   = 'Currencies';

// Text
$_['text_success']                    = 'Success: You have modified currencies!';
$_['text_list']                       = 'Currency List';
$_['text_add']                        = 'Add Currency';
$_['text_edit']                       = 'Edit Currency';
$_['text_iso']                        = 'You can find a full list of ISO currency codes and settings <a href="http://www.xe.com/iso4217.php" target="_blank" class="alert-link">here</a>.';
$_['text_currency_engine']            = 'Source of currency updates:';
$_['text_unknown_engine']             = 'The exchange rate source is not defined <b><a href="%1">System &gt; Settings</b></a>';
$_['text_provider_rates']             = 'Current rates';
$_['text_success_refresh']            = 'The exchange rates in the source have been updated!';
$_['text_currency_engine_extensions'] = '<i class="fa fa-exclamation-triangle"></i> Currency update engines are not installed. Configure';
$_['placeholder_reverse_rate']        = 'Enter the reverse rate';

$_['text_edit_module_settings']       = 'Edit module settings';
$_['text_edit_currency_settings']     = 'Change currency update source';

// Column
$_['column_title']                    = 'Currency Title';
$_['column_code']                     = 'Code';
$_['column_value']                    = 'Value';
$_['column_reverse_value']            = 'Reverse course';
$_['column_status']                   = 'Status';
$_['column_date_modified']            = 'Last Updated';
$_['column_action']                   = 'Action';
$_['column_correction_rate']          = 'Correction rate';
$_['column_provider_rate']            = 'Provider Rate';
$_['column_correction_percent']       = 'Deviation';
$_['column_provider_title']           = 'Title';
$_['column_rate']                     = 'Rate';

// Entry
$_['entry_title']                     = 'Currency Title';
$_['entry_code']                      = 'Code';
$_['entry_value']                     = 'Value';
$_['entry_symbol_left']               = 'Symbol Left';
$_['entry_symbol_right']              = 'Symbol Right';
$_['entry_decimal_place']             = 'Decimal Places';
$_['entry_status']                    = 'Status';
$_['entry_reverse_rate']              = 'Reverse rate';

$_['entry_correction_rate']           = 'Correction rate';

// Help
$_['help_code']                       = 'Do not change if this is your default currency.';
$_['help_value']                      = 'Set to 1.00000 if this is your default currency.';
$_['help_correction_rate']            = 'Multiplier for rate correction. 1.00000000 - no change, 1.02000000 - increase by 2%, 0.98000000 - decrease by 2%.';

// Error
$_['error_permission']                = 'Warning: You do not have permission to modify currencies!';
$_['error_title']                     = 'Currency Title must be between 3 and 32 characters!';
$_['error_code']                      = 'Currency Code must contain 3 characters!';
$_['error_default']                   = 'Warning: This currency cannot be deleted as it is currently assigned as the default store currency!';
$_['error_store']                     = 'Warning: This currency cannot be deleted as it is currently assigned to %s stores!';
$_['error_order']                     = 'Warning: This currency cannot be deleted as it is currently assigned to %s orders!';
$_['error_currency_engine']           = 'Warning: No active Currency Rate Engine found!';
$_['error_inconsistent_rates']        = 'Forward and reverse rates do not match. Forward rate was recalculated based on reverse rate.';
$_['error_correction_rate']           = 'The correction rate must be a number greater than 0!';
$_['error_no_rates']                  = 'Rates were not received from the source.';
$_['error_base_currency_missing']     = 'Rates were not updated. The store\'s base currency is missing from the source.';
$_['error_no_matching_currencies']    = 'Rates were not updated. There are no matching store currencies in the source data.';
$_['warning_update_needed']           = 'The exchange rate for one or more currencies has changed by more than %.2f%%. The currencies may need to be updated!';
