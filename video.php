<?php
// Usando caminho relativo a partir da localização do video.php
$videoPath = __DIR__ . '/rtx/intro/intro.mp4';

// Verificar se o arquivo existe
if (file_exists($videoPath)) {
    header('Content-Type: video/mp4');
    header('Content-Length: ' . filesize($videoPath));
    readfile($videoPath);
    exit();
} else {
    header('HTTP/1.1 404 Not Found');
    echo 'Vídeo não encontrado em: ' . $videoPath;
}
?>