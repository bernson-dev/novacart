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
$_['text_information']     = 'Информация<p>При включённых языковых префиксах публичный <strong>SEO URL</strong> берётся из основного языка каталога и используется для всех языков; различается только языковой префикс. SEO URL остальных языков сохраняются в базе как резервные и снова используются после отключения префиксов.</p><p>Keyword остаются уникальными в пределах магазина. Используйте латинские буквы, цифры, дефис (-) и подчёркивание (_). Языковые префиксы зарезервированы.</p>';

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
$_['text_audit_stats']          = 'Общая статистика';
$_['text_stat_total']           = 'Всего записей';
$_['text_stat_issues']          = 'Проблемных записей';
$_['text_stat_keyword_groups']  = 'Групп конфликтов SEO URL';
$_['text_stat_query_groups']    = 'Групп дублей ссылок';
$_['text_issue_all']           = 'Все проблемы';
$_['text_issue_keyword']       = 'Конфликт SEO URL';
$_['text_issue_query']         = 'Дубликат ссылки';
$_['text_issue_prefix']        = 'Конфликт языкового префикса';
$_['text_issue_keyword_short'] = 'SEO URL';
$_['text_issue_query_short']   = 'Ссылка';
$_['text_issue_prefix_short']  = 'Префикс';
$_['column_issue']             = 'Проблема';
$_['entry_issue']              = 'Проблема';
$_['help_issue_keyword']       = 'Одинаковый keyword уже используется в этом магазине. Резервные SEO URL языков тоже должны оставаться уникальными, чтобы режим префиксов можно было безопасно отключить.';
$_['help_issue_query']         = 'Одинаковый query повторяется в одном магазине и языке.';
$_['help_issue_prefix']        = 'Keyword совпадает с зарезервированным языковым префиксом.';

$_['text_issue_shared_language']       = 'Совпадает между языками';
$_['text_issue_orphan']                = 'Осиротевший SEO URL';
$_['text_issue_shared_language_short'] = 'Между языками';
$_['text_issue_orphan_short']          = 'Осиротевший';
$_['help_issue_shared_language']       = 'Одинаковый keyword найден в разных языках одного магазина. Такие записи требуют проверки, потому что резервные языковые SEO URL должны оставаться уникальными.';
$_['help_issue_orphan']                = 'SEO URL ссылается на сущность, которой больше нет в базе данных.';

$_['help_issue_all']          = 'Все найденные проблемы SEO URL, которые могут влиять на маршрутизацию или безопасное переключение языкового режима.';
$_['button_generate']         = 'Сгенерировать из названия';
$_['button_inline_save']      = 'Сохранить SEO URL';
$_['text_inline_saved']       = 'SEO URL сохранён';
$_['text_saving']             = 'Сохранение...';
$_['text_open_source']        = 'Открыть исходный объект';
$_['text_open_storefront'] = 'Открыть на витрине';
$_['error_request_method']    = 'Недопустимый метод запроса.';
$_['error_not_found']         = 'Запись SEO URL не найдена.';
$_['error_ajax']              = 'Не удалось сохранить SEO URL. Проверьте журнал ошибок и повторите попытку.';

$_['entry_search']            = 'Быстрый поиск';
$_['help_search']             = 'Поиск по фрагменту SEO URL, query или названию связанного товара, категории, производителя, страницы или статьи.';
$_['text_group_records']       = 'записей в группе';

$_['text_history'] = 'История ЧПУ';
$_['text_history_migration'] = 'Таблица истории отсутствует. Выполните миграцию docs/migrations/seo-url-history.sql с префиксом вашей базы данных.';
$_['text_history_help'] = 'История хранит старые ключевые слова, а не полные URL. Действующие ЧПУ имеют приоритет. Наличие текущего ключевого слова не гарантирует доступность страницы.';
$_['text_history_search'] = 'Старое ЧПУ или идентификатор сущности';
$_['column_history_old'] = 'Старое ЧПУ';
$_['column_history_current'] = 'Текущее ЧПУ';
$_['column_history_date'] = 'Дата изменения';
$_['text_history_shadowed'] = 'Занято';
$_['text_history_missing'] = 'Нет текущего ЧПУ';

$_['text_redirects'] = 'Перенаправления';
$_['text_redirect_migration'] = 'Таблица отсутствует. Выполните миграцию docs/migrations/seo-redirect.sql.';
$_['text_redirect_help'] = 'Укажите внутренние пути с начальным /, включая языковой префикс и подкаталог магазина. Без домена и параметров. Для 410 цель не нужна. Действующие ЧПУ имеют приоритет.';
$_['entry_redirect_source'] = 'Исходный путь';
$_['entry_redirect_target'] = 'Целевой путь';
$_['entry_redirect_status'] = 'Статус';
$_['error_redirect_scope'] = 'Некорректный магазин или язык.';
$_['error_redirect_rule'] = 'Некорректное правило.';
$_['error_redirect_path'] = 'Недопустимый путь. Разрешены только внутренние пути магазина.';
$_['error_redirect_conflict'] = 'Исходный путь занят действующим ЧПУ или совпадает с целью.';
$_['error_redirect_duplicate'] = 'Такое правило уже существует.';
$_['error_redirect_chain'] = 'Правило создаёт цепочку или цикл перенаправлений.';

$_['text_not_found_log'] = 'Журнал 404';
$_['text_not_found_migration'] = 'Таблица журнала отсутствует. Выполните миграцию docs/migrations/seo-not-found.sql.';
$_['text_not_found_help'] = 'Журнал объединяет обращения к отсутствующим ЧПУ по магазину, языку и пути. Параметры запроса не сохраняются. Для устранения ошибки создайте перенаправление из записи.';
$_['column_not_found_hits'] = 'Обращения';
$_['column_not_found_first'] = 'Первое обращение';
$_['column_not_found_last'] = 'Последнее обращение';
$_['button_not_found_redirect'] = 'Создать перенаправление';
$_['button_not_found_prune'] = 'Удалить старше %d дней';
$_['text_not_found_prune_confirm'] = 'Удалить записи, к которым не обращались более %d дней?';
$_['entry_not_found_status'] = 'Регистрация ошибок 404';
$_['entry_not_found_retention'] = 'Срок хранения (дней)';
$_['text_not_found_settings_help'] = 'Общие настройки для всех магазинов. Очистка выполняется ежедневно планировщиком, даже если регистрация отключена. Для регистрации задания выполните php cli.php cron:install.';
$_['error_not_found_settings'] = 'Укажите срок хранения от 1 до 3650 дней и допустимый статус.';
