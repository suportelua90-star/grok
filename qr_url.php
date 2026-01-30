<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
include(__DIR__ . '/includes/functions.php');
$static_url = dirname($_SERVER['SCRIPT_NAME']) . '/assets/';
include 'session_check.php';

$qr_code = $db->select('qr_code', '*', 'id = :id', '', [':id' => '1']);
$qrCodeURL = $qr_code[0]['url'];
$qrCodename = $qr_code[0]['name'];

$page_title = "QR Code URL";

$current_url = $qrCodeURL;
$current_label = $qrCodename;

$success_message = "";
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["web_label"])) {
        $name = trim($_POST["web_label"]);
        
        // Mantém a URL existente, apenas atualiza o label
        $data = ['name' => $name];
        $db->update('qr_code', $data, 'id = :id', [':id' => '1']);
        
        $success_message = "Label saved successfully!";
        $current_label = $name;
    }
}

$page_content = '<div class="container mt-5">
    <div class="card mb-5 mb-xl-12">
        <div class="card-body py-12">
            <h2 class="mb-9">QR Code Management</h2>';

if (!empty($success_message)) {
    $page_content .= '<div class="alert alert-success" role="alert">' . htmlspecialchars($success_message) . '</div>';
}

$page_content .= '<div class="row mb-12">
                <div class="col-xl-12 mb-15 mb-xl-0 pe-5">
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="web_label">QR Label:</label>
                            <input 
                                type="text" 
                                class="form-control" 
                                name="web_label" 
                                id="web_label" 
                                placeholder="Example: Test IPTV Service" 
                                value="' . htmlspecialchars($current_label) . '">
                        </div>
                        <div class="text-center mt-4">
                            <input 
                                type="submit" 
                                name="submit" 
                                value="Save" 
                                class="btn btn-primary btn-block">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>';

include 'includes/layout.php';
?>