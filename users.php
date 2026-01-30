<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
$static_url = dirname($_SERVER['SCRIPT_NAME']) . '/assets/';
include 'session_check.php';

$page_title = "Users";

$page_content = '
<div class="card">
    <div class="card-header border-0 pt-6">
        <div class="card-title">
            <h3>Users</h3>
        </div>
    </div>
    <div class="card-body pt-0">
        <table class="table align-middle table-row-dashed fs-6 gy-5" id="users_table">
            <thead>
                <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                    <th class="min-w-125px">ID</th>
                    <th class="min-w-125px">Username</th>
                    <th class="min-w-70px">Actions</th>
                </tr>
            </thead>
            <tbody id="users_table_body"></tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="users_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modal_title" class="fw-bold">Update User</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="users_id">
                <div class="mb-7">
                    <label for="username" class="fs-6 fw-semibold mb-2">Username</label>
                    <input type="text" class="form-control" id="username" placeholder="Enter Username" required>
                </div>
                <div class="mb-7">
                    <label for="password" class="fs-6 fw-semibold mb-2">Password</label>
                    <input type="password" class="form-control" id="password" placeholder="Enter Password" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="updateUser()">Update</button>
            </div>
        </div>
    </div>
</div>
';

include 'includes/layout.php';
?>

<script>
let usersTable;

function loadUsers() {
    fetch("actions/user_actions.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ action: "view" }),
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                const tableBody = document.getElementById("users_table_body");
                tableBody.innerHTML = "";

                data.data.forEach((user) => {
                    tableBody.innerHTML += `
                        <tr id="row_${user.id}">
                            <td>${user.id}</td>
                            <td>${user.username}</td>
                            <td>
                                <button class="btn btn-sm btn-warning" onclick="openEditModal(${user.id}, '${user.username}')">Edit</button>
                            </td>
                        </tr>
                    `;
                });

                if (usersTable) {
                    usersTable.destroy();
                }

                usersTable = $("#users_table").DataTable();
            }
        })
        .catch((error) => console.error("Error loading users:", error));
}

function openEditModal(id, username) {
    document.getElementById("modal_title").textContent = "Edit User";
    document.getElementById("users_id").value = id;
    document.getElementById("username").value = username;
    document.getElementById("password").value = "";

    new bootstrap.Modal(document.getElementById("users_modal")).show();
}

function updateUser() {
    const id = document.getElementById("users_id").value;
    const username = document.getElementById("username").value.trim();
    const password = document.getElementById("password").value.trim();

    if (!username || !password) {
        Swal.fire("Error", "Username and Password are required!", "error");
        return;
    }

    fetch("actions/user_actions.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ action: "edit", id, username, password }),
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                Swal.fire("Success", data.message, "success");

                const modal = bootstrap.Modal.getInstance(document.getElementById("users_modal"));
                modal.hide();

                const row = document.querySelector(`#row_${id}`);
                if (row) {
                    row.innerHTML = `
                        <td>${id}</td>
                        <td>${username}</td>
                        <td>
                            <button class="btn btn-sm btn-warning" onclick="openEditModal(${id}, '${username}')">Edit</button>
                        </td>
                    `;
                }
            } else {
                Swal.fire("Error", data.message, "error");
            }
        })
        .catch((error) => {
            console.error("Error updating user:", error);
            Swal.fire("Unexpected Error", "An unexpected error occurred.", "error");
        });
}

document.addEventListener("DOMContentLoaded", loadUsers);
</script>
