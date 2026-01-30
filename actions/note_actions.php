<?php
session_start();
header('Content-Type: application/json');

try {
    $db = new PDO('sqlite:../ibo_panel.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? 'view';

    if ($action === 'view') {
        $stmt = $db->query("SELECT * FROM settings");
        $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'data' => $notes]);
    } elseif ($action === 'add') {
        $note_title = trim($input['note_title']);
        $note_content = trim($input['note_content']);
    
        if (empty($note_title) || empty($note_content)) {
            echo json_encode(['success' => false, 'message' => 'Title and Content are required']);
            exit();
        }
    
        $stmt = $db->prepare("INSERT INTO settings (note_title, note_content) VALUES (:note_title, :note_content)");
        $stmt->bindParam(':note_title', $note_title);
        $stmt->bindParam(':note_content', $note_content);
        $stmt->execute();
    
        $newId = $db->lastInsertId();
    
        echo json_encode(['success' => true, 'message' => 'Note added successfully', 'new_id' => $newId]);
    } elseif ($action === 'edit') {
        $id = $input['id'];
        $note_title = trim($input['note_title']);
        $note_content = trim($input['note_content']);

        $stmt = $db->prepare("UPDATE settings SET note_title = :note_title, note_content = :note_content WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':note_title', $note_title);
        $stmt->bindParam(':note_content', $note_content);
        $stmt->execute();

        echo json_encode(['success' => true, 'message' => 'Note updated successfully']);
    } elseif ($action === 'delete') {
        $id = $input['id'];

        $stmt = $db->prepare("DELETE FROM settings WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        echo json_encode(['success' => true, 'message' => 'Note deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
