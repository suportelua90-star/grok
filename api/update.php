<?php
ini_set("display_errors", 0);
include __DIR__ . "/../includes/functions.php";

$jsonIn = file_get_contents("php://input");
$resonse = json_decode($jsonIn, true);

if (!isset($resonse["data"])) {
    exit(json_encode(["success" => 0, "message" => "Missing 'data' in input"]));
}

$decoded = base64_decode($resonse["data"]);
if (!$decoded) {
    exit(json_encode(["success" => 0, "message" => "Invalid base64 encoded data"]));
}

$playlistData = json_decode($decoded, true);
if (!$playlistData) {
    exit(json_encode(["success" => 0, "message" => "Invalid JSON data in decoded input"]));
}

// Check for required keys
$requiredKeys = ["playlist_id", "playlist_url", "playlist_name", "username", "password"];
foreach ($requiredKeys as $key) {
    if (!isset($playlistData[$key])) {
        exit(json_encode(["success" => 0, "message" => "Missing required field: $key"]));
    }
}

$dnsId = $playlistData["playlist_id"];
$newURL = $playlistData["playlist_url"];
$playlistName = $playlistData["playlist_name"];
$username = $playlistData["username"];
$password = $playlistData["password"];
$macAddress = strtoupper($playlistData["mac_address"] ?? "");
$pin = $playlistData["parent_control"] ?? null;
$deleteFlag = $playlistData["delete"] ?? false;

$macAddress = preg_replace('/[^A-F0-9]/', '', $macAddress);
$macAddress = substr($macAddress, 0, 12);
$macAddressFormatted = implode(":", str_split($macAddress, 2));

if ($deleteFlag) {
    $response = deletePlaylist($dnsId, $macAddressFormatted, $db);
    echo json_encode($response);
    exit;
}

if ($username === '0' && $password === '0') {
    $response = deletePlaylist($dnsId, $macAddressFormatted, $db);
    echo json_encode($response);
    exit;
}

if ($pin) {
    $result = $db->select("playlist", "*", "mac_address = :mac_address", "", [":mac_address" => $macAddressFormatted]);
    if (!empty($result)) {
        if ($result[0]["pin"] == $pin) {
            echo json_encode(["status" => true, "message" => "Parental Pin Set"]);
        } else {
            $data = ["pin" => $pin];
            $db->update("playlist", $data, "mac_address = :mac_address", [":mac_address" => $macAddressFormatted]);
            echo json_encode(["status" => true, "message" => "Parental Pin updated Successfully"]);
        }
    } else {
        echo json_encode(["success" => 0, "message" => "MAC address not found"]);
    }
} else {
    $result = $db->select("playlist", "*", "dns_id = :dns_id AND mac_address = :mac_address", "", [":dns_id" => $dnsId, ":mac_address" => $macAddressFormatted]);
    if (!empty($result)) {
        $data = ["username" => $username, "password" => $password];
        $db->update("playlist", $data, "dns_id = :dns_id AND mac_address = :mac_address", [":dns_id" => $dnsId, ":mac_address" => $macAddressFormatted]);
        $response = ["success" => 1, "id" => $dnsId, "name" => $playlistName, "url" => $newURL];
        echo json_encode($response);
    } else {
        echo json_encode(["success" => 0, "message" => "Playlist not found"]);
    }
}

function deletePlaylist($dnsId, $macAddressFormatted, $db) {
    $deleted = $db->delete("playlist", "dns_id = :dns_id AND mac_address = :mac_address", [":dns_id" => $dnsId, ":mac_address" => $macAddressFormatted]);
    if ($deleted) {
        return ["success" => 1, "message" => "Playlist deleted successfully."];
    } else {
        return ["success" => 0, "message" => "Error deleting the playlist."];
    }
}
?>
