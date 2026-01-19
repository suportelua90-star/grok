<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$static_url = dirname($_SERVER['SCRIPT_NAME']) . '/assets/';
include 'session_check.php';

$keyFilePath = __DIR__ . '/api/key.json';
$keys = file_exists($keyFilePath) ? json_decode(file_get_contents($keyFilePath), true) : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? '';
    $macAddress = strtoupper(trim($_POST['mac_address'] ?? ''));
    $message = trim($_POST['message'] ?? '');

    try {
        if ($action === 'add' || $action === 'edit') {
            $keys[$macAddress] = [
                'key' => $keys[$macAddress]['key'] ?? bin2hex(random_bytes(8)),
                'message' => $message
            ];
        } elseif ($action === 'delete') {
            unset($keys[$macAddress]);
        }
        file_put_contents($keyFilePath, json_encode($keys, JSON_PRETTY_PRINT));
        echo json_encode(['status' => 'success', 'message' => ucfirst($action) . ' realizado com sucesso.']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Erro: ' . $e->getMessage()]);
    }
    exit;
}

$page_title = "Mensagens por MAC";

$page_content = '
<div class="card card-flush shadow-sm border-0">
    <div class="card-header align-items-center border-0 pt-6 pb-0">
        <div class="card-title">
            <div class="d-flex align-items-center position-relative my-1">
                <i class="ki-outline ki-magnifier fs-2 position-absolute ms-4 text-gray-500"></i>
                <input type="text" id="search_message" class="form-control form-control-solid w-300px ps-12" placeholder="Buscar MAC ou mensagem..." />
            </div>
        </div>
        <div class="card-toolbar">
            <button type="button" class="btn btn-sm btn-primary" onclick="openAddModal()">
                <i class="ki-outline ki-plus fs-2 me-2"></i>Adicionar Mensagem
            </button>
        </div>
    </div>

    <div class="card-body pt-3">
        <div class="table-responsive">
            <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4" id="message_table">
                <thead>
                    <tr class="fw-bold text-muted bg-light">
                        <th class="ps-4 min-w-180px">MAC Address</th>
                        <th class="min-w-180px">Chave</th>
                        <th class="min-w-300px">Mensagem</th>
                        <th class="min-w-120px text-end pe-4">Ações</th>
                    </tr>
                </thead>
                <tbody id="message_table_body" class="fw-semibold text-gray-600">';

foreach ($keys as $mac => $data) {
    $page_content .= "
        <tr data-mac='{$mac}'>
            <td class='ps-4'>{$mac}</td>
            <td>{$data['key']}</td>
            <td class='message-cell'>{$data['message']}</td>
            <td class='text-end pe-4'>
                <button class='btn btn-sm btn-warning me-2' onclick=\"editMessage('{$mac}')\">
                    <i class='ki-outline ki-pencil fs-5'></i> Editar
                </button>
                <button class='btn btn-sm btn-danger' onclick=\"deleteMessage('{$mac}')\">
                    <i class='ki-outline ki-trash fs-5'></i> Excluir
                </button>
            </td>
        </tr>";
}

$page_content .= '
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="message_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-550px">
        <div class="modal-content rounded-3 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h2 id="modal_title" class="fw-bolder fs-3">Adicionar Mensagem</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-4">
                <div class="mb-6">
                    <label class="form-label fw-semibold">MAC Address</label>
                    <input type="text" class="form-control form-control-solid" id="mac_address" placeholder="00:1A:79:XX:XX:XX" required>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Mensagem</label>
                    <input type="text" class="form-control form-control-solid" id="message" placeholder="Digite a mensagem para este MAC" required>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="saveMessage()">
                    <i class="ki-outline ki-save fs-2 me-2"></i>Salvar
                </button>
            </div>
        </div>
    </div>
</div>
';

include 'includes/layout.php';
?>

<script>
let dataTable = $('#message_table').DataTable({
    language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json' },
    pageLength: 10,
    order: [[0, 'asc']]
});

document.getElementById("search_message").addEventListener("input", e => {
    dataTable.search(e.target.value).draw();
});

function openAddModal() {
    document.getElementById('modal_title').textContent = 'Adicionar Mensagem';
    document.getElementById('mac_address').value = '';
    document.getElementById('mac_address').readOnly = false;
    document.getElementById('message').value = '';
    new bootstrap.Modal(document.getElementById('message_modal')).show();
}

function editMessage(mac) {
    const row = document.querySelector(`tr[data-mac="${mac}"]`);
    if (!row) return Swal.fire('Erro', 'MAC não encontrado', 'error');

    const message = row.querySelector('.message-cell')?.textContent.trim();
    document.getElementById('modal_title').textContent = 'Editar Mensagem';
    document.getElementById('mac_address').value = mac;
    document.getElementById('mac_address').readOnly = true;
    document.getElementById('message').value = message;
    new bootstrap.Modal(document.getElementById('message_modal')).show();
}

function saveMessage() {
    const mac = document.getElementById('mac_address').value.trim().toUpperCase();
    const msg = document.getElementById('message').value.trim();

    if (!mac || !msg) return Swal.fire('Erro', 'Preencha MAC e Mensagem', 'error');

    const action = document.getElementById('mac_address').readOnly ? 'edit' : 'add';

    fetch('', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ action, mac_address: mac, message: msg })
    })
    .then(r => r.json())
    .then(data => {
        Swal.fire(data.status === 'success' ? 'Sucesso' : 'Erro', data.message, data.status);
        if (data.status === 'success') location.reload();
    })
    .catch(() => Swal.fire('Erro', 'Falha na conexão', 'error'));
}

function deleteMessage(mac) {
    Swal.fire({
        title: 'Confirmar?',
        text: `Excluir mensagem do MAC ${mac}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonText: 'Cancelar',
        confirmButtonText: 'Sim, excluir'
    }).then(r => {
        if (r.isConfirmed) {
            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ action: 'delete', mac_address: mac })
            })
            .then(res => res.json())
            .then(data => {
                Swal.fire(data.status === 'success' ? 'Excluído' : 'Erro', data.message, data.status);
                if (data.status === 'success') location.reload();
            })
            .catch(() => Swal.fire('Erro', 'Falha ao excluir', 'error'));
        }
    });
}
</script>