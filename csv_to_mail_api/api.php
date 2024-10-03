<?php
require_once 'config.php';
require_once 'csv_uploader.php';
require_once 'mailing_queue.php';
require_once 'utils.php';

$csvUploader = new CSVUploader();
$mailingQueue = new MailingQueue();

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

switch ("$method $path") {
    // Обработка запроса на загрузку CSV-файла
    case 'POST /upload-csv':
        try {
            $result = $csvUploader->upload();
            sendJsonResponse(['message' => 'CSV успешно загружен']);
        } catch (Exception $e) {
            sendJsonResponse(['error' => $e->getMessage()], 500);
        }
        break;

    // Обработка запроса на создание новой кампании
    case 'POST /campaigns':
        $data = getRequestBody();
        validateRequiredParams($data, ['name']);
        $campaignId = $mailingQueue->createCampaign($data['name']);
        sendJsonResponse(['id' => $campaignId, 'message' => 'Кампания успешно создана']);
        break;

    // Обработка запроса на запуск кампании
    case 'POST /campaigns/start':
        $data = getRequestBody();
        validateRequiredParams($data, ['campaign_id']);
        $mailingQueue->updateCampaignStatus($data['campaign_id'], 'running');
        sendJsonResponse(['message' => 'Кампания успешно запущена']);
        break;

    // Обработка запроса на остановку кампании
    case 'POST /campaigns/stop':
        $data = getRequestBody();
        validateRequiredParams($data, ['campaign_id']);
        $mailingQueue->updateCampaignStatus($data['campaign_id'], 'stopped');
        sendJsonResponse(['message' => 'Кампания успешно остановлена']);
        break;

    // Обработка запроса на возобновление кампании
    case 'POST /campaigns/resume':
        $data = getRequestBody();
        validateRequiredParams($data, ['campaign_id']);
        $mailingQueue->updateCampaignStatus($data['campaign_id'], 'running');
        sendJsonResponse(['message' => 'Кампания успешно возобновлена']);
        break;

    // Обработка запроса на получение информации о кампании
    case 'GET /campaigns':
        $campaignId = $_GET['id'] ?? null;
        if ($campaignId) {
            $campaign = $mailingQueue->getCampaign($campaignId);
            if ($campaign) {
                sendJsonResponse($campaign);
            } else {
                sendJsonResponse(['error' => 'Кампания не найдена'], 404);
            }
        } else {
            sendJsonResponse(['error' => 'Требуется указать ID кампании'], 400);
        }
        break;

    default:
        sendJsonResponse(['error' => 'Не найдено'], 404);
        break;
}
