<?php
// ============================================
// INICIALIZAÇÃO DE SESSÃO
// ============================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// ============================================
// CONFIGURAÇÃO DE AMBIENTE E SEGURANÇA
// ============================================
$env = (isset($_SERVER['SERVER_NAME']) && strpos($_SERVER['SERVER_NAME'], 'localhost') !== false)
    ? 'dev'
    : 'prod';
if ($env === 'prod') {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
}
include 'session_check.php';
$static_url = dirname($_SERVER['SCRIPT_NAME']) . '/assets/';
$page_title = "MAC Playlist Manager";
// Gerar token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
$page_content = '
<div class="mac-playlist-container">
    <!-- Meta tag para CSRF Token -->
    <meta name="csrf-token" content="' . htmlspecialchars($csrf_token) . '">
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-outline ki-magnifier fs-3 position-absolute ms-5 text-gray-500"></i>
                    <input type="text" id="search_playlist" class="form-control form-control-solid w-250px ps-12" placeholder="Search MAC entries..." autocomplete="off" />
                </div>
            </div>
            <div class="card-toolbar">
                <div class="d-flex justify-content-end">
                    <button type="button" class="btn btn-primary d-flex align-items-center" onclick="openAddModal()">
                        <i class="ki-outline ki-plus fs-2 me-2"></i>Add Playlist
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="playlist_table">
                    <thead>
                        <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                            <th class="min-w-80px">ID</th>
                            <th class="min-w-120px">DNS</th>
                            <th class="min-w-150px">MAC Address</th>
                            <th class="min-w-120px">Username</th>
                            <th class="min-w-120px">Password</th>
                            <th class="min-w-80px">PIN</th>
                            <th class="min-w-150px text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="playlist_table_body" class="fw-semibold text-gray-700"></tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="playlist_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-650px">
            <div class="modal-content border-0">
                <div class="modal-header">
                    <h2 id="modal_title" class="fw-bold text-dark d-flex align-items-center">
                        <i class="ki-outline ki-shield-tick fs-1 me-3"></i>
                        <span>Add Playlist (Multi-Server)</span>
                    </h2>
                    <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="playlist_id">
                    <!-- MAC NO TOPO -->
                    <div class="mb-7">
                        <label for="mac_address" class="fs-6 fw-semibold mb-2 d-flex align-items-center">
                            <i class="ki-outline ki-laptop fs-2 me-2 text-primary"></i>
                            MAC Address <span class="text-danger ms-1">*</span>
                        </label>
                        <input type="text" class="form-control form-control-lg" id="mac_address" placeholder="Ex: AA:BB:CC:DD:EE:FF" required autocomplete="off">
                        <div id="mac_feedback" class="form-text mt-2">Format: AA:BB:CC:DD:EE:FF (case insensitive)</div>
                    </div>
                    <div class="separator separator-dashed my-5 border-gray-700"></div>
                    <!-- CONTAINER SERVIDORES -->
                    <div id="servers_container">
                        <!-- Servidores serão gerados dinamicamente -->
                    </div>
                    <button type="button" class="btn btn-light-primary w-100 mb-5 d-flex align-items-center justify-content-center py-3" id="add_server_btn">
                        <i class="ki-outline ki-plus fs-1 me-2"></i>
                        <span class="fs-4 fw-bold">Add Another Server</span>
                    </button>
                </div>
                <div class="modal-footer py-4">
                    <button type="button" class="btn btn-light btn-active-light-primary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary d-flex align-items-center px-5 py-3" id="save_playlist_btn" onclick="savePlaylist()">
                        <i class="ki-outline ki-check-circle fs-1 me-2"></i>
                        <span class="fs-3 fw-bold">Save All Servers</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
// ============================================
// CONSTANTES E VARIÁVEIS GLOBAIS
// ============================================
const playlistState = {
    table: null,
    searchTimeout: null,
    dnsOptionsCache: null,
    MAC_REGEX: /^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/,
    CSRF_TOKEN: document.querySelector(\'meta[name="csrf-token"]\')?.content || \'\'
};
// ============================================
// FUNÇÕES DE UTILIDADE
// ============================================
function getCsrfHeaders() {
    return {"Content-Type": "application/json","X-CSRF-Token": playlistState.CSRF_TOKEN};
}
function escapeHtml(str) {
    return str.toString().replace(/[&<>"\']/g, m => ({\'&\': \'&amp;\',\'<\': \'&lt;\',\'>\': \'&gt;\',\'"\': \'&quot;\',"\'": \'&#039;\'}[m]));
}
function showLoading(element, message = \'Loading...\') {
    element.innerHTML = `<tr><td colspan="7" class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2 text-muted">${message}</p></td></tr>`;
}
function showError(element, message) {
    element.innerHTML = `<tr><td colspan="7" class="text-center py-5 text-danger"><i class="ki-outline ki-information fs-1 text-danger mb-3"></i><p>${message}</p></td></tr>`;
}
// ============================================
// INICIALIZAÇÃO DO DOM
// ============================================
document.addEventListener(\'DOMContentLoaded\', () => {
    initializeMacValidation();
    initializeSearch();
    initializeAddServerButton();
    initializePinValidation();
    loadPlaylistTable();
});
// ============================================
// VALIDAÇÃO MAC
// ============================================
function initializeMacValidation() {
    const macInput = document.getElementById(\'mac_address\');
    const macFeedback = document.getElementById(\'mac_feedback\');
    if (!macInput || !macFeedback) return;
    macInput.addEventListener(\'input\', function(e) {
        let mac = e.target.value.trim().toUpperCase();
        mac = mac.replace(/[^0-9A-F:-]/g, \'\');
        e.target.value = mac;
        const isValid = mac && playlistState.MAC_REGEX.test(mac);
        updateMacFeedback(macFeedback, mac, isValid);
        e.target.classList.toggle(\'is-invalid\', !isValid && mac);
    });
    macInput.addEventListener(\'blur\', function(e) {
        let mac = e.target.value.trim().toUpperCase();
        mac = mac.replace(/[^0-9A-F]/g, \'\');
        if (mac.length === 12) {
            mac = mac.match(/.{1,2}/g).join(\':\');
            e.target.value = mac;
        }
    });
}
function updateMacFeedback(feedbackElement, mac, isValid) {
    if (isValid) {
        feedbackElement.textContent = \'✅ Valid MAC format\';
        feedbackElement.className = \'form-text text-success fw-bold mt-2\';
    } else if (mac) {
        feedbackElement.textContent = \'⚠️ Invalid format! Example: AA:BB:CC:DD:EE:FF\';
        feedbackElement.className = \'form-text text-danger fw-bold mt-2\';
    } else {
        feedbackElement.textContent = \'Format: AA:BB:CC:DD:EE:FF (case insensitive)\';
        feedbackElement.className = \'form-text mt-2\';
    }
}
// ============================================
// BUSCA COM DEBOUNCE
// ============================================
function initializeSearch() {
    const searchInput = document.getElementById(\'search_playlist\');
    if (!searchInput) return;
    searchInput.style.display = \'block\';
    searchInput.style.visibility = \'visible\';
    searchInput.style.opacity = \'1\';
    searchInput.value = \'\';
    searchInput.setAttribute(\'autocomplete\', \'off\');
    searchInput.addEventListener(\'input\', function() {
        clearTimeout(playlistState.searchTimeout);
        playlistState.searchTimeout = setTimeout(() => {
            if (playlistState.table) playlistState.table.search(this.value).draw();
        }, 300);
    });
    document.getElementById(\'playlist_modal\').addEventListener(\'show.bs.modal\', function() {
        if (searchInput) {
            searchInput.value = \'\';
            searchInput.setAttribute(\'autocomplete\', \'off\');
        }
        if (playlistState.table) playlistState.table.search("").draw();
    });
}
// ============================================
// VALIDAÇÃO DE PIN
// ============================================
function initializePinValidation() {
    document.addEventListener(\'input\', function(e) {
        if (e.target.id.startsWith(\'pin_\')) e.target.value = e.target.value.replace(/[^0-9]/g, \'\');
    });
}
// ============================================
// CARREGAMENTO DA TABELA
// ============================================
function loadPlaylistTable() {
    const tableBody = document.getElementById("playlist_table_body");
    showLoading(tableBody, \'Loading playlists...\');
    fetch("actions/mac_actions.php", {
        method: "POST",
        headers: getCsrfHeaders(),
        body: JSON.stringify({ action: "view" }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) renderPlaylistTable(data.data);
        else showError(tableBody, data.message || \'No data available\');
    })
    .catch(error => {
        console.error("Error loading playlist table:", error);
        showError(tableBody, \'Error loading data. Please try again.\');
    });
}
function renderPlaylistTable(records) {
    const tableBody = document.getElementById("playlist_table_body");
    const macGroups = {};
    records.forEach(record => {
        if (!macGroups[record.mac_address]) macGroups[record.mac_address] = [];
        macGroups[record.mac_address].push(record);
    });
    tableBody.innerHTML = "";
    Object.entries(macGroups).forEach(([mac, records]) => {
        records.forEach(record => {
            tableBody.innerHTML += `
                <tr id="row_${record.id}" class="hover-row" data-mac="${escapeHtml(mac)}">
                    <td class="fw-bold text-primary">${escapeHtml(record.id)}</td>
                    <td><span class="badge badge-light-primary fw-bold">${escapeHtml(record.dns_id)}</span></td>
                    <td><span class="badge badge-light-info fw-bold fs-7">${escapeHtml(mac)}</span></td>
                    <td>${escapeHtml(record.username)}</td>
                    <td>
                        <span class="password-masked">••••••••</span>
                        <button class="btn btn-sm btn-icon btn-light btn-reveal" data-password="${escapeHtml(record.password)}" title="Reveal password (5s)">
                            <i class="ki-outline ki-eye fs-3"></i>
                        </button>
                    </td>
                    <td><span class="badge badge-light-success fw-bold">${escapeHtml(record.pin)}</span></td>
                    <td class="text-end">
                        <button class="btn-action btn-edit edit-btn" data-mac="${escapeHtml(mac)}" title="Edit all servers for this MAC">
                            <i class="ki-outline ki-notepad fs-3"></i>
                        </button>
                        <button class="btn-action btn-delete delete-btn" data-mac="${escapeHtml(mac)}" title="Delete all servers for this MAC">
                            <i class="ki-outline ki-trash fs-3"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
    });
    initializeDataTable();
}
function initializeDataTable() {
    if (playlistState.table) playlistState.table.destroy();
    playlistState.table = $(\'#playlist_table\').DataTable({
        stateSave: false,
        searchDelay: 350,
        language: {
            search: "",
            searchPlaceholder: "Search records...",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: "No entries found",
            zeroRecords: "No matching records found"
        }
    });
    const searchInput = document.getElementById(\'search_playlist\');
    if (searchInput) {
        searchInput.value = \'\';
        searchInput.setAttribute(\'autocomplete\', \'off\');
    }
}
// ============================================
// CARREGAMENTO DE OPÇÕES DNS
// ============================================
function loadDnsOptionsForIndex(index, selectedValue = "") {
    const select = document.getElementById(`dns_id_${index}`);
    if (!select) return;
    if (playlistState.dnsOptionsCache) {
        populateDnsSelect(select, playlistState.dnsOptionsCache, selectedValue);
        return;
    }
    fetch("actions/mac_actions.php", {
        method: "POST",
        headers: getCsrfHeaders(),
        body: JSON.stringify({ action: "get_dns_options" }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            playlistState.dnsOptionsCache = data.data;
            populateDnsSelect(select, data.data, selectedValue);
        }
    })
    .catch(error => console.error("Error loading DNS options:", error));
}
function populateDnsSelect(selectElement, options, selectedValue) {
    selectElement.innerHTML = \'<option value="">Select DNS Server</option>\';
    options.forEach(dns => {
        selectElement.innerHTML += `<option value="${dns.id}" ${dns.id == selectedValue ? "selected" : ""}>${dns.title}</option>`;
    });
}
// ============================================
// ADIÇÃO DE SERVIDORES
// ============================================
function initializeAddServerButton() {
    const addServerBtn = document.getElementById(\'add_server_btn\');
    if (!addServerBtn) return;
    addServerBtn.addEventListener(\'click\', addServerBlock);
    setTimeout(() => addServerBlock(), 100);
}
function addServerBlock() {
    const container = document.getElementById(\'servers_container\');
    const idx = container.children.length;
    const block = document.createElement(\'div\');
    block.className = \'server-block mb-5 p-4 rounded border\';
    block.setAttribute(\'data-index\', idx);
    block.innerHTML = createServerBlockHTML(idx);
    container.appendChild(block);
    loadDnsOptionsForIndex(idx);
    setupTogglePassword(idx);
}
function createServerBlockHTML(index) {
    return `
        <div class="d-flex justify-content-between align-items-start mb-4 pb-2 border-bottom">
            <div class="d-flex align-items-center">
                <span class="bullet bullet-dot bg-primary h-10px w-10px me-3 mt-1"></span>
                <h5 class="fw-bold mb-0 server-title">Server #${index + 1}</h5>
            </div>
            <button type="button" class="btn btn-icon btn-active-color-danger remove-server" data-index="${index}" title="Remove this server">
                <i class="ki-outline ki-trash fs-2"></i>
            </button>
        </div>
        <div class="mb-5">
            <label class="fs-6 fw-semibold mb-2 d-flex align-items-center">
                <i class="ki-outline ki-shield-cloud fs-2 me-2"></i>
                DNS Server <span class="text-danger ms-1">*</span>
            </label>
            <select class="form-select form-select-solid fw-bold" id="dns_id_${index}" required autocomplete="off">
                <option value="">Select DNS Server</option>
            </select>
        </div>
        <div class="mb-5">
            <label class="fs-6 fw-semibold mb-2 d-flex align-items-center">
                <i class="ki-outline ki-link fs-2 me-2"></i>
                M3U URL (Auto-fill credentials)
            </label>
            <div class="input-group">
                <input type="text" class="form-control form-control-solid" id="m3u_url_${index}" placeholder="Paste M3U URL to auto-fill fields" autocomplete="off">
                <button class="btn btn-primary" type="button" onclick="fillFromM3u(${index})">
                    <span class="indicator-label"><i class="ki-outline ki-check fs-2"></i> Fill</span>
                    <span class="indicator-progress">
                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                    </span>
                </button>
            </div>
            <div class="form-text">Example: http://domain.com/get.php?username=user&password=pass</div>
        </div>
        <div class="row mb-5">
            <div class="col-md-6">
                <label class="fs-6 fw-semibold mb-2 d-flex align-items-center">
                    <i class="ki-outline ki-profile-user fs-2 me-2"></i>
                    Username <span class="text-danger ms-1">*</span>
                </label>
                <input type="text" class="form-control form-control-solid" id="username_${index}" placeholder="Enter username" required autocomplete="new-password">
            </div>
            <div class="col-md-6">
                <label class="fs-6 fw-semibold mb-2 d-flex align-items-center">
                    <i class="ki-outline ki-lock fs-2 me-2"></i>
                    Password <span class="text-danger ms-1">*</span>
                </label>
                <div class="input-group">
                    <input type="password" class="form-control form-control-solid password-field" id="password_${index}" placeholder="Enter password" required autocomplete="new-password">
                    <button class="btn btn-icon btn-light btn-toggle-password" type="button" data-index="${index}">
                        <i class="ki-outline ki-eye toggle-icon fs-2"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="mb-3">
            <label class="fs-6 fw-semibold mb-2 d-flex align-items-center">
                <i class="ki-outline ki-key-square fs-2 me-2"></i>
                PIN <span class="text-danger ms-1">*</span>
            </label>
            <input type="text" class="form-control form-control-solid" id="pin_${index}" value="0000" placeholder="Enter PIN" required autocomplete="off">
        </div>
    `;
}
// ============================================
// TOGGLE DE SENHA
// ============================================
function setupTogglePassword(index) {
    const toggleBtn = document.querySelector(`.btn-toggle-password[data-index="${index}"]`);
    if (toggleBtn) toggleBtn.addEventListener(\'click\', () => togglePasswordVisibility(index));
}
function togglePasswordVisibility(index) {
    const passwordField = document.getElementById(`password_${index}`);
    const icon = document.querySelector(`.btn-toggle-password[data-index="${index}"] .toggle-icon`);
    if (!passwordField || !icon) return;
    if (passwordField.type === "password") {
        passwordField.type = "text";
        icon.classList.remove(\'ki-eye\');
        icon.classList.add(\'ki-eye-slash\');
    } else {
        passwordField.type = "password";
        icon.classList.remove(\'ki-eye-slash\');
        icon.classList.add(\'ki-eye\');
    }
}
// ============================================
// EVENTOS DE CLIQUE
// ============================================
document.addEventListener(\'click\', (e) => {
    if (e.target.closest(\'.btn-reveal\')) handleRevealPassword(e.target.closest(\'.btn-reveal\'));
    if (e.target.closest(\'.remove-server\')) handleRemoveServer(e.target.closest(\'.remove-server\'));
    if (e.target.classList.contains(\'edit-btn\') || e.target.closest(\'.edit-btn\')) {
        const btn = e.target.closest(\'.edit-btn\');
        editPlaylist(btn.dataset.mac);
    }
    if (e.target.classList.contains(\'delete-btn\') || e.target.closest(\'.delete-btn\')) {
        const btn = e.target.closest(\'.delete-btn\');
        deletePlaylist(btn.dataset.mac);
    }
});
function handleRevealPassword(btn) {
    const span = btn.previousElementSibling;
    const realPass = btn.dataset.password;
    if (!realPass) return;
    span.textContent = realPass;
    btn.innerHTML = \'<i class="ki-outline ki-eye-slash fs-3 text-warning"></i>\';
    btn.title = "Hide password";
    setTimeout(() => {
        span.textContent = \'••••••••\';
        btn.innerHTML = \'<i class="ki-outline ki-eye fs-3"></i>\';
        btn.title = "Reveal password (5s)";
    }, 5000);
}
function handleRemoveServer(btn) {
    const blocks = document.querySelectorAll(\'.server-block\');
    if (blocks.length > 1) btn.closest(\'.server-block\').remove();
    else Swal.fire({icon: \'info\',title: \'Minimum Required\',text: \'At least one server configuration is required\',timer: 2500,showConfirmButton: false});
}
// ============================================
// PREENCHIMENTO VIA M3U URL
// ============================================
function fillFromM3u(index) {
    const btn = event.target.closest(\'button\');
    const originalHTML = btn.innerHTML;
    btn.disabled = true;
    btn.querySelector(\'.indicator-label\')?.classList.add(\'d-none\');
    btn.querySelector(\'.indicator-progress\')?.classList.remove(\'d-none\');
    const url = document.getElementById(`m3u_url_${index}`).value.trim();
    if (!url) {
        resetM3uButton(btn, originalHTML);
        return Swal.fire({icon: \'warning\',title: \'Empty URL\',text: \'Paste M3U URL first!\',timer: 2000,showConfirmButton: false});
    }
    const parsed = parseM3uUrl(url);
    if (parsed) {
        document.getElementById(`username_${index}`).value = parsed.username || \'\';
        document.getElementById(`password_${index}`).value = parsed.password || \'\';
        document.getElementById(`pin_${index}`).value = parsed.pin || \'0000\';
        matchDnsIdByDomain(parsed.domain, index);
        Swal.fire({icon: \'success\',title: \'Auto-filled!\',text: \'Username, Password and PIN updated successfully\',timer: 2000,showConfirmButton: false});
    } else {
        Swal.fire({icon: \'error\',title: \'Invalid URL\',text: \'Could not parse M3U URL format\',timer: 2500,showConfirmButton: false});
    }
    resetM3uButton(btn, originalHTML);
}
function resetM3uButton(btn, html) {
    setTimeout(() => {btn.disabled = false; btn.innerHTML = html;}, 1000);
}
function matchDnsIdByDomain(domain, index) {
    fetch("actions/mac_actions.php", {
        method: "POST",
        headers: getCsrfHeaders(),
        body: JSON.stringify({ action: "get_dns_options" }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const matched = data.data.find(dns =>
                dns.title.toLowerCase().includes(domain.toLowerCase()) ||
                domain.toLowerCase().includes(dns.title.toLowerCase())
            );
            if (matched) {
                document.getElementById(`dns_id_${index}`).value = matched.id;
                Swal.fire({icon: \'info\',title: \'DNS Matched\',text: `Auto-selected: ${matched.title}`,timer: 1800,showConfirmButton: false});
            }
        }
    })
    .catch(error => console.error("DNS match error:", error));
}
function parseM3uUrl(url) {
    try {
        const u = new URL(url);
        const p = new URLSearchParams(u.search);
        return {username: p.get(\'username\') || \'\',password: p.get(\'password\') || \'\',domain: u.hostname,pin: \'0000\'};
    } catch (e) {return null;}
}
// ============================================
// MODAL - ABRIR E EDITAR
// ============================================
function openAddModal() {
    document.getElementById("modal_title").innerHTML = `<i class="ki-outline ki-shield-tick fs-1 me-3"></i><span>Add Playlist (Multi-Server)</span>`;
    document.getElementById("playlist_id").value = "";
    document.getElementById("mac_address").value = "";
    const container = document.getElementById(\'servers_container\');
    container.innerHTML = \'\';
    playlistState.dnsOptionsCache = null;
    setTimeout(() => document.getElementById(\'add_server_btn\').click(), 100);
    const modal = new bootstrap.Modal(document.getElementById("playlist_modal"));
    modal.show();
}
function editPlaylist(mac) {
    document.getElementById("modal_title").innerHTML = `<i class="ki-outline ki-edit fs-1 me-3 text-warning"></i><span>Edit Playlist Entry</span>`;
    document.getElementById("playlist_id").value = "";
    document.getElementById("mac_address").value = mac;
    const container = document.getElementById(\'servers_container\');
    container.innerHTML = \'\';
    fetch("actions/mac_actions.php", {
        method: "POST",
        headers: getCsrfHeaders(),
        body: JSON.stringify({action: "view",filter_mac: mac}),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            data.data.forEach((record, index) => {
                const block = document.createElement(\'div\');
                block.className = \'server-block mb-5 p-4 rounded border\';
                block.setAttribute(\'data-index\', index);
                block.innerHTML = createServerBlockHTML(index);
                container.appendChild(block);
                document.getElementById(`username_${index}`).value = record.username;
                document.getElementById(`password_${index}`).value = record.password;
                document.getElementById(`pin_${index}`).value = record.pin;
                document.getElementById(`m3u_url_${index}`).value = \'\';
                setupTogglePassword(index);
                loadDnsOptionsForIndex(index, record.dns_id);
            });
            const modal = new bootstrap.Modal(document.getElementById("playlist_modal"));
            modal.show();
        } else {
            Swal.fire({icon: \'error\',title: \'Error\',text: data.message || \'Failed to load servers\',timer: 3000,showConfirmButton: false});
        }
    })
    .catch(error => {
        console.error(\'Error loading servers:\', error);
        Swal.fire({icon: \'error\',title: \'Network Error\',text: \'Failed to load server data\',timer: 3000,showConfirmButton: false});
    });
}
// ============================================
// SALVAR PLAYLIST
// ============================================
function savePlaylist() {
    const saveBtn = document.getElementById(\'save_playlist_btn\');
    const originalHTML = saveBtn.innerHTML;
    saveBtn.disabled = true;
    saveBtn.innerHTML = `<span class="indicator-progress d-flex align-items-center"><span class="spinner-border spinner-border-sm me-3"></span><span>Saving...</span></span>`;
    const mac = document.getElementById(\'mac_address\').value.trim();
    if (!mac || !playlistState.MAC_REGEX.test(mac)) {
        resetSaveButton(saveBtn, originalHTML);
        return Swal.fire({icon: \'error\',title: \'Invalid MAC\',text: \'Please enter a valid MAC address format\',timer: 2500,showConfirmButton: false});
    }
    const servers = collectServerData();
    if (!servers) {
        resetSaveButton(saveBtn, originalHTML);
        return;
    }
    const payload = {
        action: document.getElementById("playlist_id").value ? "edit" : "add",
        mac_address: mac,
        servers: servers
    };
    fetch("actions/mac_actions.php", {
        method: "POST",
        headers: getCsrfHeaders(),
        body: JSON.stringify(payload),
    })
    .then(r => r.json())
    .then(data => {
        Swal.fire({
            icon: data.success ? \'success\' : \'error\',
            title: data.success ? \'Success!\' : \'Error\',
            text: data.message,
            timer: data.success ? 2000 : 4000,
            showConfirmButton: !data.success
        });
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById("playlist_modal"));
            if (modal) modal.hide();
            loadPlaylistTable();
        }
        resetSaveButton(saveBtn, originalHTML);
    })
    .catch(error => {
        console.error(\'Erro ao salvar:\', error);
        Swal.fire({icon: \'error\',title: \'Network Error\',text: \'Failed to save servers. Check connection.\',timer: 3000,showConfirmButton: false});
        resetSaveButton(saveBtn, originalHTML);
    });
}
function collectServerData() {
    const servers = [];
    document.querySelectorAll(\'.server-block\').forEach((block, index) => {
        const i = block.getAttribute(\'data-index\');
        const serverData = {
            dns_id: document.getElementById(`dns_id_${i}`)?.value.trim() || \'\',
            username: document.getElementById(`username_${i}`)?.value.trim() || \'\',
            password: document.getElementById(`password_${i}`)?.value.trim() || \'\',
            pin: document.getElementById(`pin_${i}`)?.value.trim() || \'\',
            m3u_url: document.getElementById(`m3u_url_${i}`)?.value.trim() || \'\'
        };
        if (!serverData.dns_id || !serverData.username || !serverData.password || !serverData.pin) {
            Swal.fire({icon: \'error\',title: \'Validation Error\',text: `Server #${index + 1} Incomplete: All fields are required`,timer: 3000,showConfirmButton: false});
            return false;
        }
        servers.push(serverData);
    });
    return servers.length > 0 ? servers : false;
}
function resetSaveButton(btn, html) {
    setTimeout(() => {btn.disabled = false; btn.innerHTML = html;}, 800);
}
// ============================================
// DELETAR PLAYLIST
// ============================================
function deletePlaylist(macAddress) {
    Swal.fire({
        title: "Confirm Deletion?",
        html: `<strong class="text-danger">This action cannot be undone!</strong><br>MAC: <strong>${macAddress || \'Unknown\'}</strong><br>All server entries for this MAC will be permanently deleted.`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "<i class=\'ki-outline ki-trash me-2\'></i>Delete All Servers",
        cancelButtonText: "<i class=\'ki-outline ki-cross me-2\'></i>Cancel",
        reverseButtons: true,
        customClass: {confirmButton: \'btn btn-danger\',cancelButton: \'btn btn-light-primary\'}
    }).then(result => {
        if (result.isConfirmed) {
            const swalLoading = Swal.fire({title: "Deleting...",html: "Please wait while we process your request",allowOutsideClick: false,didOpen: () => { Swal.showLoading(); }});
            fetch("actions/mac_actions.php", {
                method: "POST",
                headers: getCsrfHeaders(),
                body: JSON.stringify({action: "delete",mac_address: macAddress}),
            })
            .then(r => r.json())
            .then(data => {
                swalLoading.close();
                Swal.fire({
                    icon: data.success ? \'success\' : \'error\',
                    title: data.success ? \'Deleted!\' : \'Error\',
                    text: data.message,
                    timer: data.success ? 1800 : 3000,
                    showConfirmButton: !data.success
                });
                if (data.success && playlistState.table) loadPlaylistTable();
            })
            .catch(() => {
                swalLoading.close();
                Swal.fire({icon: \'error\',title: \'Deletion Failed\',text: \'Network error occurred\',timer: 2500,showConfirmButton: false});
            });
        }
    });
}
</script>
<style>
/* ====== CONTAINER PRINCIPAL ====== */
.mac-playlist-container {font-family: \'Inter\', -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif;animation: fadeIn 0.4s ease-out;}
@keyframes fadeIn {from {opacity: 0; transform: translateY(10px);}to {opacity: 1; transform: translateY(0);}}

/* ====== CARD E TABELA ====== */
.mac-playlist-container .card {border: none;border-radius: 0.75rem;box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);overflow: hidden;transition: transform 0.2s ease;}
.mac-playlist-container .card-header {background: #ffffff;border-bottom: 1px solid #e5e7eb;padding: 1.5rem 1.5rem 1rem;}
.mac-playlist-container .card-body {padding: 1.5rem;}
.mac-playlist-container .table {margin-bottom: 0;font-size: 0.95rem;}
.mac-playlist-container .table thead th {background: #f9fafb;color: #4b5563;font-weight: 600;letter-spacing: 0.5px;border-bottom: 1.5px solid #e5e7eb;padding: 1rem 1.25rem;text-transform: uppercase;font-size: 0.82rem;}
.mac-playlist-container .table tbody tr {border-bottom: 1px solid #e5e7eb;transition: all 0.2s ease;}
.mac-playlist-container .table tbody tr.hover-row:hover {background-color: #f9fafb;transform: translateX(2px);box-shadow: 0 2px 4px -1px rgba(0,0,0,0.05);}
.mac-playlist-container .table td {vertical-align: middle;padding: 1rem 1.25rem;color: #374151;font-weight: 500;}

/* Badges */
.mac-playlist-container .badge {padding: 0.4rem 0.75rem;border-radius: 0.5rem;font-weight: 600;font-size: 0.85rem;letter-spacing: 0.3px;}
.badge-light-primary {background: #dbeafe;color: #1d4ed8;border: 1px solid #bfdbfe;}
.badge-light-info {background: #d1fafe;color: #0d9488;border: 1px solid #a5f3fc;}
.badge-light-success {background: #dcfce7;color: #166534;border: 1px solid #bbf7d0;}

/* ====== BOTÕES DE AÇÃO ====== */
.btn-action {width: 40px;height: 40px;border-radius: 0.65rem;display: inline-flex;align-items: center;justify-content: center;margin: 0 4px;transition: all 0.2s ease;box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05);border: none;font-size: 1.1rem;position: relative;overflow: hidden;}
.btn-action::after {content: \'\';position: absolute;width: 100%;height: 100%;top: 0;left: 0;background: rgba(255,255,255,0.3);transform: translateX(-100%);transition: transform 0.4s ease;}
.btn-action:hover::after {transform: translateX(100%);}
.btn-edit {background: #e0f2fe;color: #0d9488;border: 1px solid #bae6fd;}
.btn-edit:hover {background: #bae6fd;transform: translateY(-2px);box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);color: #0d9488;}
.btn-delete {background: #fee2e2;color: #dc2626;border: 1px solid #fecaca;}
.btn-delete:hover {background: #fecaca;transform: translateY(-2px);box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);color: #b91c1c;}
.btn-reveal {background: #f0fdf4;color: #166534;width: 36px;height: 36px;border-radius: 0.5rem;box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05);}
.btn-reveal:hover {background: #d1fae5;transform: scale(1.05);box-shadow: 0 2px 4px 0 rgba(0,0,0,0.08);}

/* ====== MODAL ====== */
.mac-playlist-container .modal-content {border-radius: 0.75rem !important;box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04) !important;border: none;overflow: hidden;position: relative;}
.mac-playlist-container .modal-header {background: linear-gradient(120deg, #10b981 0%, #059669 100%) !important;color: white;border-bottom: none;padding: 1.5rem 2rem !important;position: relative;overflow: hidden;border-top-left-radius: 0.75rem !important;border-top-right-radius: 0.75rem !important;height: auto !important;}
.mac-playlist-container .modal-header::before {content: \'\' !important;position: absolute !important;top: 0 !important;left: 0 !important;right: 0 !important;height: 100% !important;background: linear-gradient(120deg, #10b981 0%, #059669 100%) !important;z-index: -1 !important;border-radius: 0.75rem 0.75rem 0 0 !important;}
.mac-playlist-container .modal-title {font-weight: 700;font-size: 1.5rem;letter-spacing: -0.3px;display: flex;align-items: center;gap: 10px;}
.mac-playlist-container .modal-title i {font-size: 1.8rem;}
.mac-playlist-container .btn-close {filter: brightness(0) invert(1);opacity: 0.8;width: 26px;height: 26px;transition: all 0.2s ease;position: relative;top: -2px;}
.mac-playlist-container .btn-close:hover {opacity: 1;transform: rotate(90deg) scale(1.1);background: rgba(255,255,255,0.15);border-radius: 50%;}
.mac-playlist-container .modal-body {padding: 2rem;max-height: 75vh;overflow-y: auto;}

/* Blocos de servidor */
.mac-playlist-container .server-block {border-radius: 0.65rem !important;padding: 1.5rem !important;margin-bottom: 1.5rem !important;border: 1px solid #e5e7eb !important;box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05) !important;transition: all 0.2s ease;position: relative;overflow: hidden;}
.mac-playlist-container .server-block:hover {box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1) !important;transform: translateX(3px);border-color: #d1d5db !important;}
.mac-playlist-container .server-block::before {content: \'\' !important;position: absolute !important;left: 0 !important;top: 0 !important;height: 100% !important;width: 4px !important;background: linear-gradient(to bottom, #10b981, #059669) !important;}

/* ====== FORMULÁRIOS ====== */
.mac-playlist-container .form-label {font-weight: 600;color: #4b5563;margin-bottom: 0.5rem;display: flex;align-items: center;gap: 6px;font-size: 0.9rem;}
.mac-playlist-container .form-label i {font-size: 1.1rem;opacity: 0.8;}
.mac-playlist-container .form-control,.mac-playlist-container .form-select {border: 1.5px solid #e5e7eb;padding: 0.75rem 1rem;border-radius: 0.5rem;transition: all 0.2s ease;background-color: #ffffff;font-size: 1rem;}
.mac-playlist-container .form-control:focus,.mac-playlist-container .form-select:focus {border-color: #10b981;box-shadow: 0 0 0 3px rgba(16,185,129,0.15);outline: none;}
.mac-playlist-container .form-control-solid {background-color: #f9fafb;border-color: #e5e7eb;}
.mac-playlist-container .form-control-solid:focus {background-color: white;}
.mac-playlist-container .input-group .btn {border-radius: 0 0.5rem 0.5rem 0 !important;background: #10b981;border: none;padding: 0 1rem;font-weight: 500;transition: all 0.2s ease;}
.mac-playlist-container .input-group .btn:hover {background: #059669;transform: translateY(-1px);}
.mac-playlist-container .indicator-progress {display: none;}
.btn-toggle-password {border-radius: 0 0.5rem 0.5rem 0 !important;border-left: 1px solid #e5e7eb;width: 42px;}
.btn-toggle-password:hover {background-color: #f3f4f6;}

/* ====== BOTÕES PRINCIPAIS ====== */
.mac-playlist-container .btn-primary {background: linear-gradient(135deg, #10b981 0%, #059669 100%);border: none;padding: 0.75rem 1.5rem;font-weight: 600;letter-spacing: 0.5px;box-shadow: 0 2px 4px -1px rgba(0,0,0,0.1);transition: all 0.2s ease;font-size: 1.05rem;}
.mac-playlist-container .btn-primary:hover {transform: translateY(-2px);box-shadow: 0 4px 6px -1px rgba(0,0,0,0.15);background: linear-gradient(135deg, #059669 0%, #047857 100%);}
.mac-playlist-container .btn-light-primary {background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);color: #0d9488;border: none;font-weight: 600;box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05);transition: all 0.2s ease;font-size: 1.05rem;}
.mac-playlist-container .btn-light-primary:hover {background: linear-gradient(135deg, #bae6fd 0%, #94e6fc 100%);color: #0d9488;transform: translateY(-2px);box-shadow: 0 2px 4px 0 rgba(0,0,0,0.1);}
.mac-playlist-container .btn-light {background: #f9fafb;border: 1px solid #e5e7eb;color: #4b5563;font-weight: 500;transition: all 0.2s ease;}
.mac-playlist-container .btn-light:hover {background: #f3f4f6;transform: translateY(-1px);box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05);}

/* ====== RESPONSIVO ====== */
@media (max-width: 992px) {.mac-playlist-container .card-header {flex-direction: column;align-items: flex-start;}.mac-playlist-container .card-toolbar {width: 100%;margin-top: 1rem;justify-content: space-between !important;}.mac-playlist-container .server-block {padding: 1.25rem !important;}}
@media (max-width: 768px) {.mac-playlist-container .modal-dialog {margin: 10px !important;max-width: 95% !important;}.mac-playlist-container .modal-body {padding: 1.5rem;}.mac-playlist-container .modal-title {font-size: 1.3rem;}.mac-playlist-container .modal-title i {font-size: 1.6rem;}.mac-playlist-container .table thead th,.mac-playlist-container .table tbody td {padding: 0.75rem 0.75rem;font-size: 0.85rem;}.btn-action {width: 36px;height: 36px;margin: 0 2px;}.btn-reveal {width: 32px;height: 32px;}}

/* Bullet para servidores */
.bullet {display: inline-block;border-radius: 50%;position: relative;}
.bullet-dot {width: 10px;height: 10px;}

/* ====== DARK MODE - CORREÇÃO COMPLETA ====== */
.dark-mode .mac-playlist-container .card {background: #0a0e17 !important;border-color: #2a3042 !important;}
.dark-mode .mac-playlist-container .card-header {background: #0d111d !important;border-bottom: 1px solid #2a3042 !important;}
.dark-mode .mac-playlist-container .table {background: #0a0e17 !important;}
.dark-mode .mac-playlist-container .table thead th {background: #141a29 !important;color: #ffffff !important;border-bottom: 1px solid #2a3042 !important;}
.dark-mode .mac-playlist-container .table tbody tr {border-bottom: 1px solid #2a3042 !important;}
.dark-mode .mac-playlist-container .table td {color: #ffffff !important;}

/* ====== ELEMENTOS DO FORMULÁRIO NO DARK MODE ====== */
.dark-mode .mac-playlist-container .form-label,
.dark-mode .mac-playlist-container .server-title,
.dark-mode .mac-playlist-container label {
    color: #ffffff !important;
    font-weight: 600 !important;
    text-shadow: 0 1px 2px rgba(0,0,0,0.4) !important;
    font-size: 0.95rem !important;
}

.dark-mode .mac-playlist-container .form-label i {
    color: #00d9ff !important;
    opacity: 1 !important;
}

.dark-mode .mac-playlist-container .form-control,
.dark-mode .mac-playlist-container .form-select {
    background-color: #161d2b !important;
    border-color: #2a354d !important;
    color: #ffffff !important;
    text-shadow: 0 1px 1px rgba(0,0,0,0.3) !important;
    font-size: 1rem !important;
}

.dark-mode .mac-playlist-container .form-control::placeholder,
.dark-mode .mac-playlist-container .form-select::placeholder {
    color: #a0a8bc !important;
    opacity: 1 !important;
    font-weight: 400 !important;
}

.dark-mode .mac-playlist-container .form-control:focus,
.dark-mode .mac-playlist-container .form-select:focus {
    border-color: #00d9ff !important;
    box-shadow: 0 0 0 3px rgba(0,217,255,0.3) !important;
    background-color: #1a2232 !important;
    color: #ffffff !important;
}

/* ====== MODAL NO DARK MODE ====== */
.dark-mode .mac-playlist-container .modal-header {
    background: linear-gradient(120deg, #00d9ff 0%, #00a3ff 100%) !important;
    color: #ffffff !important;
    border-bottom: none !important;
    padding: 1.5rem 2rem !important;
    border-radius: 0.75rem 0.75rem 0 0 !important;
    position: relative !important;
    overflow: hidden !important;
    min-height: 4rem !important;
}

.dark-mode .mac-playlist-container .modal-header::before {
    content: \'\' !important;
    position: absolute !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    height: 100% !important;
    background: linear-gradient(120deg, #00d9ff 0%, #00a3ff 100%) !important;
    z-index: -1 !important;
    border-radius: 0.75rem 0.75rem 0 0 !important;
}

.dark-mode .mac-playlist-container .modal-title {
    color: #ffffff !important;
    text-shadow: 0 1px 3px rgba(0,0,0,0.5) !important;
}

.dark-mode .mac-playlist-container .modal-content {
    background: #0a0e17 !important;
    border: 1px solid #2a3042 !important;
    box-shadow: 0 20px 25px -5px rgba(0,0,0,0.5), 0 10px 10px -5px rgba(0,0,0,0.4) !important;
}

.dark-mode .mac-playlist-container .modal-body {
    background: #0a0e17 !important;
    color: #ffffff !important;
}

/* ====== BLOCOS DE SERVIDOR NO DARK MODE ====== */
.dark-mode .mac-playlist-container .server-block {
    border: 1px solid #2a3042 !important;
    background: #141a29 !important;
}

.dark-mode .mac-playlist-container .server-block::before {
    background: linear-gradient(to bottom, #00d9ff, #00a3ff) !important;
}

/* ====== BOTÕES NO DARK MODE ====== */
.dark-mode .mac-playlist-container .btn {
    color: #ffffff !important;
}

.dark-mode .mac-playlist-container .btn-light {
    color: #ffffff !important;
    background-color: #1e2435 !important;
    border-color: #3d455a !important;
}

.dark-mode .mac-playlist-container .badge-light-primary,
.dark-mode .mac-playlist-container .badge-light-info,
.dark-mode .mac-playlist-container .badge-light-success {
    background: #00d9ff !important;
    color: #0a0e17 !important;
    border: 1px solid #00b8ff !important;
}

.dark-mode .mac-playlist-container .btn-primary {
    background: linear-gradient(135deg, #00d9ff 0%, #00a3ff 100%) !important;
    border: none !important;
    box-shadow: 0 2px 4px -1px rgba(0,217,255,0.4) !important;
}

.dark-mode .mac-playlist-container .btn-primary:hover {
    background: linear-gradient(135deg, #00a3ff 0%, #007acc 100%) !important;
    box-shadow: 0 4px 6px -1px rgba(0,217,255,0.5) !important;
}

/* ====== CAMPO DE PESQUISA NO DARK MODE ====== */
.dark-mode #search_playlist {
    background: #161d2b !important;
    border: 1px solid #2a354d !important;
    color: #ffffff !important;
}

.dark-mode #search_playlist::placeholder {
    color: #94a3b8 !important;
}

/* ====== CARREGAMENTO ====== */
.spinner-border {display: inline-block;width: 1.25rem;height: 1.25rem;vertical-align: text-bottom;border: 0.2em solid currentColor;border-right-color: transparent;border-radius: 50%;animation: spinner-border .75s linear infinite;}
@keyframes spinner-border {to {transform: rotate(360deg);}}
</style>
';
include 'includes/layout.php';
?>