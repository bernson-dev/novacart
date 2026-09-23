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
$_['text_information']     = 'Інформація<p><strong>SEO URL (keyword)</strong> має бути унікальним у межах того URL-простору, де може виникнути реальний конфлікт. Коли багатомовні SEO URL увімкнені, однаковий <strong>keyword</strong> у різних мовах допустимий, тому що мова визначається окремим префіксом. У межах одного магазину та мови дублікати залишаються помилкою.</p><p>Використовуйте латинські літери, цифри, дефіс (-) і підкреслення (_). Мовні префікси зарезервовані та не повинні використовуватися як звичайні SEO URL.</p>';

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
$_['text_audit_summary']       = 'усього записів: %d; проблемних записів: %d; груп конфліктних SEO URL: %d; груп дубльованих посилань: %d; конфліктів із мовними префіксами: %d; збігів між мовами: %d; осиротілих записів: %d.';
$_['text_issue_all']           = 'Усі проблеми';
$_['text_issue_keyword']       = 'Конфлікт SEO URL';
$_['text_issue_query']         = 'Дубль посилання';
$_['text_issue_prefix']        = 'Конфлікт мовного префікса';
$_['text_issue_keyword_short'] = 'SEO URL';
$_['text_issue_query_short']   = 'Посилання';
$_['text_issue_prefix_short']  = 'Префікс';
$_['column_issue']             = 'Проблема';
$_['entry_issue']              = 'Проблема';
$_['help_issue_keyword']       = 'Однаковий keyword конфліктує в поточному режимі магазину. Для багатомовних SEO URL однакові keyword у різних мовах допустимі.';
$_['help_issue_query']         = 'Однаковий query повторюється в одному магазині та мові.';
$_['help_issue_prefix']        = 'Keyword збігається із зарезервованим мовним префіксом.';

$_['text_issue_shared_language']       = 'Збігається між мовами';
$_['text_issue_orphan']                = 'Осиротілий SEO URL';
$_['text_issue_shared_language_short'] = 'Між мовами';
$_['text_issue_orphan_short']          = 'Осиротілий';
$_['help_issue_shared_language']       = 'Однаковий keyword використовується в різних мовах одного магазину. Зараз це допустимо для багатомовних SEO URL, але стане конфліктом після їх вимкнення.';
$_['help_issue_orphan']                = 'SEO URL посилається на сутність, якої більше немає в базі даних.';

$_['help_issue_all']          = 'Поточні конфлікти, які вже можуть впливати на маршрутизацію. Збіги між мовами при ввімкнених мовних префіксах сюди не входять.';
$_['button_generate']         = 'Згенерувати з назви';
$_['button_inline_save']      = 'Зберегти SEO URL';
$_['text_inline_saved']       = 'SEO URL збережено';
$_['text_saving']             = 'Збереження...';
$_['text_open_source']        = 'Відкрити вихідний об’єкт';
$_['error_request_method']    = 'Неприпустимий метод запиту.';
$_['error_not_found']         = 'Запис SEO URL не знайдено.';
$_['error_ajax']              = 'Не вдалося зберегти SEO URL. Перевірте журнал помилок і повторіть спробу.';

$_['entry_search']            = 'Швидкий пошук';
$_['help_search']             = 'Пошук за фрагментом SEO URL, query або назвою пов’язаного товару, категорії, виробника, сторінки чи статті.';
