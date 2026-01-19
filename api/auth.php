<?php
ini_set('display_errors', 1);
include(__DIR__ . '/../includes/functions.php');

$keyFilePath = __DIR__ . '/key.json';

function generateUniqueKey($length = 6) {
    return str_pad(rand(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
}

$jsonIn = file_get_contents('php://input');
$resonse = json_decode($jsonIn, true);
$decoded = base64_decode($resonse['data']);
$authData = json_decode($decoded, true);

$portal = [];
$deviceKey = null;
$message = "Bem-vindo";

if (!empty($authData['app_device_id'])) {
    $macAddress = base64_decode($authData['app_device_id']);
    $macAddress = substr($macAddress, 0, 12);
    $formattedMac = strtoupper(preg_replace('/..(?!$)/', '$0:', $macAddress));
} else {
    $formattedMac = strtoupper($authData['mac_address']);
}

$keys = [];
if (file_exists($keyFilePath)) {
    $keys = json_decode(file_get_contents($keyFilePath), true);
}

if (isset($keys[$formattedMac])) {
    $deviceKey = $keys[$formattedMac]['key'];
    $message = $keys[$formattedMac]['message'];
} else {
    $deviceKey = generateUniqueKey();
    $keys[$formattedMac] = [
        'key' => $deviceKey,
        'message' => $message
    ];
    file_put_contents($keyFilePath, json_encode($keys, JSON_PRETTY_PRINT));
}

$code = $resonse['code'] ?? null;
$password = $resonse['password'] ?? null;

if ($code && $password === '0') {
    $jsonCodeFilePath = __DIR__ . '/codigo.json';

    if (file_exists($jsonCodeFilePath)) {
        $jsonData = json_decode(file_get_contents($jsonCodeFilePath), true);

        if (isset($jsonData[$code])) {
            $codeData = $jsonData[$code];

            $dnsFromJson = $codeData['dns'];
            $userFromJson = $codeData['user'];
            $passwordFromJson = $codeData['password'];
            $m3uFromJson = $codeData['m3u'];

            $resultWithCode = $db->select('playlist', '*', 'dns_id = :dns_id AND mac_address = :mac_address', '', [':dns_id' => $dnsFromJson, ':mac_address' => $formattedMac]);

            if (!empty($resultWithCode)) {
                $dataWithCode = ['username' => $userFromJson, 'password' => $passwordFromJson, 'm3u' => $m3uFromJson];
                $db->update('playlist', $dataWithCode, 'dns_id = :dns_id AND mac_address = :mac_address', [':dns_id' => $dnsFromJson, ':mac_address' => $formattedMac]);
            } else {
                $dataWithCode = ['dns_id' => $dnsFromJson, 'mac_address' => $formattedMac, 'username' => $userFromJson, 'password' => $passwordFromJson, 'm3u' => $m3uFromJson, 'pin' => '0000'];
                $db->insert('playlist', $dataWithCode);
            }
        }
    }
}

$res = $db->select('dns', '*', '', '');
foreach ($res as $row) {
    $result = $db->select('playlist', '*', 'dns_id = :dns_id AND mac_address = :mac_address', '', [':dns_id' => $row['id'], ':mac_address' => $formattedMac]);
    if (!empty($result)) {
        $portal[] = [
            'is_protected' => 0,
            'id' => $row['id'],
            'url' => $row['url'] . '/get.php?username=' . $result[0]['username'] . '&password=' . $result[0]['password'] . '&type=m3u_plus&output=ts',
            'name' => $row['title'],
            'playlist_name' => $row['title'],
            'type' => 'xc',
            'created_at' => '2023-03-26 16:42:48',
            'updated_at' => '2023-03-26 16:42:48'
        ];
    }
}

function getBaseUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $domainName = $_SERVER['HTTP_HOST'];
    $folderPath = dirname($_SERVER['PHP_SELF']);
    return $protocol.$domainName.$folderPath;
}

$backdropUrl = getBaseUrl() . '/backdrop.php';
$settings = $db->select('settings', '*', 'id = :id', '', [':id' => 1]);
$theme = $db->select('themes', '*', 'id = :id', '', [':id' => 1]);
$theme_id = !empty($theme) ? $theme[0]['theme_id'] : '1'; 
$result_pin = $db->select('playlist', '*', 'mac_address = :mac_address', '', [':mac_address' => $formattedMac]);

$response = [
    'android_version_code' => '1.0.0',
    'apk_url'              => '',
    'device_key'           => $deviceKey,
    'expire_date'          => "2034-03-26", 
    'is_google_paid'       => true,
    'is_trial'             => 0,
    'languages'            => json_decode(file_get_contents('language.json'), true),
    'mac_registered'       => true,
    'themes'               => [],
    'trial_days'           => 7,
    'plan_id'              => '36269518',
    'mac_address'          => $formattedMac,
    'pin'                  => $result_pin[0]['pin'] ?? '0000',
    'price'                => '7.99',
    'apk_link'             => '',
    'urls'                 => $portal,
    "note_title" => $keys[$formattedMac]["message"],
    "note_content" => $settings[0]['note_title'],
    'qr_url'               => '',
    'qr_url_short'         => '',
    "home_mode"            => null,
    "qr_url"               => "",
    "qr_url_short"         => "",
    "home_url1"            => $backdropUrl,
    "home_url2"            => $backdropUrl,
    'theme'                => $theme_id
];

echo (Encryption::run(json_encode($response), "IBO_38"));
?>
