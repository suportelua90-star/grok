<?php
$jsonFile = __DIR__ . '/images/logo_filenames.json';

if (file_exists($jsonFile)) {
    $data = json_decode(file_get_contents($jsonFile), true);
    if (!empty($data[0]['ImageName'])) {
        $filename = $data[0]['ImageName'];
        $fullPath = __DIR__ . '/images/' . $filename;
        
        if (file_exists($fullPath)) {
            $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
            $mime = ($ext === 'png') ? 'image/png' : 'image/jpeg';
            header('Content-Type: ' . $mime);
            readfile($fullPath);
            exit;
        }
    }
}

// Fallback se não encontrar
header('Content-Type: image/png');
echo file_get_contents('https://via.placeholder.com/300x200?text=Logo+Nao+Encontrado');
exit;