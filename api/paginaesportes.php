<?php
$url = file_get_contents('urlesportes.txt');
if ($url !== false && !empty($url)) {
    header("Location: $url");
    exit();
} else {
    echo "No URL was saved.";
}
?>
