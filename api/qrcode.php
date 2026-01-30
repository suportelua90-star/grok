<?php
error_reporting(0);

// Obter o caminho base dinâmico do servidor
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$uri = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');

// Caminho base dinâmico
$baseUrl = $protocol . $host . $uri . '/qr/';

$db = new SQLite3('studiolivecode.db');
$res = $db->query('SELECT * FROM qrcode'); 
$rows = array();
$rowsn = array();
$json_response = array(); 

while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
	$row_array['AdName'] = $row['title']; 

	// Concatena o caminho base com o nome do arquivo da imagem
	$row_array['AdUrl'] = $baseUrl . basename($row['url']); 

	array_push($json_response, $row_array);  
}

header('Content-type: application/json; charset=UTF-8');
$final = json_encode($json_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
echo ($final);
?>
