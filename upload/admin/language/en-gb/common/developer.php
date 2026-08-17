<?php

// Heading
$_['heading_title']                    = 'Developer Settings';

// Text
$_['text_success']                     = 'Success: Developer settings have been modified!';
$_['text_theme']                       = 'Twig templates';
$_['text_sass']                        = 'SASS';
$_['text_systemcache']                 = 'system';
$_['text_imgcache']                    = 'image resize';
$_['text_allcache']                    = 'all components';
$_['text_type']                        = 'Type:';
$_['text_cache_section']               = 'Cache and development mode';
$_['text_environment_section']         = 'Environment and PHP';
$_['text_cache_cleared']               = 'The %s cache has been cleared: %d files removed, %s freed.';
$_['text_cache_partial']               = 'Cache was cleared partially: %d files removed, %s freed, %d delete errors.';
$_['text_stat_error']                  = 'Error';
$_['text_enabled']                     = 'Enabled';
$_['text_disabled']                    = 'Disabled';

// Column
$_['column_component']                 = 'Component';
$_['column_action']                    = 'Action';
$_['column_information']               = 'Parameter';
$_['column_recommended']               = 'Recommended';
$_['column_value']                     = 'Value';
$_['column_size']                      = 'Size';
$_['column_files']                     = 'Files';

// Entry
$_['entry_theme']                      = 'Twig template cache';
$_['entry_sass']                       = 'SASS';
$_['entry_cache']                      = 'Mode';
$_['entry_systemcache']                = 'System cache';
$_['entry_imgcache']                   = 'Image resizes';
$_['entry_allcache']                   = 'Clear all, including image resizes';
$_['entry_php_version']                = 'PHP version';
$_['entry_ioncube_version']            = 'ionCube Loader version';
$_['entry_twig_version']               = 'Twig version';
$_['entry_opcache']                    = 'OPcache';

// Help
$_['help_imgcache']                    = 'Only generated OpenCart resized copies in image/cache/ are removed. Original images are not affected.';

// Confirm
$_['confirm_imgcache']                 = 'Delete all resized images? They will be regenerated on subsequent requests.';
$_['confirm_allcache']                 = 'Clear all cache types and delete resized images?';

// Button
$_['button_on']                        = 'On';
$_['button_off']                       = 'Off';

// Error
$_['error_permission']                 = 'Warning: You do not have permission to modify developer settings!';
$_['error_not_found_ic']               = 'Not installed';
$_['error_not_found']                  = 'Not detected';

// PHP info
$_['php_info_max_input_vars']          = 'Recommended value is %s or higher. Smaller values may truncate data when large forms are saved.';
$_['php_info_session.gc_maxlifetime']  = 'Recommended value is %s seconds or higher. This controls when session data may be considered stale and removed by garbage collection.';
$_['php_info_session.cookie_lifetime'] = 'Recommended value is %s. A value of 0 keeps the session cookie until the browser is closed.';
$_['php_info_memory_limit']            = 'Maximum amount of memory one PHP process may use.';
$_['php_info_max_execution_time']      = 'Maximum PHP script execution time in seconds.';
$_['php_info_upload_max_filesize']     = 'Maximum size of a single uploaded file.';
$_['php_info_post_max_size']           = 'Maximum POST request size. For uploads it should normally be at least upload_max_filesize.';
