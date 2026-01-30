<?php
error_reporting(0);

$jsonFile = __DIR__ . '/qrcode.json';
$data = json_decode(file_get_contents($jsonFile), true);

if (empty($data['qrcode'])) exit;

$qrValue = urlencode($data['qrcode']);

// QR maior para boa qualidade
$qrImage = "https://image-charts.com/chart?chs=300x300&cht=qr&chld=L|1&chl={$qrValue}";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>QR Code</title>
<style>
    #box {
        position: absolute;
        width: 140px;
        height: 140px;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: transparent;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    #qr {
        width: 120px;
        height: 120px;
        background: #fff;
    }
</style>

</head>
<body>
    <div id="box">
        <img id="qr" src="<?= $qrImage ?>" alt="QR Code">
    </div>
</body>
</html>
