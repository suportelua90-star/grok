<?php
ini_set('display_errors', 1);
include(__DIR__ . '/../includes/functions.php');

$infos = explode("{}", base64_decode($_GET['info']));
$dnsId = $infos[0];
$username = $infos[1];
$password = $infos[2];
$macAddress = base64_decode($infos[3]);

$macAddress = strtoupper($macAddress);

$formattedMac = implode(':', str_split($macAddress, 2));

$macAddressParts = explode(':', $formattedMac);
if (count($macAddressParts) > 6) {
    $macAddressReduced = implode(':', array_slice($macAddressParts, 0, 6));
} else {
    $macAddressReduced = $formattedMac;
}

$existingResult = $db->select('playlist', '*', 'mac_address = :mac_address AND username = :username', '', [':mac_address' => $macAddressReduced, ':username' => 'default_user']);

if (!empty($existingResult)) {
    $db->delete('playlist', 'mac_address = :mac_address AND username = :username', [':mac_address' => $macAddressReduced, ':username' => 'default_user']);
}

$result = $db->select('playlist', '*', 'dns_id = :dns_id AND mac_address = :mac_address', '', [':dns_id' => $dnsId, ':mac_address' => $macAddressReduced]);

if (!empty($result)) {
    $data = ['username' => $username, 'password' => $password, 'pin' => '0000'];
    $db->update('playlist', $data, 'dns_id = :dns_id AND mac_address = :mac_address', [':dns_id' => $dnsId, ':mac_address' => $macAddressReduced]);
} else {
    $data = ['dns_id' => $dnsId, 'mac_address' => $macAddressReduced, 'username' => $username, 'password' => $password, 'pin' => '0000'];
    $db->insert('playlist', $data);
}

$response = ['success' => 1, 'id' => $dnsId, 'name' => null, 'url' => null];
echo json_encode($response);
?>
