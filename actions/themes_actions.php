<?php
session_start();
header('Content-Type: application/json');

// Verificar autenticação
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Não autenticado']);
    exit;
}

try {
    $db = new PDO('sqlite:../ibo_panel.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        $theme_id = $_POST['theme_id'] ?? '';
        
        if ($action === 'update' && $theme_id) {
            // --- ATUALIZAÇÃO AQUI: Mapear tema 3 para seu respectivo layout ---
            $layout_map = [
                1 => 'activity_main',
                2 => 'activity_main_v2',
                3 => 'activity_main_v3' // Adicionado novo layout
            ];
            
            $layout_name = $layout_map[$theme_id] ?? 'activity_main';
            
            // LÓGICA DE ATUALIZAÇÃO ÚNICA:
            // Tentamos atualizar o registro ID 1 (o registro principal do sistema)
            $stmt = $db->prepare("UPDATE themes SET theme_id = ?, layout_name = ?, is_active = 1 WHERE id = 1");
            $stmt->execute([$theme_id, $layout_name]);

            // Caso o banco esteja vazio (primeira execução), inserimos o registro 1
            if ($stmt->rowCount() === 0) {
                $check = $db->query("SELECT id FROM themes WHERE id = 1");
                if (!$check->fetch()) {
                    $insert = $db->prepare("INSERT INTO themes (id, theme_id, layout_name, is_active) VALUES (1, ?, ?, 1)");
                    $insert->execute([$theme_id, $layout_name]);
                }
            }
            
            // Garantir que nenhum outro tema esteja ativo
            $db->prepare("UPDATE themes SET is_active = 0 WHERE id != 1")->execute();
            
            echo json_encode([
                'success' => true,
                'message' => 'Configurações de tema atualizadas!',
                'theme_id' => $theme_id,
                'layout_name' => $layout_name
            ]);
            
        } else {
            echo json_encode(['success' => false, 'message' => 'Ação ou ID inválido']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro no banco: ' . $e->getMessage()]);
}
?>