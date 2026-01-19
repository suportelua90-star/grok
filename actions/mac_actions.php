<?php
session_start();
header('Content-Type: application/json');

try {
    $db = new PDO('sqlite:../ibo_panel.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? 'view';

    if ($action === 'view') {
        $stmt = $db->query("SELECT * FROM playlist");
        $playlistRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $playlistRecords]);
    } elseif ($action === 'get_dns_options') {
        $stmt = $db->query("SELECT id, title FROM dns");
        $dnsOptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $dnsOptions]);
    } elseif ($action === 'add' || $action === 'edit') {
        $id = ($action === 'edit') ? trim($input['id'] ?? '') : null;
        $mac_address = trim($input['mac_address'] ?? '');
        $pin = trim($input['pin'] ?? '0000');

        // Validação básica
        if (empty($mac_address)) {
            echo json_encode(['success' => false, 'message' => 'MAC Address é obrigatório']);
            exit();
        }

        // Servidores (agora aceita múltiplos)
        $servers = $input['servers'] ?? [];
        if (empty($servers) || !is_array($servers)) {
            echo json_encode(['success' => false, 'message' => 'Adicione pelo menos um servidor']);
            exit();
        }

        foreach ($servers as $server) {
            $dns_id    = trim($server['dns_id'] ?? '');
            $username  = trim($server['username'] ?? '');
            $password  = trim($server['password'] ?? '');

            if (empty($dns_id) || empty($username) || empty($password)) {
                echo json_encode(['success' => false, 'message' => 'Todos os servidores precisam de DNS, Username e Senha']);
                exit();
            }

            if ($action === 'add') {
                $stmt = $db->prepare("
                    INSERT INTO playlist (dns_id, mac_address, username, password, pin)
                    VALUES (:dns_id, :mac_address, :username, :password, :pin)
                ");
                $stmt->execute([
                    ':dns_id'     => $dns_id,
                    ':mac_address' => $mac_address,
                    ':username'   => $username,
                    ':password'   => $password,
                    ':pin'        => $pin
                ]);
            } else { // edit
                $stmt = $db->prepare("
                    UPDATE playlist 
                    SET dns_id = :dns_id, mac_address = :mac_address, username = :username, 
                        password = :password, pin = :pin 
                    WHERE id = :id
                ");
                $stmt->execute([
                    ':dns_id'     => $dns_id,
                    ':mac_address' => $mac_address,
                    ':username'   => $username,
                    ':password'   => $password,
                    ':pin'        => $pin,
                    ':id'         => $id
                ]);
            }
        }

        $message = ($action === 'add') ? 'Playlist(s) adicionada(s) com sucesso' : 'Playlist atualizada com sucesso';
        echo json_encode(['success' => true, 'message' => $message]);
    } elseif ($action === 'delete') {
        $id = trim($input['id'] ?? '');
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'ID não informado']);
            exit();
        }
        $stmt = $db->prepare("DELETE FROM playlist WHERE id = :id");
        $stmt->execute([':id' => $id]);
        echo json_encode(['success' => true, 'message' => 'Playlist excluída com sucesso']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Ação inválida']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro no banco de dados: ' . $e->getMessage()]);
}
?>