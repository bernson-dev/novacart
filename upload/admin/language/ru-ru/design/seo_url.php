<?php
// *	@source		See SOURCE.txt for source and other copyright.
// *	@license	GNU General Public License version 3; see LICENSE.txt

// Heading
$_['heading_title']        = 'SEO URL';

// Text
$_['text_success']         = 'Настройки успешно изменены!';
$_['text_list']            = 'Список SEO URL';
$_['text_add']             = 'Добавить SEO URL';
$_['text_edit']            = 'Редактировать SEO URL';
$_['text_filter']          = 'Фильтр';
$_['text_default']         = 'По умолчанию';
$_['text_information']     = 'Информация<p><strong>SEO URL (keyword)</strong> должен быть уникален в пределах того URL-пространства, где может возникнуть реальный конфликт. При включённых мультиязычных SEO URL одинаковый <strong>keyword</strong> в разных языках допустим, потому что язык определяется отдельным префиксом. В пределах одного магазина и языка дубликаты остаются ошибкой.</p><p>Используйте латинские буквы, цифры, дефис (-) и подчёркивание (_). Языковые префиксы зарезервированы и не должны использоваться как обычные SEO URL.</p>';

// Column
$_['column_query']         = 'Ссылка';
$_['column_keyword']       = 'SEO URL';
$_['column_store']         = 'Магазин';
$_['column_language']      = 'Язык';
$_['column_action']        = 'Действие';

// Entry
$_['entry_query']          = 'Ссылка';
$_['entry_keyword']        = 'SEO URL';
$_['entry_store']          = 'Магазин';
$_['entry_language']       = 'Язык';

// Help
$_['help_keyword']         = 'Используете только символы a–z и 0–9, а также символы – или _ вместо пробелов.';
$_['help_query']           = 'URL-запрос который нужно заменить.';

// Error
$_['error_permission']     = 'У вас недостаточно прав для внесения изменений в SEO URL!';
$_['error_keyword']        = 'SEO URL должен содержать от 3 до 64 символов (кроме страницы common/home - может быть пустым)!';
$_['error_keyword_exists'] = 'SEO URL уже используется!';
$_['error_query']          = 'Ссылка должна содержать от 3 до 255 символов!';
$_['error_query_exists']   = 'Такой запрос (ссылка) существует в этом магазине!';

$_['text_all']                 = 'Все';
$_['text_audit']               = 'Аудит SEO URL:';
$_['text_audit_summary']       = 'всего записей: %d; проблемных записей: %d; групп конфликтующих SEO URL: %d; групп дублирующихся ссылок: %d; конфликтов с языковыми префиксами: %d; совпадений между языками: %d; осиротевших записей: %d.';
$_['text_issue_all']           = 'Все проблемы';
$_['text_issue_keyword']       = 'Конфликт SEO URL';
$_['text_issue_query']         = 'Дубликат ссылки';
$_['text_issue_prefix']        = 'Конфликт языкового префикса';
$_['text_issue_keyword_short'] = 'SEO URL';
$_['text_issue_query_short']   = 'Ссылка';
$_['text_issue_prefix_short']  = 'Префикс';
$_['column_issue']             = 'Проблема';
$_['entry_issue']              = 'Проблема';
$_['help_issue_keyword']       = 'Одинаковый keyword конфликтует в текущем режиме магазина. При мультиязычных SEO URL одинаковые keyword в разных языках допустимы.';
$_['help_issue_query']         = 'Одинаковый query повторяется в одном магазине и языке.';
$_['help_issue_prefix']        = 'Keyword совпадает с зарезервированным языковым префиксом.';

$_['text_issue_shared_language']       = 'Совпадает между языками';
$_['text_issue_orphan']                = 'Осиротевший SEO URL';
$_['text_issue_shared_language_short'] = 'Между языками';
$_['text_issue_orphan_short']          = 'Осиротевший';
$_['help_issue_shared_language']       = 'Одинаковый keyword используется в разных языках одного магазина. Сейчас это допустимо при мультиязычных SEO URL, но станет конфликтом после их отключения.';
$_['help_issue_orphan']                = 'SEO URL ссылается на сущность, которой больше нет в базе данных.';
