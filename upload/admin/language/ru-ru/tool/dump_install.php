<?php
// admin/language/ru-ru/tool/dump_install.php

// Heading
$_['heading_title']                        = 'Дамп БД для установки';

// Text
$_['text_list']                            = 'Экспорт SQL-дампа';

$_['text_help']                            = 'Инструмент формирует SQL-дамп для установки магазина. Структура таблиц экспортируется всегда, а данные выгружаются только для отмеченных таблиц.';

$_['text_quick_help_title']                = 'Что попадёт в дамп';
$_['text_quick_help_structure']            = 'Структура таблиц CREATE TABLE экспортируется для всех таблиц.';
$_['text_quick_help_data']                 = 'Данные INSERT экспортируются только для отмеченных таблиц.';
$_['text_quick_help_safe']                 = 'По умолчанию исключены живые и служебные данные: заказы, клиенты, пользователи, сессии, загрузки и временные таблицы.';

$_['text_installer_mode']                  = 'Безопасный режим экспорта для установщика';
$_['text_installer_mode_help']             = 'Уменьшает размер INSERT-блоков, чтобы дамп стабильнее импортировался через установщик и на хостингах с ограничениями.';
$_['text_installer_auto_hint']             = 'Режим включён автоматически';
$_['text_installer_auto_reason']           = 'Обнаружены ограничения хостинга, которые могут помешать экспорту или импорту большого дампа.';
$_['text_installer_mode_notice']           = 'Флажок можно снять вручную, но при слабом хостинге безопаснее оставить этот режим включённым.';

$_['text_table_filter_help']               = 'Фильтр изменяет только отображение списка. Уже выбранные, но скрытые фильтром таблицы не сбрасываются.';

$_['text_visible_tables']                  = 'Показано';
$_['text_selected_tables']                 = 'Выбрано с данными';
$_['text_default_excluded_tables']         = 'Исключено по умолчанию';

$_['text_data_include_notice']             = 'Проверьте выбранные таблицы перед экспортом. Структура будет добавлена в дамп в любом случае.';

$_['text_hosting_limit_details_title']     = 'Подробности обнаруженных ограничений';
$_['text_hosting_limit_memory']            = 'memory_limit: %s. Для больших дампов желательно 256M или выше.';
$_['text_hosting_limit_execution_time']    = 'max_execution_time: %s сек. Для больших баз желательно 120 сек или выше.';
$_['text_hosting_limit_packet']            = 'mysqli.max_allowed_packet: %s. Для крупных INSERT-запросов желательно 8M или выше.';
$_['text_hosting_limit_set_time_missing']  = 'Функция set_time_limit() недоступна. Скрипт не сможет самостоятельно увеличить время выполнения.';
$_['text_hosting_limit_set_time_disabled'] = 'Функция set_time_limit() отключена в disable_functions. Скрипт не сможет самостоятельно увеличить время выполнения.';

$_['text_export_processing']               = 'Формирование дампа...';

$_['text_default_store']                   = 'Основной магазин';
$_['text_comment_store_none']              = 'Не добавлять имя магазина';

// Entry
$_['entry_comment']                        = 'Комментарий к дампу';
$_['entry_comment_help']                   = 'Комментарий будет добавлен в начало SQL-файла. Используйте его для пометки версии, даты или назначения дампа.';

$_['entry_installer_mode']                 = 'Режим экспорта';

$_['entry_include_drop']                   = 'Добавлять DROP TABLE IF EXISTS';
$_['entry_include_drop_help']              = 'Перед CREATE TABLE будет добавлена команда DROP TABLE IF EXISTS. Это удобно для чистой установки или полного обновления структуры.';

$_['entry_table_filter']                   = 'Быстрый поиск таблицы';
$_['entry_table_filter_placeholder']       = 'Например: product, setting, order';

$_['entry_data_include']                   = 'Таблицы для выгрузки данных';
$_['entry_data_include_help']              = 'Отмеченные таблицы будут экспортированы со структурой и данными. Неотмеченные таблицы попадут в дамп только со структурой.';

$_['entry_comment_store']                  = 'Магазин в комментарии';
$_['entry_comment_store_help']             = 'Если выбрать магазин, его название будет добавлено в комментарий SQL-файла. Оставьте пустым, чтобы не добавлять имя магазина.';

// Button
$_['button_export']                        = 'Скачать дамп';
$_['button_sel_all']                       = 'Выбрать видимые';
$_['button_sel_none']                      = 'Снять видимые';
$_['button_default']                       = 'Вернуть выбор по умолчанию';
$_['button_clear_filter']                  = 'Очистить';

// Error
$_['error_permission']                     = 'У вас нет прав для доступа к этому разделу!';
