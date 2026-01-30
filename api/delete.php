<?php

ini_set("display_errors", 0);

include(__DIR__ . '/../includes/functions.php');

$jsonIn = file_get_contents('php://input');

$resonse = json_decode($jsonIn, true);

$decoded = getDecodedString($resonse['data']);

//var_dump($decoded);

$playlistData = json_decode($decoded, true);

$playlist_id = $playlistData['playlist_id'];

$macAddress = strtoupper($playlistData['mac_address']);

$dnsId = $playlistData["playlist_id"];

if ($dnsId && $macAddress) {
    // Attempt to delete the playlist based on dns_id and mac_address
    $deleted = $db->delete("playlist", "dns_id = :dns_id AND mac_address = :mac_address", [":dns_id" => $dnsId, ":mac_address" => $macAddress]);

    // Respond with the result
    if ($deleted) {
        $response = ["success" => 1, "message" => "Playlist deleted successfully."];
    } else {
        $response = ["success" => 0, "message" => "Error deleting the playlist."];
    }

    echo json_encode($response);
} else {
    // If dns_id or mac_address is missing
    $response = ["success" => 0, "message" => "Invalid data for deletion."];
    echo json_encode($response);
}
?>