<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$static_url = dirname($_SERVER['SCRIPT_NAME']) . '/assets/';
include 'session_check.php';

$page_title = "Gerenciar Servidores";

$page_content = '
<div class="card card-custom gutter-b shadow-lg">
    <div class="card-header border-0 py-5 px-6 d-flex align-items-center justify-content-between bg-primary text-white rounded-top">
        <div class="d-flex align-items-center">
            <span class="svg-icon svg-icon-white svg-icon-2x mr-3">
                <i class="ki-outline ki-server fs-2"></i>
            </span>
            <h3 class="card-title font-weight-bolder m-0">Gerenciar Servidores DNS</h3>
        </div>
        <button type="button" class="btn btn-light btn-sm font-weight-bold" onclick="openAddModal()">
            <i class="ki-outline ki-plus fs-2 mr-2"></i>Adicionar Servidor
        </button>
    </div>

    <div class="card-body pt-0 pb-5 px-6">
        <div class="mb-5 position-relative">
            <i class="ki-outline ki-magnifier fs-2 position-absolute text-muted" style="top: 12px; left: 15px;"></i>
            <input type="text" id="search_dns" class="form-control form-control-solid pl-12" placeholder="Buscar por nome ou URL..." />
        </div>

        <div class="table-responsive">
            <table class="table table-head-custom table-vertical-center table-head-bg table-borderless" id="kt_customers_table">
                <thead>
                    <tr class="text-left text-uppercase font-weight-bold text-muted">
                        <th class="pl-6 min-w-100px">ID</th>
                        <th class="min-w-200px">Nome do Servidor</th>
                        <th class="min-w-300px">URL / DNS</th>
                        <th class="min-w-150px text-right pr-6">Ações</th>
                    </tr>
                </thead>
                <tbody id="dns_table_body" class="font-weight-bold text-dark"></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Adicionar/Editar -->
<div class="modal fade" id="rainbow_dns" tabindex="-1" role="dialog" aria-labelledby="modal_title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content rounded-lg shadow-lg">
            <div class="modal-header bg-primary text-white border-0">
                <h5 id="modal_title" class="modal-title font-weight-bold">Adicionar Novo Servidor</h5>
                <button type="button" class="close text-white" data-bs-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body px-8 py-6">
                <input type="hidden" id="dns_id" name="dns_id">
                
                <div class="form-group mb-6">
                    <label for="dns_title" class="font-weight-bold">Nome do Servidor</label>
                    <input type="text" class="form-control form-control-solid" id="dns_title" name="title" placeholder="Ex: Servidor Principal - Brasil" required>
                </div>

                <div class="form-group mb-4">
                    <label for="dns_url" class="font-weight-bold">URL / Endereço DNS</label>
                    <input type="url" class="form-control form-control-solid" id="dns_url" name="url" placeholder="http://dominio.com:8080/c/get.php" required>
                </div>
            </div>
            <div class="modal-footer border-0 px-8 pb-6">
                <button type="button" class="btn btn-light btn-lg px-6" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary btn-lg px-6 d-flex align-items-center" onclick="saveDNS()">
                    <i class="ki-outline ki-save fs-2 mr-2"></i> Salvar
                </button>
            </div>
        </div>
    </div>
</div>
';

include 'includes/layout.php';
?>

<script>
// Todas as funções JavaScript necessárias

let dataTable;

function loadDNSTable() {
    fetch("actions/dns_actions.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ action: "view" }),
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            const tableBody = document.getElementById("dns_table_body");
            tableBody.innerHTML = "";
            data.data.forEach(record => {
                tableBody.innerHTML += `
                    <tr id="row_${record.id}">
                        <td class="pl-6">${record.id}</td>
                        <td>${record.title}</td>
                        <td><code>${record.url}</code></td>
                        <td class="text-right pr-6">
                            <button class="btn btn-sm btn-warning mr-2" onclick="editDNS(${record.id}, '${record.title.replace(/'/g, "\\'")}', '${record.url.replace(/'/g, "\\'")}')">
                                <i class="ki-outline ki-pencil fs-5"></i> Editar
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteDNS(${record.id})">
                                <i class="ki-outline ki-trash fs-5"></i> Excluir
                            </button>
                        </td>
                    </tr>
                `;
            });

            if (dataTable) dataTable.destroy();
            dataTable = $('#kt_customers_table').DataTable({
                language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json' },
                searching: true,
                paging: true,
                ordering: true,
                info: true,
                responsive: false,
                pageLength: 10
            });

            $('#search_dns').on('keyup', function() {
                dataTable.search(this.value).draw();
            });
        } else {
            alert("Erro ao carregar dados: " + (data.message || "Resposta inválida"));
        }
    })
    .catch(error => {
        console.error("Erro ao carregar tabela:", error);
        alert("Falha ao carregar a lista de servidores.");
    });
}

function openAddModal() {
    document.getElementById("modal_title").innerText = "Adicionar Novo Servidor";
    document.getElementById("dns_id").value = "";
    document.getElementById("dns_title").value = "";
    document.getElementById("dns_url").value = "";
    $('#rainbow_dns').modal('show');
}

function editDNS(id, title, url) {
    document.getElementById("modal_title").innerText = "Editar Servidor";
    document.getElementById("dns_id").value = id;
    document.getElementById("dns_title").value = title;
    document.getElementById("dns_url").value = url;
    $('#rainbow_dns').modal('show');
}

function saveDNS() {
    const id = document.getElementById("dns_id").value;
    const title = document.getElementById("dns_title").value.trim();
    const url = document.getElementById("dns_url").value.trim();

    if (!title || !url) {
        alert("Preencha todos os campos obrigatórios!");
        return;
    }

    const action = id ? "edit" : "add";
    const body = { action: action, id: id, title: title, url: url };

    fetch("actions/dns_actions.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(body),
    })
    .then(response => {
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        return response.json();
    })
    .then(data => {
        if (data.success) {
            $('#rainbow_dns').modal('hide');
            loadDNSTable();
            alert(data.message || "Servidor salvo com sucesso!");
        } else {
            alert("Erro: " + (data.message || "Falha ao salvar."));
        }
    })
    .catch(error => {
        console.error("Erro ao salvar:", error);
        alert("Erro de conexão ou arquivo não encontrado.");
    });
}

function deleteDNS(id) {
    if (!confirm("Tem certeza que deseja excluir este servidor?")) return;

    fetch("actions/dns_actions.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ action: "delete", id: id }),
    })
    .then(response => {
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        return response.json();
    })
    .then(data => {
        if (data.success) {
            loadDNSTable();
            alert(data.message || "Excluído com sucesso!");
        } else {
            alert("Erro ao excluir: " + data.message);
        }
    })
    .catch(error => {
        console.error("Erro ao excluir:", error);
        alert("Falha na conexão.");
    });
}

document.addEventListener("DOMContentLoaded", loadDNSTable);
</script>