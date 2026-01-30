<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'session_check.php';

/* ==========================
   JSON NO MESMO DIRETÓRIO
========================== */
$jsonFile = __DIR__ . '/maintenance.json';

if (!file_exists($jsonFile)) {
    die('Arquivo maintenance.json não encontrado.');
}

$data = json_decode(file_get_contents($jsonFile), true);

/* Valores atuais */
$btnCanais = (bool)($data['btnCanais'] ?? false);
$btnFilmes = (bool)($data['btnFilmes'] ?? false);
$btnSeries = (bool)($data['btnSeries'] ?? false);
$message   = $data['message'] ?? '';

/* ==========================
   SALVAR VIA AJAX
========================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $data['btnCanais'] = ($_POST['btnCanais'] ?? 0) == 1;
    $data['btnFilmes'] = ($_POST['btnFilmes'] ?? 0) == 1;
    $data['btnSeries'] = ($_POST['btnSeries'] ?? 0) == 1;
    $data['message']   = trim($_POST['message'] ?? '');

    if ($data['message'] === '') {
        echo json_encode([
            'status' => 'error',
            'message' => '⚠️ A mensagem de manutenção é obrigatória.'
        ]);
        exit;
    }

    file_put_contents(
        $jsonFile,
        json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );

    echo json_encode([
        'status' => 'success',
        'message' => '🛠️ Configurações de manutenção salvas com sucesso!'
    ]);
    exit;
}

$page_title = "🛠️ Modo Manutenção";

$page_content = '
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="card shadow-sm border-0">
    <div class="card-header bg-warning">
        <h3 class="card-title mb-0">🛠️ Gerenciar Modo Manutenção</h3>
    </div>

    <div class="card-body">

        <div class="alert alert-warning">
            ⚠️ Marque os recursos que devem ficar <strong>bloqueados</strong> durante a manutenção.
        </div>

        <div class="row mb-4">

            <div class="col-md-4">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="btnCanais" ' . ($btnCanais ? 'checked' : '') . '>
                    <label class="form-check-label fw-bold" for="btnCanais">📺 Canais</label>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="btnFilmes" ' . ($btnFilmes ? 'checked' : '') . '>
                    <label class="form-check-label fw-bold" for="btnFilmes">🎬 Filmes</label>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="btnSeries" ' . ($btnSeries ? 'checked' : '') . '>
                    <label class="form-check-label fw-bold" for="btnSeries">📀 Séries</label>
                </div>
            </div>

        </div>

        <div class="mb-4">
            <label class="form-label fw-bold">📝 Mensagem de Manutenção</label>
            <textarea class="form-control" id="message" rows="3">' . htmlspecialchars($message, ENT_QUOTES) . '</textarea>
        </div>

        <div class="text-end">
            <button class="btn btn-success btn-lg px-4" id="btnSalvar">
                💾 Salvar Configurações
            </button>
        </div>

    </div>
</div>

<script>
$(function () {

    $("#btnSalvar").click(function () {

        const data = {
            btnCanais: $("#btnCanais").is(":checked") ? 1 : 0,
            btnFilmes: $("#btnFilmes").is(":checked") ? 1 : 0,
            btnSeries: $("#btnSeries").is(":checked") ? 1 : 0,
            message: $("#message").val().trim()
        };

        if (!data.message) {
            Swal.fire("⚠️ Atenção", "A mensagem de manutenção é obrigatória.", "warning");
            return;
        }

        Swal.fire({
            title: "🛠️ Confirmar",
            text: "Deseja salvar as configurações de manutenção?",
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Sim, salvar",
            cancelButtonText: "Cancelar"
        }).then((result) => {

            if (result.isConfirmed) {
                $.post(window.location.href, data, function (response) {

                    if (response.status === "success") {
                        Swal.fire("✅ Sucesso", response.message, "success");
                    } else {
                        Swal.fire("❌ Erro", response.message, "error");
                    }

                }, "json").fail(function () {
                    Swal.fire("❌ Erro", "Erro ao salvar o arquivo.", "error");
                });
            }
        });
    });

});
</script>
';

include 'includes/layout.php';
