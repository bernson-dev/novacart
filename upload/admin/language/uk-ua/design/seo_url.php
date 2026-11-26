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

$_['text_history'] = 'Історія ЧПУ';
$_['text_history_migration'] = 'Таблиця історії відсутня. Виконайте міграцію docs/migrations/seo-url-history.sql із префіксом вашої бази даних.';
$_['text_history_help'] = 'Історія зберігає старі ключові слова, а не повні URL. Чинні ЧПУ мають пріоритет. Наявність поточного ключового слова не гарантує доступність сторінки.';
$_['text_history_search'] = 'Старе ЧПУ або ідентифікатор сутності';
$_['column_history_old'] = 'Старе ЧПУ';
$_['column_history_current'] = 'Поточне ЧПУ';
$_['column_history_date'] = 'Дата зміни';
$_['text_history_shadowed'] = 'Зайнято';
$_['text_history_missing'] = 'Немає поточного ЧПУ';

$_['text_redirects'] = 'Перенаправлення';
$_['text_redirect_migration'] = 'Таблиця відсутня. Виконайте міграцію docs/migrations/seo-redirect.sql.';
$_['text_redirect_help'] = 'Вкажіть внутрішні шляхи з початковим /, включно з мовним префіксом і підкаталогом магазину. Без домену та параметрів. Для 410 ціль не потрібна. Чинні ЧПУ мають пріоритет.';
$_['entry_redirect_source'] = 'Початковий шлях';
$_['entry_redirect_target'] = 'Цільовий шлях';
$_['entry_redirect_status'] = 'Статус';
$_['error_redirect_scope'] = 'Некоректний магазин або мова.';
$_['error_redirect_rule'] = 'Некоректне правило.';
$_['error_redirect_path'] = 'Неприпустимий шлях. Дозволені лише внутрішні шляхи магазину.';
$_['error_redirect_conflict'] = 'Початковий шлях зайнятий чинним ЧПУ або збігається з ціллю.';
$_['error_redirect_duplicate'] = 'Таке правило вже існує.';
$_['error_redirect_chain'] = 'Правило створює ланцюжок або цикл перенаправлень.';

$_['text_not_found_log'] = 'Журнал 404';
$_['text_not_found_migration'] = 'Таблиця журналу відсутня. Виконайте міграцію docs/migrations/seo-not-found.sql.';
$_['text_not_found_help'] = 'Журнал об’єднує звернення до відсутніх ЧПУ за магазином, мовою та шляхом. Параметри запиту не зберігаються. Для усунення помилки створіть перенаправлення із запису.';
$_['column_not_found_hits'] = 'Звернення';
$_['column_not_found_first'] = 'Перше звернення';
$_['column_not_found_last'] = 'Останнє звернення';
$_['button_not_found_redirect'] = 'Створити перенаправлення';
$_['button_not_found_prune'] = 'Видалити старше %d днів';
$_['text_not_found_prune_confirm'] = 'Видалити записи, до яких не зверталися понад %d днів?';
$_['entry_not_found_status'] = 'Реєстрація помилок 404';
$_['entry_not_found_retention'] = 'Термін зберігання (днів)';
$_['text_not_found_settings_help'] = 'Спільні налаштування для всіх магазинів. Очищення виконується щодня планувальником, навіть якщо реєстрацію вимкнено. Для реєстрації завдання виконайте php cli.php cron:install.';
$_['error_not_found_settings'] = 'Вкажіть термін зберігання від 1 до 3650 днів і допустимий статус.';
$_['text_missing_keywords'] = 'Відсутні ЧПУ';
$_['text_missing_help'] = 'Пошук об’єктів без ЧПУ та попередній перегляд. Враховуються також вимкнені об’єкти.';
$_['text_missing_shared'] = 'Режим спільних ЧПУ: перевіряється лише основна мова магазину.';
$_['text_missing_type'] = 'Тип об’єкта';
$_['text_missing_total'] = 'Усього відсутні';
$_['text_missing_preview'] = 'Попередній перегляд';
$_['text_missing_name'] = 'Назва';
$_['text_missing_no_keyword'] = 'Не вдалося підібрати ЧПУ';
$_['text_missing_no_languages'] = 'Немає доступної мови для перевірки.';
$_['text_missing_product'] = 'Товари';
$_['text_missing_category'] = 'Категорії';
$_['text_missing_manufacturer'] = 'Виробники';
$_['text_missing_information'] = 'Інформаційні сторінки';
$_['text_missing_article'] = 'Статті блогу';
$_['text_missing_blog_category'] = 'Категорії блогу';
$_['text_missing_created'] = 'Створено';
$_['text_missing_skipped'] = 'Пропущено';
$_['text_missing_errors'] = 'Помилок';
$_['text_missing_confirm'] = 'Створити ЧПУ для вибраних об’єктів?';
$_['button_missing_generate'] = 'Створити вибрані ЧПУ';
$_['error_missing_selection'] = 'Виберіть від 1 до 50 об’єктів у допустимому магазині та мові.';
$_['button_missing_all'] = 'Створити всі відсутні ЧПУ';
$_['text_missing_all_confirm'] = 'Створити всі відсутні ЧПУ вибраного магазину за всіма доступними мовами та типами об’єктів?';
$_['button_missing_stop'] = 'Зупинити';
$_['button_missing_refresh'] = 'Оновити результати';
$_['error_missing_batch'] = 'Пакетну генерацію перервано. Оновіть результати та повторіть запуск.';
$_['text_diagnostics'] = 'SEO-діагностика';
$_['text_diag_readonly'] = 'Діагностика не змінює дані до явного підтвердження вибраних виправлень.';
$_['text_diag_urls'] = 'Проблеми ЧПУ та перенаправлень';
$_['text_diag_global'] = 'Статистика ЧПУ та перенаправлень усіх магазинів і мов.';
$_['text_diag_empty'] = 'Порожні ЧПУ';
$_['text_diag_invalid'] = 'Некоректні символи в ЧПУ';
$_['text_diag_redirect'] = 'Ланцюжки та цикли ручних перенаправлень';
$_['text_diag_unavailable'] = 'Таблиця недоступна';
$_['text_diag_meta'] = 'Метадані';
$_['text_diag_meta_help'] = 'Активні об’єкти вибраного магазину й мови. Дублікати Title перевіряються окремо для кожного типу.';
$_['text_diag_missing_title'] = 'Порожній Meta Title';
$_['text_diag_missing_description'] = 'Порожній Meta Description';
$_['text_diag_duplicate_title'] = 'Дублікати Meta Title';
$_['text_diag_source'] = 'Початковий шлях';
$_['text_diag_target'] = 'Цільовий шлях';
$_['text_diag_updated'] = 'Оброблено';
$_['text_diag_skipped'] = 'Пропущено';
$_['text_diag_errors'] = 'Помилки';
$_['text_diag_shared_meta'] = 'Перевірте запропоновані значення й виберіть записи. Метадані об’єкта й мови спільні для всіх його магазинів.';
$_['text_diag_orphan_help'] = 'Виберіть осиротілі ЧПУ для видалення. Існування об’єкта буде перевірено повторно.';
$_['text_diag_select_page'] = 'Вибрати поточну сторінку';
$_['text_diag_proposal'] = 'Запропоноване значення';
$_['text_diag_manual'] = 'Потрібна ручна перевірка';
$_['text_diag_confirm'] = 'Застосувати зміни до вибраних записів?';
$_['text_diag_apply'] = 'Застосувати вибрані';
