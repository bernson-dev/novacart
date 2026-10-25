<?php
$_['heading_title'] = 'Планувальник завдань';
$_['text_home'] = 'Головна'; $_['text_no_results'] = 'Завдань немає';
$_['text_enabled'] = 'Увімкнено'; $_['text_disabled'] = 'Вимкнено';
$_['text_success'] = 'Статус завдання оновлено';
$_['text_cli'] = 'Для запуску використовуйте системний cron: php cli.php jobs:run. Ця сторінка показує стан завдань, але не запускає їх.';
$_['column_code'] = 'Код'; $_['column_route'] = 'Маршрут'; $_['column_interval'] = 'Інтервал';
$_['column_status'] = 'Статус'; $_['column_last'] = 'Останній запуск';
$_['column_next'] = 'Наступний запуск'; $_['column_result'] = 'Результат';
$_['column_message'] = 'Повідомлення'; $_['column_action'] = 'Дія';
$_['button_enable'] = 'Увімкнути'; $_['button_disable'] = 'Вимкнути';
$_['error_permission'] = 'Недостатньо прав для зміни завдань';
$_['error_action'] = 'Неправильні параметри запиту';

$_['text_monitor'] = 'Моніторинг запусків';
$_['text_monitor_empty'] = 'Історія запусків відсутня';
$_['text_heartbeat_ok'] = 'Автоматичний запуск працює';
$_['text_heartbeat_late'] = 'Немає запусків понад п’ять хвилин';
$_['text_heartbeat_missing'] = 'Запуски ще не зареєстровано';
$_['column_started'] = 'Початок';
$_['column_finished'] = 'Завершення';
$_['column_duration'] = 'Тривалість';
$_['column_scheduler'] = 'Scheduler (оброблено/помилок)';
$_['column_queue'] = 'Queue (оброблено/помилок)';
$_['column_run_status'] = 'Результат';
$_['column_run_error'] = 'Помилка';

$_['text_heartbeat_error'] = 'Останній запуск завершився з помилкою';
$_['text_heartbeat_running'] = 'Завдання виконується';

$_['text_action_run'] = 'Запустити зараз';
$_['text_action_edit'] = 'Редагувати';
$_['text_action_delete'] = 'Видалити';
$_['text_action_confirm'] = 'Видалити завдання?';
$_['text_action_add'] = 'Додати завдання';
$_['text_action_code'] = 'Код завдання';
$_['text_action_route'] = 'Внутрішній маршрут';
$_['text_action_interval'] = 'Інтервал, секунд';
$_['text_action_args'] = 'Параметри JSON';
$_['text_action_cancel'] = 'Скасувати';
$_['text_action_save'] = 'Зберегти';

$_['text_queue_panel'] = 'Черга фонових завдань';
$_['text_queue_manage'] = 'Керування чергою';
$_['text_queue_pending'] = 'Очікує';
$_['text_queue_processing'] = 'Обробляється';
$_['text_queue_done'] = 'Виконано';
$_['text_queue_failed'] = 'Помилка';
$_['text_queue_search'] = 'Пошук за кодом або маршрутом';
$_['text_queue_filter'] = 'Фільтр';
$_['text_queue_attempts'] = 'Спроби';
$_['text_queue_available'] = 'Доступно з';
$_['text_queue_error'] = 'Остання помилка';

$_['text_cron_commands'] = 'Команди запуску CRON';
$_['text_cron_help'] = 'Для автоматичного запуску рекомендовано CLI. HTTPS-команда містить секретний ключ — не публікуйте її.';
$_['text_cron_cli'] = 'CLI-команда';
$_['text_cron_web'] = 'HTTPS Web Cron-команда';
$_['text_cron_copy'] = 'Копіювати';

$_['text_queue_run'] = 'Запустити чергу';
$_['text_queue_retry'] = 'Повторити';
$_['text_queue_confirm_delete'] = 'Видалити завдання з черги?';

$_['text_system_task'] = 'Системне';

$_['text_queue_all'] = 'Усі статуси';


$_['text_queue_run_result'] = 'Черга: оброблено %d, помилок %d';
$_['error_queue_failed'] = 'Черга завершилася з помилками: оброблено %d, помилок %d';

$_['column_source'] = 'Джерело';
$_['text_source_cli'] = 'CLI (вручну/без позначки)';
$_['text_source_scheduled'] = 'CLI (за розкладом)';
$_['text_source_http'] = 'HTTPS Web Cron';
$_['text_source_unknown'] = 'Не визначено';

$_['text_queue_locked'] = 'Заблоковано з';
$_['text_queue_cleanup'] = 'Очистити завершені старші за 7 днів';
$_['text_queue_cleanup_confirm'] = 'Видалити завершені завдання старші за 7 днів?';

$_['text_monitor_history'] = 'Історія запусків';

$_['text_monitor_history_hint'] = 'Натисніть, щоб розгорнути або згорнути';

$_['text_currency_refresh_success'] = 'Курси валют успішно оновлено';
$_['text_interval_quantity'] = 'Кількість';
$_['text_interval_unit'] = 'Одиниця часу';
$_['text_interval_minutes'] = 'Хвилини';
$_['text_interval_hours'] = 'Години';
$_['text_interval_days'] = 'Дні';
$_['text_interval_weeks'] = 'Тижні';
$_['text_interval_seconds'] = 'сек.';
$_['text_interval_invalid'] = 'Недопустимий інтервал (60–31536000 сек.)';
$_['text_interval_apply'] = 'Вставити в поле';

$_['text_interval_years'] = 'Роки';
$_['text_interval_months'] = 'Місяці';
$_['text_interval_assumptions'] = '1 рік = 365 днів; 1 місяць = 30 днів.';

$_['text_duration_year'] = 'р.';
$_['text_duration_month'] = 'міс.';
$_['text_duration_week'] = 'тиж.';
$_['text_duration_day'] = 'д.';
$_['text_duration_hour'] = 'год';
$_['text_duration_minute'] = 'хв';
$_['text_duration_second'] = 'с';
$_['text_cron_curl_help'] = 'Параметри curl: -sS показує помилки, -i виводить HTTP-заголовки, -L дозволяє переспрямування (не більше 3), --max-time 120 обмежує час запиту 120 секундами. Тіло відповіді при HTTP-помилці залишається видимим.';

$_['text_cron_key_rotate'] = 'Згенерувати новий секретний ключ';
$_['text_cron_key_confirm'] = 'Замінити секретний ключ HTTPS Web Cron? Старі URL та команди перестануть працювати.';
$_['text_cron_key_rotated'] = 'Секретний ключ HTTPS Web Cron замінено. Скопіюйте оновлену команду.';

$_['text_schedule_mode'] = 'Режим розкладу';
$_['text_schedule_interval'] = 'Інтервал';
$_['text_schedule_cron'] = 'Cron-вираз';
$_['text_cron_expression'] = 'Розклад Cron (5 полів)';
$_['text_cron_preview'] = 'Показати найближчі запуски';
$_['text_cron_example'] = 'Приклад: 0 3 * * * — щодня о 03:00 за часом бази даних.';

$_['text_cron_preview_failed'] = 'Не вдалося отримати попередній перегляд';

$_['text_task_history'] = 'Історія завдання';
$_['text_task_manual'] = 'Ручний запуск';
$_['text_task_scheduled'] = 'За розкладом';
$_['text_task_history_empty'] = 'Запусків поки немає';
$_['text_run_status_success'] = 'Успішно';
$_['text_run_status_error'] = 'Помилка';
$_['text_run_status_warning'] = 'Попередження';
$_['text_run_status_running'] = 'Виконується';
$_['text_run_status_locked'] = 'Заблоковано';
$_['text_run_status_pending'] = 'Очікує';

$_['text_cron_handler_hint'] = 'Виберіть доступний обробник із підказок або введіть маршрут cron/... вручну.';

$_['text_cron_required_error'] = 'Заповніть поле коректним значенням.';
