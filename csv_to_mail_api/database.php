<?php
require_once 'config.php';

class Database {
    private static $instance = null;
    private $connection;

    // Конструктор класса Database
    private function __construct() {
        $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";user=" . DB_USER . ";password=" . DB_PASSWORD;
        
        try {
            $this->connection = new PDO($dsn);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Ошибка подключения: " . $e->getMessage());
        }
    }

    // Метод для получения экземпляра класса Database (паттерн Singleton)
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Метод для получения соединения с базой данных
    public function getConnection() {
        return $this->connection;
    }

    // Метод для выполнения SQL-запроса с параметрами
    public function query($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    // Метод для получения ID последней вставленной записи
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }

    // Метод для начала транзакции
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }

    // Метод для подтверждения транзакции
    public function commit() {
        return $this->connection->commit();
    }

    // Метод для отмены транзакции
    public function rollBack() {
        return $this->connection->rollBack();
    }
}

// Функция для инициализации таблиц базы данных
function initializeDatabaseTables() {
    $db = Database::getInstance();

    // Создание таблицы пользователей
    $db->query("
        CREATE TABLE IF NOT EXISTS users (
            id SERIAL PRIMARY KEY,
            phone VARCHAR(15) UNIQUE NOT NULL,
            name VARCHAR(255) NOT NULL
        )
    ");

    // Создание таблицы очереди рассылки
    $db->query("
        CREATE TABLE IF NOT EXISTS mailing_queue (
            id SERIAL PRIMARY KEY,
            campaign_id INTEGER NOT NULL,
            user_id INTEGER NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            sent_at TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )
    ");

    // Создание таблицы кампаний рассылки
    $db->query("
        CREATE TABLE IF NOT EXISTS mailing_campaigns (
            id SERIAL PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'created',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
}

// Вызов функции для обеспечения создания таблиц
initializeDatabaseTables();
