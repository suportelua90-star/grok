<?php
// Conecta ao banco de dados SQLite
$db = new SQLite3("./maxrebrandscode_qrcode.db");

// Define nome da tabela
$table_name = "qrcode";

// Cria a tabela, se não existir
$db->exec("CREATE TABLE IF NOT EXISTS $table_name (
    id INTEGER PRIMARY KEY,
    qrcode TEXT
)");

// Verifica se há dados
$res = $db->query("SELECT COUNT(*) as count FROM $table_name");
$row = $res->fetchArray();
$numRows = $row["count"];

// Insere valor padrão se estiver vazio
if ($numRows == 0) {
    $defaultQR = 'https://wa.me/5571983834846';
    $stmt = $db->prepare("INSERT INTO $table_name (id, qrcode) VALUES (:id, :qrcode)");
    $stmt->bindValue(':id', 1, SQLITE3_INTEGER);
    $stmt->bindValue(':qrcode', $defaultQR, SQLITE3_TEXT);
    $stmt->execute();
}

// Busca o QRCode
$res = $db->query("SELECT qrcode FROM $table_name WHERE id = 1");
$row = $res->fetchArray();
$qrcode = $row["qrcode"] ?? '';

if (!empty($qrcode)) {
    $qrcodeUrl = "https://image-charts.com/chart?chs=500x500&cht=qr&chl=" . urlencode($qrcode);
    ?>
    <!DOCTYPE html>
    <html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <title>QR Code</title>
        <style>
            body {
                margin: 0;
                padding: 0;
                background-color: #fff;
            }
            #qrcode-img {
                position: absolute;
                top: 0;
                left: 0;
                width: 100vw;
                height: 100vh;
                object-fit: contain;
            }
        </style>
    </head>
    <body>
        <img id="qrcode-img" src="<?php echo $qrcodeUrl; ?>" alt="QR Code">
    </body>
    </html>
    <?php
} else {
    echo "QR Code não encontrado.";
}
