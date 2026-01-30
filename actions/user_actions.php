<?php
session_start();
header('Content-Type: application/json');

try {
    $db = new PDO('sqlite:../ibo_panel.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? 'view';

    if ($action === 'view') {
        $stmt = $db->query("SELECT id, username FROM users"); // Yalnızca gerekli alanları alıyoruz
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'data' => $users]);
    } elseif ($action === 'edit') {
        $id = $input['id'];
        $username = trim($input['username']);
        $password = trim($input['password']);

        if (empty($username) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Username and Password are required']);
            exit();
        }

        $stmt = $db->prepare("UPDATE users SET username = :username, password = :password WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password);
        $stmt->execute();

        echo json_encode(['success' => true, 'message' => 'User updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>