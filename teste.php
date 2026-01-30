<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

include 'session_check.php';

$db_file = __DIR__ . "/ibo_panel.db";
$conn = new PDO("sqlite:$db_file");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$page_title = "🤖 Gerenciar Chatbots";

// 🔹 Obter lista de DNS para o <select>
$dns_stmt = $conn->query("SELECT id, title FROM dns ORDER BY title ASC");
$dns_list = $dns_stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $action = $_POST['action'] ?? '';
    $id = $_POST['id'] ?? null;
    $bot_url = $_POST['bot_url'] ?? '';
    $bot_dns = $_POST['bot_dns'] ?? '';
    $status = $_POST['status'] ?? 'Ativo';
    $bot_name = $_POST['bot_name'] ?? 'Chatbot';

    try {
        if ($action === 'add') {
            $stmt = $conn->prepare("INSERT INTO chatbot (bot_url, bot_dns, bot_status, bot_name) VALUES (?, ?, ?, ?)");
            $stmt->execute([$bot_url, $bot_dns, $status === 'Ativo' ? 1 : 0, $bot_name]);
            echo json_encode(['status' => 'success', 'message' => '✅ Chatbot adicionado com sucesso!']);
        } elseif ($action === 'edit') {
            $stmt = $conn->prepare("UPDATE chatbot SET bot_url=?, bot_dns=?, bot_status=?, bot_name=? WHERE id=?");
            $stmt->execute([$bot_url, $bot_dns, $status === 'Ativo' ? 1 : 0, $bot_name, $id]);
            echo json_encode(['status' => 'success', 'message' => '📝 Chatbot atualizado com sucesso!']);
        } elseif ($action === 'toggle_status') {
            $stmt = $conn->prepare("UPDATE chatbot SET bot_status = NOT bot_status WHERE id=?");
            $stmt->execute([$id]);
            echo json_encode(['status' => 'success', 'message' => '🔄 Status alterado com sucesso!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => '⚠️ Ação inválida.']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => '❌ Erro: ' . $e->getMessage()]);
    }
    exit;
}

// Obter lista de chatbots
$stmt = $conn->query("SELECT * FROM chatbot ORDER BY id DESC");
$chatbots = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 🔹 Criar o <select> DNS em HTML
$dns_options_html = '';
foreach ($dns_list as $dns) {
    $dns_options_html .= '<option value="' . $dns['id'] . '">🌐 ' . htmlspecialchars($dns['title']) . '</option>';
}

$page_content = '
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .chatbot-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        transition: transform 0.3s, box-shadow 0.3s;
        margin-bottom: 20px;
        overflow: hidden;
    }
    .chatbot-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.12);
    }
    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .status-active {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    .status-inactive {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    .chatbot-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 15px 20px;
        border-bottom: none;
    }
    .btn-action {
        border-radius: 8px;
        padding: 8px 15px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s;
    }
    .btn-action:hover {
        transform: scale(1.05);
    }
    .url-truncate {
        max-width: 250px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: inline-block;
    }
    .modal-custom {
        border-radius: 15px;
        overflow: hidden;
    }
    .modal-header-custom {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-bottom: none;
        padding: 20px 30px;
    }
    .modal-body-custom {
        padding: 30px;
    }
    .form-control-custom {
        border-radius: 10px;
        border: 2px solid #e0e0e0;
        padding: 12px 15px;
        transition: all 0.3s;
    }
    .form-control-custom:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
</style>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">🤖 Gerenciar Chatbots</h2>
            <p class="text-muted mb-0">Gerencie todos os seus chatbots em um só lugar</p>
        </div>
  
    </div>

    <div class="row" id="chatbotsGrid">';

foreach ($chatbots as $bot) {
    // Buscar título do DNS relacionado
    $dns_name = '';
    foreach ($dns_list as $dns) {
        if ($dns['id'] == $bot['bot_dns']) {
            $dns_name = $dns['title'];
            break;
        }
    }

    $statusClass = $bot['bot_status'] == 1 ? 'status-active' : 'status-inactive';
    $statusIcon = $bot['bot_status'] == 1 ? 'bi-check-circle' : 'bi-x-circle';
    $statusText = $bot['bot_status'] == 1 ? 'Ativo' : 'Inativo';
    
    $page_content .= '
        <div class="col-md-6 col-lg-4">
            <div class="card chatbot-card">
                <div class="card-header chatbot-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-robot"></i> ' . htmlspecialchars($bot['bot_name']) . '
                    </h5>
                    <span class="status-badge ' . $statusClass . '">
                        <i class="bi ' . $statusIcon . '"></i> ' . $statusText . '
                    </span>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small mb-1"><i class="bi bi-link-45deg"></i> URL do Chatbot</label>
                        <div class="d-flex align-items-center">
                            <input type="text" class="form-control form-control-sm bg-light border-0 url-truncate" 
                                   value="' . htmlspecialchars($bot['bot_url']) . '" readonly>
                            <button class="btn btn-sm btn-outline-secondary ms-2" onclick="copyToClipboard(\'' . htmlspecialchars($bot['bot_url']) . '\')">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="text-muted small mb-1"><i class="bi bi-globe"></i> DNS Associado</label>
                        <p class="mb-0 fw-semibold">' . htmlspecialchars($dns_name) . '</p>
                    </div>
                    
                    <div class="row">
                        <div class="col">
                            <button class="btn btn-warning btn-action w-100 editBtn" 
                                data-id="' . $bot['id'] . '" 
                                data-url="' . htmlspecialchars($bot['bot_url']) . '" 
                                data-dns="' . $bot['bot_dns'] . '" 
                                data-status="' . $statusText . '" 
                                data-name="' . htmlspecialchars($bot['bot_name']) . '">
                                <i class="bi bi-pencil"></i> Editar
                            </button>
                        </div>
                        <div class="col">
                            <button class="btn ' . ($bot['bot_status'] == 1 ? 'btn-secondary' : 'btn-success') . ' btn-action w-100 toggleBtn" 
                                data-id="' . $bot['id'] . '" 
                                data-current-status="' . $bot['bot_status'] . '">
                                <i class="bi ' . ($bot['bot_status'] == 1 ? 'bi-pause' : 'bi-play') . '"></i> ' . ($bot['bot_status'] == 1 ? 'Desativar' : 'Ativar') . '
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-top-0 text-end">
                    <small class="text-muted">ID: ' . $bot['id'] . ' | Criado em: ' . date('d/m/Y', strtotime($bot['created_at'] ?? 'now')) . '</small>
                </div>
            </div>
        </div>';
}

$page_content .= '
    </div>
</div>

<!-- Modal para Adicionar/Editar -->
<div class="modal fade" id="chatbotModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-custom">
        <div class="modal-content">
            <div class="modal-header modal-header-custom">
                <h5 class="modal-title" id="modalTitle">🆕 Adicionar Chatbot</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body modal-body-custom">
                <form id="chatbotForm">
                    <input type="hidden" id="bot_id" name="bot_id">
                    
                    <div class="mb-3">
                        <label for="bot_name" class="form-label"><i class="bi bi-tag"></i> Nome do Chatbot</label>
                        <input type="text" class="form-control form-control-custom" id="bot_name" name="bot_name" 
                               placeholder="Digite um nome para identificar o chatbot" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="bot_url" class="form-label"><i class="bi bi-link-45deg"></i> URL do Chatbot</label>
                        <input type="url" class="form-control form-control-custom" id="bot_url" name="bot_url" 
                               placeholder="https://exemplo.com/chatbot" required>
                        <div class="form-text">Insira a URL completa do seu chatbot</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="bot_dns" class="form-label"><i class="bi bi-globe"></i> DNS Associado</label>
                        <select class="form-select form-control-custom" id="bot_dns" name="bot_dns" required>
                            <option value="">🌐 Selecione um DNS</option>
                            ' . $dns_options_html . '
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label for="status" class="form-label"><i class="bi bi-toggle-on"></i> Status</label>
                        <div class="d-flex gap-3">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="status" id="statusActive" value="Ativo" checked>
                                <label class="form-check-label" for="statusActive">
                                    <span class="badge bg-success"><i class="bi bi-check-circle"></i> Ativo</span>
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="status" id="statusInactive" value="Inativo">
                                <label class="form-check-label" for="statusInactive">
                                    <span class="badge bg-danger"><i class="bi bi-x-circle"></i> Inativo</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg btn-action">
                            <i class="bi bi-save"></i> Salvar Chatbot
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        Swal.fire({
            icon: "success",
            title: "Copiado!",
            text: "URL copiada para a área de transferência",
            timer: 1500,
            showConfirmButton: false
        });
    });
}

$(document).ready(function() {
    const modal = new bootstrap.Modal(document.getElementById("chatbotModal"));
    let currentAction = "add";

    // ➕ Adicionar Chatbot
    $("#addChatbotBtn").click(function() {
        currentAction = "add";
        $("#modalTitle").html("<i class=\'bi bi-plus-circle\'></i> Adicionar Chatbot");
        $("#chatbotForm")[0].reset();
        $("#bot_id").val("");
        modal.show();
    });

    // ✏️ Editar Chatbot
    $(document).on("click", ".editBtn", function() {
        currentAction = "edit";
        $("#modalTitle").html("<i class=\'bi bi-pencil\'></i> Editar Chatbot");
        
        $("#bot_id").val($(this).data("id"));
        $("#bot_name").val($(this).data("name"));
        $("#bot_url").val($(this).data("url"));
        $("#bot_dns").val($(this).data("dns"));
        
        if($(this).data("status") === "Ativo") {
            $("#statusActive").prop("checked", true);
        } else {
            $("#statusInactive").prop("checked", true);
        }
        
        modal.show();
    });

    // 🔄 Alternar Status
    $(document).on("click", ".toggleBtn", function() {
        const id = $(this).data("id");
        const currentStatus = $(this).data("current-status");
        const newStatus = currentStatus == 1 ? 0 : 1;
        
        Swal.fire({
            title: "Alterar Status",
            text: "Tem certeza que deseja " + (newStatus == 1 ? "ativar" : "desativar") + " este chatbot?",
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Sim, alterar",
            cancelButtonText: "Cancelar"
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(window.location.href, {
                    action: "toggle_status",
                    id: id
                }, function(response) {
                    if (response.status === "success") {
                        Swal.fire({
                            icon: "success",
                            title: "Status Alterado!",
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => location.reload());
                    } else {
                        Swal.fire("❌ Erro", response.message, "error");
                    }
                }, "json");
            }
        });
    });

    // 📝 Enviar Formulário
    $("#chatbotForm").submit(function(e) {
        e.preventDefault();
        
        const formData = {
            action: currentAction,
            bot_name: $("#bot_name").val(),
            bot_url: $("#bot_url").val(),
            bot_dns: $("#bot_dns").val(),
            status: $("input[name=\'status\']:checked").val()
        };
        
        if (currentAction === "edit") {
            formData.id = $("#bot_id").val();
        }
        
        $.post(window.location.href, formData, function(response) {
            if (response.status === "success") {
                Swal.fire({
                    icon: "success",
                    title: "Sucesso!",
                    text: response.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    modal.hide();
                    location.reload();
                });
            } else {
                Swal.fire("❌ Erro", response.message, "error");
            }
        }, "json");
    });
});
</script>
';

include 'includes/layout.php';
?>