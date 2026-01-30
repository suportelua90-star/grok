<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include(__DIR__ . '/../includes/functions.php');

$keyFilePath = __DIR__ . '/key.json';
$logFilePath = __DIR__ . '/auth_debug.log';

function generateUniqueKey($length = 6) {
    return str_pad(rand(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
}

header('Content-Type: application/json; charset=utf-8');

function log_auth($msg) {
    global $logFilePath;
    $line = "[" . date("Y-m-d H:i:s") . "] " . $msg . "\n";
    @file_put_contents($logFilePath, $line, FILE_APPEND);
}

function decodeAuthDataSafe($dataEncoded) {
    $decoded1 = null;
    if (function_exists('getDecodedString')) {
        try { $decoded1 = getDecodedString($dataEncoded); } catch (\Throwable $e) { $decoded1 = null; }
    }
    if (is_string($decoded1) && strlen($decoded1) > 0) {
        $tmp = json_decode($decoded1, true);
        if (is_array($tmp)) return [$decoded1, 'getDecodedString'];
    }
    $decoded2 = base64_decode($dataEncoded, true);
    if (is_string($decoded2) && strlen($decoded2) > 0) {
        $tmp2 = json_decode($decoded2, true);
        if (is_array($tmp2)) return [$decoded2, 'base64_decode'];
    }
    return [$decoded1 ?: ($decoded2 ?: ''), 'failed'];
}

$jsonIn = file_get_contents('php://input');
$resonse = json_decode($jsonIn, true);

if (!is_array($resonse) || !isset($resonse['data'])) {
    echo json_encode(["error" => "invalid_request"], JSON_UNESCAPED_UNICODE);
    exit;
}

list($decoded, $decodeMode) = decodeAuthDataSafe($resonse['data']);
$authData = json_decode($decoded, true);

if (!is_array($authData)) {
    log_auth("ERRO: Falha ao decodificar data. Modo: $decodeMode");
    exit;
}

// Formatação do MAC
$formattedMac = "";
if (!empty($authData['app_device_id'])) {
    $macAddress = base64_decode($authData['app_device_id'], true);
    $macAddress = substr($macAddress, 0, 12);
    $formattedMac = strtoupper(preg_replace('/..(?!$)/', '$0:', $macAddress));
} else if (!empty($authData['mac_address'])) {
    $formattedMac = strtoupper($authData['mac_address']);
}

if (empty($formattedMac)) {
    log_auth("ERRO: MAC não encontrado no authData");
    exit;
}

// --- GESTÃO DE DEVICE KEY ---
$keys = file_exists($keyFilePath) ? json_decode(file_get_contents($keyFilePath), true) : [];
if (isset($keys[$formattedMac])) {
    $deviceKey = $keys[$formattedMac]['key'];
} else {
    $deviceKey = generateUniqueKey();
    $keys[$formattedMac] = ['key' => $deviceKey, 'message' => "ULTRA IBO 4.3"];
    file_put_contents($keyFilePath, json_encode($keys, JSON_PRETTY_PRINT));
}

// --- LÓGICA DO CHATBOT ---
$res_bot = $db->select("chatbot", "bot_status, bot_url, bot_dns", "id = :id", "", [':id' => 1]);
$bot_status = $res_bot[0]["bot_status"] ?? 0;
$bot_url = $res_bot[0]["bot_url"] ?? '';
$bot_dns_id = $res_bot[0]["bot_dns"] ?? '';

// Verifica se o MAC já existe na playlist
$existingClient = $db->select("playlist", "*", "mac_address = :mac_address", "", [":mac_address" => $formattedMac]);

if (empty($existingClient)) {
    if ($bot_status == 1 && !empty($bot_url)) {
        log_auth("BOT: Tentando cadastrar MAC $formattedMac via URL: $bot_url");

        $jsonBot = json_encode([
            "receiveMessageAppId" => "com.whatsapp",
            "receiveMessagePattern" => ["*"],
            "senderName" => "API DE CADASTRO",
            "senderMessage" => "api_cadastro",
            "messageDateTime" => time(),
            "isMessageFromGroup" => false
        ]);
        
        $ch = curl_init($bot_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonBot);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Timeout de 10 seg
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        $jsonRetorno = curl_exec($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($jsonRetorno === false) {
            log_auth("BOT ERRO cURL: " . $curlError);
        } else {
            $botDecoded = json_decode($jsonRetorno, true);
            $u = null; $p = null; $link = null;

            // Tenta formato 1 (direto)
            if (isset($botDecoded['username']) && isset($botDecoded['password'])) {
                $u = $botDecoded['username'];
                $p = $botDecoded['password'];
                $link = $botDecoded['payUrl'] ?? '';
            } 
            // Tenta formato 2 (data -> message)
            elseif (isset($botDecoded['data'][0]['message'])) {
                $parts = explode("|", $botDecoded['data'][0]['message']);
                $u = $parts[0] ?? null;
                $p = $parts[1] ?? null;
                $link = $botDecoded['data'][0]['payUrl'] ?? '';
            }

            if ($u && $p) {
                $insertData = [
                    'dns_id'      => $bot_dns_id,
                    'mac_address' => $formattedMac,
                    'username'    => $u,
                    'password'    => $p,
                    'pin'         => '0000',
                    'link'        => $link,
                    'device_key'  => $deviceKey
                ];
                
                try {
                    $db->insert('playlist', $insertData);
                    log_auth("BOT SUCESSO: MAC $formattedMac inserido. User: $u");
                    // Recarrega para garantir que apareça na resposta
                    $existingClient = $db->select("playlist", "*", "mac_address = :mac_address", "", [":mac_address" => $formattedMac]);
                } catch (Exception $e) {
                    log_auth("BOT ERRO SQL: " . $e->getMessage());
                }
            } else {
                log_auth("BOT AVISO: Bot respondeu mas não enviou user/pass. Resposta: " . substr($jsonRetorno, 0, 100));
            }
        }
    }
}

// --- PREPARAÇÃO DA RESPOSTA FINAL ---
$allDns = $db->select('dns', '*', '', '');
$urls = [];
$portals = [];

foreach ($allDns as $dns) {
    // Busca playlist para este DNS específico
    $p = $db->select('playlist', '*', 'dns_id = :d AND mac_address = :m', '', [':d' => $dns['id'], ':m' => $formattedMac]);
    if (!empty($p)) {
        $urls[] = [
            'is_protected' => 0,
            'id' => $dns['id'],
            'url' => $dns['url'] . '/get.php?username=' . $p[0]['username'] . '&password=' . $p[0]['password'] . '&type=m3u_plus&output=ts',
            'name' => $dns['title'],
            'type' => 'conectado',
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s")
        ];
    }
    $portals[] = ['id' => $dns['id'], 'url' => $dns['url'], 'name' => $dns['title'], 'type' => 'conectado'];
}

$settings = $db->select('settings', '*', 'id = :id', '', [':id' => 1]);
$theme = $db->select('themes', '*', 'id = :id', '', [':id' => 1]);

$response = [
    'android_version_code' => '1.0.0',
    'device_key' => $deviceKey,
    'expire_date' => "2034-03-26",
    'is_google_paid' => true,
    'mac_address' => $formattedMac,
    'urls' => $urls,
    'portals' => $portals,
    'STORAGE' => 'x_t541',
    'pin' => $existingClient[0]['pin'] ?? '0000',
    'note_title' => $settings[0]["note_title"] ?? '',
    'note_content' => $settings[0]["note_content"] ?? '',
    'home_url1' => (isset($_SERVER['HTTPS']) ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/backdrop.php',
    'theme' => $theme[0]['theme_id'] ?? '1'
];

$finalData = [
    'STORAGE' => 'x_t541',
    'data' => json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
];

echo json_encode($finalData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
exit;