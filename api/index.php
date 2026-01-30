<?php
ini_set('display_errors', 1);
include(__DIR__ . '/../includes/functions.php');
$res = $db->select('dns', '*', '', '');
$portal = [];
$accounts = 0;

function formatMac($mac, $doubleDecode = false, $removeSubstr = null) {
    if ($doubleDecode) {
        $mac = base64_decode(base64_decode($mac));
    } else {
        $mac = base64_decode($mac);
    }

    if ($removeSubstr) {
        $mac = str_replace($removeSubstr, "", $mac);
    }
   return strtoupper(preg_replace('/..(?!$)/', '$0:', $mac));

}

$mac = $_GET['mac'];
$macAddress = formatMac($mac, true, "afea");

$result = $db->select('playlist', '*', 'mac_address = :mac_address', '', [':mac_address' => $macAddress]);

if (!empty($result)) {
    foreach ($result as $row) {
        if (!empty($row['username'])) {
            $accounts = 1;
            break; // Exit the loop as we found a matching account
        }
    }
}

foreach ($res as $row) {
    if (!empty($res)) {
        $portal[] = ['name' => $row['title'], 'url' => $row['url'], 'id' => $row['id']];
    }
}

$data = ['portals' => $portal, "accounts" => $accounts];
echo Encryption::run(json_encode($data), "IBO_38");
?>