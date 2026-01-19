<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$static_url = dirname($_SERVER['SCRIPT_NAME']) . '/assets/';
include 'session_check.php';

$page_title = "MAC Users";

$page_content = '
<div class="card card-custom gutter-b shadow-lg">
    <div class="card-header border-0 py-6 px-6 d-flex align-items-center justify-content-between bg-primary text-white rounded-top">
        <div class="d-flex align-items-center">
            <span class="svg-icon svg-icon-white svg-icon-2x mr-3">
                <i class="ki-outline ki-user fs-2"></i>
            </span>
            <h3 class="card-title font-weight-bolder m-0">Gerenciar MAC Users / Playlists</h3>
        </div>
        <button type="button" id="btn-add-playlist" class="btn btn-light btn-sm font-weight-bold">
            <i class="ki-outline ki-plus fs-2 mr-2"></i>Adicionar Playlist
        </button>
    </div>

    <div class="card-body pt-0 pb-5 px-6">
        <div class="mb-5 position-relative">
            <i class="ki-outline ki-magnifier fs-2 position-absolute text-muted" style="top: 12px; left: 15px;"></i>
            <input type="text" id="search_playlist" class="form-control form-control-solid pl-12" placeholder="Pesquisar por MAC, username ou DNS..." />
        </div>

        <div class="table-responsive">
            <table class="table table-head-custom table-vertical-center table-head-bg table-borderless" id="playlist_table">
                <thead>
                    <tr class="text-left text-uppercase font-weight-bold text-muted">
                        <th class="pl-6 min-w-100px">ID</th>
                        <th class="min-w-150px">DNS</th>
                        <th class="min-w-150px">MAC Address</th>
                        <th class="min-w-150px">Username</th>
                        <th class="min-w-150px">Password</th>
                        <th class="min-w-100px">PIN</th>
                        <th class="min-w-150px text-right pr-6">Ações</th>
                    </tr>
                </thead>
                <tbody id="playlist_table_body" class="font-weight-bold text-dark"></tbody>
            </table>
        </div>

        <div class="mt-5 text-center" id="update-area" style="display:none;">
            <button class="btn btn-info btn-lg px-8 py-4" onclick="loadPlaylistTable()">
                <i class="ki-outline ki-refresh fs-2 mr-2"></i> Atualizar Tabela Manualmente
            </button>
        </div>
    </div>
</div>

<!-- Modal Adicionar/Editar Playlist -->
<div class="modal fade" id="playlist_modal" tabindex="-1" role="dialog" aria-labelledby="modal_title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content rounded-lg shadow-xl">
            <div class="modal-header bg-primary text-white border-0">
                <h5 id="modal_title" class="modal-title font-weight-bold">Adicionar/Editar Playlist</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body px-10 py-8">
                <input type="hidden" id="playlist_id">

                <div class="form-group mb-8">
                    <label for="mac_address" class="font-weight-bold fs-6 mb-2 required">MAC Address *</label>
                    <input type="text" class="form-control form-control-solid" id="mac_address" placeholder="Ex: 00:1A:2B:3C:4D:5E" required>
                </div>

                <div class="form-group mb-8">
                    <label for="pin" class="font-weight-bold fs-6 mb-2">PIN</label>
                    <input type="text" class="form-control form-control-solid" id="pin" value="0000" placeholder="0000" required>
                </div>

                <div id="servers-container" class="mb-6"></div>

                <button type="button" class="btn btn-secondary btn-lg px-8 py-4 d-flex align-items-center" id="add-server">
                    <i class="ki-outline ki-plus fs-3 mr-3"></i> Adicionar Outro Servidor
                </button>
            </div>
            <div class="modal-footer border-0 px-10 pb-8">
                <button type="button" class="btn btn-light btn-lg px-8" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary btn-lg px-10 d-flex align-items-center" id="btn-save-playlist">
                    <i class="ki-outline ki-save fs-3 mr-3"></i> Salvar
                </button>
            </div>
        </div>
    </div>
</div>
';

include 'includes/layout.php';
?>

<script>
// Função escapeHtml segura
function escapeHtml(value) {
    if (value == null || value === undefined) return '—';
    if (typeof value !== 'string') return String(value);
    return value
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

let playlistTable = null;
let serverCount = 0;

function loadPlaylistTable() {
    console.log("Inicializando ou recarregando tabela...");
    if (playlistTable) {
        playlistTable.ajax.reload(null, false);
        return;
    }
    playlistTable = $('#playlist_table').DataTable({
        language: { url: "//cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json" },
        pageLength: 10,
        order: [[0, 'desc']],
        destroy: true,
        ajax: {
            url: 'actions/mac_actions.php',
            type: 'POST',
            data: function(d) {
                d.action = 'view';
                d.t = new Date().getTime();
                return d;
            },
            dataSrc: function(json) {
                if (json && json.success) {
                    return (json.data || []).map(row => ({
                        ...row,
                        dns_title: row.dns_title || '—'
                    }));
                }
                return [];
            }
        },
        columns: [
            { data: 'id', defaultContent: '—' },
            {
                data: 'dns_id',
                defaultContent: '—',
                render: function(data, type, row) {
                    return escapeHtml(row.dns_title || row.dns_id || '—');
                }
            },
            { data: 'mac_address', defaultContent: '—' },
            { data: 'username', defaultContent: '—' },
            { data: 'password', defaultContent: '—' },
            { data: 'pin', defaultContent: '—' },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    if (!row || typeof row !== 'object' || !row.id) return '';
                    const id = row.id;
                    const dns_id = escapeHtml(row.dns_id);
                    const mac = escapeHtml(row.mac_address);
                    const user = escapeHtml(row.username);
                    const pass = escapeHtml(row.password);
                    const pin = escapeHtml(row.pin);
                    const m3u = escapeHtml(row.m3u_url || '');
                    return `
                        <button class="btn btn-sm btn-warning mr-2" onclick="editPlaylist(${id}, '${dns_id}', '${mac}', '${user}', '${pass}', '${pin}', '${m3u}')">
                            Editar
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deletePlaylist(${id})">
                            Excluir
                        </button>
                    `;
                }
            }
        ],
        drawCallback: function(settings) {
            const api = this.api();
            if (api.rows().count() === 0) {
                $('#playlist_table_body').html('<tr><td colspan="7" class="text-center py-5">Nenhuma playlist cadastrada</td></tr>');
                document.getElementById("update-area").style.display = "block";
            } else {
                document.getElementById("update-area").style.display = "none";
            }
        }
    });
}

function editPlaylist(id, dns_id, mac_address, username, password, pin, m3u_url) {
    document.getElementById("modal_title").textContent = "Editar Playlist";
    document.getElementById("playlist_id").value = id || '';
    document.getElementById("mac_address").value = mac_address || '';
    document.getElementById("pin").value = pin || '0000';
    const container = document.getElementById("servers-container");
    container.innerHTML = '';
    serverCount = 0;
    addServerBlock({ dns_id, username, password, m3u_url });
    const removeBtn = container.querySelector('.remove-server');
    if (removeBtn) removeBtn.style.display = 'none';
    $('#playlist_modal').modal('show');
}

function deletePlaylist(id) {
    Swal.fire({
        title: "Tem certeza?",
        text: "Esta playlist será excluída permanentemente!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: "Sim, excluir",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.isConfirmed) {
            fetch("actions/mac_actions.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ action: "delete", id: id })
            })
            .then(response => response.json())
            .then(data => {
                Swal.fire({
                    title: data.success ? "Sucesso" : "Erro",
                    text: data.message || (data.success ? "Playlist excluída com sucesso" : "Falha ao excluir"),
                    icon: data.success ? "success" : "error",
                    confirmButtonText: "OK"
                }).then(() => {
                    if (data.success && playlistTable) {
                        playlistTable.ajax.reload(null, false);
                    }
                });
            })
            .catch(() => Swal.fire("Erro", "Falha ao excluir", "error"));
        }
    });
}

function loadDnsOptions(selectElement, selected = "") {
    fetch("actions/mac_actions.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ action: "get_dns_options" }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            selectElement.innerHTML = '<option value="">Selecione um DNS</option>';
            (data.data || []).forEach(d => {
                selectElement.innerHTML += `<option value="${d.id}" ${d.id == selected ? 'selected' : ''}>${escapeHtml(d.title)}</option>`;
            });
        }
    })
    .catch(err => console.error("Erro ao carregar DNS:", err));
}

function addServerBlock(data = {}) {
    serverCount++;
    const container = document.getElementById("servers-container");
    const block = document.createElement('div');
    block.className = 'server-block mb-7 border p-4 rounded bg-light';
    block.innerHTML = `
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="m-0">Servidor ${serverCount}</h5>
            <button type="button" class="btn btn-sm btn-danger remove-server" style="${serverCount === 1 ? 'display:none;' : ''}">
                <i class="ki-outline ki-trash fs-4"></i> Remover
            </button>
        </div>
        <div class="mb-4">
            <label class="fs-6 fw-semibold mb-2">URL da Lista M3U (opcional)</label>
            <input type="url" class="form-control m3u_url" placeholder="http://dominio.com/get.php?username=xxx&password=yyy" value="${data.m3u_url || ''}">
        </div>
        <div class="mb-4">
            <label class="fs-6 fw-semibold mb-2 required">DNS *</label>
            <select class="form-control form-control-solid dns_id" required>
                <option value="">Selecione DNS</option>
            </select>
        </div>
        <div class="mb-4">
            <label class="fs-6 fw-semibold mb-2 required">Username *</label>
            <input type="text" class="form-control username" placeholder="Digite o username" value="${data.username || ''}" required>
        </div>
        <div class="mb-4">
            <label class="fs-6 fw-semibold mb-2 required">Senha *</label>
            <input type="text" class="form-control password" placeholder="Digite a senha" value="${data.password || ''}" required>
        </div>
    `;
    container.appendChild(block);
    const select = block.querySelector('.dns_id');
    loadDnsOptions(select, data.dns_id || '');
    attachM3UListener(block.querySelector('.m3u_url'), block);
    block.querySelector('.remove-server').addEventListener('click', () => {
        block.remove();
        updateServerTitles();
    });
}

function updateServerTitles() {
    const blocks = document.querySelectorAll('.server-block');
    blocks.forEach((block, index) => {
        block.querySelector('h5').textContent = `Servidor ${index + 1}`;
    });
    serverCount = blocks.length;
    if (serverCount === 1) {
        blocks[0].querySelector('.remove-server').style.display = 'none';
    }
}

function attachM3UListener(input, block) {
    input.addEventListener("input", function() {
        const m3uUrl = this.value.trim();
        if (!m3uUrl) return;
        try {
            let fixedUrl = m3uUrl;
            if (!fixedUrl.startsWith('http://') && !fixedUrl.startsWith('https://')) {
                fixedUrl = 'http://' + fixedUrl;
            }
            const url = new URL(fixedUrl);
            const params = url.searchParams;
            const username = params.get('username');
            const password = params.get('password');
            if (username) block.querySelector('.username').value = username;
            if (password) block.querySelector('.password').value = password;
            const domain = url.hostname.toLowerCase();
            const select = block.querySelector('.dns_id');
            if (select.options.length <= 1) loadDnsOptions(select);
            const keywords = [
                "pmatch", "match", "space", "pmatch.space", "pmatch space",
                "pmatch80", "pmatch portal", "stalker pmatch", "get.php pmatch"
            ];
            for (let opt of select.options) {
                if (opt.value === "") continue;
                const text = opt.textContent.toLowerCase();
                if (text.includes(domain) || keywords.some(kw => text.includes(kw))) {
                    select.value = opt.value;
                    break;
                }
            }
        } catch (e) {
            console.error("Erro parse M3U:", e.message);
        }
    });
}

function openAddModal() {
    document.getElementById("modal_title").textContent = "Adicionar Playlist";
    document.getElementById("playlist_id").value = "";
    document.getElementById("mac_address").value = "";
    document.getElementById("pin").value = "0000";
    const container = document.getElementById("servers-container");
    container.innerHTML = '';
    serverCount = 0;
    addServerBlock();
    document.getElementById("add-server").style.display = 'block';
    $('#playlist_modal').modal('show');
}

function savePlaylist() {
    const id = document.getElementById("playlist_id").value.trim();
    const mac_address = document.getElementById("mac_address").value.trim();
    const pin = document.getElementById("pin").value.trim() || "0000";

    if (!mac_address) {
        Swal.fire("Erro", "O MAC Address é obrigatório!", "error");
        return;
    }
    if (!/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/.test(mac_address)) {
        Swal.fire("Erro", "Formato de MAC inválido! Use XX:XX:XX:XX:XX:XX", "error");
        return;
    }

    const servers = [];
    let allValid = true;
    document.querySelectorAll('.server-block').forEach(block => {
        const dns_id = block.querySelector('.dns_id').value.trim();
        const username = block.querySelector('.username').value.trim();
        const password = block.querySelector('.password').value.trim();
        const m3u_url = block.querySelector('.m3u_url').value.trim();
        if (dns_id && username && password) {
            servers.push({ dns_id, username, password, m3u_url });
        } else {
            allValid = false;
        }
    });

    if (servers.length === 0 || !allValid) {
        Swal.fire("Erro", "Todos os servidores precisam ter DNS, Username e Senha preenchidos!", "error");
        return;
    }

    const action = id ? "edit" : "add";
    fetch("actions/mac_actions.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ action, id, mac_address, pin, servers })
    })
    .then(response => response.json())
    .then(data => {
        Swal.fire({
            title: data.success ? "Sucesso" : "Erro",
            text: data.message || (data.success ? "Playlist salva com sucesso!" : "Falha ao salvar. Verifique os campos."),
            icon: data.success ? "success" : "error",
            confirmButtonText: "OK"
        }).then(() => {
            if (data.success) {
                if (playlistTable) playlistTable.ajax.reload(null, false);
                $('#playlist_modal').modal('hide');
            }
        });
    })
    .catch(error => {
        Swal.fire("Erro", "Falha na conexão ou resposta inválida do servidor", "error");
    });
}

document.addEventListener("DOMContentLoaded", () => {
    loadPlaylistTable();
    document.getElementById("btn-add-playlist")?.addEventListener("click", openAddModal);
    document.getElementById("btn-save-playlist")?.addEventListener("click", savePlaylist);
    document.getElementById("add-server")?.addEventListener("click", () => addServerBlock());
});
</script>