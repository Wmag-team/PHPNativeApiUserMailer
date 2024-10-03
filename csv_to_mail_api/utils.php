<?php
// Функция для отправки JSON-ответа
function sendJsonResponse($data, $statusCode = 200) {
    if (!headers_sent()) {
        header('Content-Type: application/json');
        http_response_code($statusCode);
    }
    echo json_encode($data);
    exit;
}

// Функция для получения тела запроса в формате JSON
function getRequestBody() {
    return json_decode(file_get_contents('php://input'), true);
}

// Функция для проверки наличия обязательных параметров в запросе
function validateRequiredParams($params, $requiredKeys) {
    foreach ($requiredKeys as $key) {
        if (!isset($params[$key]) || empty($params[$key])) {
            sendJsonResponse(['error' => "Отсутствует обязательный параметр: $key"], 400);
        }
    }
}
