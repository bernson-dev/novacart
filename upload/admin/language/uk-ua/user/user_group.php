<?php
// Heading
$_['heading_title']                       = 'Групи користувачів';

// Text
$_['text_success']                        = 'Налаштування успішно змінено!';
$_['text_list']                           = 'Список груп користувачів';
$_['text_add']                            = 'Додати';
$_['text_edit']                           = 'Редагування';
$_['text_admin_permissions_restored']     = 'Критичні дозволи для групи Administrator були автоматично відновлені.';
$_['text_permission_search_placeholder']  = 'Швидкий пошук дії, наприклад: catalog/product';
$_['text_permission_full_access']         = 'Повний доступ';
$_['text_permission_readonly']            = 'Тільки перегляд';
$_['text_permission_no_rights']           = 'Ніяких прав';
$_['text_permission_empty']               = 'Нічого не знайдено.';
$_['text_permission_critical_missing']    = 'Видалені критичні права: %s';
$_['text_permission_filter_all']          = 'Всі';
$_['text_permission_filter_no_access']    = 'Без перегляду';
$_['text_permission_filter_no_modify']    = 'Без редагування';
$_['text_permission_filter_hidden']       = 'Приховані';
$_['text_permission_filter_inconsistent'] = 'Конфліктні';

// Entry
$_['entry_name']                          = 'Назва групи користувачів';
$_['entry_access']                        = 'Перегляд';
$_['entry_modify']                        = 'Редагування';
$_['entry_hide']                          = 'Приховати';
$_['entry_permission']                    = 'Права';

// Column
$_['column_name']                         = 'Група користувачів';
$_['column_action']                       = 'Дія';
$_['column_permission_route']             = 'Дія / route';

// Button
$_['button_permission_search_clear']      = 'Очистити пошук';
$_['button_permission_reset_filters']     = 'Скинути фільтри';

// Help
$_['help_access']                         = 'Дозволяє перегляд та використання доповнення без можливості зміни.';
$_['help_modify']                         = 'Надає право змінювати налаштування або видаляти додатки.';
$_['help_no_rights']                      = 'Заборонено переглядати та редагувати';
$_['help_hide']                           = 'Вибрані додатки не відображатимуться на сторінках модулів, платежів, доставок.';
$_['help_permission_modify_access_logic'] = 'Синхронізація прав (редагування неможливе без перегляду): якщо дозволити редагування, перегляд буде увімкнено автоматично. Якщо заборонити перегляд, редагування буде вимкнено.';

// Error
$_['error_permission']                    = 'У вас немає прав для внесення змін!';
$_['error_name']                          = 'Назва повинна містити від 3 до 64 символів!';
$_['error_user']                          = 'Цю групу користувачів не можна видалити, оскільки до неї входить %s користувачів!';

// Foolproof protection
$_['error_admin_empty_permissions']       = 'Не можна зняти всі дозволи у групи Administrator - буде втрачено доступ до адмін-панелі.';
$_['error_admin_required_access']         = 'Для групи Administrator обов\'язковий доступ до: %s';
$_['error_admin_required_modify']         = 'Для групи Administrator обов\'язковий modify доступ до: %s';
