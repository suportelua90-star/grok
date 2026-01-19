<?php
$url = file_get_contents('url.txt');
if ($url !== false && !empty($url)) {
    header("Location: $url");
    exit();
} else {
    echo "https://appsnscripts.com/index.php";
}
?>
