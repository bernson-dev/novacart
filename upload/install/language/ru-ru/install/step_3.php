<?php
// *	@source		See SOURCE.txt for source and other copyright.
// *	@license	GNU General Public License version 3; see LICENSE.txt

// Heading
$_['heading_title'] = 'Конфигурация БД';

// Text
$_['text_step_3'] = 'Настройка подключения к БД и входа в админ-панель';
$_['text_db_connection'] = '1. Введите настройки для подключения к БД.';
$_['text_db_administration'] = '2. Введите логин, пароль и e-mail администратора.';
$_['text_mysqli'] = 'MySQLi';
$_['text_pdo'] = 'PDO';
$_['text_pgsql'] = 'PostgreSQL';
$_['text_dump_select'] = '3. Выберите SQL дамп';
$_['text_dump'] = 'SQL дамп';
$_['text_repair_schema'] = 'Привести структуру базы к эталону после импорта дампа';

// Entry
$_['entry_db_driver'] = 'Драйвер БД';
$_['entry_db_hostname'] = 'Хост БД';
$_['entry_db_username'] = 'Пользователь БД';
$_['entry_db_password'] = 'Пароль БД';
$_['entry_db_database'] = 'Имя БД';
$_['entry_db_port'] = 'Порт БД';
$_['entry_db_prefix'] = 'Префикс таблиц БД';
$_['entry_username'] = 'Логин';
$_['entry_password'] = 'Пароль';
$_['entry_email'] = 'E-Mail';
$_['entry_repair_schema'] = 'Структура БД';

$_['help_repair_schema'] = 'Если дамп старый или неполный, инсталлятор создаст отсутствующие таблицы, колонки и индексы. Существующие данные не удаляются.';

// Error
$_['error_db_driver'] = 'Необходимо указать драйвер БД!';
$_['error_db_hostname'] = 'Необходимо указать сервер БД!';
$_['error_db_username'] = 'Необходимо указать логин!';
$_['error_db_database'] = 'Необходимо указать название базы данных!';
$_['error_db_port'] = 'Необходимо указать порт БД!';
$_['error_db_prefix'] = 'Префикс может содержать следующие символы: a-z в нижнем регистре, цифры 0-9 и символ подчеркивания';
$_['error_db_connect'] = 'Ошибка: Не удалось подключиться к базе данных. Убедитесь, что сервер базы данных, логин и пароль указаны правильно!';
$_['error_username'] = 'Необходимо указать логин!';
$_['error_password'] = 'Необходимо указать пароль!';
$_['error_email'] = 'Необходимо указать корректный E-Mail адрес!';
$_['error_config'] = 'Ошибка: Не удалось записать в config.php, проверьте, правильно ли вы установили разрешения: ';
