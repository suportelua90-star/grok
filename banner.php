<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$static_url = dirname($_SERVER['SCRIPT_NAME']) . '/assets/';
include 'session_check.php';

$page_title = "Banners";

$directory = __DIR__ . '/assets/media/banners/';
if (!is_dir($directory)) mkdir($directory, 0755, true);

$message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['banner'])) {
    if (!empty($_FILES['banner']['name'])) {
        $ext = pathinfo($_FILES['banner']['name'], PATHINFO_EXTENSION);
        $simple_name = uniqid() . '.' . strtolower($ext);
        $target = $directory . $simple_name;
        if (move_uploaded_file($_FILES['banner']['tmp_name'], $target)) {
            $message = "Banner enviado com sucesso!";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $message = "Erro ao enviar: " . $_FILES['banner']['error'];
        }
    } else {
        $message = "Nenhum arquivo selecionado.";
    }
}

if (isset($_GET['delete'])) {
    $file = $_GET['delete'];
    if (file_exists($file) && strpos($file, $directory) === 0) {
        unlink($file);
        $message = "Banner excluído!";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

$banner_files = glob($directory . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);
$banner_files = array_reverse($banner_files);

$page_content = '
<div class="card card-flush shadow-sm border-0 mb-5">
    <div class="card-header border-0 pt-6 pb-3">
        <h3 class="fw-bolder">Gerenciar Banners</h3>
    </div>
    <div class="card-body pt-0">
        <form action="" method="post" enctype="multipart/form-data" class="d-flex align-items-center gap-3">
            <input type="file" name="banner" class="form-control form-control-solid" accept="image/*" required>
            <button type="submit" class="btn btn-sm btn-primary d-flex align-items-center gap-2 px-4">
                <i class="ki-outline ki-upload fs-3"></i>Enviar
            </button>
        </form>
        ' . ($message ? '<div class="alert alert-info mt-4">' . htmlspecialchars($message) . '</div>' : '') . '
    </div>
</div>

<div class="row g-4">';

if (!empty($banner_files)) {
    foreach ($banner_files as $file) {
        $file_url = $static_url . 'media/banners/' . basename($file);
        $page_content .= '
            <div class="col-md-4 col-sm-6">
                <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
                    <img src="' . $file_url . '" class="w-100" style="height: 220px; object-fit: cover;" alt="Banner">
                    <div class="card-body text-center py-3">
                        <a href="?delete=' . urlencode($file) . '" class="btn btn-sm btn-danger" 
                           onclick="return confirm(\'Excluir este banner?\');">
                            <i class="ki-outline ki-trash fs-5 me-2"></i>Excluir
                        </a>
                    </div>
                </div>
            </div>';
    }
} else {
    $page_content .= '<div class="col-12 text-center py-5"><p class="text-muted">Nenhum banner encontrado.</p></div>';
}

$page_content .= '</div>';

include 'includes/layout.php';
?>