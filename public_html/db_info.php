<?php
// Hata raporlamayı aç
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Veritabanı bağlantı bilgileri
$host = 'localhost';
$user = 'nizameddinalyaprak';
$password = '8Gy#KuMi';

try {
    // MySQL sunucusuna bağlan (belirli bir veritabanı seçmiyoruz)
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $schemasToInspect = ['information_schema'];
    $result = [];

    foreach ($schemasToInspect as $schema) {
        $schemaInfo = [];
        
        // Tabloları getir
        $stmt = $pdo->prepare("SELECT TABLE_NAME, TABLE_TYPE, TABLE_COMMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = ?");
        $stmt->execute([$schema]);
        $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($tables as $table) {
            $tableName = $table['TABLE_NAME'];
            
            // Sütunları getir
            $colStmt = $pdo->prepare("SELECT COLUMN_NAME, DATA_TYPE, COLUMN_TYPE, COLUMN_COMMENT FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?");
            $colStmt->execute([$schema, $tableName]);
            $columns = $colStmt->fetchAll(PDO::FETCH_ASSOC);
            
            $schemaInfo[$tableName] = [
                'type' => $table['TABLE_TYPE'],
                'comment' => $table['TABLE_COMMENT'],
                'columns' => $columns
            ];
        }
        $result[$schema] = $schemaInfo;
    }

    // JSON olarak çıktı ver
    header('Content-Type: application/json');
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Connection failed: ' . $e->getMessage()]);
}
?>
