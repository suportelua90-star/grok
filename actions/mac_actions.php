<?php
session_start();
header('Content-Type: application/json');

try {
    $db = new PDO('sqlite:../ibo_panel.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? 'view';
    
    // ============================================
    // VIEW - LISTAR PLAYLISTS
    // ============================================
    if ($action === 'view') {
        $filter_mac = $input['filter_mac'] ?? null;
        
        if ($filter_mac) {
            // Busca servidores por MAC específico
            $stmt = $db->prepare("SELECT * FROM playlist WHERE mac_address = :mac_address ORDER BY id ASC");
            $stmt->execute([':mac_address' => $filter_mac]);
            $playlistRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            // Busca todos os registros
            $stmt = $db->query("SELECT * FROM playlist ORDER BY mac_address, id ASC");
            $playlistRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        echo json_encode(['success' => true, 'data' => $playlistRecords]);
    
    // ============================================
    // GET DNS OPTIONS
    // ============================================
    } elseif ($action === 'get_dns_options') {
        $stmt = $db->query("SELECT id, title FROM dns");
        $dnsOptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $dnsOptions]);
    
    // ============================================
    // ADD - ADICIONAR NOVO MAC COM MÚLTIPLOS SERVIDORES
    // ============================================
    } elseif ($action === 'add') {
        $mac_address = trim($input['mac_address'] ?? '');
        $servers = $input['servers'] ?? [];
        
        if (empty($mac_address) || empty($servers) || !is_array($servers)) {
            echo json_encode(['success' => false, 'message' => 'MAC Address and at least one server are required']);
            exit();
        }
        
        try {
            $db->beginTransaction();
            $insertedCount = 0;
            
            foreach ($servers as $server) {
                $dns_id = trim($server['dns_id'] ?? '');
                $username = trim($server['username'] ?? '');
                $password = trim($server['password'] ?? '');
                $pin = trim($server['pin'] ?? '');
                
                if (empty($dns_id) || empty($username) || empty($password) || empty($pin)) {
                    throw new Exception('All server fields are required for each server');
                }
                
                $stmt = $db->prepare("INSERT INTO playlist (dns_id, mac_address, username, password, pin) VALUES (:dns_id, :mac_address, :username, :password, :pin)");
                $stmt->execute([
                    ':dns_id' => $dns_id,
                    ':mac_address' => $mac_address,
                    ':username' => $username,
                    ':password' => $password,
                    ':pin' => $pin,
                ]);
                $insertedCount++;
            }
            
            $db->commit();
            
            echo json_encode([
                'success' => true,
                'message' => "Successfully added $insertedCount server(s) for MAC $mac_address",
                'mac' => $mac_address,
                'count' => $insertedCount
            ]);
            
        } catch (Exception $e) {
            $db->rollBack();
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    
    // ============================================
    // EDIT - EDITAR MAC COM MÚLTIPLOS SERVIDORES
    // ============================================
    } elseif ($action === 'edit') {
        $mac_address = trim($input['mac_address'] ?? '');
        $servers = $input['servers'] ?? [];
        
        if (empty($mac_address) || empty($servers) || !is_array($servers)) {
            echo json_encode(['success' => false, 'message' => 'MAC Address and servers are required']);
            exit();
        }
        
        try {
            $db->beginTransaction();
            
            // 1. Deleta todos os servidores antigos deste MAC
            $stmt = $db->prepare("DELETE FROM playlist WHERE mac_address = :mac_address");
            $stmt->execute([':mac_address' => $mac_address]);
            
            // 2. Insere os novos servidores
            $insertedCount = 0;
            foreach ($servers as $server) {
                $dns_id = trim($server['dns_id'] ?? '');
                $username = trim($server['username'] ?? '');
                $password = trim($server['password'] ?? '');
                $pin = trim($server['pin'] ?? '');
                
                if (empty($dns_id) || empty($username) || empty($password) || empty($pin)) {
                    throw new Exception('All server fields are required for each server');
                }
                
                $stmt = $db->prepare("INSERT INTO playlist (dns_id, mac_address, username, password, pin) VALUES (:dns_id, :mac_address, :username, :password, :pin)");
                $stmt->execute([
                    ':dns_id' => $dns_id,
                    ':mac_address' => $mac_address,
                    ':username' => $username,
                    ':password' => $password,
                    ':pin' => $pin,
                ]);
                $insertedCount++;
            }
            
            $db->commit();
            
            echo json_encode([
                'success' => true,
                'message' => "Successfully updated $insertedCount server(s) for MAC $mac_address",
                'mac' => $mac_address,
                'count' => $insertedCount
            ]);
            
        } catch (Exception $e) {
            $db->rollBack();
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    
    // ============================================
    // DELETE - DELETAR
    // ============================================
    } elseif ($action === 'delete') {
        $id = $input['id'] ?? null;
        $mac_address = $input['mac_address'] ?? null;
        
        if ($id) {
            // Deleta um registro específico pelo ID
            $stmt = $db->prepare("DELETE FROM playlist WHERE id = :id");
            $stmt->execute([':id' => $id]);
            echo json_encode(['success' => true, 'message' => 'Playlist entry deleted successfully']);
        } elseif ($mac_address) {
            // Deleta TODOS os servidores deste MAC
            $stmt = $db->prepare("DELETE FROM playlist WHERE mac_address = :mac_address");
            $stmt->execute([':mac_address' => $mac_address]);
            echo json_encode(['success' => true, 'message' => "All servers for MAC $mac_address deleted successfully"]);
        } else {
            echo json_encode(['success' => false, 'message' => 'ID or MAC Address is required']);
        }
    
    // ============================================
    // AÇÃO INVÁLIDA
    // ============================================
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>