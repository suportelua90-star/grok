<?php
$static_url = dirname($_SERVER['SCRIPT_NAME']) . '/assets/';
include 'session_check.php';

$page_title = "Messages";

$file_path = "./api/ad_descriptions.txt";

$file_content = "";
$text_color = "#000000";
$alert_message = "";

if (isset($_SESSION['alert_message'])) {
    $alert_message = $_SESSION['alert_message'];
    unset($_SESSION['alert_message']);
}

if (file_exists($file_path)) {
    $file_content = file_get_contents($file_path);
    $lines = explode("\n", $file_content);
    if (count($lines) > 1) {
        $text_color = trim($lines[count($lines) - 1]);
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $text_color = $_POST["text_color"];
    $ad_description = $_POST["ad_item"];

    if (empty($ad_description)) {
        $_SESSION['alert_message'] = '<div class="alert alert-danger" role="alert">Error: Please enter an ad description.</div>';
    } else {
        $file = fopen($file_path, "w");
        fwrite($file, $ad_description . "\n" . $text_color . "\n");
        fclose($file);

        $_SESSION['alert_message'] = '<div class="alert alert-success" role="alert">Message saved successfully!</div>';
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

$page_content = '<div class="container mt-5">
    <div class="card mb-5 mb-xl-12">
        <div class="card-body py-12">
            <h2 class="mb-9">Message Management</h2>
            <!-- Alert Mesajını Göster -->
            ' . $alert_message . '
            <div class="row mb-12">
                <div class="col-xl-12 mb-15 mb-xl-0 pe-5">
                    <h4 class="mb-0">Add or Update Messages</h4>
                    <p class="fs-6 fw-semibold text-gray-600 py-4 m-0">Manage your messages effectively by updating or adding new content. Use the form below to set your message and customize its appearance with a color choice.</p>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="ad_item">Add a message:</label>
                            <input 
                                type="text" 
                                class="form-control" 
                                name="ad_item" 
                                id="ad_item" 
                                placeholder="Example: Server temporarily down.">
                        </div>
                        <div class="form-group">
                            <label for="text_color">Choose text color:</label>
                            <input 
                                type="color" 
                                class="form-control form-control-color" 
                                name="text_color" 
                                id="text_color" 
                                value="' . htmlspecialchars($text_color) . '">
                        </div>
                        <div class="text-center">
                            <input 
                                type="submit" 
                                name="submit" 
                                value="Save" 
                                class="btn btn-primary btn-block">
                        </div>
                    </form>
                </div>
            </div>
            <div class="row mt-5">
                <div class="col-xl-12">
                    <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed p-6">
                        <i class="ki-outline ki-bank fs-2tx text-primary me-4"></i>
                        <div class="d-flex flex-stack flex-grow-1 flex-wrap flex-md-nowrap">
                            <div class="mb-3 mb-md-0 fw-semibold">
                                <h4 class="text-gray-900 fw-bold">Last Recorded Message</h4>
                                <div class="fs-6 text-gray-700 pe-7" style="color: ' . htmlspecialchars($text_color) . ';">' . nl2br(htmlspecialchars($file_content)) . '</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>';

include 'includes/layout.php';
?>
