<?php

// Heading
$_['heading_title']        = 'SEO URL';

// Text
$_['text_success']         = 'Налаштування змінені';
$_['text_list']            = 'Список SEO URL';
$_['text_add']             = 'Додати SEO URL';
$_['text_edit']            = 'Редагувати SEO URL';
$_['text_filter']          = 'Фільтр';
$_['text_default']         = 'За замовчуванням';
$_['text_information']     = 'Інформація<p>Коли мовні префікси увімкнені, публічний <strong>SEO URL</strong> береться з основної мови каталогу та використовується для всіх мов; відрізняється лише мовний префікс. SEO URL інших мов зберігаються в базі як резервні та знову використовуються після вимкнення префіксів.</p><p>Keyword залишаються унікальними в межах магазину. Використовуйте латинські літери, цифри, дефіс (-) і підкреслення (_). Мовні префікси зарезервовані.</p>';

// Column
$_['column_query']         = 'Посилання';
$_['column_keyword']       = 'SEO URL';
$_['column_store']         = 'Магазин';
$_['column_language']      = 'Мова';
$_['column_action']        = 'Дія';

// Entry
$_['entry_query']          = 'Посилання';
$_['entry_keyword']        = 'SEO URL';
$_['entry_store']          = 'Магазин';
$_['entry_language']       = 'Мова';

// Help
$_['help_keyword']         = 'Використовуєте лише символи a–z та 0–9, а також символи – або _ замість пробілів.';
$_['help_query']           = 'URL-запит, який потрібно замінити.';

// Error
$_['error_permission']     = 'У вас недостатньо прав для внесення змін до SEO URL!';
$_['error_keyword']        = 'SEO URL повинен містити від 3 до 64 символів (крім сторінки common/home - може бути порожнім)!';
$_['error_keyword_exists'] = 'SEO URL вже використовується!';
$_['error_query']          = 'Посилання має містити від 3 до 255 символів!';
$_['error_query_exists']   = 'Такий запит (посилання) існує в цьому магазині!';

$_['text_all']                 = 'Усі';
$_['text_audit']               = 'Аудит SEO URL:';
$_['text_audit_stats']          = 'Загальна статистика';
$_['text_stat_total']           = 'Усього записів';
$_['text_stat_issues']          = 'Проблемних записів';
$_['text_stat_keyword_groups']  = 'Груп конфліктів SEO URL';
$_['text_stat_query_groups']    = 'Груп дублів посилань';
$_['text_issue_all']           = 'Усі проблеми';
$_['text_issue_keyword']       = 'Конфлікт SEO URL';
$_['text_issue_query']         = 'Дубль посилання';
$_['text_issue_prefix']        = 'Конфлікт мовного префікса';
$_['text_issue_keyword_short'] = 'SEO URL';
$_['text_issue_query_short']   = 'Посилання';
$_['text_issue_prefix_short']  = 'Префікс';
$_['column_issue']             = 'Проблема';
$_['entry_issue']              = 'Проблема';
$_['help_issue_keyword']       = 'Однаковий keyword уже використовується в цьому магазині. Резервні SEO URL мов також мають залишатися унікальними, щоб режим префіксів можна було безпечно вимкнути.';
$_['help_issue_query']         = 'Однаковий query повторюється в одному магазині та мові.';
$_['help_issue_prefix']        = 'Keyword збігається із зарезервованим мовним префіксом.';

$_['text_issue_shared_language']       = 'Збігається між мовами';
$_['text_issue_orphan']                = 'Осиротілий SEO URL';
$_['text_issue_shared_language_short'] = 'Між мовами';
$_['text_issue_orphan_short']          = 'Осиротілий';
$_['help_issue_shared_language']       = 'Однаковий keyword знайдено в різних мовах одного магазину. Такі записи потрібно перевірити, оскільки резервні мовні SEO URL мають залишатися унікальними.';
$_['help_issue_orphan']                = 'SEO URL посилається на сутність, якої більше немає в базі даних.';

$_['help_issue_all']          = 'Усі знайдені проблеми SEO URL, які можуть впливати на маршрутизацію або безпечне перемикання мовного режиму.';
$_['button_generate']         = 'Згенерувати з назви';
$_['button_inline_save']      = 'Зберегти SEO URL';
$_['text_inline_saved']       = 'SEO URL збережено';
$_['text_saving']             = 'Збереження...';
$_['text_open_source']        = 'Відкрити вихідний об’єкт';
$_['text_open_storefront'] = 'Відкрити на вітрині';
$_['error_request_method']    = 'Неприпустимий метод запиту.';
$_['error_not_found']         = 'Запис SEO URL не знайдено.';
$_['error_ajax']              = 'Не вдалося зберегти SEO URL. Перевірте журнал помилок і повторіть спробу.';

$_['entry_search']            = 'Швидкий пошук';
$_['help_search']             = 'Пошук за фрагментом SEO URL, query або назвою пов’язаного товару, категорії, виробника, сторінки чи статті.';
$_['text_group_records']       = 'записів у групі';
