<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);

include 'session_check.php';

$page_title = "🎨 Configurar Background";

// Diretório onde a imagem será salva
$uploadDir = 'img/';
$targetFile = $uploadDir . 'bg.png';

// Criar diretório se não existir
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$success_message = '';
$error_message = '';

// Processar remoção do background
if (isset($_GET['action']) && $_GET['action'] == 'delete_bg') {
    if (file_exists($targetFile)) {
        unlink($targetFile);
        header("Location: " . strtok($_SERVER["REQUEST_URI"], '?') . "?success=deleted");
        exit;
    }
}

if(isset($_GET['success']) && $_GET['success'] == 'deleted') {
    $success_message = "✅ Background removido com sucesso!";
}

// Processar upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['background'])) {
    $file = $_FILES['background'];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error_message = "Erro no upload: " . $file['error'];
    } else {
        $check = getimagesize($file['tmp_name']);
        if ($check === false) {
            $error_message = "O arquivo não é uma imagem válida.";
        } else {
            // Verificar tamanho da imagem (recomendado para backgrounds)
            $image = null;
            if ($check['mime'] == 'image/png') $image = imagecreatefrompng($file['tmp_name']);
            elseif ($check['mime'] == 'image/jpeg') $image = imagecreatefromjpeg($file['tmp_name']);
            elseif ($check['mime'] == 'image/gif') $image = imagecreatefromgif($file['tmp_name']);
            else {
                $error_message = "Formato não suportado. Use PNG, JPG ou GIF.";
            }
            
            if ($image) {
                $width = imagesx($image);
                $height = imagesy($image);
                
                // Redimensionar se for muito grande (opcional)
                $maxWidth = 1920;
                $maxHeight = 1080;
                
                if ($width > $maxWidth || $height > $maxHeight) {
                    // Calcular novo tamanho mantendo proporção
                    $ratio = $width / $height;
                    if ($maxWidth / $maxHeight > $ratio) {
                        $newWidth = $maxHeight * $ratio;
                        $newHeight = $maxHeight;
                    } else {
                        $newWidth = $maxWidth;
                        $newHeight = $maxWidth / $ratio;
                    }
                    
                    $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
                    imagealphablending($resizedImage, false);
                    imagesavealpha($resizedImage, true);
                    imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                    
                    $image = $resizedImage;
                    $width = $newWidth;
                    $height = $newHeight;
                }
                
                $pngImage = imagecreatetruecolor($width, $height);
                
                imagealphablending($pngImage, false);
                imagesavealpha($pngImage, true);
                $transparent = imagecolorallocatealpha($pngImage, 0, 0, 0, 127);
                imagefill($pngImage, 0, 0, $transparent);
                
                imagecopy($pngImage, $image, 0, 0, 0, 0, $width, $height);
                
                if (imagepng($pngImage, $targetFile, 9)) {
                    $success_message = "✅ Background atualizado com sucesso!";
                } else {
                    $error_message = "❌ Erro ao gravar na pasta img/.";
                }
                imagedestroy($image);
                imagedestroy($pngImage);
                if (isset($resizedImage)) imagedestroy($resizedImage);
            }
        }
    }
}

$bg_exists = file_exists($targetFile);
$bg_url = $bg_exists ? $targetFile . '?t=' . time() : '';

$page_content = '
<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Background Manager - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --dark-bg: #0f172a;
            --darker-bg: #0a0f1c;
            --card-bg: #1e293b;
            --border-color: #334155;
            --accent-color: #8b5cf6;
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
        }
        
        body {
            background: linear-gradient(135deg, var(--darker-bg) 0%, var(--dark-bg) 100%);
            color: var(--text-primary);
            min-height: 100vh;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            position: relative;
        }
        
        /* Preview do background em tempo real */
        body.bg-preview::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url("' . $bg_url . '");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            opacity: 0.15;
            z-index: -1;
            filter: blur(0px);
        }
        
        .card-custom {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(10px);
            transition: transform 0.3s ease;
        }
        
        .card-custom:hover {
            transform: translateY(-5px);
        }
        
        .card-header-custom {
            background: linear-gradient(135deg, var(--accent-color) 0%, #7c3aed 100%);
            border-bottom: 1px solid var(--border-color);
            border-radius: 16px 16px 0 0 !important;
            padding: 1.75rem;
        }
        
        .btn-custom {
            background: linear-gradient(135deg, var(--accent-color) 0%, #7c3aed 100%);
            border: none;
            color: white;
            font-weight: 600;
            padding: 14px 28px;
            border-radius: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(139, 92, 246, 0.3);
        }
        
        .btn-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(139, 92, 246, 0.4);
            color: white;
        }
        
        .form-control-custom {
            background-color: rgba(30, 41, 59, 0.8);
            border: 2px solid var(--border-color);
            color: var(--text-primary);
            padding: 14px 18px;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .form-control-custom:focus {
            background-color: rgba(30, 41, 59, 1);
            border-color: var(--accent-color);
            box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.2);
            color: var(--text-primary);
        }
        
        .preview-section {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            border-radius: 16px;
            padding: 2rem;
            border: 2px solid var(--border-color);
            position: relative;
            overflow: hidden;
            min-height: 400px;
        }
        
        .full-bg-preview {
            position: relative;
            width: 100%;
            height: 300px;
            border-radius: 12px;
            overflow: hidden;
            border: 3px solid var(--border-color);
            background: var(--darker-bg);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }
        
        .bg-preview-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .bg-preview-image:hover {
            transform: scale(1.05);
        }
        
        .bg-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
            padding: 1.5rem;
            color: white;
        }
        
        .effect-controls {
            background: rgba(30, 41, 59, 0.7);
            border-radius: 10px;
            padding: 1.5rem;
            margin-top: 1.5rem;
            border: 1px solid var(--border-color);
        }
        
        .effect-slider {
            width: 100%;
            margin: 10px 0;
        }
        
        .slider-value {
            display: inline-block;
            min-width: 40px;
            text-align: center;
            font-weight: bold;
            color: var(--accent-color);
        }
        
        .dimension-badge {
            background: rgba(139, 92, 246, 0.2);
            color: var(--accent-color);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            border: 1px solid rgba(139, 92, 246, 0.3);
        }
        
        .recommended-size {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.1), rgba(21, 128, 61, 0.1));
            border: 1px solid rgba(34, 197, 94, 0.3);
            border-radius: 10px;
            padding: 1rem;
            margin-top: 1rem;
        }
        
        .info-card {
            background: rgba(30, 41, 59, 0.5);
            border-radius: 12px;
            padding: 1.25rem;
            border-left: 4px solid var(--accent-color);
            margin-bottom: 1rem;
        }
        
        .effect-preview {
            height: 100px;
            border-radius: 10px;
            overflow: hidden;
            position: relative;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body class="' . ($bg_exists ? 'bg-preview' : '') . '">
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-12 col-xxl-10">
                <div class="card-custom">
                    <div class="card-header-custom">
                        <div class="d-flex align-items-center justify-content-center flex-wrap">
                            <div class="bg-white bg-opacity-20 p-3 rounded-circle me-3">
                                <i class="fas fa-image fa-2x text-white"></i>
                            </div>
                            <div class="text-center text-md-start">
                                <h1 class="mb-1 fw-bold">🎨 Gerenciador de Background</h1>
                                <p class="mb-0 opacity-90">Personalize o plano de fundo do Aplicativo</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body p-4 p-lg-5">
                        ' . (!empty($success_message) ? '
                        <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 12px; border: none;">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle fa-2x me-3"></i>
                                <div>
                                    <h5 class="alert-heading mb-1">Sucesso!</h5>
                                    ' . $success_message . '
                                </div>
                            </div>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>' : '') . '
                        
                        ' . (!empty($error_message) ? '
                        <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 12px; border: none;">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                                <div>
                                    <h5 class="alert-heading mb-1">Atenção!</h5>
                                    ' . $error_message . '
                                </div>
                            </div>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>' : '') . '

                        <div class="row g-4">
                            <!-- Coluna de Upload -->
                            <div class="col-12 col-lg-7">
                                <div class="p-4 rounded-3 h-100" style="background: rgba(30, 41, 59, 0.6); border: 1px solid var(--border-color);">
                                    <div class="d-flex align-items-center mb-4">
                                        <div class="bg-gradient p-3 rounded-circle me-3" style="background: linear-gradient(135deg, var(--accent-color), #7c3aed);">
                                            <i class="fas fa-cloud-upload-alt fa-2x text-white"></i>
                                        </div>
                                        <div>
                                            <h3 class="mb-1">📤 Upload de Background</h3>
                                            <p class="text-muted mb-0">Envie uma imagem para ser o plano de fundo do sistema</p>
                                        </div>
                                    </div>
                                    
                                    <form method="POST" enctype="multipart/form-data" id="uploadForm">
                                        <div class="mb-4">
                                            <label class="form-label fw-bold mb-3 fs-5">Selecione a imagem de fundo:</label>
                                            <div class="position-relative">
                                                <input type="file" class="form-control form-control-custom form-control-lg" id="bgInput" name="background" accept="image/png, image/jpeg, image/jpg, image/gif" required>
                                                <div class="mt-3">
                                                    <div class="recommended-size">
                                                        <h6 class="text-success mb-2"><i class="fas fa-bullseye me-2"></i>Dimensões Recomendadas</h6>
                                                        <div class="row">
                                                            <div class="col-6">
                                                                <div class="text-center p-2">
                                                                    <div class="dimension-badge">1920x1080</div>
                                                                    <small class="text-muted d-block mt-1">Full HD</small>
                                                                </div>
                                                            </div>
                                                            <div class="col-6">
                                                                <div class="text-center p-2">
                                                                    <div class="dimension-badge">2560x1440</div>
                                                                    <small class="text-muted d-block mt-1">2K QHD</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <p class="text-muted mt-2 mb-0"><small>Imagens maiores serão redimensionadas automaticamente</small></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="info-card">
                                            <div class="d-flex">
                                                <i class="fas fa-lightbulb text-warning fa-lg mt-1 me-3"></i>
                                                <div>
                                                    <h6 class="mb-2">Dicas para um bom background</h6>
                                                    <ul class="mb-0 ps-3" style="font-size: 0.9rem;">
                                                        <li>Use imagens de alta resolução</li>
                                                        <li>Prefira cores escuras para melhor legibilidade</li>
                                                        <li>Evite textos ou elementos distrativos no centro</li>
                                                        <li>Formato PNG com transparência (opcional)</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-custom btn-lg w-100 py-3 mt-3">
                                            <i class="fas fa-upload me-2"></i>Definir como Background do Sistema
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Coluna de Visualização -->
                            <div class="col-12 col-lg-5">
                                <div class="preview-section h-100">
                                    <div class="text-center mb-4">
                                        <div class="bg-gradient p-3 rounded-circle d-inline-block mb-3" style="background: linear-gradient(135deg, #3b82f6, #1d4ed8);">
                                            <i class="fas fa-desktop fa-2x text-white"></i>
                                        </div>
                                        <h3 class="mb-2">🖥️ Visualização</h3>
                                        <p class="text-muted">Como seu background aparecerá</p>
                                    </div>
                                    
                                    <div class="full-bg-preview">
                                        ' . ($bg_exists ? '
                                        <img src="' . $bg_url . '" id="bgPreview" class="bg-preview-image">
                                        ' : '
                                        <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center">
                                            <i class="fas fa-mountain fa-4x text-secondary mb-3 opacity-50"></i>
                                            <p class="text-muted mb-1">Nenhum background definido</p>
                                            <small class="text-muted">Faça upload de uma imagem</small>
                                        </div>
                                        ') . '
                                        
                                        ' . ($bg_exists ? '
                                        <div class="bg-overlay">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-0">Background Ativo</h6>
                                                    <small>bg.png</small>
                                                </div>
                                                <div>
                                                    <span class="badge bg-dark me-2">Sistema</span>
                                                    <span class="badge bg-primary">Ativo</span>
                                                </div>
                                            </div>
                                        </div>
                                        ' : '') . '
                                    </div>
                                    
                                    ' . ($bg_exists ? '
                                    <div class="d-flex gap-2 justify-content-center mt-4 flex-wrap">
                                        <a href="' . $targetFile . '" download class="btn btn-outline-light btn-lg flex-grow-1">
                                            <i class="fas fa-download me-2"></i>Baixar BG
                                        </a>
                                        <button onclick="confirmDelete()" class="btn btn-outline-danger btn-lg flex-grow-1">
                                            <i class="fas fa-trash-alt me-2"></i>Remover
                                        </button>
                                        <button onclick="applyEffects()" class="btn btn-outline-primary btn-lg flex-grow-1">
                                            <i class="fas fa-magic me-2"></i>Efeitos
                                        </button>
                                    </div>
                                    
                                    <div class="effect-controls" id="effectControls" style="display: none;">
                                        <h6 class="mb-3"><i class="fas fa-sliders-h me-2"></i>Ajustes do Background</h6>
                                        
                                        <div class="mb-3">
                                            <label class="form-label d-flex justify-content-between">
                                                <span>Opacidade: <span id="opacityValue" class="slider-value">50%</span></span>
                                            </label>
                                            <input type="range" class="effect-slider" id="opacitySlider" min="5" max="100" value="50" oninput="updateOpacity(this.value)">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label d-flex justify-content-between">
                                                <span>Desfoque: <span id="blurValue" class="slider-value">0px</span></span>
                                            </label>
                                            <input type="range" class="effect-slider" id="blurSlider" min="0" max="20" value="0" oninput="updateBlur(this.value)">
                                        </div>
                                        
                                        <div class="d-flex justify-content-between">
                                            <button onclick="resetEffects()" class="btn btn-outline-secondary">
                                                <i class="fas fa-redo me-1"></i> Resetar
                                            </button>
                                            <button onclick="saveEffects()" class="btn btn-primary">
                                                <i class="fas fa-save me-1"></i> Aplicar
                                            </button>
                                        </div>
                                    </div>
                                    ' : '
                                    <div class="text-center py-4">
                                        <div class="alert alert-info" style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3);">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Após fazer o upload, você poderá ajustar efeitos e visualizar o background.
                                        </div>
                                    </div>
                                    ') . '
                                </div>
                            </div>
                        </div>
                        
                        <!-- Status do Sistema -->
                        <div class="row mt-5">
                            <div class="col-12">
                                <div class="p-4 rounded-3" style="background: rgba(30, 41, 59, 0.4); border: 1px solid var(--border-color);">
                                    <h5 class="mb-3"><i class="fas fa-info-circle text-info me-2"></i>Informações do Background</h5>
                                    <div class="row">
                                        <div class="col-md-3 col-6 mb-3">
                                            <div class="text-center p-3 rounded" style="background: rgba(30, 41, 59, 0.6);">
                                                <div class="fs-1 mb-2">
                                                    ' . ($bg_exists ? '✅' : '❌') . '
                                                </div>
                                                <div class="fw-bold">Status</div>
                                                <small class="text-muted">' . ($bg_exists ? 'Ativo' : 'Não configurado') . '</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-6 mb-3">
                                            <div class="text-center p-3 rounded" style="background: rgba(30, 41, 59, 0.6);">
                                                <div class="fs-1 mb-2">
                                                    <i class="fas fa-file-image text-primary"></i>
                                                </div>
                                                <div class="fw-bold">Arquivo</div>
                                                <small class="text-muted">bg.png</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-6 mb-3">
                                            <div class="text-center p-3 rounded" style="background: rgba(30, 41, 59, 0.6);">
                                                <div class="fs-1 mb-2">
                                                    <i class="fas fa-folder text-warning"></i>
                                                </div>
                                                <div class="fw-bold">Localização</div>
                                                <small class="text-muted">/img/bg.png</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-6 mb-3">
                                            <div class="text-center p-3 rounded" style="background: rgba(30, 41, 59, 0.6);">
                                                <div class="fs-1 mb-2">
                                                    <i class="fas fa-sync-alt text-success"></i>
                                                </div>
                                                <div class="fw-bold">Atualização</div>
                                                <small class="text-muted">Instantânea</small>
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
    document.getElementById("bgInput").onchange = function (evt) {
        const [file] = this.files;
        if (file) {
            // Verificar tamanho do arquivo (max 10MB para backgrounds)
            if (file.size > 10 * 1024 * 1024) {
                alert("Arquivo muito grande! Tamanho máximo: 10MB");
                this.value = "";
                return;
            }
            
            // Criar preview
            const preview = document.getElementById("bgPreview") || createPreviewElement();
            preview.src = URL.createObjectURL(file);
            
            // Atualizar visualização em tempo real
            document.body.classList.add("bg-preview");
            document.body.style.backgroundImage = `url(${preview.src})`;
            
            // Mostrar preview no container
            const previewContainer = document.querySelector(".full-bg-preview");
            const placeholder = previewContainer.querySelector(".text-muted");
            if (placeholder) placeholder.style.display = "none";
            preview.style.display = "block";
        }
    }
    
    function createPreviewElement() {
        const container = document.querySelector(".full-bg-preview");
        const img = document.createElement("img");
        img.id = "bgPreview";
        img.className = "bg-preview-image";
        container.insertBefore(img, container.firstChild);
        return img;
    }
    
    // Confirmação para deletar
    function confirmDelete() {
        if (confirm("⚠️ Tem certeza que deseja remover o background atual?\n\nEsta ação não pode ser desfeita.")) {
            window.location.href = "?action=delete_bg";
        }
    }
    
    // Controles de efeitos
    function applyEffects() {
        const controls = document.getElementById("effectControls");
        controls.style.display = controls.style.display === "none" ? "block" : "none";
    }
    
    function updateOpacity(value) {
        document.getElementById("opacityValue").textContent = value + "%";
        document.body.style.opacity = value / 100;
    }
    
    function updateBlur(value) {
        document.getElementById("blurValue").textContent = value + "px";
        document.body.style.filter = `blur(${value}px)`;
    }
    
    function resetEffects() {
        document.getElementById("opacitySlider").value = 50;
        document.getElementById("blurSlider").value = 0;
        updateOpacity(50);
        updateBlur(0);
    }
    
    function saveEffects() {
        alert("Configurações de efeito aplicadas (visualização apenas). Para salvar permanentemente, ajuste o CSS do sistema.");
        document.getElementById("effectControls").style.display = "none";
    }
    
    // Adicionar SweetAlert se disponível para confirmações mais bonitas
    window.addEventListener("DOMContentLoaded", function() {
        if (typeof Swal === "undefined") {
            const script = document.createElement("script");
            script.src = "https://cdn.jsdelivr.net/npm/sweetalert2@11";
            document.head.appendChild(script);
        }
        
        // Se houver background, aplicar efeito de preview na página
        if (' . ($bg_exists ? 'true' : 'false') . ') {
            document.body.classList.add("bg-preview");
        }
    });
    </script>
</body>
</html>
';

// Se você ainda usa o layout.php, mantenha esta linha:
include 'includes/layout.php';
?>