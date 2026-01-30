<?php
session_start();
header('Content-Type: application/json');

// Verificar se o usuário está logado (se necessário)
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit();
}

try {
    // Ajusta o caminho da base de dados se necessário
    $db = new PDO('sqlite:../ibo_panel.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? 'view';

    if ($action === 'view') {
        $stmt = $db->query("SELECT * FROM dns ORDER BY id ASC");
        $dnsRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $dnsRecords]);

    } elseif ($action === 'add') {
        $title = trim($input['title']);
        $url = trim($input['url']);
    
        if (empty($title) || empty($url)) {
            echo json_encode(['success' => false, 'message' => 'Título e URL são obrigatórios']);
            exit();
        }
    
        $stmt = $db->prepare("INSERT INTO dns (title, url) VALUES (:title, :url)");
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':url', $url);
        $stmt->execute();
    
        echo json_encode(['success' => true, 'message' => 'DNS adicionado', 'new_id' => $db->lastInsertId()]);

    } elseif ($action === 'edit') {
        $id = intval($input['id']);
        $title = trim($input['title']);
        $url = trim($input['url']);

        if (empty($title) || empty($url)) {
            echo json_encode(['success' => false, 'message' => 'Título e URL são obrigatórios']);
            exit();
        }

        // Verificar se o DNS existe
        $check = $db->prepare("SELECT COUNT(*) FROM dns WHERE id = :id");
        $check->bindParam(':id', $id);
        $check->execute();
        
        if ($check->fetchColumn() == 0) {
            echo json_encode(['success' => false, 'message' => 'DNS não encontrado']);
            exit();
        }

        $stmt = $db->prepare("UPDATE dns SET title = :title, url = :url WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':url', $url);
        $stmt->execute();

        echo json_encode(['success' => true, 'message' => 'DNS atualizado']);

    } elseif ($action === 'delete') {
        $id = intval($input['id']);

        // 🛡️ VERIFICAR SE É O DNS PRINCIPAL (ID = 1) 🛡️
        if ($id === 1) {
            echo json_encode([
                'success' => false, 
                'message' => 'O DNS Principal (ID 1) não pode ser apagado!'
            ]);
            exit();
        }

        // Verificar se o DNS existe
        $check = $db->prepare("SELECT COUNT(*) FROM dns WHERE id = :id");
        $check->bindParam(':id', $id);
        $check->execute();
        
        if ($check->fetchColumn() == 0) {
            echo json_encode(['success' => false, 'message' => 'DNS não encontrado']);
            exit();
        }

        // 1. Elimina o registo
        $stmt = $db->prepare("DELETE FROM dns WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        // 2. Reajusta a sequência do SQLite (o segredo para o ID não saltar)
        $stmtMax = $db->query("SELECT MAX(id) as max_id FROM dns");
        $row = $stmtMax->fetch(PDO::FETCH_ASSOC);
        $newSeq = ($row['max_id'] !== null) ? $row['max_id'] : 0;

        $stmtSeq = $db->prepare("UPDATE sqlite_sequence SET seq = :new_seq WHERE name = 'dns'");
        $stmtSeq->bindParam(':new_seq', $newSeq);
        $stmtSeq->execute();

        echo json_encode(['success' => true, 'message' => 'DNS eliminado e ID ajustado']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Ação inválida']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
}