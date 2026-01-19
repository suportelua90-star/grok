<?php
$mac = $_GET['mac'];
$devID = $_GET['devID'];
$deviceName = $_GET['deviceName'];

$data = array(
    'mac' => $mac,
    'devID' => $devID,
    'deviceName' => $deviceName
);

$filename = './request.json';

if (file_exists($filename)) {
    $jsonData = file_get_contents($filename);
    $existingData = json_decode($jsonData, true);
    $existingData[] = $data;
    $jsonData = json_encode($existingData);
} else {
    $jsonData = json_encode([$data]);
}

file_put_contents($filename, $jsonData);

echo 'Data send successfully';
?>
