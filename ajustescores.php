<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

include 'session_check.php';

$page_title = "🎨 Gerenciador de Cores";

// Caminho do arquivo JSON
$jsonFile = 'cores.json';

// Código para cor transparente
define('TRANSPARENT_COLOR', '#00000000');

// Verificar/criar arquivo JSON com valores padrão
if (!file_exists($jsonFile)) {
    $defaultColors = [
        "START_COLOR_XML" => '#4a90e2',
        "END_COLOR_XML" => '#4a90e2',
        "BORDER_COLOR_NORMAL" => '#cccccc',
        "BORDER_COLOR_FOCUS" => '#4a90e2',
        "BORDER_WIDTH_DP" => 2,
        "CORNER_RADIUS_DP" => 8
    ];
    file_put_contents($jsonFile, json_encode($defaultColors, JSON_PRETTY_PRINT));
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newColors = [
        "START_COLOR_XML" => isset($_POST['start_transparent']) ? TRANSPARENT_COLOR : $_POST['start_color'],
        "END_COLOR_XML" => isset($_POST['end_transparent']) ? TRANSPARENT_COLOR : $_POST['end_color'],
        "BORDER_COLOR_NORMAL" => $_POST['border_color'],
        "BORDER_COLOR_FOCUS" => $_POST['border_color_focus'],
        "BORDER_WIDTH_DP" => (int)$_POST['border_width'],
        "CORNER_RADIUS_DP" => (int)$_POST['corner_radius']
    ];

    file_put_contents($jsonFile, json_encode($newColors, JSON_PRETTY_PRINT));
    $success_message = "🎉 Cores atualizadas com sucesso!";
}

// Carregar configurações
$cores = json_decode(file_get_contents($jsonFile), true);
$isStartTransparent = ($cores['START_COLOR_XML'] === TRANSPARENT_COLOR);
$isEndTransparent = ($cores['END_COLOR_XML'] === TRANSPARENT_COLOR);

// Conteúdo da página
$page_content = '
<div class="container-fluid">
    <div class="card radius-10">
        <div class="card-header bg-gradient-primary text-white">
            <center>
                <h4 class="card-title mb-0">🌈 Painel de Cores Personalizadas</h4>
                <p class="mb-0">Personalize as cores do seu sistema</p>
            </center>
        </div>
        <div class="card-body">';

if (isset($success_message)) {
    $page_content .= '
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <span class="h5">' . $success_message . '</span>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>';
}

$page_content .= '
            <form method="POST">
                <div class="row">
                    <!-- Coluna 1 - Cores Principais -->
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">🖌️ Cores de Fundo</h5>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="start_color">🔷 Cor Inicial:</label>
                                    <div class="input-group">
                                        <input type="color" class="form-control" name="start_color" 
                                               value="' . ($isStartTransparent ? '#ffffff' : htmlspecialchars($cores['START_COLOR_XML'])) . '"
                                               ' . ($isStartTransparent ? 'disabled' : '') . '>
                                        <div class="input-group-append">
                                            <div class="input-group-text">
                                                <input type="checkbox" name="start_transparent" id="start_transparent" 
                                                       ' . ($isStartTransparent ? 'checked' : '') . '>
                                                <label for="start_transparent" class="mb-0 ml-2">👻 Transparente</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="end_color">🔶 Cor Final:</label>
                                    <div class="input-group">
                                        <input type="color" class="form-control" name="end_color" 
                                               value="' . ($isEndTransparent ? '#ffffff' : htmlspecialchars($cores['END_COLOR_XML'])) . '"
                                               ' . ($isEndTransparent ? 'disabled' : '') . '>
                                        <div class="input-group-append">
                                            <div class="input-group-text">
                                                <input type="checkbox" name="end_transparent" id="end_transparent" 
                                                       ' . ($isEndTransparent ? 'checked' : '') . '>
                                                <label for="end_transparent" class="mb-0 ml-2">👻 Transparente</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Coluna 2 - Bordas -->
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">📏 Configurações de Bordas</h5>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="border_color">🔳 Cor Normal:</label>
                                    <input type="color" class="form-control" name="border_color" 
                                           value="' . htmlspecialchars($cores['BORDER_COLOR_NORMAL']) . '">
                                </div>

                                <div class="form-group">
                                    <label for="border_color_focus">🔲 Cor em Foco:</label>
                                    <input type="color" class="form-control" name="border_color_focus" 
                                           value="' . htmlspecialchars($cores['BORDER_COLOR_FOCUS']) . '">
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="border_width">📏 Largura (px):</label>
                                            <input type="number" class="form-control" name="border_width" 
                                                   value="' . htmlspecialchars($cores['BORDER_WIDTH_DP']) . '" min="0" max="10">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="corner_radius">🔵 Raio (px):</label>
                                            <input type="number" class="form-control" name="corner_radius" 
                                                   value="' . htmlspecialchars($cores['CORNER_RADIUS_DP']) . '" min="0" max="50">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-3">
                    <button type="submit" class="btn btn-primary btn-lg">
                        💾 Salvar Configurações
                    </button>
                </div>
            </form>
        </div>
        <div class="card-footer text-muted text-center">
            🎨 Dica: Use cores harmoniosas para uma melhor experiência visual
        </div>
    </div>
</div>

<script>
// Habilitar/desabilitar inputs de cor
document.getElementById("start_transparent").addEventListener("change", function() {
    document.querySelector("input[name=\'start_color\']").disabled = this.checked;
    if(this.checked) {
        document.querySelector("input[name=\'start_color\']").value = "#ffffff";
    }
});

document.getElementById("end_transparent").addEventListener("change", function() {
    document.querySelector("input[name=\'end_color\']").disabled = this.checked;
    if(this.checked) {
        document.querySelector("input[name=\'end_color\']").value = "#ffffff";
    }
});
</script>';

include 'includes/layout.php';