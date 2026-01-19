<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$static_url = dirname($_SERVER['SCRIPT_NAME']) . '/assets/';
include 'session_check.php';

$page_title = "Configurações / Notas";

$page_content = '
<div class="card card-flush shadow-sm border-0">
    <div class="card-header align-items-center border-0 pt-6 pb-0">
        <div class="card-title">
            <h3 class="fw-bolder">Configurações / Notas</h3>
        </div>
    </div>

    <div class="card-body pt-3">
        <div class="table-responsive">
            <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4" id="settings_table">
                <thead>
                    <tr class="fw-bold text-muted bg-light">
                        <th class="ps-4 min-w-80px">ID</th>
                        <th class="min-w-200px">Título</th>
                        <th class="min-w-400px">Conteúdo</th>
                        <th class="min-w-100px text-end pe-4">Ações</th>
                    </tr>
                </thead>
                <tbody id="settings_table_body" class="fw-semibold text-gray-600"></tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="settings_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content rounded-3 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h2 id="modal_title" class="fw-bolder fs-3">Editar Nota</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-4">
                <input type="hidden" id="settings_id">
                <div class="mb-6">
                    <label class="form-label fw-semibold">Título</label>
                    <input type="text" class="form-control form-control-solid" id="note_title" placeholder="Digite o título" required>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Conteúdo</label>
                    <textarea class="form-control form-control-solid" id="note_content" rows="5" placeholder="Digite o conteúdo da nota" required></textarea>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="updateNote()">
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
let settingsTable;

function loadSettings() {
    fetch("actions/note_actions.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ action: "view" }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const tbody = document.getElementById("settings_table_body");
            tbody.innerHTML = "";
            data.data.forEach(s => {
                tbody.innerHTML += `
                    <tr id="row_${s.id}">
                        <td class="ps-4">${s.id}</td>
                        <td>${s.note_title}</td>
                        <td>${s.note_content}</td>
                        <td class="text-end pe-4">
                            <button class="btn btn-sm btn-warning" onclick="openEditModal(${s.id}, '${s.note_title.replace(/'/g,"\\'")}', '${s.note_content.replace(/'/g,"\\'")}')">
                                <i class="ki-outline ki-pencil fs-5"></i> Editar
                            </button>
                        </td>
                    </tr>`;
            });

            if (settingsTable) settingsTable.destroy();

            settingsTable = $("#settings_table").DataTable({
                language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json' },
                pageLength: 10
            });
        }
    })
    .catch(e => console.error("Erro carregando notas:", e));
}

function openEditModal(id, title, content) {
    document.getElementById("modal_title").textContent = "Editar Nota";
    document.getElementById("settings_id").value = id;
    document.getElementById("note_title").value = title;
    document.getElementById("note_content").value = content;
    new bootstrap.Modal(document.getElementById("settings_modal")).show();
}

function updateNote() {
    const id = document.getElementById("settings_id").value;
    const title = document.getElementById("note_title").value.trim();
    const content = document.getElementById("note_content").value.trim();

    if (!title || !content) {
        Swal.fire("Erro", "Título e conteúdo são obrigatórios!", "error");
        return;
    }

    fetch("actions/note_actions.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ action: "edit", id, note_title: title, note_content: content }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            Swal.fire("Sucesso", data.message, "success");
            bootstrap.Modal.getInstance(document.getElementById("settings_modal")).hide();

            const row = document.querySelector(`#row_${id}`);
            if (row) {
                row.innerHTML = `
                    <td class="ps-4">${id}</td>
                    <td>${title}</td>
                    <td>${content}</td>
                    <td class="text-end pe-4">
                        <button class="btn btn-sm btn-warning" onclick="openEditModal(${id}, '${title.replace(/'/g,"\\'")}', '${content.replace(/'/g,"\\'")}')">
                            <i class="ki-outline ki-pencil fs-5"></i> Editar
                        </button>
                    </td>`;
            }
        } else {
            Swal.fire("Erro", data.message, "error");
        }
    })
    .catch(() => Swal.fire("Erro", "Falha ao atualizar", "error"));
}

document.addEventListener("DOMContentLoaded", loadSettings);
</script>