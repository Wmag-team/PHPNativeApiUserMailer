<?php
require_once 'database.php';
require_once 'config.php';

class MailingQueue {
    private $db;

    // Конструктор класса MailingQueue
    public function __construct() {
        $this->db = Database::getInstance();
    }

    // Метод для создания новой кампании рассылки
    public function createCampaign($name) {
        $sql = "INSERT INTO mailing_campaigns (name) VALUES (?) RETURNING id";
        $result = $this->db->query($sql, [$name]);
        return $result->fetchColumn();
    }

    // Метод для получения информации о кампании по ID
    public function getCampaign($id) {
        $sql = "SELECT * FROM mailing_campaigns WHERE id = ?";
        return $this->db->query($sql, [$id])->fetch(PDO::FETCH_ASSOC);
    }

    // Метод для обновления статуса кампании
    public function updateCampaignStatus($id, $status) {
        $sql = "UPDATE mailing_campaigns SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        $this->db->query($sql, [$status, $id]);
    }

    // Метод для добавления пользователя в очередь рассылки
    public function addToQueue($campaignId, $userId) {
        $sql = "INSERT INTO mailing_queue (campaign_id, user_id) VALUES (?, ?) ON CONFLICT (campaign_id, user_id) DO NOTHING";
        $this->db->query($sql, [$campaignId, $userId]);
    }

    // Метод для получения следующей партии пользователей для рассылки
    public function getNextBatch($campaignId, $limit = MAILING_BATCH_SIZE) {
        $sql = "SELECT mq.id, mq.user_id, u.phone, u.name 
                FROM mailing_queue mq 
                JOIN users u ON mq.user_id = u.id 
                WHERE mq.campaign_id = ? AND mq.status = 'pending' 
                ORDER BY mq.created_at 
                LIMIT ?";
        return $this->db->query($sql, [$campaignId, $limit])->fetchAll(PDO::FETCH_ASSOC);
    }

    // Метод для отметки сообщения как отправленного
    public function markAsSent($id) {
        $sql = "UPDATE mailing_queue SET status = 'sent', sent_at = CURRENT_TIMESTAMP WHERE id = ?";
        $this->db->query($sql, [$id]);
    }

    // Метод для обработки кампании рассылки
    public function processCampaign($campaignId) {
        $campaign = $this->getCampaign($campaignId);
        if (!$campaign || $campaign['status'] !== 'running') {
            return;
        }

        while (true) {
            $batch = $this->getNextBatch($campaignId);
            if (empty($batch)) {
                $this->updateCampaignStatus($campaignId, 'completed');
                break;
            }

            foreach ($batch as $item) {
                $this->sendMessage($item['phone'], $item['name']);
                $this->markAsSent($item['id']);
            }

            sleep(MAILING_SLEEP_TIME);
        }
    }

    // Метод для отправки сообщения (заглушка)
    private function sendMessage($phone, $name) {
        // Фиктивный метод для отправки сообщений
        // В реальном сценарии здесь была бы интеграция с SMS-шлюзом или сервисом электронной почты
        error_log("Отправка сообщения пользователю $name на номер $phone");
    }
}
