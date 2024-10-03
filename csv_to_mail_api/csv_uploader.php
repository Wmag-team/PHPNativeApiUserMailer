<?php
require_once 'config.php';
require_once 'user_manager.php';

class CSVUploader {
    private $userManager;

    // Конструктор класса CSVUploader
    public function __construct() {
        $this->userManager = new UserManager();
    }

    // Метод для загрузки и обработки CSV-файла
    public function upload() {
        if (!file_exists(CSV_FILE_PATH)) {
            throw new Exception("CSV файл не найден");
        }

        $file = fopen(CSV_FILE_PATH, 'r');
        if (!$file) {
            throw new Exception("Не удалось открыть CSV файл");
        }

        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            while (($data = fgetcsv($file, 0, ';')) !== FALSE) {
                if (count($data) !== 2) {
                    continue; // Пропуск некорректных строк
                }
                $phone = trim($data[0]);
                $name = trim($data[1]);
                $this->userManager->addUser($phone, $name);
            }
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            fclose($file);
            throw $e;
        }

        fclose($file);
        return true;
    }
}
