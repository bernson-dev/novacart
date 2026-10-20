<?php
$_['heading_title'] = 'Состояние магазина';

$_['text_extension'] = 'Расширения';
$_['text_success'] = 'Настройки состояния магазина сохранены!';
$_['text_edit'] = 'Настройка состояния магазина';
$_['text_settings'] = 'Настройки';
$_['text_view'] = 'Перейти';
$_['text_enabled'] = 'Включено';
$_['text_disabled'] = 'Отключено';

$_['entry_status'] = 'Статус';
$_['entry_width'] = 'Ширина';
$_['entry_sort_order'] = 'Порядок сортировки';
$_['entry_low_stock_limit'] = 'Низкий остаток';
$_['entry_log_warning_mb'] = 'Предупреждать о размере журнала, MB';

$_['help_low_stock_limit'] = 'Активные товары с остатком от 1 до указанного значения считаются товарами с низким остатком.';
$_['help_log_warning_mb'] = 'Если основной журнал ошибок превышает этот размер, Health покажет предупреждение.';

$_['section_system'] = 'Система';
$_['section_catalog'] = 'Каталог';
$_['section_seo'] = 'SEO';

$_['state_ok'] = 'OK';
$_['state_warning'] = 'Внимание';
$_['state_error'] = 'Ошибка';
$_['state_info'] = 'Информация';

$_['check_php_version'] = 'Версия PHP';
$_['check_php_version_help'] = 'NovaCart требует PHP 7.3 или новее.';
$_['check_php_extensions'] = 'PHP-расширения';
$_['check_php_extensions_help'] = 'Проверяются базовые расширения, необходимые для штатной работы NovaCart.';
$_['check_storage_location'] = 'Расположение storage';
$_['check_storage_location_help'] = 'Рекомендуется хранить storage за пределами публичного web-root.';
$_['check_writable_directories'] = 'Системные каталоги';
$_['check_writable_directories_help'] = 'Cache, logs, upload, modification и session должны существовать и быть доступны для записи.';
$_['check_install_directory'] = 'Каталог install';
$_['check_install_directory_help'] = 'После установки публичный каталог install следует удалить.';
$_['check_error_log'] = 'Журнал ошибок';
$_['check_error_log_help'] = 'Большой журнал ошибок обычно означает накопившиеся проблемы или отсутствие обслуживания.';

$_['check_core_integrity'] = 'Целостность ядра';
$_['check_core_integrity_help'] = 'Проверяются SHA-256 критичных файлов NovaCart из зафиксированного manifest релиза. Изменение не всегда означает взлом: причиной может быть неполное обновление или локальная правка.';
$_['integrity_changed'] = 'Изменён';
$_['integrity_missing'] = 'Отсутствует';
$_['integrity_invalid'] = 'Manifest повреждён';
$_['check_out_of_stock'] = 'Товары без остатка';
$_['check_out_of_stock_help'] = 'Количество активных товаров с остатком 0 или меньше.';
$_['check_low_stock'] = 'Низкий остаток';
$_['check_low_stock_help'] = 'Количество активных товаров с остатком ниже установленного порога.';
$_['check_catalog_query'] = 'Проверка каталога';
$_['check_catalog_query_help'] = 'Не удалось получить данные о складских остатках.';
$_['check_seo_product'] = 'Товары без SEO URL';
$_['check_seo_product_help'] = 'Активные товары без SEO URL для текущего языка и магазина.';
$_['check_seo_category'] = 'Категории без SEO URL';
$_['check_seo_category_help'] = 'Активные категории без SEO URL для текущего языка и магазина.';
$_['check_seo_manufacturer'] = 'Производители без SEO URL';
$_['check_seo_manufacturer_help'] = 'Производители без SEO URL для текущего языка и магазина.';
$_['check_seo_information'] = 'Страницы без SEO URL';
$_['check_seo_information_help'] = 'Активные информационные страницы без SEO URL для текущего языка и магазина.';
$_['check_seo_query'] = 'Проверка SEO';
$_['check_seo_query_help'] = 'Не удалось выполнить проверку таблицы SEO URL.';

$_['error_permission'] = 'У вас нет прав для изменения настроек состояния магазина!';

// Quick actions
$_['health_quick_product'] = 'Добавить товар';
$_['health_quick_orders'] = 'Заказы';
$_['health_quick_products'] = 'Товары';
$_['health_quick_logs'] = 'Журнал ошибок';
$_['health_quick_seo'] = 'SEO URL';
