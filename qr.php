<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
$static_url = dirname($_SERVER['SCRIPT_NAME']) . '/assets/';
include 'session_check.php';

$page_title = "Qr Code";
$directory = __DIR__ . '/assets/media/qrcode/';

if (!is_dir($directory)) {
    mkdir($directory, 0755, true);
}

$message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['qrcode'])) {
    if (!empty($_FILES['qrcode']['name'])) {
        $filename = $_FILES['qrcode']['name'];
        $simple_name = uniqid() . '.jpg';
        $target = $directory . $simple_name;

        if (move_uploaded_file($_FILES['qrcode']['tmp_name'], $target)) {
            $message = "Qrcode uploaded successfully.";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $message = "Error uploading the qrcode: ";
            $message .= "Temp file: " . $_FILES['qrcode']['tmp_name'] . ", ";
            $message .= "Target: $target.";
        }
    } else {
        $message = "No file selected.";
    }
}

if (isset($_GET['delete'])) {
    $filename = $_GET['delete'];
    if (file_exists($filename)) {
        unlink($filename);
        $message = "Qrcode deleted successfully.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $message = "The file does not exist.";
    }
}

$qrcode_files = glob($directory . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);
$qrcode_files = array_reverse($qrcode_files);

$page_content = '<div class="card mb-4">
    <div class="card-body">
        <h4>Upload New Qrcode</h4>';

if ($message) {
    $page_content .= '<div class="alert alert-info">' . htmlspecialchars($message) . '</div>';
}

$page_content .= '
        <form action="" method="post" enctype="multipart/form-data" class="d-flex align-items-center">
            <input type="file" name="qrcode" class="form-control me-2" required>
            <button type="submit" class="btn btn-success">Upload</button>
        </form>
    </div>
</div>';

$page_content .= '
<div class="row">';

if (!empty($qrcode_files)) {
    foreach ($qrcode_files as $file) {
        $file_url = htmlspecialchars($static_url . 'media/qrcode/' . basename($file));
        $page_content .= '
            <div class="col-md-4 mb-4">
                <div class="card">
                    <img src="' . $file_url . '" class="card-img-top" alt="Qrcode" style="height: 200px; object-fit: cover;">
                    <div class="card-body text-center">
                        <a href="?delete=' . urlencode($file) . '" class="btn btn-danger" onclick="return confirm(\'Are you sure you want to delete this banner?\');">Delete</a>
                    </div>
                </div>
            </div>';
    }
} else {
    $page_content .= '<div class="col-12"><p>No qrcode found.</p></div>';
}

$page_content .= '</div>';

include 'includes/layout.php';
?>
