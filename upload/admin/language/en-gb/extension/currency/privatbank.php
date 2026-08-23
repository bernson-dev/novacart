<?php

// Heading
$_['heading_title']                = 'PrivatBank currency rate engine settings';

// Text
$_['text_extension']               = 'Extensions';
$_['text_success']                 = 'PrivatBank rate engine settings saved';
$_['text_edit']                    = 'Edit PrivatBank rate engine';
$_['text_information']             = 'This extension requires the UAH currency to be an available currency. When enabled, it can be chosen as the currency rate engine at <b><a href="%2">System &gt; Settings</b></a> and you can refresh the currency rates via <b><a href="%1">System &gt; Localisation &gt; Currencies</a></b>.';
$_['text_cash']                    = 'Cash rate (in branches)'; // Transfer for cash
$_['text_non_cash']                = 'Non-cash rate (conversion to card, Privat24)'; // Transfer for non_cash

$_['text_currency_table']          = 'Current exchange rates';
$_['column_currency']              = 'Currency';
$_['column_original_rate']         = 'Rate (without correction)';
$_['column_adjusted_rate']         = 'Rate (with correction)';
$_['column_inverse_rate']          = 'Inverse rate';
$_['column_date_modified']         = 'Date of update';
$_['text_no_currencies']           = 'No exchange rates found. Check your API settings.';

// Entry
$_['entry_cron']                   = 'Cron command';
$_['entry_status']                 = 'Status';
$_['entry_api_choice']             = 'API choice';
$_['entry_rate_coefficient']       = 'Correction coefficient';

// Help
$_['help_cron']                    = 'Use this command when setting up a CRON task on your server.';

// Error
$_['error_permission']             = 'Warning: You do not have permission to modify National Bank of Ukraine!';
$_['error_uah']                    = 'This extension requires the hryvnia as an available currency!';
$_['error_rate_coefficient']       = 'The correction coefficient must be 1 or greater.';

$_['error_no_rates']               = 'Rates were not received from the source.';
$_['error_base_currency_missing']  = 'Rates were not updated. The store\'s base currency is missing from the source.';
$_['error_no_matching_currencies'] = 'Rates were not updated. There are no matching store currencies in the source data.';

$_['text_success_refresh']         = 'The exchange rates in the source have been updated!';
