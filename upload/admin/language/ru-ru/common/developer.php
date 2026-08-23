<?php
// * @source See SOURCE.txt for source and other copyright.
// * @license GNU General Public License version 3; see LICENSE.txt

// Heading
$_['heading_title']                    = 'Настройки разработчика';

// Text
$_['text_success']                     = 'Настройки успешно изменены!';
$_['text_theme']                       = 'шаблонов Twig';
$_['text_sass']                        = 'SASS';
$_['text_systemcache']                 = 'системный';
$_['text_imgcache']                    = 'ресайзов изображений';
$_['text_allcache']                    = 'всех компонентов';
$_['text_type']                        = 'Тип:';
$_['text_cache_section']               = 'Кэш и режим разработки';
$_['text_environment_section']         = 'Среда и PHP';
$_['text_cache_cleared']               = 'Кэш %s очищен: удалено файлов — %d, освобождено — %s.';
$_['text_cache_partial']               = 'Очистка выполнена частично: удалено файлов — %d, освобождено — %s, ошибок удаления — %d.';
$_['text_stat_error']                  = 'Ошибка';
$_['text_enabled']                     = 'Включён';
$_['text_disabled']                    = 'Выключен';

// Column
$_['column_component']                 = 'Компонент';
$_['column_action']                    = 'Действие';
$_['column_information']               = 'Параметр';
$_['column_recommended']               = 'Рекомендуем';
$_['column_value']                     = 'Значение';
$_['column_size']                      = 'Размер';
$_['column_files']                     = 'Файлов';

// Entry
$_['entry_theme']                      = 'Кэш шаблонов Twig';
$_['entry_sass']                       = 'SASS';
$_['entry_cache']                      = 'Режим';
$_['entry_systemcache']                = 'Системный кэш';
$_['entry_imgcache']                   = 'Ресайзы изображений';
$_['entry_allcache']                   = 'Очистить всё, включая ресайзы изображений';
$_['entry_php_version']                = 'Версия PHP';
$_['entry_ioncube_version']            = 'Версия ionCube Loader';
$_['entry_twig_version']               = 'Версия Twig';
$_['entry_opcache']                    = 'OPcache';

// Help
$_['help_imgcache']                    = 'Удаляются только созданные OpenCart уменьшенные копии из image/cache/. Оригинальные изображения не затрагиваются.';

// Confirm
$_['confirm_imgcache']                 = 'Удалить все ресайзы изображений? Они будут созданы повторно при следующих запросах.';
$_['confirm_allcache']                 = 'Очистить все типы кэша и удалить ресайзы изображений?';

// Button
$_['button_on']                        = 'Вкл';
$_['button_off']                       = 'Выкл';

// Error
$_['error_permission']                 = 'У вас недостаточно прав для внесения изменений!';
$_['error_not_found_ic']               = 'Не установлено';
$_['error_not_found']                  = 'Не определено';

// PHP info
$_['php_info_max_input_vars']          = 'Рекомендуется значение %s и больше. При меньшем значении при сохранении больших форм часть данных может быть потеряна.';
$_['php_info_session.gc_maxlifetime']  = 'Рекомендуется значение %s секунд и больше. Параметр определяет, через какое время данные сессии могут считаться устаревшими и удаляться сборщиком мусора.';
$_['php_info_session.cookie_lifetime'] = 'Рекомендуется значение %s. Значение 0 означает, что cookie сессии действует до закрытия браузера.';
$_['php_info_memory_limit']            = 'Максимальный объём памяти, который разрешено использовать одному PHP-процессу.';
$_['php_info_max_execution_time']      = 'Максимальное время выполнения PHP-скрипта в секундах.';
$_['php_info_upload_max_filesize']     = 'Максимальный размер одного загружаемого файла.';
$_['php_info_post_max_size']           = 'Максимальный размер данных POST. Для загрузки файлов обычно должен быть не меньше upload_max_filesize.';
