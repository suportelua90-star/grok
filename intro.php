<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

include 'session_check.php';

$page_title = "Gerenciar Vídeo de Introdução";

// Configurações
$target_dir = "rtx/intro/";
$target_file = $target_dir . "intro.mp4";
$uploadOk = 1;
$message = "";
$video_info = "";

// Criar diretório se não existir
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true);
}

// Processar upload quando o formulário é enviado
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["videoFile"])) {
    $check = getimagesize($_FILES["videoFile"]["tmp_name"]);
    
    // Verificar se é realmente um vídeo MP4
    $videoFileType = strtolower(pathinfo($_FILES["videoFile"]["name"], PATHINFO_EXTENSION));
    if($videoFileType != "mp4") {
        $message = '<div class="alert alert-danger">Apenas arquivos MP4 são permitidos.</div>';
        $uploadOk = 0;
    }

    // Verificar tamanho do arquivo (limite de 50MB)
    if ($_FILES["videoFile"]["size"] > 50000000) {
        $message = '<div class="alert alert-danger">Desculpe, seu arquivo é muito grande (máximo 50MB).</div>';
        $uploadOk = 0;
    }

    // Tentar fazer o upload se todas as verificações passarem
    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES["videoFile"]["tmp_name"], $target_file)) {
            $message = '<div class="alert alert-success">O vídeo '. htmlspecialchars(basename($_FILES["videoFile"]["name"])) . ' foi enviado e salvo como intro.mp4.</div>';
        } else {
            $message = '<div class="alert alert-danger">Ocorreu um erro ao enviar seu arquivo.</div>';
        }
    }
}

// Obter informações do vídeo atual (se existir)
if (file_exists($target_file)) {
    $video_info = '
    <div class="card mt-3">
        <div class="card-body">
            <h5 class="card-title">Informações do Vídeo Atual</h5>
            <p>Tamanho: ' . round(filesize($target_file) / (1024 * 1024), 2) . ' MB</p>
            <p>Última modificação: ' . date("d/m/Y H:i:s", filemtime($target_file)) . '</p>
        </div>
    </div>';
} else {
    $video_info = '<div class="alert alert-warning mt-3">Nenhum vídeo de introdução foi encontrado.</div>';
}

// Conteúdo da página
$page_content = '
<div class="container-fluid">
    <div class="card radius-10">
        <div class="card-body">
            <h4 class="card-title">Gerenciar Vídeo de Introdução</h4>
            
            ' . $message . '
            
            <div class="row">
                <div class="col-md-6">
                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="videoFile">Selecione um vídeo MP4:</label>
                            <input type="file" class="form-control-file" id="videoFile" name="videoFile" accept="video/mp4" required>
                            <small class="form-text text-muted">Apenas arquivos MP4 são aceitos. O vídeo será salvo como "intro.mp4".</small>
                        </div>
                        <button type="submit" class="btn btn-primary mt-3">Enviar Vídeo</button>
                    </form>
                    
                    ' . $video_info . '
                </div>
                
                <div class="col-md-6">
                    <h5>Pré-visualização:</h5>';

if (file_exists($target_file)) {
    $page_content .= '
                    <video width="100%" controls>
                        <source src="' . $target_file . '?t=' . time() . '" type="video/mp4">
                        Seu navegador não suporta a tag de vídeo.
                    </video>';
} else {
    $page_content .= '<div class="alert alert-info">Nenhum vídeo disponível para pré-visualização.</div>';
}

$page_content .= '
                </div>
            </div>
        </div>
    </div>
</div>';

include 'includes/layout.php';