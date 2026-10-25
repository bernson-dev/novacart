<?php
$_['heading_title'] = 'Планировщик заданий';
$_['text_home'] = 'Главная';
$_['text_no_results'] = 'Заданий нет';
$_['text_enabled'] = 'Включено';
$_['text_disabled'] = 'Отключено';
$_['text_success'] = 'Статус задания обновлён';
$_['text_cli'] = 'Для запуска используйте системный cron: php cli.php jobs:run. Интерфейс показывает состояние заданий, но не запускает их самостоятельно.';
$_['column_code'] = 'Код'; $_['column_route'] = 'Маршрут'; $_['column_interval'] = 'Интервал';
$_['column_status'] = 'Статус'; $_['column_last'] = 'Последний запуск';
$_['column_next'] = 'Следующий запуск'; $_['column_result'] = 'Результат';
$_['column_message'] = 'Сообщение'; $_['column_action'] = 'Действие';
$_['button_enable'] = 'Включить'; $_['button_disable'] = 'Отключить';
$_['error_permission'] = 'Недостаточно прав для изменения заданий';
$_['error_action'] = 'Некорректные параметры запроса';

$_['text_monitor'] = 'Мониторинг запусков';
$_['text_monitor_empty'] = 'История запусков отсутствует';
$_['text_heartbeat_ok'] = 'Автоматический запуск работает';
$_['text_heartbeat_late'] = 'Нет запусков более пяти минут';
$_['text_heartbeat_missing'] = 'Запуски ещё не зарегистрированы';
$_['column_started'] = 'Начало';
$_['column_finished'] = 'Завершение';
$_['column_duration'] = 'Длительность';
$_['column_scheduler'] = 'Scheduler (обработано/ошибок)';
$_['column_queue'] = 'Queue (обработано/ошибок)';
$_['column_run_status'] = 'Результат';
$_['column_run_error'] = 'Ошибка';

$_['text_heartbeat_error'] = 'Последний запуск завершился с ошибкой';
$_['text_heartbeat_running'] = 'Задание выполняется';

$_['text_action_run'] = 'Запустить сейчас';
$_['text_action_edit'] = 'Редактировать';
$_['text_action_delete'] = 'Удалить';
$_['text_action_confirm'] = 'Удалить задание?';
$_['text_action_add'] = 'Добавить задание';
$_['text_action_code'] = 'Код задания';
$_['text_action_route'] = 'Внутренний маршрут';
$_['text_action_interval'] = 'Интервал, секунд';
$_['text_action_args'] = 'Параметры JSON';
$_['text_action_cancel'] = 'Отмена';
$_['text_action_save'] = 'Сохранить';

$_['text_queue_panel'] = 'Очередь фоновых задач';
$_['text_queue_manage'] = 'Управление очередью';
$_['text_queue_pending'] = 'В ожидании';
$_['text_queue_processing'] = 'В обработке';
$_['text_queue_done'] = 'Выполнено';
$_['text_queue_failed'] = 'Ошибка';
$_['text_queue_search'] = 'Поиск по коду или маршруту';
$_['text_queue_filter'] = 'Фильтр';
$_['text_queue_attempts'] = 'Попытки';
$_['text_queue_available'] = 'Доступно с';
$_['text_queue_error'] = 'Последняя ошибка';

$_['text_cron_commands'] = 'Команды запуска CRON';
$_['text_cron_help'] = 'Для автоматического запуска рекомендуется CLI. HTTPS-команда содержит секретный ключ — не публикуйте её.';
$_['text_cron_cli'] = 'CLI-команда';
$_['text_cron_web'] = 'HTTPS Web Cron-команда';
$_['text_cron_copy'] = 'Копировать';

$_['text_queue_run'] = 'Запустить очередь';
$_['text_queue_retry'] = 'Повторить';
$_['text_queue_confirm_delete'] = 'Удалить задание из очереди?';

$_['text_system_task'] = 'Системное';

$_['text_queue_all'] = 'Все статусы';


$_['text_queue_run_result'] = 'Очередь: обработано %d, ошибок %d';
$_['error_queue_failed'] = 'Очередь завершена с ошибками: обработано %d, ошибок %d';

$_['column_source'] = 'Источник';
$_['text_source_cli'] = 'CLI (вручную/без метки)';
$_['text_source_scheduled'] = 'CLI (по расписанию)';
$_['text_source_http'] = 'HTTPS Web Cron';
$_['text_source_unknown'] = 'Не определён';

$_['text_queue_locked'] = 'Заблокировано с';
$_['text_queue_cleanup'] = 'Очистить завершённые старше 7 дней';
$_['text_queue_cleanup_confirm'] = 'Удалить завершённые задания старше 7 дней?';

$_['text_monitor_history'] = 'История запусков';

$_['text_monitor_history_hint'] = 'Нажмите, чтобы раскрыть или свернуть';

$_['text_currency_refresh_success'] = 'Курсы валют успешно обновлены';
$_['text_interval_quantity'] = 'Количество';
$_['text_interval_unit'] = 'Единица времени';
$_['text_interval_minutes'] = 'Минуты';
$_['text_interval_hours'] = 'Часы';
$_['text_interval_days'] = 'Дни';
$_['text_interval_weeks'] = 'Недели';
$_['text_interval_seconds'] = 'сек.';
$_['text_interval_invalid'] = 'Недопустимый интервал (60–31536000 сек.)';
$_['text_interval_apply'] = 'Вставить в поле';

$_['text_interval_years'] = 'Годы';
$_['text_interval_months'] = 'Месяцы';
$_['text_interval_assumptions'] = '1 год = 365 дней; 1 месяц = 30 дней.';

$_['text_duration_year'] = 'г.';
$_['text_duration_month'] = 'мес.';
$_['text_duration_week'] = 'нед.';
$_['text_duration_day'] = 'д.';
$_['text_duration_hour'] = 'ч';
$_['text_duration_minute'] = 'мин';
$_['text_duration_second'] = 'с';
$_['text_cron_curl_help'] = 'Параметры curl: -sS показывает ошибки, -i выводит HTTP-заголовки, -L разрешает перенаправления (не более 3), --max-time 120 ограничивает время запроса 120 секундами. Тело ответа при HTTP-ошибке сохраняется для диагностики.';

$_['text_cron_key_rotate'] = 'Сгенерировать новый секретный ключ';
$_['text_cron_key_confirm'] = 'Заменить секретный ключ HTTPS Web Cron? Старые URL и команды перестанут работать.';
$_['text_cron_key_rotated'] = 'Секретный ключ HTTPS Web Cron заменён. Скопируйте обновлённую команду.';

$_['text_schedule_mode'] = 'Режим расписания';
$_['text_schedule_interval'] = 'Интервал';
$_['text_schedule_cron'] = 'Cron-выражение';
$_['text_cron_expression'] = 'Расписание Cron (5 полей)';
$_['text_cron_preview'] = 'Показать ближайшие запуски';
$_['text_cron_example'] = 'Пример: 0 3 * * * — ежедневно в 03:00 по времени базы данных.';

$_['text_cron_preview_failed'] = 'Не удалось получить предпросмотр';
