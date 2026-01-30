<?php
ini_set('display_errors', 0);
error_reporting(0);
header('Content-Type: application/json');

// Caminho para o seu banco de dados SQLite
$db_file = __DIR__ . "/ibo_panel.db";

try {
    // Conexão com SQLite
    $db = new PDO("sqlite:" . $db_file);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Consulta a tabela themes para pegar o theme_id e layout
    // Mantida a lógica original de buscar o tema ativo (is_active = 1)
    $query = "SELECT theme_id, layout_name FROM themes WHERE is_active = 1 LIMIT 1";
    $stmt = $db->query($query);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        // ✅ Mantida a lógica original, garantindo que theme_id seja string para o parse do Android
        // O Android lerá "theme_id": "2" e usará o layout_v2 na próxima abertura
        echo json_encode([
            "theme_id" => (string)$row['theme_id'],
            "layout" => $row['layout_name'] ?: "activity_main"
        ]);
    } else {
        // Se a tabela estiver vazia, retorna o tema 1 por padrão
        echo json_encode([
            "theme_id" => "1",
            "layout" => "activity_main"
        ]);
    }

} catch (PDOException $e) {
    // Em caso de erro, retorna o tema padrão para evitar que o App fique sem resposta
    echo json_encode([
        "theme_id" => "1",
        "layout" => "activity_main",
        "error" => $e->getMessage()
    ]);
}
?>