<?php

ini_set('display_errors', 0);

include(__DIR__ . '/../includes/functions.php');

$jsonIn = file_get_contents('php://input');

$resonse = json_decode($jsonIn, true);

$decoded = getDecodedString($resonse['data']);

//$decoded = $resonse['data'];

//$decoded = base64_decode($resonse['data']);

//var_dump($decoded);

$playlistData = json_decode($decoded, true);

$dnsId = $playlistData['playlist_id'];
$playlist = $playlistData['playlist_url'];
$playlist_name = $playlistData['playlist_name'];
$playlist_url = parse_url($playlist);
parse_str($playlist_url['query'], $query);

$username = json_encode($query['username']);
$password = json_encode($query['password']);
$hostname = json_encode($playlist_url['host']);

$username = str_replace(['"', "'"], '', $username);
$password = str_replace(['"', "'"], '', $password);
$hostname = str_replace(['"', "'"], '', $hostname);

//$macAddress = $playlistData['mac_address'];
$macAddress = strtoupper($playlistData['mac_address']);

//$playlist_seq = $db->select('sqlite_sequence', '*', 'name = :name', '', [':name' => 'playlist']);
//print_r($playlist_seq);
//$playlistSeq =  $playlist_seq[0]['seq'] + 1;
//echo $playlistSeq;

if (empty($dnsId)) {
	$dns_id = $db->select('dns', '*', 'title = :title', '', [':title' => $playlist_name]);
	//print_r($dns_id);
	$dnsId = $dns_id[0]['id'];
	//echo $dnsId;
} else {
       $dns_id = $db->select('dns', '*', 'id = :id', '', [':id' => $dnsId]);
       //print_r($dns_id);
       $playlist_name = $dns_id[0]['title'];
}

$result = $db->select('playlist', '*', 'dns_id = :dns_id AND mac_address = :mac_address', '', [':dns_id' => $dnsId,':mac_address' => $macAddress]);

if (!empty($result)) {
	$data = ['username' => $username,'password' => $password,'pin' => '0000',];
	$db->update('playlist', $data, 'dns_id = :dns_id AND mac_address = :mac_address', [':dns_id' => $dnsId,':mac_address' => $macAddress]);
} else {
	$data = ['dns_id' => $dnsId,'mac_address' => $macAddress,'username' => $username,'password' => $password,'pin' => '0000',];
	$db->insert('playlist', $data);
}

$response = ['success' => 1, 'id' => $dnsId, 'name' =>$playlist_name, 'url' => $playlist];

//echo (Encryption::run(json_encode($response)));
echo json_encode($response);