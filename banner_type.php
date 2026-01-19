<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$static_url = dirname($_SERVER['SCRIPT_NAME']) . '/assets/';
include 'session_check.php';

$page_title = "Tipo de Banner";

function getLastSelection() {
    $file = "./api/ultima_opcao.txt";
    return file_exists($file) ? trim(file_get_contents($file)) : "";
}

function saveLastSelection($selection) {
    $phpFileMap = [
        'banner_1'  => 'autoads.php',
        'banner_2'  => 'main_movies.php',
        'banner_3'  => 'autocima.php',
        'banner_4'  => 'autop.php',
        'banner_5'  => 'autosinopsebaixo.php',
        'banner_6'  => 'autosinopsecima.php',
        'banner_7'  => 'autosinopselado.php',
        'banner_8'  => 'note.php',
        'banner_9'  => 'menads.php',
        'banner_10' => 'pagina.php'
    ];
    $phpFile = $phpFileMap[$selection] ?? 'unknown.php';
    file_put_contents("./api/opcao.txt", $phpFile);
    file_put_contents("./api/ultima_opcao.txt", $selection);
}

$currentBanner = getLastSelection();

$banners = [
    'banner_1'  => 'Automático',
    'banner_2'  => 'Automático 2',
    'banner_3'  => 'Automático 3',
    'banner_4'  => 'Automático 4 (pequenos)',
    'banner_5'  => 'Automático 5',
    'banner_6'  => 'Automático 6',
    'banner_7'  => 'Automático 7 (ícones inferiores)',
    'banner_8'  => 'Com mensagem',
    'banner_9'  => 'Manual',
    'banner_10' => 'Página Web'
];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['banner_select'])) {
    $selected = $_POST['banner_select'];
    saveLastSelection($selected);
    $response = ['success' => true, 'message' => 'Tipo de banner atualizado!'];
    
    $redirects = [
        'banner_8'  => 'messages.php',
        'banner_9'  => 'banner.php',
        'banner_10' => 'web_url.php'
    ];
    
    if (isset($redirects[$selected])) {
        $response['redirect'] = $redirects[$selected];
    }
    
    echo json_encode($response);
    exit;
}

$page_content = '
<div class="card card-flush shadow-sm border-0">
    <div class="card-header border-0 pt-6 pb-3">
        <h3 class="fw-bolder">Selecionar Tipo de Banner</h3>
    </div>
    <div class="card-body pt-0">
        <div class="row g-4">';

foreach ($banners as $id => $label) {
    $image = $static_url . "media/banner-type/$id.jpeg";
    $active = ($currentBanner === $id);
    $activeClass = $active ? 'active-banner shadow-lg' : 'shadow-sm hover-shadow';
    
    $page_content .= '
            <div class="col-md-4 col-sm-6">
                <div class="position-relative banner-card rounded-3 overflow-hidden ' . $activeClass . '" 
                     onclick="updateBanner(\'' . $id . '\')" style="cursor:pointer;">
                    <img src="' . $image . '" alt="' . $label . '" class="w-100" style="height: 280px; object-fit: cover;">
                    <div class="position-absolute bottom-0 start-0 end-0 p-3 bg-gradient-dark text-white text-center">
                        <h6 class="mb-1">' . $label . '</h6>
                        ' . ($active ? '<span class="badge bg-success">Ativo</span>' : '') . '
                    </div>
                </div>
            </div>';
}

$page_content .= '
        </div>
    </div>
</div>

<style>
.banner-card {
    transition: all 0.4s ease;
    border-radius: 12px;
    overflow: hidden;
    background: #fff;
}
.banner-card:hover {
    transform: scale(1.05);
    box-shadow: 0 15px 35px rgba(0,0,0,0.2);
}
.active-banner {
    border: 4px solid #28a745 !important;
    box-shadow: 0 0 0 8px rgba(40,167,69,0.25);
    transform: scale(1.03);
}
.bg-gradient-dark {
    background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
}
</style>

<script>
function updateBanner(bannerId) {
    fetch("", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({ banner_select: bannerId })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            Swal.fire("Sucesso", data.message, "success")
                .then(() => {
                    if (data.redirect) window.location.href = data.redirect;
                    else location.reload();
                });
        } else {
            Swal.fire("Erro", data.message || "Falha ao atualizar", "error");
        }
    })
    .catch(() => Swal.fire("Erro", "Erro de conexão", "error"));
}
</script>';

include 'includes/layout.php';
?>