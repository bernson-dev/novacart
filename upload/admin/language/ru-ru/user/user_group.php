<?php
// Heading
$_['heading_title']                       = 'Группы пользователей';

// Text
$_['text_success']                        = 'Настройки успешно изменены!';
$_['text_list']                           = 'Список групп пользователей';
$_['text_add']                            = 'Добавить';
$_['text_edit']                           = 'Редактирование';
$_['text_admin_permissions_restored']     = 'Критические разрешения для группы Administrator были автоматически восстановлены.';
$_['text_permission_search_placeholder']  = 'Быстрый поиск действия, например: catalog/product';
$_['text_permission_full_access']         = 'Полный доступ';
$_['text_permission_readonly']            = 'Только просмотр';
$_['text_permission_no_rights']           = 'Никаких прав';
$_['text_permission_empty']               = 'Ничего не найдено.';
$_['text_permission_critical_missing']    = 'Удалены критические права: %s';
$_['text_permission_filter_all']          = 'Все';
$_['text_permission_filter_no_access']    = 'Без просмотра';
$_['text_permission_filter_no_modify']    = 'Без редактирования';
$_['text_permission_filter_hidden']       = 'Скрытые';
$_['text_permission_filter_inconsistent'] = 'Конфликтные';

// Entry
$_['entry_name']                          = 'Название группы пользователей';
$_['entry_access']                        = 'Просмотр';
$_['entry_modify']                        = 'Редактирование';
$_['entry_hide']                          = 'Скрыть';
$_['entry_permission']                    = 'Права';

// Column
$_['column_name']                         = 'Группа пользователей';
$_['column_action']                       = 'Действие';
$_['column_permission_route']             = 'Действие / route';

// Button
$_['button_permission_search_clear']      = 'Очистить поиск';
$_['button_permission_reset_filters']     = 'Сбросить фильтры';

// Help
$_['help_access']                         = 'Разрешает просмотр и использование дополнения без возможности изменения.';
$_['help_modify']                         = 'Предоставляет право изменять настройки или удалять дополнение.';
$_['help_no_rights']                      = 'Запрещен просмотр и редактирование';
$_['help_hide']                           = 'Выбранные дополнения не будут отображаться на страницах модулей, платежей, доставок.';
$_['help_permission_modify_access_logic'] = 'Синхронизация прав (редактирование невозможно без просмотра): если разрешить редактирование, просмотр будет включен автоматически. Если запретить просмотр, редактирование для этого действия будет отключено.';

// Error
$_['error_permission']                    = 'У вас нет прав для внесения изменений!';
$_['error_name']                          = 'Название должно содержать от 3 до 64 символов!';
$_['error_user']                          = 'Эту группу пользователей нельзя удалить, поскольку в нее входит %s пользователей!';

// Foolproof protection
$_['error_admin_empty_permissions']       = 'Нельзя снять все разрешения у группы Administrator — будет потерян доступ к админ-панели.';
$_['error_admin_required_access']         = 'Для группы Administrator обязателен доступ к: %s';
$_['error_admin_required_modify']         = 'Для группы Administrator обязателен modify доступ к: %s';
