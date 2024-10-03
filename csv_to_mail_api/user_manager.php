<?php
require_once 'database.php';

class UserManager {
    private $db;

    // Конструктор класса UserManager
    public function __construct() {
        $this->db = Database::getInstance();
    }

    // Метод для добавления или обновления пользователя
    public function addUser($phone, $name) {
        $sql = "INSERT INTO users (phone, name) VALUES (?, ?) ON CONFLICT (phone) DO UPDATE SET name = EXCLUDED.name";
        $this->db->query($sql, [$phone, $name]);
    }

    // Метод для получения всех пользователей
    public function getAllUsers() {
        $sql = "SELECT * FROM users";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // Метод для получения пользователя по ID
    public function getUserById($id) {
        $sql = "SELECT * FROM users WHERE id = ?";
        return $this->db->query($sql, [$id])->fetch(PDO::FETCH_ASSOC);
    }
}
