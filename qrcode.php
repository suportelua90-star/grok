<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

include 'session_check.php';

$page_title = "Gerenciar QR Code";

/* ===============================
   CONEXÃO COM BANCO SQLITE
=============================== */

$db = new SQLite3("./api/maxrebrandscode_qrcode.db");
$table_name = "qrcode";

/* ===============================
   CRIA TABELA SE NÃO EXISTIR
=============================== */

$db->exec("
CREATE TABLE IF NOT EXISTS $table_name (
    id INTEGER PRIMARY KEY,
    qrcode TEXT
)
");

/* ===============================
   GARANTE REGISTRO PADRÃO
=============================== */

$res = $db->query("SELECT COUNT(*) as count FROM $table_name");
$row = $res->fetchArray(SQLITE3_ASSOC);

if ($row['count'] == 0) {
    $defaultQr = 'https://wa.me/5571983834846';
    $db->exec("INSERT INTO $table_name (id, qrcode) VALUES (1, '$defaultQr')");
}

/* ===============================
   FUNÇÃO PARA ATUALIZAR JSON
=============================== */

function atualizarJsonQr($valor) {
    $jsonPath = __DIR__ . '/api/qrcode.json';

    $data = [
        "qrcode" => $valor
    ];

    file_put_contents(
        $jsonPath,
        json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
    );
}

/* ===============================
   ATUALIZA QR VIA POST
=============================== */

if (isset($_POST['submit'])) {
    $newQrcode = trim($_POST['qrcode']);

    // Validação básica
    if (!filter_var($newQrcode, FILTER_VALIDATE_URL)) {
        die('URL inválida');
    }

    // Atualiza banco
    $db->exec(
        "UPDATE $table_name 
         SET qrcode='" . SQLite3::escapeString($newQrcode) . "' 
         WHERE id=1"
    );

    // Atualiza JSON
    atualizarJsonQr($newQrcode);

    $db->close();
    header("Location: qrcode.php?r=atualizado");
    exit;
}

/* ===============================
   OBTÉM QR ATUAL
=============================== */

$res = $db->query("SELECT qrcode FROM $table_name WHERE id=1");
$row = $res->fetchArray(SQLITE3_ASSOC);
$qrcode = $row['qrcode'];

/* ===============================
   PREVIEW DO QR
=============================== */

$qrcodeUrl = "https://image-charts.com/chart?chs=500x500&cht=qr&chl=" . urlencode($qrcode);

/* ===============================
   CONTEÚDO DA PÁGINA
=============================== */

$page_content = '
<div class="container-fluid">
    <div class="card radius-10">
        <div class="card-body">
            <center>
                <h4 class="card-title mb-3">QR Code Atual</h4>';

if (!empty($qrcode)) {
    $page_content .= '
                <img 
                    src="' . $qrcodeUrl . '" 
                    alt="QR Code" 
                    style="max-width:300px;width:100%;height:auto;margin-bottom:20px;"
                >';
}

$page_content .= '
                <form method="POST" action="">
                    <div class="form-group mt-3">
                        <label for="qrcode">Novo link do QR Code</label>
                        <input 
                            type="text" 
                            name="qrcode" 
                            id="qrcode" 
                            class="form-control" 
                            value="' . htmlspecialchars($qrcode) . '" 
                            required
                        >
                    </div>
                    <button type="submit" name="submit" class="btn btn-primary mt-3">
                        Atualizar QR Code
                    </button>
                </form>
            </center>
        </div>
    </div>
</div>';

/* ===============================
   LAYOUT FINAL
=============================== */

include 'includes/layout.php';
