<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$static_url = dirname($_SERVER['SCRIPT_NAME']) . '/assets/';
include 'session_check.php';

$page_title = "Temas";

$currentTheme = null;
try {
    $db = new PDO('sqlite:ibo_panel.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $db->query("SELECT theme_id FROM themes LIMIT 1");
    $currentThemeNumber = $stmt->fetchColumn();
    $currentTheme = $currentThemeNumber ? 'theme_' . $currentThemeNumber : null;
} catch (PDOException $e) {
    $currentTheme = null;
}

$themes = range(1, 26);

$page_content = '
<div class="card card-flush shadow-sm border-0">
    <div class="card-header border-0 pt-6 pb-3">
        <h3 class="fw-bolder">Selecionar Tema</h3>
    </div>
    <div class="card-body pt-0">
        <div class="row g-4">';

foreach ($themes as $theme) {
    $theme_id = "theme_$theme";
    $theme_image = $static_url . "media/themes/$theme_id.png";
    $isActive = ($currentTheme === $theme_id);
    $activeClass = $isActive ? 'active-theme shadow-lg' : 'shadow-sm hover-shadow';
    
    $page_content .= '
            <div class="col-md-4 col-sm-6">
                <div class="position-relative theme-card rounded-3 overflow-hidden ' . $activeClass . '" 
                     onclick="updateTheme(\'' . $theme_id . '\')" style="cursor:pointer;">
                    <img src="' . $theme_image . '" alt="Tema ' . $theme . '" class="w-100">
                    <div class="position-absolute bottom-0 start-0 end-0 p-3 bg-gradient-dark text-white text-center">
                        <h6 class="mb-1">Tema ' . $theme . '</h6>
                        ' . ($isActive ? '<span class="badge bg-success">Ativo</span>' : '') . '
                    </div>
                </div>
            </div>';
}

$page_content .= '
        </div>
    </div>
</div>

<style>
.theme-card {
    transition: all 0.4s ease;
    border-radius: 12px;
    overflow: hidden;
    background: #fff;
}
.theme-card:hover {
    transform: scale(1.05);
    box-shadow: 0 15px 35px rgba(0,0,0,0.2);
}
.active-theme {
    border: 4px solid #28a745 !important;
    box-shadow: 0 0 0 8px rgba(40,167,69,0.25);
    transform: scale(1.03);
}
.bg-gradient-dark {
    background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
}
</style>

<script>
function updateTheme(themeId) {
    fetch("actions/themes_actions.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ action: "edit", theme_id: themeId })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            Swal.fire("Sucesso", "Tema atualizado!", "success")
                .then(() => location.reload());
        } else {
            Swal.fire("Erro", data.message || "Falha ao atualizar", "error");
        }
    })
    .catch(() => Swal.fire("Erro", "Erro de conexão", "error"));
}
</script>';

include 'includes/layout.php';
?>