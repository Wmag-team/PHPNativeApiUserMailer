<?php
require_once 'config.php';
require_once 'utils.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Логирование ошибок в файл
ini_set('log_errors', 1);
ini_set('error_log', '/tmp/php-error.log');

// Логирование доступа
error_log("Получен запрос: " . $_SERVER['REQUEST_METHOD'] . " " . $_SERVER['REQUEST_URI']);

// Обработка запроса
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

error_log("Обработка запроса: $method $path");

switch ("$method $path") {
    case 'GET /':
        error_log("Доступ к корневому URL");
        sendJsonResponse(['message' => 'API работает']);
        break;
    default:
        error_log("Перенаправление запроса в api.php");
        require_once 'api.php';
        break;
}
