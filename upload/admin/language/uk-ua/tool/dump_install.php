<?php
// admin/language/uk-ua/tool/dump_install.php

// Heading
$_['heading_title']                        = 'Дамп БД для встановлення';

// Text
$_['text_list']                            = 'Експорт SQL-дампа';

$_['text_help']                            = 'Інструмент формує SQL-дамп для встановлення магазину. Структура таблиць експортується завжди, а дані вивантажуються лише для зазначених таблиць.';

$_['text_quick_help_title']                = 'Що потрапить у дамп';
$_['text_quick_help_structure']            = 'Структура таблиць CREATE TABLE експортується для всіх таблиць.';
$_['text_quick_help_data']                 = 'Дані INSERT експортуються лише для зазначених таблиць.';
$_['text_quick_help_safe']                 = 'За замовчуванням виключені живі та службові дані: замовлення, клієнти, користувачі, сесії, завантаження та тимчасові таблиці.';

$_['text_installer_mode']                  = 'Безпечний режим експорту для інсталятора';
$_['text_installer_mode_help']             = 'Зменшує розмір INSERT-блоків, щоб дамп стабільніше імпортувався через установник та на хостингах з обмеженнями.';
$_['text_installer_auto_hint']             = 'Режим включений автоматично';
$_['text_installer_auto_reason']           = 'Виявлено обмеження хостингу, які можуть перешкодити експорту або імпорту великої дампи.';
$_['text_installer_mode_notice']           = 'Прапорець можна зняти вручну, але при слабкому хостингу безпечніше залишити цей режим увімкненим.';

$_['text_table_filter_help']               = 'Фільтр змінює лише відображення списку. Вибрані, але приховані фільтром таблиці не скидаються.';

$_['text_visible_tables']                  = 'Показано';
$_['text_selected_tables']                 = 'Вибрано з даними';
$_['text_default_excluded_tables']         = 'Виключено за замовчуванням';

$_['text_data_include_notice']             = 'Перевірте вибрані таблиці перед експортом. Структура буде додана в дамп у будь-якому випадку.';

$_['text_hosting_limit_details_title']     = 'Подробиці виявлених обмежень';
$_['text_hosting_limit_memory']            = 'memory_limit: %s. Для великих дамп бажано 256M або вище.';
$_['text_hosting_limit_execution_time']    = 'max_execution_time: %s сек. Для великих баз бажано 120 с або вище.';
$_['text_hosting_limit_packet']            = 'mysqli.max_allowed_packet: %s. Для великих запитів INSERT бажано 8M або вище.';
$_['text_hosting_limit_set_time_missing']  = 'Функція set_time_limit() недоступна. Скрипт зможе самостійно збільшити час виконання.';
$_['text_hosting_limit_set_time_disabled'] = 'Функція set_time_limit() вимкнена у disable_functions. Скрипт зможе самостійно збільшити час виконання.';

$_['text_export_processing']               = 'Формування дампа...';

$_['text_default_store']                   = 'Основний магазин';
$_['text_comment_store_none']              = 'Не додавати ім\'я магазину';

// Entry
$_['entry_comment']                        = 'Коментар до дампи';
$_['entry_comment_help']                   = 'Коментар буде додано на початок SQL-файлу. Використовуйте його для позначення версії, дати або призначення дампа.';

$_['entry_installer_mode']                 = 'Режим експорту';

$_['entry_include_drop']                   = 'Додавати DROP TABLE IF EXISTS';
$_['entry_include_drop_help']              = 'Перед CREATE TABLE буде додано команду DROP TABLE IF EXISTS. Це зручно для чистої установки або повного оновлення структури.';

$_['entry_table_filter']                   = 'Швидкий пошук таблиці';
$_['entry_table_filter_placeholder']       = 'Наприклад: product, setting, order';

$_['entry_data_include']                   = 'Таблиці для розвантаження даних';
$_['entry_data_include_help']              = 'Позначені таблиці будуть експортовані зі структурою та даними. Невідзначені таблиці потраплять у дамп лише з структурою.';

$_['entry_comment_store']                  = 'Магазин у коментарі';
$_['entry_comment_store_help']             = 'Якщо вибрати магазин, його назву буде додано до коментаря SQL-файлу. Залишіть порожнім, щоб не додавати ім\'я магазину.';

// Button
$_['button_export']                        = 'Завантажити дамп';
$_['button_sel_all']                       = 'Вибрати видимі';
$_['button_sel_none']                      = 'Зняти видимі';
$_['button_default']                       = 'Повернути стандартний вибір';
$_['button_clear_filter']                  = 'Очистити';

// Error
$_['error_permission']                     = 'У вас немає прав для доступу до цього розділу!';
