<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$static_url = dirname($_SERVER['SCRIPT_NAME']) . '/assets/';
include 'session_check.php';

$page_title = "Gerenciador de Temas";

// Obter o tema ativo atual
$currentTheme = null;
$currentLayout = null;

try {
    $db = new PDO('sqlite:ibo_panel.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Buscar tema ativo
    $stmt = $db->query("SELECT theme_id, layout_name FROM themes WHERE is_active = 1 LIMIT 1");
    $activeTheme = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($activeTheme) {
        $currentTheme = $activeTheme['theme_id'];
        $currentLayout = $activeTheme['layout_name'];
    }
} catch (PDOException $e) {
    $currentTheme = null;
    $currentLayout = null;
}

// Definir temas disponíveis (apenas 2 temas conforme solicitado)
// Definir temas disponíveis
$themes = [
    1 => [
        'id' => 1,
        'name' => 'Tema 1',
        'layout' => 'activity_main',
        'image' => $static_url . 'media/themes/theme_1.png'
    ],
    2 => [
        'id' => 2,
        'name' => 'Tema 2', 
        'layout' => 'activity_main_v2',
        'image' => $static_url . 'media/themes/theme_2.png'
    ],
    3 => [
        'id' => 3,
        'name' => 'Tema 3', 
        'layout' => 'activity_main_v3',
        'image' => $static_url . 'media/themes/theme_3.png'
    ]
];

// Gerar conteúdo da página
$page_content = '
<div class="card mb-5">
    <div class="card-body">
        <div class="text-center mb-4">
            <h4 class="mb-1">Selecione seu Tema</h4>
            <p class="text-muted">Clique em uma imagem abaixo para aplicar o tema</p>
        </div>
        
        <div class="row justify-content-center">';

foreach ($themes as $theme) {
    $theme_id = $theme['id'];
    $theme_name = $theme['name'];
    $theme_image = $theme['image'];
    
    // Verificar se é o tema atual
    $isActive = ($currentTheme == $theme_id);
    $activeClass = $isActive ? 'active-theme' : '';
    $activeBadge = $isActive ? '<span class="badge bg-success position-absolute top-0 start-0 m-2">✓ Ativo</span>' : '';
    
    $page_content .= '
            <div class="col-md-4 mb-4 text-center">
                <div class="position-relative theme-container">
                    ' . $activeBadge . '
                    <div class="theme-image-wrapper" onclick="applyTheme(event, ' . $theme_id . ')" style="cursor: pointer;">
                        <img src="' . $theme_image . '" alt="' . $theme_name . '" 
                             class="img-fluid rounded shadow ' . $activeClass . '" 
                             style="max-height: 180px; border: 3px solid ' . ($isActive ? '#28a745' : '#dee2e6') . ';">
                    </div>
                    <div class="mt-3">
                        <h5 class="mb-1">' . $theme_name . '</h5>
                        <small class="text-muted">ID: ' . $theme_id . '</small>
                    </div>
                </div>
            </div>';
}

$page_content .= '
        </div>
        
        <div class="row mt-5">
            <div class="col-md-12">
                <div class="alert alert-success" id="currentThemeInfo" style="' . ($currentTheme ? '' : 'display: none;') . '">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="alert-heading mb-1">Tema Ativo</h6>
                            <p class="mb-0" id="themeStatusText">
                                Tema ' . ($currentTheme ? $currentTheme : '') . ' está ativo no sistema.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>';

// CSS personalizado
$page_content .= '
<style>
.theme-container {
    padding: 15px;
    border-radius: 10px;
    transition: all 0.3s ease;
}

.theme-container:hover {
    background-color: #f8f9fa;
    transform: translateY(-5px);
}

.theme-image-wrapper {
    position: relative;
    overflow: hidden;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.theme-image-wrapper:hover img {
    transform: scale(1.05);
}

.theme-image-wrapper img {
    transition: all 0.3s ease;
    width: 100%;
    object-fit: cover;
}

img.active-theme {
    border: 3px solid #28a745 !important;
    box-shadow: 0 0 15px rgba(40, 167, 69, 0.3);
}

.badge.bg-success {
    font-size: 0.75rem;
    padding: 4px 8px;
    z-index: 10;
}

#currentThemeInfo {
    border-left: 4px solid #28a745;
    animation: fadeIn 0.5s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255,255,255,0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    z-index: 20;
}
</style>';

// JavaScript Corrigido
$page_content .= '
<script>
function applyTheme(event, themeId) {
    // Identifica o container do tema clicado
    const themeContainer = event.currentTarget.closest(".theme-container");
    
    // Se o tema clicado já possui o badge de "Ativo", não faz nada
    if (themeContainer.querySelector(".badge.bg-success")) {
        return;
    }
    
    // Mostra loading na imagem clicada
    const loadingHTML = \'<div class="loading-overlay"><div class="spinner-border text-primary"></div></div>\';
    themeContainer.insertAdjacentHTML("beforeend", loadingHTML);
    
    // Envia requisição para o servidor
    fetch("actions/themes_actions.php", {
        method: "POST",
        headers: { 
            "Content-Type": "application/x-www-form-urlencoded",
        },
        body: "action=update&theme_id=" + themeId
    })
    .then(response => response.json())
    .then(data => {
        // Remove loading
        const loading = themeContainer.querySelector(".loading-overlay");
        if (loading) loading.remove();
        
        if (data.success) {
            // Remove classe active de todas as imagens do painel
            document.querySelectorAll(".theme-container img").forEach(img => {
                img.classList.remove("active-theme");
                img.style.borderColor = "#dee2e6";
            });
            
            // Remove badges ativos existentes
            document.querySelectorAll(".badge.bg-success").forEach(badge => {
                badge.remove();
            });
            
            // Adiciona classe active na imagem dentro do container clicado
            const clickedImg = themeContainer.querySelector("img");
            clickedImg.classList.add("active-theme");
            clickedImg.style.borderColor = "#28a745";
            
            // Adiciona o badge de ativo no container atual
            const activeBadge = \'<span class="badge bg-success position-absolute top-0 start-0 m-2">✓ Ativo</span>\';
            themeContainer.insertAdjacentHTML("afterbegin", activeBadge);
            
            // Atualiza informação do tema ativo no rodapé
            document.getElementById("currentThemeInfo").style.display = "block";
            document.getElementById("themeStatusText").textContent = `Tema ${themeId} está ativo no sistema.`;
            
            // Feedback de sucesso
            showSuccessMessage("Tema aplicado com sucesso!");
        } else {
            // Feedback de erro
            showErrorMessage(data.message || "Erro ao aplicar tema");
        }
    })
    .catch(error => {
        // Remove loading em caso de erro
        const loading = themeContainer.querySelector(".loading-overlay");
        if (loading) loading.remove();
        
        console.error("Error:", error);
        showErrorMessage("Erro na comunicação com o servidor");
    });
}

function showSuccessMessage(message) {
    const toastHTML = `
        <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
            <div class="toast show" role="alert">
                <div class="toast-header bg-success text-white">
                    <strong class="me-auto"><i class="fas fa-check-circle"></i> Sucesso</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    ${message}
                </div>
            </div>
        </div>
    `;
    
    const existingToast = document.querySelector(".toast.show");
    if (existingToast) existingToast.remove();
    document.body.insertAdjacentHTML("beforeend", toastHTML);
    
    setTimeout(() => {
        const toast = document.querySelector(".toast.show");
        if (toast) toast.remove();
    }, 3000);
}

function showErrorMessage(message) {
    const toastHTML = `
        <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
            <div class="toast show" role="alert">
                <div class="toast-header bg-danger text-white">
                    <strong class="me-auto"><i class="fas fa-exclamation-circle"></i> Erro</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    ${message}
                </div>
            </div>
        </div>
    `;
    
    const existingToast = document.querySelector(".toast.show");
    if (existingToast) existingToast.remove();
    document.body.insertAdjacentHTML("beforeend", toastHTML);
    
    setTimeout(() => {
        const toast = document.querySelector(".toast.show");
        if (toast) toast.remove();
    }, 4000);
}

document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll(".theme-image-wrapper").forEach(wrapper => {
        wrapper.addEventListener("mouseenter", function() {
            const img = this.querySelector("img");
            if (!img.classList.contains("active-theme")) {
                img.style.borderColor = "#adb5bd";
            }
        });
        
        wrapper.addEventListener("mouseleave", function() {
            const img = this.querySelector("img");
            if (!img.classList.contains("active-theme")) {
                img.style.borderColor = "#dee2e6";
            }
        });
    });
});
</script>';

include 'includes/layout.php';
?>