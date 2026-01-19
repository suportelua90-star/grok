<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
$static_url = dirname($_SERVER['SCRIPT_NAME']) . '/assets/';
include 'session_check.php';

$page_title = "Web URL";

$file_path = "./api/url.txt";

// Get the current URL from the file if it exists
$current_url = file_exists($file_path) ? file_get_contents($file_path) : "";

$success_message = "";
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["web_url"])) {
        $url = trim($_POST["web_url"]);

        // Validate URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $error_message = "Please enter a valid URL.";
        } else {
            // Save the URL to the file
            file_put_contents($file_path, $url);
            $success_message = "URL saved successfully!";
            $current_url = $url;
        }
    }
}

$page_content = '<div class="container mt-5">
    <div class="card mb-5 mb-xl-12">
        <div class="card-body py-12">
            <h2 class="mb-9">Web URL Management</h2>';

if (!empty($success_message)) {
    $page_content .= '<div class="alert alert-success" role="alert">' . htmlspecialchars($success_message) . '</div>';
}

if (!empty($error_message)) {
    $page_content .= '<div class="alert alert-danger" role="alert">' . htmlspecialchars($error_message) . '</div>';
}

$page_content .= '<div class="row mb-12">
                <div class="col-xl-12 mb-15 mb-xl-0 pe-5">
                    <h4 class="mb-0">Add or Update Web URL</h4>
                    <p class="fs-6 fw-semibold text-gray-600 py-4 m-0">Manage your web URL effectively by updating or adding a new link. Use the form below to set your URL.</p>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="web_url">Web URL:</label>
                            <input 
                                type="text" 
                                class="form-control" 
                                name="web_url" 
                                id="web_url" 
                                placeholder="Example: https://example.com" 
                                value="' . htmlspecialchars($current_url) . '">
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
            <div class="row mt-5">
                <div class="col-xl-12">
                    <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed p-6">
                        <i class="ki-outline ki-link fs-2tx text-primary me-4"></i>
                        <div class="d-flex flex-stack flex-grow-1 flex-wrap flex-md-nowrap">
                            <div class="mb-3 mb-md-0 fw-semibold">
                                <h4 class="text-gray-900 fw-bold">Last Saved URL</h4>
                                <div class="fs-6 text-gray-700 pe-7">' . (!empty($current_url) ? '<a href="' . htmlspecialchars($current_url) . '" target="_blank" style="color: blue;">' . htmlspecialchars($current_url) . '</a>' : 'No URL saved yet.') . '</div>
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
