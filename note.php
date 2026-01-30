<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
$static_url = dirname($_SERVER['SCRIPT_NAME']) . '/assets/';
include 'session_check.php';

$page_title = "Settings";

$page_content = '
<div class="card">
    <div class="card-header border-0 pt-6">
        <div class="card-title">
            <h3>Settings</h3>
        </div>
    </div>
    <div class="card-body pt-0">
        <table class="table align-middle table-row-dashed fs-6 gy-5" id="settings_table">
            <thead>
                <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                    <th class="min-w-125px">ID</th>
                    <th class="min-w-125px">Title</th>
                    <th class="min-w-125px">Content</th>
                    <th class="min-w-70px">Actions</th>
                </tr>
            </thead>
            <tbody id="settings_table_body"></tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="settings_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modal_title" class="fw-bold">Update Note</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="settings_id">
                <div class="mb-7">
                    <label for="note_title" class="fs-6 fw-semibold mb-2">Title</label>
                    <input type="text" class="form-control" id="note_title" placeholder="Enter Title" required>
                </div>
                <div class="mb-7">
                    <label for="note_content" class="fs-6 fw-semibold mb-2">Content</label>
                    <textarea class="form-control" id="note_content" placeholder="Enter Content" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="updateNote()">Update</button>
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
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                const tableBody = document.getElementById("settings_table_body");
                tableBody.innerHTML = "";

                data.data.forEach((setting) => {
                    tableBody.innerHTML += `
                        <tr id="row_${setting.id}">
                            <td>${setting.id}</td>
                            <td>${setting.note_title}</td>
                            <td>${setting.note_content}</td>
                            <td>
                                <button class="btn btn-sm btn-warning" onclick="openEditModal(${setting.id}, '${setting.note_title}', '${setting.note_content}')">Edit</button>
                            </td>
                        </tr>
                    `;
                });

                if (settingsTable) {
                    settingsTable.destroy();
                }

                settingsTable = $("#settings_table").DataTable();
            }
        })
        .catch((error) => console.error("Error loading settings:", error));
}

function openEditModal(id, title, content) {
    document.getElementById("modal_title").textContent = "Edit Note";
    document.getElementById("settings_id").value = id;
    document.getElementById("note_title").value = title;
    document.getElementById("note_content").value = content;

    new bootstrap.Modal(document.getElementById("settings_modal")).show();
}


function updateNote() {
    const id = document.getElementById("settings_id").value;
    const note_title = document.getElementById("note_title").value.trim();
    const note_content = document.getElementById("note_content").value.trim();

    if (!note_title || !note_content) {
        Swal.fire("Error", "Title and Content are required!", "error");
        return;
    }

    fetch("actions/note_actions.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ action: "edit", id, note_title, note_content }),
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                Swal.fire("Success", data.message, "success");

                // Modalı kapatma
                const modal = bootstrap.Modal.getInstance(document.getElementById("settings_modal"));
                modal.hide();

                // Güncellenen satırı tamamen yenile
                const row = document.querySelector(`#row_${id}`);
                if (row) {
                    row.innerHTML = `
                        <td>${id}</td>
                        <td>${note_title}</td>
                        <td>${note_content}</td>
                        <td>
                            <button class="btn btn-sm btn-warning" onclick="openEditModal(${id}, '${note_title}', '${note_content}')">Edit</button>
                        </td>
                    `;
                }
            } else {
                Swal.fire("Error", data.message, "error");
            }
        })
        .catch((error) => {
            console.error("Error updating note:", error);
            Swal.fire("Unexpected Error", "An unexpected error occurred.", "error");
        });
}



document.addEventListener("DOMContentLoaded", loadSettings);
</script>
