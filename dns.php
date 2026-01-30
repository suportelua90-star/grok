<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
$static_url = dirname($_SERVER['SCRIPT_NAME']) . '/assets/';
include 'session_check.php';

$page_title = "DNS Page";

$page_content = '
<div class="card">
    <div class="card-header border-0 pt-6">
        <div class="card-title">
            <div class="d-flex align-items-center position-relative my-1">
                <i class="ki-outline ki-magnifier fs-3 position-absolute ms-5"></i>
                <input type="text" id="search_dns" class="form-control form-control-solid w-250px ps-12" placeholder="Pesquisar DNS" />
            </div>
        </div>
        <div class="card-toolbar">
            <div class="d-flex justify-content-end">
                <button type="button" class="btn btn-primary" onclick="openAddModal()">Add DNS</button>
            </div>
        </div>
    </div>
    <div class="card-body pt-0">
        <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_customers_table">
            <thead>
                <tr class="text-start text-gray-800 fw-bold fs-7 text-uppercase gs-0">
                    <th class="min-w-50px">ID</th>
                    <th class="min-w-125px">Título</th>
                    <th class="min-w-125px">URL</th>
                    <th class="min-w-125px">Ações</th>
                </tr>
            </thead>
            <tbody id="dns_table_body" class="fw-semibold text-gray-600"></tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="rainbow_dns" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modal_title" class="fw-bold">Adicionar DNS</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="dns_id">
                <div class="mb-7">
                    <label class="fs-6 fw-semibold mb-2">Título</label>
                    <input type="text" class="form-control" id="dns_title" placeholder="Ex: DNS Principal">
                </div>
                <div class="mb-7">
                    <label class="fs-6 fw-semibold mb-2">URL</label>
                    <input type="url" class="form-control" id="dns_url" placeholder="http://exemplo.com">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="saveDNS()">Guardar</button>
            </div>
        </div>
    </div>
</div>
';

include 'includes/layout.php'; ?>

<script>
let dataTable;

function loadDNSTable() {
    fetch("actions/dns_actions.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ action: "view" }),
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Destruir DataTable existente corretamente
            if ($.fn.DataTable.isDataTable('#kt_customers_table')) {
                $('#kt_customers_table').DataTable().clear().destroy();
            }

            const tableBody = document.getElementById("dns_table_body");
            tableBody.innerHTML = ""; 

            data.data.forEach((record) => {
                // Determinar se é o primeiro DNS (ID = 1)
                const isFirstDNS = record.id == 1;
                
                // CORREÇÃO: Uso de crases para Template Literals
                tableBody.innerHTML += `
                    <tr id="row_${record.id}">
                        <td>${record.id}</td>
                        <td>
                            ${record.title} 
                            ${isFirstDNS ? '<span class="badge badge-light-primary ms-1">Principal</span>' : ''}
                        </td>
                        <td>${record.url}</td>
                        <td>
                            <button class="btn btn-sm btn-warning" onclick="editDNS(${record.id})">Editar</button>
                            <button class="btn btn-sm btn-danger" onclick="deleteDNS(${record.id})" 
                                ${isFirstDNS ? 'disabled title="DNS Principal não pode ser apagado"' : ''}>
                                Apagar
                            </button>
                        </td>
                    </tr>`;
            });

            // Reinicializar com pequeno delay para o DOM processar
            setTimeout(() => {
                dataTable = $('#kt_customers_table').DataTable({
                    searching: true, paging: true, info: true, responsive: false,
                    language: { search: "" }
                });
            }, 50);
        }
    });
}

function openAddModal() {
    document.getElementById("modal_title").textContent = "Adicionar DNS";
    document.getElementById("dns_id").value = "";
    document.getElementById("dns_title").value = "";
    document.getElementById("dns_url").value = "";
    new bootstrap.Modal(document.getElementById("rainbow_dns")).show();
}

function editDNS(id) {
    // Buscar dados do DNS para preencher o modal
    fetch("actions/dns_actions.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ action: "view" }),
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const dns = data.data.find(d => d.id == id);
            if (dns) {
                document.getElementById("modal_title").textContent = "Editar DNS";
                document.getElementById("dns_id").value = dns.id;
                document.getElementById("dns_title").value = dns.title;
                document.getElementById("dns_url").value = dns.url;
                new bootstrap.Modal(document.getElementById("rainbow_dns")).show();
            }
        }
    });
}

function saveDNS() {
    const id = document.getElementById("dns_id").value;
    const title = document.getElementById("dns_title").value.trim();
    const url = document.getElementById("dns_url").value.trim();
    const action = id ? "edit" : "add";

    if (!title || !url) {
        Swal.fire("Erro", "Preencha todos os campos!", "error");
        return;
    }

    const data = { action, title, url };
    if (action === "edit") {
        data.id = id;
    }

    fetch("actions/dns_actions.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(data),
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Swal.fire("Sucesso", data.message, "success");
            bootstrap.Modal.getInstance(document.getElementById("rainbow_dns")).hide();
            loadDNSTable();
        } else {
            Swal.fire("Erro", data.message, "error");
        }
    });
}

function deleteDNS(id) {
    // Verificar se é o primeiro DNS (ID = 1)
    if (id == 1) {
        Swal.fire({
            title: "Ação não permitida",
            text: "O DNS Principal (ID 1) não pode ser apagado.",
            icon: "warning",
            confirmButtonText: "Entendi"
        });
        return;
    }

    Swal.fire({
        title: "Tem a certeza?",
        text: "O registo será removido permanentemente.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sim, apagar!",
        cancelButtonText: "Não"
    }).then((result) => {
        if (result.isConfirmed) {
            fetch("actions/dns_actions.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ action: "delete", id }),
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire("Apagado!", data.message, "success").then(() => {
                        loadDNSTable();
                    });
                } else {
                    Swal.fire("Erro", data.message, "error");
                }
            });
        }
    });
}

document.addEventListener("DOMContentLoaded", loadDNSTable);
</script>