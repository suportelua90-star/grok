<?php

// Configura o cabeçalho para retorno JSON
header('Content-Type: application/json');

// --- INÍCIO DAS FUNÇÕES DE LOG E ERRO ---

/**
 * Salva informações de debug em um arquivo JSON.
 * @param string $step O passo do script.
 * @param array $data Dados relevantes para log.
 */
function debug_log($step, $data = []) {
    $log_file = __DIR__ . "/debug_log.json";
    $log_entry = [
        "timestamp" => date("Y-m-d H:i:s"),
        "step" => $step,
        "data" => $data
    ];
    $json_line = json_encode($log_entry) . "\n";
    // Usamos @ para suprimir erros caso o arquivo não possa ser criado/escrito
    @file_put_contents($log_file, $json_line, FILE_APPEND | LOCK_EX);
}

// Função para retornar JSON de erro e registrar o log
function die_with_log($message, $data = []) {
    debug_log("ERRO_FATAL", ["message" => $message, "details" => $data]);
    die(json_encode(["error" => $message]));
}

// --- FIM DAS FUNÇÕES DE LOG E ERRO ---

$db_file = __DIR__ . "/ibo_panel.db";

// Tenta obter o device_id de forma mais robusta (POST raw data)
$raw_post_data = file_get_contents('php://input');
$post_data_array = json_decode($raw_post_data, true);
$device_id = $_POST['device_id'] ?? ($post_data_array['device_id'] ?? null);


// =======================================================
// ⭐ BLOCO DE CORREÇÃO PARA FORMATAR O MAC ADDRESS ⭐
// 
// Objetivo: Receber algo como "50:31:c0:69:ff:59:ef:20" 
// e formatar para o padrão "50:31:C0:69:FF:59" (12 caracteres, maiúsculas, com :)
// =======================================================

if ($device_id) {
    // Salva o original para log
    $original_device_id = $device_id;
    
    // 1. Remove todos os separadores (:)
    $mac_clean = str_replace(':', '', $device_id);
    
    // 2. Converte para MAIÚSCULAS
    $mac_upper = strtoupper($mac_clean);
    
    // 3. Pega apenas os primeiros 12 caracteres (6 pares)
    $mac_12_chars = substr($mac_upper, 0, 12);
    
    // 4. Formata, inserindo : a cada 2 caracteres e removendo o último :
    $mac_formatted = rtrim(chunk_split($mac_12_chars, 2, ':'), ':');
    
    // Substitui o $device_id original pelo valor formatado
    $device_id = $mac_formatted;
    
    debug_log("MAC_FORMATADO", [
        "mac_original" => $original_device_id,
        "mac_formatado_final" => $device_id
    ]);
}

// =======================================================
// ⭐ FIM DO BLOCO DE CORREÇÃO ⭐
// =======================================================

debug_log("INICIO_EXECUCAO", [
    "db_path" => $db_file,
    "device_id_recebido" => $device_id,
    "metodo_requisicao" => $_SERVER['REQUEST_METHOD']
]);

if (!$device_id) {
    die_with_log("Device ID não fornecido. Verifique se está enviando via POST com a chave 'device_id'.", ["post" => $_POST, "raw_data" => $raw_post_data]);
}

try {
    $db = new PDO("sqlite:$db_file");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // =======================================================
    // BLOCO DE INICIALIZAÇÃO E GARANTIA DE ESTRUTURA DO BANCO
    // =======================================================
    
    // 1. Garante que a tabela 'dns' exista
    $db->exec("
        CREATE TABLE IF NOT EXISTS dns (
            id INTEGER PRIMARY KEY,
            url TEXT NOT NULL UNIQUE
        )
    ");

    // 2. Garante que a tabela 'chatbot_slc_max' exista
    $db->exec("
        CREATE TABLE IF NOT EXISTS chatbot_slc_max (
            id INTEGER PRIMARY KEY,
            chatbot_link TEXT NOT NULL,
            status TEXT NOT NULL DEFAULT 'Inativo',
            dns_id INTEGER NOT NULL
        )
    ");

    // 3. Garante que a tabela 'playlist' exista
    // Note que a definição inclui device_key e last_used
    $db->exec("
        CREATE TABLE IF NOT EXISTS playlist (
            id INTEGER PRIMARY KEY,
            dns_id INTEGER NOT NULL,
            mac_address TEXT NOT NULL,
            username TEXT,
            password TEXT,
            pin TEXT,
            device_key TEXT,
            link TEXT,
            last_used INTEGER DEFAULT (strftime('%s','now'))
        )
    ");

    // 4. Correção da estrutura: Adiciona colunas se elas faltarem na tabela playlist (para DBs antigos)
    $stmt = $db->query("PRAGMA table_info(playlist)");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN, 1); // Pega o nome das colunas
    
    // Lista de colunas a serem verificadas e corrigidas
    $required_columns = [
        'device_key' => 'TEXT', 
        'last_used' => 'INTEGER DEFAULT (strftime(\'%s\',\'now\'))'
    ];
    
    foreach ($required_columns as $col_name => $col_definition) {
        if (!in_array($col_name, $columns)) {
            // Adiciona a coluna com sua definição completa (tipo e default value)
            $db->exec("ALTER TABLE playlist ADD COLUMN $col_name $col_definition");
            debug_log("CORRECAO_DB", ["detalhe" => "Coluna '$col_name' adicionada à tabela 'playlist'."]);
        }
    }
    
    // =======================================================
    // FIM DO BLOCO DE GARANTIA DE ESTRUTURA
    // =======================================================

    // 1. Busca de Configurações
    $stmt = $db->prepare("
        SELECT c.chatbot_link, c.status, c.dns_id, d.url as dns_url 
        FROM chatbot_slc_max c 
        LEFT JOIN dns d ON c.dns_id = d.id 
        ORDER BY c.id DESC LIMIT 1
    ");
    $stmt->execute();
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    debug_log("CONFIG_BUSCA", ["config" => $config]);

    if (!$config) {
        die_with_log("Configurações do chatbot não encontradas. Verifique se as tabelas 'dns' e 'chatbot_slc_max' foram populadas.");
    }
    
    if ($config['status'] !== 'Ativo') {
        die_with_log("Chatbot está desativado no momento.", ["status" => $config['status']]);
    }
    
    $bot_url = $config['chatbot_link'];
    $dns_url = $config['dns_url'];
    
    if (empty($dns_url) && $config['dns_id'] > 0) { // Se dns_id > 0, mas dns_url está vazio, há problema de JOIN.
        die_with_log("DNS não configurada para o chatbot (DNS ID encontrado, mas URL ausente).");
    }
    
} catch (Exception $e) {
    die_with_log("Erro ao acessar banco de dados (Config/Init): " . $e->getMessage(), ["file" => $e->getFile(), "line" => $e->getLine()]);
}

// Monta e Envia o JSON para o chatbot
$json_to_chatbot = json_encode([
    "receiveMessageAppId" => "com.whatsapp",
    "receiveMessagePattern" => ["*"],
    "senderName" => "API DE TESTE",
    "groupName" => "",
    "senderMessage" => "api_cadastro",
    "messageDateTime" => time(),
    "isMessageFromGroup" => false
]);

debug_log("REQUISICAO_CHATBOT", ["url" => $bot_url, "payload" => $json_to_chatbot]);

$ch = curl_init($bot_url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $json_to_chatbot);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($json_to_chatbot)
]);

$response = curl_exec($ch);
if ($response === false) {
    $curl_error = curl_error($ch);
    curl_close($ch);
    die_with_log("Erro na requisição cURL para o chatbot: " . $curl_error);
}
curl_close($ch);

debug_log("RESPOSTA_CHATBOT", ["response_raw" => $response]);

$data = json_decode($response, true);
if (is_null($data)) {
    die_with_log("Resposta inválida do chatbot. Não foi possível decodificar o JSON.", ["response_raw" => $response]);
}

$username = $data['username'] ?? null;
$password = $data['password'] ?? null;
$payUrl   = $data['payUrl'] ?? null;

debug_log("DADOS_CHATBOT_DECODIFICADOS", ["username" => $username, "password" => $password, "payUrl" => $payUrl]);

if (empty($username) || empty($password)) {
    die_with_log("Usuário ou senha não retornados pelo chatbot.", ["data_recebida" => $data]);
}

$iptv_url = $dns_url . "/get.php?username=" . urlencode($username) . "&password=" . urlencode($password) . "&type=m3u_plus&output=ts";

try {
    // Reabre a conexão (embora já esteja aberta, é uma prática comum ou um vestígio do código original)
    $db = new PDO("sqlite:$db_file");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2. VERIFICAÇÃO DE TESTE EXISTENTE
    // A query agora usará o $device_id JÁ FORMATADO
    $stmtCheck = $db->prepare("SELECT * FROM playlist WHERE mac_address = :mac_address");
    $stmtCheck->execute([':mac_address' => $device_id]);
    $existing_record = $stmtCheck->fetch();

    debug_log("VERIFICACAO_TESTE_EXISTENTE", ["mac_address" => $device_id, "registro_encontrado" => (bool)$existing_record]);

    if ($existing_record) {
        debug_log("TESTE_JA_EXISTE", ["mac_address" => $device_id]);
        
        // Retorno original que o app recebe
        die(json_encode([
            "success" => false,
            "message" => "⚠️ Desculpe, você já fez o teste! Entre em contato com suporte."
        ]));
    }

    // 3. INSERÇÃO DO NOVO TESTE
    // A inserção também usará o $device_id JÁ FORMATADO
    $stmt = $db->prepare("
        INSERT INTO playlist (dns_id, mac_address, username, password, pin, device_key, link, last_used)
        VALUES (:dns_id, :mac_address, :username, :password, :pin, :device_key, :link, :last_used)
    ");

    $insert_params = [
        ':dns_id' => $config['dns_id'] ?? 1,
        ':mac_address' => $device_id,
        ':username' => $username,
        ':password' => $password,
        ':pin' => '0000',
        ':device_key' => null,
        ':link' => $payUrl,
        ':last_used' => time()
    ];
    
    $stmt->execute($insert_params);

    debug_log("INSERCAO_SUCESSO", $insert_params);
    
    // Retorno de Sucesso
    echo json_encode([
        "success" => true,
        "message" => "✅ Seu teste foi gerado com sucesso!",
        "username" => $username,
        "password" => $password,
        "iptv_url" => $iptv_url,
        "payUrl" => $payUrl
    ]);

} catch (Exception $e) {
    die_with_log("Erro no banco de dados (Operação): " . $e->getMessage(), ["file" => $e->getFile(), "line" => $e->getLine()]);
}
?>