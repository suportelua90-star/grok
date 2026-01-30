<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
$static_url = dirname($_SERVER['SCRIPT_NAME']) . '/assets/';
include 'session_check.php';

$keyFilePath = __DIR__ . '/api/key.json';
$keys = [];
if (file_exists($keyFilePath)) {
    $keys = json_decode(file_get_contents($keyFilePath), true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $action = $_POST['action'];
    $macAddress = strtoupper(trim($_POST['mac_address']));
    $message = isset($_POST['message']) ? trim($_POST['message']) : null;

    try {
        if ($action === 'add' || $action === 'edit') {
            $keys[$macAddress] = [
                'key' => $keys[$macAddress]['key'] ?? generateUniqueKey(),
                'message' => $message
            ];
            file_put_contents($keyFilePath, json_encode($keys, JSON_PRETTY_PRINT));
            echo json_encode(['status' => 'success', 'message' => 'Message saved successfully.']);
        } elseif ($action === 'delete') {
            if (isset($keys[$macAddress])) {
                unset($keys[$macAddress]);
                file_put_contents($keyFilePath, json_encode($keys, JSON_PRETTY_PRINT));
                echo json_encode(['status' => 'success', 'message' => 'Message deleted successfully.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'MAC address not found.']);
            }
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'An error occurred: ' . $e->getMessage()]);
    }

    exit;
}


$page_title = "MAC Message";

$page_content = '
<div class="card">
    <div class="card-header border-0 pt-6">
        <div class="card-title">
            <div class="d-flex align-items-center position-relative my-1">
                <i class="ki-outline ki-magnifier fs-3 position-absolute ms-5"></i>
                <input type="text" id="search_message" class="form-control form-control-solid w-250px ps-12" placeholder="Search Message" />
            </div>
        </div>
        <div class="card-toolbar">
            <div class="d-flex justify-content-end">
                <button type="button" class="btn btn-primary" onclick="openAddModal()">Add Message</button>
            </div>
        </div>
    </div>
    <div class="card-body pt-0">
        <table class="table align-middle table-row-dashed fs-6 gy-5" id="message_table">
            <thead>
                <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                    <th class="min-w-125px">MAC</th>
                    <th class="min-w-125px">Key</th>
                    <th class="min-w-125px">Message</th>
                    <th class="min-w-70px">Actions</th>
                </tr>
            </thead>
            <tbody id="message_table_body" class="fw-semibold text-gray-600">
';

foreach ($keys as $mac => $data) {
    $page_content .= "<tr data-mac='{$mac}'>
        <td>{$mac}</td>
        <td>{$data['key']}</td>
        <td class='message-cell'>{$data['message']}</td>
        <td>
            <button class='btn btn-sm btn-light-primary' onclick=\"editMessage('{$mac}')\">Edit</button>
            <button class='btn btn-sm btn-light-danger' onclick=\"deleteMessage('{$mac}')\">Delete</button>
        </td>
    </tr>";
}

$page_content .= '
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="message_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modal_title" class="fw-bold">Add Message</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="message_form">
                    <div class="mb-7">
                        <label for="mac_address" class="fs-6 fw-semibold mb-2">MAC Address</label>
                        <input type="text" class="form-control" id="mac_address" placeholder="Enter MAC Address" required>
                    </div>
                    <div class="mb-7">
                        <label for="message" class="fs-6 fw-semibold mb-2">Message</label>
                        <input type="text" class="form-control" id="message" placeholder="Enter message" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveMessage()">Save</button>
            </div>
        </div>
    </div>
</div>

';

function generateUniqueKey() {
    return bin2hex(random_bytes(8));
}

include 'includes/layout.php';
?>

<script>
    let dataTable;

    if (dataTable) {
        dataTable.destroy();
    }

    document.getElementById("search_message").addEventListener("input", function () {
        if (dataTable) {
            dataTable.search(this.value).draw();
        }
    });

    dataTable = $('#message_table').DataTable({
        searching: true,
        paging: true,
        ordering: true,
        info: true,
        responsive: false
    });


    function openAddModal() {
        document.getElementById('modal_title').textContent = 'Add Message';
        document.getElementById('mac_address').value = '';
        document.getElementById('mac_address').readOnly = false;
        document.getElementById('message').value = '';
        $('#message_modal').modal('show');
    }

    function editMessage(mac) {
        const row = document.querySelector(`#message_table_body tr[data-mac="${mac}"]`);
        if (!row) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: `No row found for MAC: ${mac}`,
            });
            return;
        }

        const message = row.querySelector('.message-cell')?.textContent.trim();
        if (!message) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: `Message not found for MAC: ${mac}`,
            });
            return;
        }

        document.getElementById('modal_title').textContent = 'Edit Message';
        document.getElementById('mac_address').value = mac;
        document.getElementById('mac_address').readOnly = true;
        document.getElementById('message').value = message;
        $('#message_modal').modal('show');
    }

    function saveMessage() {
        const macAddress = document.getElementById('mac_address').value.trim();
        const message = document.getElementById('message').value.trim();

        if (!macAddress || !message) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Both MAC Address and Message are required!',
            });
            return;
        }

        const action = document.getElementById('mac_address').readOnly ? 'edit' : 'add';

        fetch('mac_message.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: action,
                mac_address: macAddress,
                message: message,
            }),
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: data.message,
                    }).then(() => location.reload());
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message,
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Unexpected Error',
                    text: 'An unexpected error occurred. Please try again later.',
                });
            });
    }

    function deleteMessage(mac) {
        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to delete the message for MAC: ${mac}.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('mac_message.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'delete',
                        mac_address: mac,
                    }),
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: data.message,
                            }).then(() => location.reload());
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message,
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Unexpected Error',
                            text: 'An unexpected error occurred. Please try again later.',
                        });
                    });
            }
        });
    }
</script>
