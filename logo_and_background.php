<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$static_url = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/'; // ex: /ibopw/
include 'session_check.php';

$page_title = "Logo e Background";

$logoJsonFile       = './api/images/logo_filenames.json';
$bgJsonFile         = './api/images/image_filenames.json';
$apiImageDirectory  = './api/images/';

if (!is_dir($apiImageDirectory)) {
    mkdir($apiImageDirectory, 0755, true);
}

$message = null;

function handleUpload($file, $type) {
    global $message, $apiImageDirectory, $logoJsonFile, $bgJsonFile;

    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    
    if (empty($file['name']) || !in_array($file['type'], $allowed)) {
        $message = "Arquivo inválido. Apenas JPEG, PNG, GIF ou WEBP.";
        return;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if ($type === 'logo') {
        $fileName = 'logo.' . $ext;
        $dest = $apiImageDirectory . $fileName;
        
        if (move_uploaded_file($file['tmp_name'], $dest)) {
            $jsonData = [['ImageName' => $fileName]];
            file_put_contents($logoJsonFile, json_encode($jsonData, JSON_PRETTY_PRINT));
            $message = "Logo enviado com sucesso! ($fileName)";
        } else {
            $message = "Falha ao mover o arquivo do logo.";
        }
    } else { // background
        $fileName = 'background.' . $ext;
        $dest = $apiImageDirectory . $fileName;
        
        if (move_uploaded_file($file['tmp_name'], $dest)) {
            $jsonData = [['ImageName' => $fileName]];
            file_put_contents($bgJsonFile, json_encode($jsonData, JSON_PRETTY_PRINT));
            $message = "Background enviado com sucesso! ($fileName)";
        } else {
            $message = "Falha ao mover o arquivo do background.";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['upload_type'], $_FILES[$_POST['upload_type']])) {
        handleUpload($_FILES[$_POST['upload_type']], $_POST['upload_type']);
    }
}

function getCurrentImage($jsonFile, $defaultFile) {
    if (file_exists($jsonFile)) {
        $data = json_decode(file_get_contents($jsonFile), true);
        if (is_array($data) && !empty($data[0]['ImageName'])) {
            $filename = $data[0]['ImageName'];
            if (file_exists('./api/images/' . $filename)) {
                return $filename;
            }
        }
    }
    return $defaultFile;
}

$currentLogoFile = getCurrentImage($logoJsonFile, 'logo.png');
$currentBgFile   = getCurrentImage($bgJsonFile, 'background.jpg');

$logoSrc = $static_url . 'api/logo.php'; // Ajustado para pasta api
$bgSrc   = $static_url . 'api/bg.php';   // Ajustado para pasta api

// Fallback se não existir
if (!file_exists('./api/images/' . $currentLogoFile)) {
    $logoSrc = 'https://via.placeholder.com/300x200?text=Sem+Logo';
}
if (!file_exists('./api/images/' . $currentBgFile)) {
    $bgSrc = 'https://via.placeholder.com/300x200?text=Sem+Background';
}

$page_content = '
<div class="card shadow-lg border-0 rounded-3 mb-5">
    <div class="card-header bg-primary text-white py-4 px-5 d-flex align-items-center justify-content-between">
        <h3 class="card-title fw-bold m-0">Gerenciamento de Logo e Background</h3>
        <i class="bi bi-image fs-3"></i> <!-- Ícone Bootstrap Icons para modernidade -->
    </div>
    <div class="card-body p-5">
        ' . ($message ? '<div class="alert alert-success alert-dismissible fade show" role="alert">' . htmlspecialchars($message) . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>' : '') . '
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card h-100 shadow-sm border-0 rounded-3 overflow-hidden transition-all">
                    <div class="card-header bg-dark text-white py-3 px-4">
                        <h5 class="fw-semibold m-0">Logo Atual</h5>
                    </div>
                    <div class="card-body p-0">
                        <img src="' . htmlspecialchars($logoSrc) . '" class="img-fluid w-100" style="height: 250px; object-fit: contain; transition: transform 0.3s ease;" alt="Logo Atual" onerror="this.src=\'https://via.placeholder.com/300x200?text=Sem+Logo\';">
                    </div>
                    <div class="card-footer bg-light py-3 px-4">
                        <form action="" method="post" enctype="multipart/form-data" class="d-flex flex-column gap-3">
                            <input type="hidden" name="upload_type" value="logo">
                            <input type="file" name="logo" class="form-control form-control-lg" accept="image/*" required>
                            <button type="submit" class="btn btn-primary btn-lg w-100 d-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-upload fs-4"></i> Enviar Logo
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100 shadow-sm border-0 rounded-3 overflow-hidden transition-all">
                    <div class="card-header bg-dark text-white py-3 px-4">
                        <h5 class="fw-semibold m-0">Background Atual</h5>
                    </div>
                    <div class="card-body p-0">
                        <img src="' . htmlspecialchars($bgSrc) . '" class="img-fluid w-100" style="height: 250px; object-fit: cover; transition: transform 0.3s ease;" alt="Background Atual" onerror="this.src=\'https://via.placeholder.com/300x200?text=Sem+Background\';">
                    </div>
                    <div class="card-footer bg-light py-3 px-4">
                        <form action="" method="post" enctype="multipart/form-data" class="d-flex flex-column gap-3">
                            <input type="hidden" name="upload_type" value="background">
                            <input type="file" name="background" class="form-control form-control-lg" accept="image/*" required>
                            <button type="submit" class="btn btn-primary btn-lg w-100 d-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-upload fs-4"></i> Enviar Background
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
    .transition-all { transition: all 0.3s ease; }
    .card:hover img { transform: scale(1.05); }
</style>
<script>
    // Adiciona feedback moderno com SweetAlert (inclua SweetAlert2 no layout.php se necessário)
    document.addEventListener("DOMContentLoaded", function() {
        const forms = document.querySelectorAll("form");
        forms.forEach(form => {
            form.addEventListener("submit", function(e) {
                const fileInput = form.querySelector("input[type=file]");
                if (!fileInput.value) {
                    e.preventDefault();
                    Swal.fire("Atenção!", "Selecione um arquivo antes de enviar.", "warning");
                }
            });
        });
    });
</script>';

include 'includes/layout.php';
?>