<?php
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');

include(__DIR__ . '/../includes/functions.php');

$jsonIn = file_get_contents('php://input');
$resonse = json_decode($jsonIn, true);

if (!is_array($resonse) || !isset($resonse['data'])) {
    echo json_encode([
        "success" => 0,
        "message" => "Missing field: data"
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$decoded = getDecodedString($resonse['data']);
$playlistData = json_decode($decoded, true);

if (!is_array($playlistData)) {
    echo json_encode([
        "success" => 0,
        "message" => "Invalid decoded data",
        "raw_decoded" => $decoded
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * ✅ MAC: aceita:
 * - mac_address (AA:BB:CC:DD:EE:FF)
 * - app_device_id (base64 do HEX12, igual seu app manda)
 */
$macAddress = "";

if (!empty($playlistData['mac_address'])) {
    $macAddress = strtoupper(trim((string)$playlistData['mac_address']));
} elseif (!empty($playlistData['app_device_id'])) {
    $macRaw = base64_decode((string)$playlistData['app_device_id'], true);
    if (!is_string($macRaw)) $macRaw = "";
    $macRaw = substr($macRaw, 0, 12);
    if ($macRaw !== "") {
        $macAddress = strtoupper(preg_replace('/..(?!$)/', '$0:', $macRaw));
    }
}

if ($macAddress === "") {
    echo json_encode([
        "success" => 0,
        "message" => "mac_address/app_device_id not provided"
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// PIN
$pin = isset($playlistData['parent_control']) ? trim((string)$playlistData['parent_control']) : "";

/**
 * ✅ Se veio PIN (e não é vazio/"0"), salva pin para esse MAC
 */
if ($pin !== "" && $pin !== "0") {

    // pega qualquer registro do MAC
    $result = $db->select('playlist', '*', 'mac_address = :mac_address', '', [':mac_address' => $macAddress]);

    if (!empty($result)) {
        // atualiza pin em todas as entradas do MAC (mantém simples)
        $db->update('playlist', ['pin' => $pin], 'mac_address = :mac_address', [':mac_address' => $macAddress]);

        echo json_encode([
            "success" => 1,
            "status" => true,
            "message" => "Parental Pin updated Successfully",
            "mac_address" => $macAddress
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    // ✅ se não existe nenhuma linha pro MAC ainda, cria uma linha base
    $db->insert('playlist', [
        'dns_id' => 0,
        'mac_address' => $macAddress,
        'username' => '',
        'password' => '',
        'pin' => $pin
    ]);

    echo json_encode([
        "success" => 1,
        "status" => true,
        "message" => "Parental Pin Set",
        "mac_address" => $macAddress
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * ✅ Salvar playlist (user/pass) para dns_id + mac
 */
$newURL = isset($playlistData['playlist_url']) ? trim((string)$playlistData['playlist_url']) : "";
$dnsId = isset($playlistData['playlist_id']) ? trim((string)$playlistData['playlist_id']) : "";
$playlistName = isset($playlistData['playlist_name']) ? trim((string)$playlistData['playlist_name']) : "";

if ($newURL === "" || $dnsId === "") {
    echo json_encode([
        "success" => 0,
        "message" => "Missing playlist_url or playlist_id",
        "mac_address" => $macAddress
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// extrai username/password do get.php
$query = parse_url($newURL, PHP_URL_QUERY);
$parsed = [];
if (is_string($query) && $query !== "") {
    parse_str($query, $parsed);
}

$username = isset($parsed['username']) ? trim((string)$parsed['username']) : "";
$password = isset($parsed['password']) ? trim((string)$parsed['password']) : "";

if ($username === "" || $password === "") {
    echo json_encode([
        "success" => 0,
        "message" => "Could not extract username/password from playlist_url",
        "playlist_url" => $newURL,
        "mac_address" => $macAddress,
        "dns_id" => $dnsId
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// verifica se já existe
$result = $db->select(
    'playlist',
    '*',
    'dns_id = :dns_id AND mac_address = :mac_address',
    '',
    [':dns_id' => $dnsId, ':mac_address' => $macAddress]
);

if (!empty($result)) {
    // ✅ UPDATE
    $db->update(
        'playlist',
        ['username' => $username, 'password' => $password],
        'dns_id = :dns_id AND mac_address = :mac_address',
        [':dns_id' => $dnsId, ':mac_address' => $macAddress]
    );

    echo json_encode([
        "success" => 1,
        "id" => $dnsId,
        "name" => $playlistName,
        "url" => $newURL,
        "mac_address" => $macAddress,
        "updated" => 1
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// ✅ INSERT (isso é o que faltava!)
$db->insert('playlist', [
    'dns_id' => $dnsId,
    'mac_address' => $macAddress,
    'username' => $username,
    'password' => $password,
    'pin' => '0000'
]);

echo json_encode([
    "success" => 1,
    "id" => $dnsId,
    "name" => $playlistName,
    "url" => $newURL,
    "mac_address" => $macAddress,
    "inserted" => 1
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
exit;
