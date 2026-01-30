<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'session_check.php';

/* ==========================
   JSON NO MESMO DIRETÓRIO
========================== */
$jsonFile = __DIR__ . '/alerta_status.json';

if (!file_exists($jsonFile)) {
    die('Arquivo alerta_status.json não encontrado.');
}

$data = json_decode(file_get_contents($jsonFile), true);

$alertaAtivo   = $data['alerta_vencimento_ativo'] ?? false;
$mensagemHoje  = $data['mensagem_vencimento_hoje'] ?? '';
$mensagemDias  = $data['mensagem_vencimento_dias'] ?? '';

/* ==========================
   SALVAR VIA AJAX
========================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $alertaAtivo   = filter_var($_POST['alerta_ativo'] ?? false, FILTER_VALIDATE_BOOLEAN);
    $mensagemHoje  = trim($_POST['mensagem_hoje'] ?? '');
    $mensagemDias  = trim($_POST['mensagem_dias'] ?? '');

    if ($mensagemHoje === '' || $mensagemDias === '') {
        echo json_encode([
            'status' => 'error',
            'message' => '⚠️ Preencha todas as mensagens!'
        ]);
        exit;
    }

    $data['alerta_vencimento_ativo']  = $alertaAtivo;
    $data['mensagem_vencimento_hoje'] = $mensagemHoje;
    $data['mensagem_vencimento_dias'] = $mensagemDias;

    file_put_contents(
        $jsonFile,
        json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );

    echo json_encode([
        'status' => 'success',
        'message' => '✅ Configurações salvas com sucesso!'
    ]);
    exit;
}

$page_title = "🔔 Alertas de Vencimento";

$page_content = '
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="card shadow-sm border-0">
    <div class="card-header bg-primary text-white">
        <h3 class="card-title mb-0">🔔 Gerenciar Alertas de Assinatura</h3>
    </div>

    <div class="card-body">

        <div class="alert alert-info">
            💡 Essas mensagens são exibidas automaticamente para o usuário conforme a data de vencimento.
        </div>

        <!-- ATIVAR / DESATIVAR -->
        <div class="form-check form-switch mb-4">
            <input class="form-check-input" type="checkbox" id="alertaAtivo" ' . ($alertaAtivo ? 'checked' : '') . '>
            <label class="form-check-label fw-bold" for="alertaAtivo">
                Ativar alerta de vencimento
            </label>
        </div>

        <div class="row">

            <div class="col-md-6 mb-4">
                <div class="card h-100 border-primary">
                    <div class="card-body">
                        <h5 class="card-title">📅 Vencimento Hoje</h5>
                        <input type="text" class="form-control mt-3"
                            id="mensagemHoje"
                            value="' . htmlspecialchars($mensagemHoje, ENT_QUOTES) . '">
                        <small class="text-muted mt-2 d-block">
                            Exemplo: <strong>Sua assinatura vence hoje!</strong>
                        </small>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card h-100 border-warning">
                    <div class="card-body">
                        <h5 class="card-title">⏳ Vencimento em Dias</h5>
                        <input type="text" class="form-control mt-3"
                            id="mensagemDias"
                            value="' . htmlspecialchars($mensagemDias, ENT_QUOTES) . '">
                        <small class="text-muted mt-2 d-block">
                            Use <strong>%d</strong> para os dias restantes
                        </small>
                    </div>
                </div>
            </div>

        </div>

        <div class="text-end mt-4">
            <button class="btn btn-success btn-lg px-4" id="btnSalvar">
                💾 Salvar Alterações
            </button>
        </div>

    </div>
</div>

<script>
$(function () {

    $("#btnSalvar").click(function () {

        const mensagemHoje = $("#mensagemHoje").val().trim();
        const mensagemDias = $("#mensagemDias").val().trim();
        const alertaAtivo  = $("#alertaAtivo").is(":checked");

        if (!mensagemHoje || !mensagemDias) {
            Swal.fire("⚠️ Atenção", "Preencha todas as mensagens.", "warning");
            return;
        }

        Swal.fire({
            title: "💾 Confirmar",
            text: "Deseja salvar as configurações de alerta?",
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Sim, salvar",
            cancelButtonText: "Cancelar"
        }).then((result) => {

            if (result.isConfirmed) {
                $.post(window.location.href, {
                    alerta_ativo: alertaAtivo,
                    mensagem_hoje: mensagemHoje,
                    mensagem_dias: mensagemDias
                }, function (response) {

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
