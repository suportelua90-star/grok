<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

include 'session_check.php';

$page_title = "🖼�? Upload de logo";

// Diretório onde a imagem será salva
$uploadDir = 'img/';
$targetFile = $uploadDir . 'logo.png';

// Criar diretório se não existir
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$success_message = '';
$error_message = '';

// Processar remoção da logo
if (isset($_GET['action']) && $_GET['action'] == 'delete_logo') {
    if (file_exists($targetFile)) {
        unlink($targetFile);
        header("Location: " . strtok($_SERVER["REQUEST_URI"], '?') . "?success=deleted");
        exit;
    }
}

if(isset($_GET['success']) && $_GET['success'] == 'deleted') {
    $success_message = "�? logo removida com sucesso!";
}

// Processar upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['logo'])) {
    $file = $_FILES['logo'];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error_message = "Erro no upload: " . $file['error'];
    } else {
        $check = getimagesize($file['tmp_name']);
        if ($check === false) {
            $error_message = "O arquivo não é uma imagem válida.";
        } else {
            $image = null;
            if ($check['mime'] == 'image/png') $image = imagecreatefrompng($file['tmp_name']);
            elseif ($check['mime'] == 'image/gif') $image = imagecreatefromgif($file['tmp_name']);
            else $image = imagecreatefromjpeg($file['tmp_name']);
            
            if ($image) {
                $width = imagesx($image);
                $height = imagesy($image);
                $pngImage = imagecreatetruecolor($width, $height);
                
                imagealphablending($pngImage, false);
                imagesavealpha($pngImage, true);
                $transparent = imagecolorallocatealpha($pngImage, 0, 0, 0, 127);
                imagefill($pngImage, 0, 0, $transparent);
                
                imagecopy($pngImage, $image, 0, 0, 0, 0, $width, $height);
                
                if (imagepng($pngImage, $targetFile, 9)) {
                    $success_message = "�? logo atualizada com sucesso!";
                } else {
                    $error_message = "�? Erro ao gravar na pasta img/.";
                }
                imagedestroy($image);
                imagedestroy($pngImage);
            }
        }
    }
}

$preview_exists = file_exists($targetFile);
$preview_url = $preview_exists ? $targetFile . '?t=' . time() : '';

$page_content = '
<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>logo Manager - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --dark-bg: #0f172a;
            --darker-bg: #0a0f1c;
            --card-bg: #1e293b;
            --border-color: #334155;
            --accent-color: #3b82f6;
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
        }
        
        body {
            background: linear-gradient(135deg, var(--darker-bg) 0%, var(--dark-bg) 100%);
            color: var(--text-primary);
            min-height: 100vh;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        
        .card-custom {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .card-custom:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.6);
        }
        
        .card-header-custom {
            background: linear-gradient(135deg, var(--accent-color) 0%, #1d4ed8 100%);
            border-bottom: 1px solid var(--border-color);
            border-radius: 12px 12px 0 0 !important;
            padding: 1.5rem;
        }
        
        .btn-custom {
            background: linear-gradient(135deg, var(--accent-color) 0%, #1d4ed8 100%);
            border: none;
            color: white;
            font-weight: 600;
            padding: 12px 24px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(59, 130, 246, 0.4);
            color: white;
        }
        
        .form-control-custom {
            background-color: rgba(30, 41, 59, 0.8);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            padding: 12px 15px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .form-control-custom:focus {
            background-color: rgba(30, 41, 59, 1);
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25);
            color: var(--text-primary);
        }
        
        .preview-container {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            border-radius: 12px;
            padding: 30px;
            border: 2px solid var(--border-color);
            position: relative;
            overflow: hidden;
        }
        
        .preview-container::before {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.1) 1px, transparent 1px);
            background-size: 20px 20px;
            animation: moveBackground 20s linear infinite;
            z-index: 0;
        }
        
        @keyframes moveBackground {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .preview-content {
            position: relative;
            z-index: 1;
        }
        
        .alert-custom {
            border-radius: 10px;
            border: none;
            font-weight: 500;
        }
        
        .btn-outline-light-custom {
            border: 2px solid var(--border-color);
            color: var(--text-primary);
            transition: all 0.3s ease;
        }
        
        .btn-outline-light-custom:hover {
            border-color: var(--accent-color);
            background-color: rgba(59, 130, 246, 0.1);
        }
        
        .glass-effect {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-12 col-xl-10">
                <div class="card-custom">
                    <div class="card-header-custom">
                        <div class="d-flex align-items-center justify-content-center">
                            <i class="fas fa-palette fa-2x me-3"></i>
                            <h2 class="mb-0 fw-bold">🖼�? Gerenciamento de logotipo</h2>
                        </div>
                        <p class="text-center mb-0 mt-2 opacity-75">Faça upload, visualize ou remova o logotipo do sistema</p>
                    </div>
                    
                    <div class="card-body p-4 p-lg-5">
                        ' . (!empty($success_message) ? '
                        <div class="alert alert-success alert-custom alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>' . $success_message . '
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>' : '') . '
                        
                        ' . (!empty($error_message) ? '
                        <div class="alert alert-danger alert-custom alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>' . $error_message . '
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>' : '') . '

                        <div class="row g-4">
                            <!-- Coluna de Upload -->
                            <div class="col-12 col-lg-6">
                                <div class="glass-effect p-4 p-lg-5 rounded-3 h-100">
                                    <div class="d-flex align-items-center mb-4">
                                        <div class="bg-primary bg-opacity-20 p-3 rounded-circle me-3">
                                            <i class="fas fa-cloud-upload-alt fa-2x text-primary"></i>
                                        </div>
                                        <div>
                                            <h4 class="mb-1">📤 Upload de logotipo</h4>
                                            <p class="text-muted mb-0">Envie uma nova imagem para substituir a atual</p>
                                        </div>
                                    </div>
                                    
                                    <form method="POST" enctype="multipart/form-data" id="uploadForm">
                                        <div class="mb-4">
                                            <label class="form-label fw-bold mb-3">Selecione o arquivo de imagem:</label>
                                            <div class="input-group input-group-lg">
                                                <input type="file" class="form-control form-control-custom" id="logoInput" name="logo" accept="image/png, image/jpeg, image/jpg, image/gif" required>
                                                <button class="btn btn-outline-secondary" type="button" onclick="document.getElementById(\'logoInput\').click()">
                                                    <i class="fas fa-folder-open"></i>
                                                </button>
                                            </div>
                                            <div class="mt-3">
                                                <div class="d-flex align-items-center text-muted">
                                                    <i class="fas fa-info-circle me-2 text-primary"></i>
                                                    <small>Formatos suportados: PNG, JPG, GIF. Tamanho máximo: 5MB</small>
                                                </div>
                                                <div class="d-flex align-items-center text-muted mt-1">
                                                    <i class="fas fa-sync-alt me-2 text-primary"></i>
                                                    <small>A imagem será automaticamente convertida e salva como logo.png</small>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-custom btn-lg w-100 py-3">
                                            <i class="fas fa-upload me-2"></i>Upload e Salvar logotipo
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Coluna de Visualização -->
                            <div class="col-12 col-lg-6">
                                <div class="preview-container h-100">
                                    <div class="preview-content">
                                        <div class="text-center mb-4">
                                            <div class="bg-primary bg-opacity-20 p-3 rounded-circle d-inline-block mb-3">
                                                <i class="fas fa-eye fa-2x text-primary"></i>
                                            </div>
                                            <h4 class="mb-2">👁�? Visualização Atual</h4>
                                            <p class="text-muted">Veja como sua logo aparece no sistema</p>
                                        </div>
                                        
                                        <div class="mb-4 position-relative">
                                            <div class="preview-container" style="background: linear-gradient(135deg, #0f0f23 0%, #1a1a2e 100%); min-height: 250px;">
                                                <img src="' . $preview_url . '" id="imgPreview" class="img-fluid ' . ($preview_exists ? '' : 'd-none') . '" style="max-height: 200px; filter: drop-shadow(0 0 10px rgba(59, 130, 246, 0.5));">
                                                
                                                <div id="placeholderText" class="' . ($preview_exists ? 'd-none' : '') . '">
                                                    <div class="text-center py-5">
                                                        <i class="fas fa-image fa-4x text-secondary mb-3 opacity-50"></i>
                                                        <p class="text-muted">Nenhuma logomarca carregada</p>
                                                        <small class="text-muted">Faça upload de uma imagem para visualização</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        ' . ($preview_exists ? '
                                        <div class="d-flex gap-3 justify-content-center">
                                            <a href="' . $targetFile . '" download class="btn btn-outline-light-custom btn-lg flex-fill">
                                                <i class="fas fa-download me-2"></i>Baixar PNG
                                            </a>
                                            <button onclick="confirmDelete()" class="btn btn-outline-danger btn-lg flex-fill">
                                                <i class="fas fa-trash-alt me-2"></i>Remover logo
                                            </button>
                                        </div>' : '
                                        <div class="text-center py-3">
                                            <div class="alert alert-info glass-effect">
                                                <i class="fas fa-lightbulb me-2"></i>
                                                Após fazer o upload, você poderá baixar ou remover a logo aqui.
                                            </div>
                                        </div>') . '
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Informações Adicionais -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="glass-effect p-4 rounded-3">
                                    <h5 class="mb-3"><i class="fas fa-lightbulb text-warning me-2"></i>Dicas e Informações</h5>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="d-flex align-items-start mb-3">
                                                <i class="fas fa-check-circle text-success mt-1 me-2"></i>
                                                <div>
                                                    <small class="fw-bold d-block">Recomendado</small>
                                                    <small class="text-muted">Use PNG com fundo transparente</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="d-flex align-items-start mb-3">
                                                <i class="fas fa-desktop text-info mt-1 me-2"></i>
                                                <div>
                                                    <small class="fw-bold d-block">Resolução</small>
                                                    <small class="text-muted">Ideal: 500x500 pixels ou proporcional</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="d-flex align-items-start mb-3">
                                                <i class="fas fa-sync-alt text-primary mt-1 me-2"></i>
                                                <div>
                                                    <small class="fw-bold d-block">Atualização</small>
                                                    <small class="text-muted">Mudanças são aplicadas instantaneamente</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    // Preview Instantâneo ao selecionar arquivo
    document.getElementById("logoInput").onchange = function (evt) {
        const [file] = this.files;
        if (file) {
            const img = document.getElementById("imgPreview");
            const placeholder = document.getElementById("placeholderText");
            
            // Verificar tamanho do arquivo (max 5MB)
            if (file.size > 5 * 1024 * 1024) {
                alert("Arquivo muito grande! Tamanho máximo: 5MB");
                this.value = "";
                return;
            }
            
            img.src = URL.createObjectURL(file);
            img.classList.remove("d-none");
            placeholder.classList.add("d-none");
            
            // Mostrar preview também no título
            document.getElementById("uploadForm").scrollIntoView({ behavior: "smooth" });
        }
    }
    
    // Confirmação para deletar
    function confirmDelete() {
        Swal.fire({
            title: "Tem certeza?",
            text: "Você não poderá reverter esta ação!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Sim, remover logo!",
            cancelButtonText: "Cancelar",
            background: "#1e293b",
            color: "#f1f5f9"
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "?action=delete_logo";
            }
        });
    }
    
    // Adicionar SweetAlert se disponível
    if (typeof Swal === "undefined") {
        const script = document.createElement("script");
        script.src = "https://cdn.jsdelivr.net/npm/sweetalert2@11";
        document.head.appendChild(script);
    }
    </script>
</body>
</html>
';

// Se você ainda usa o layout.php, mantenha esta linha:
include 'includes/layout.php';
?>