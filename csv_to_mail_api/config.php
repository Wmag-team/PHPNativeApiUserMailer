<?php
// Конфигурация базы данных
define('DB_HOST', getenv('postgres'));
define('DB_PORT', getenv('5432'));
define('DB_USER', getenv('user2'));
define('DB_PASSWORD', getenv('password2'));
define('DB_NAME', getenv('csv_to_mail_api_db2'));

// Путь к CSV файлу
define('CSV_FILE_PATH', './csv.csv');

// Настройки рассылки
define('MAILING_BATCH_SIZE', 10);
define('MAILING_SLEEP_TIME', 1); // Время ожидания между партиями в секундах
