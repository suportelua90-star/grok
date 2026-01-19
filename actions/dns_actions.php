<?php
session_start();

header('Content-Type: application/json');

try {
    $db = new PDO('sqlite:../ibo_panel.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    ini_set("log_errors", 1);
    ini_set("error_log", "error_log.txt");

    $input = json_decode(file_get_contents('php://input'), true); // JSON veriyi alın
    $action = $input['action'] ?? 'view';

    if ($action === 'view') {
        $stmt = $db->query("SELECT * FROM dns");
        $dnsRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'data' => $dnsRecords]);
    } elseif ($action === 'add') {
        $title = trim($input['title']);
        $url = trim($input['url']);
    
        if (empty($title) || empty($url)) {
            echo json_encode(['success' => false, 'message' => 'Title and URL are required']);
            exit();
        }
    
        $stmt = $db->prepare("INSERT INTO dns (title, url) VALUES (:title, :url)");
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':url', $url);
        $stmt->execute();
    
        $newId = $db->lastInsertId(); // Yeni eklenen kaydın ID'sini al
    
        echo json_encode(['success' => true, 'message' => 'DNS added successfully', 'new_id' => $newId]);
    } elseif ($action === 'edit') {
        $id = $input['id'];
        $title = trim($input['title']);
        $url = trim($input['url']);

        $stmt = $db->prepare("UPDATE dns SET title = :title, url = :url WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':url', $url);
        $stmt->execute();

        echo json_encode(['success' => true, 'message' => 'DNS updated successfully']);
    } elseif ($action === 'delete') {
        $id = $input['id'];

        $stmt = $db->prepare("DELETE FROM dns WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        echo json_encode(['success' => true, 'message' => 'DNS deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
