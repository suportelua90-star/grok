<?php
session_start();
header('Content-Type: application/json');

try {
    $db = new PDO('sqlite:../ibo_panel.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? 'view';

    if ($action === 'view') {
        $stmt = $db->query("SELECT * FROM themes LIMIT 1");
        $theme = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $theme]);
    } elseif ($action === 'edit') {
        $theme_id = trim($input['theme_id']);

        if (empty($theme_id)) {
            echo json_encode(['success' => false, 'message' => 'Theme ID is required']);
            exit();
        }

        $theme_number = str_replace('theme_', '', $theme_id);

        if (!is_numeric($theme_number)) {
            echo json_encode(['success' => false, 'message' => 'Invalid Theme ID format']);
            exit();
        }

        $stmt = $db->prepare("UPDATE themes SET theme_id = :theme_id");
        $stmt->execute([':theme_id' => $theme_number]);

        echo json_encode(['success' => true, 'message' => 'Theme updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

?>
