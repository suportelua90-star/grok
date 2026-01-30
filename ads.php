<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
$static_url = dirname($_SERVER['SCRIPT_NAME']) . '/assets/';
include 'session_check.php';

// Configurações
$upload_dir = __DIR__ . '/rtx/img/';
$db_path = __DIR__ . '/ibo_panel.db';

// Conexão com o banco de dados SQLite
try {
    $db = new SQLite3($db_path);
} catch (Exception $e) {
    die("Erro ao conectar ao banco de dados: " . $e->getMessage());
}

// Processar uploads
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    try {
        if (isset($_POST['action']) && $_POST['action'] === 'delete') {
            // Processar exclusão
            $id = (int)$_POST['id'];
            
            // Obter URL da imagem
            $stmt = $db->prepare("SELECT url FROM ads WHERE id = :id");
            $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
            $result = $stmt->execute();
            $row = $result->fetchArray(SQLITE3_ASSOC);
            
            if (!$row) {
                throw new Exception('Registro não encontrado.');
            }
            
            // Extrair caminho do arquivo
            $url = $row['url'];
            $file_path = __DIR__ . parse_url($url, PHP_URL_PATH);
            
            // Excluir do banco de dados
            $stmt = $db->prepare("DELETE FROM ads WHERE id = :id");
            $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
            
            if (!$stmt->execute()) {
                throw new Exception('Falha ao excluir do banco de dados.');
            }
            
            // Excluir arquivo
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            
            echo json_encode(['status' => 'success', 'message' => 'Imagem excluída com sucesso!']);
        } elseif (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            // Validar tipo de arquivo
            $file_type = $_FILES['image']['type'];
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            
            if (!in_array($file_type, $allowed_types)) {
                throw new Exception('Tipo de arquivo não permitido. Apenas imagens JPEG, PNG, GIF e WebP são aceitas.');
            }
            
            // Validar título
            $title = isset($_POST['title']) ? trim($_POST['title']) : 'Imagem ' . date('d/m/Y H:i');
            
            // Gerar nome único para o arquivo
            $file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $file_name = uniqid('img_') . '.' . $file_ext;
            $file_path = $upload_dir . $file_name;
            
            // Mover arquivo para o diretório de uploads
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $file_path)) {
                throw new Exception('Falha ao mover o arquivo enviado.');
            }
            
            // Salvar URL no banco de dados
            $url = dirname($_SERVER['SCRIPT_NAME']) . '/rtx/img/' . $file_name;
            $stmt = $db->prepare("INSERT INTO ads (title, url) VALUES (:title, :url)");
            $stmt->bindValue(':title', $title, SQLITE3_TEXT);
            $stmt->bindValue(':url', $url, SQLITE3_TEXT);
            
            if (!$stmt->execute()) {
                unlink($file_path); // Remove o arquivo se falhar ao salvar no BD
                throw new Exception('Falha ao salvar no banco de dados.');
            }
            
            echo json_encode(['status' => 'success', 'message' => 'Imagem enviada com sucesso!', 'url' => $url]);
        } else {
            throw new Exception('Nenhum arquivo enviado ou ação inválida.');
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// Obter imagens do banco de dados
$results = $db->query("SELECT id, title, url FROM ads ORDER BY id DESC");
$images = [];
while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
    $images[] = $row;
}

$page_title = "Gerenciador de Imagens";

$page_content = '
<div class="card">
    <div class="card-header border-0 pt-6">
        <div class="card-title">
            <h2>Gerenciador de Imagens</h2>
        </div>
        <div class="card-toolbar">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                <i class="ki-outline ki-plus fs-2"></i> Enviar Imagem
            </button>
        </div>
    </div>
    <div class="card-body pt-0">
        <div class="row" id="imageGallery">
';

foreach ($images as $image) {
    $page_content .= '
            <div class="col-md-4 col-lg-3 mb-4" data-id="' . $image['id'] . '">
                <div class="card h-100">
                    <img src="' . $image['url'] . '" class="card-img-top" alt="' . htmlspecialchars($image['title']) . '" style="height: 200px; object-fit: cover;">
                    <div class="card-body">
                        <h5 class="card-title">' . htmlspecialchars($image['title']) . '</h5>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-sm btn-light-danger" onclick="deleteImage(' . $image['id'] . ')">
                            <i class="ki-outline ki-trash fs-2"></i> Excluir
                        </button>
                    </div>
                </div>
            </div>
    ';
}

$page_content .= '
        </div>
    </div>
</div>

<!-- Modal de Upload -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Enviar Nova Imagem</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="uploadForm" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="imageTitle" class="form-label">Título da Imagem</label>
                        <input type="text" class="form-control" id="imageTitle" name="title" placeholder="Digite um título para a imagem">
                    </div>
                    <div class="mb-3">
                        <label for="imageUpload" class="form-label">Selecione uma imagem</label>
                        <input class="form-control" type="file" id="imageUpload" name="image" accept="image/*" required>
                        <div class="form-text">Formatos aceitos: JPEG, PNG, GIF, WebP</div>
                    </div>
                </form>
                <div id="uploadPreview" class="text-center mt-3 d-none">
                    <img id="previewImage" src="#" alt="Pré-visualização" class="img-fluid rounded" style="max-height: 300px;">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="uploadButton">Enviar</button>
            </div>
        </div>
    </div>
</div>
';

include 'includes/layout.php';
?>

<script>
    // Pré-visualização da imagem
    document.getElementById('imageUpload').addEventListener('change', function(e) {
        const preview = document.getElementById('uploadPreview');
        const previewImage = document.getElementById('previewImage');
        
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                previewImage.src = e.target.result;
                preview.classList.remove('d-none');
            }
            
            reader.readAsDataURL(this.files[0]);
        }
    });

    // Enviar imagem
    document.getElementById('uploadButton').addEventListener('click', function() {
        const formData = new FormData();
        const fileInput = document.getElementById('imageUpload');
        const titleInput = document.getElementById('imageTitle');
        
        if (fileInput.files.length === 0) {
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: 'Por favor, selecione uma imagem para enviar.'
            });
            return;
        }
        
        formData.append('image', fileInput.files[0]);
        formData.append('title', titleInput.value);
        
        fetch('ads.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Sucesso!',
                    text: data.message
                }).then(() => {
                    $('#uploadModal').modal('hide');
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: data.message
                });
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: 'Ocorreu um erro ao enviar a imagem.'
            });
        });
    });

    // Excluir imagem
    function deleteImage(id) {
        Swal.fire({
            title: 'Tem certeza?',
            text: "Você não poderá reverter isso!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sim, excluir!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('ads.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'delete',
                        id: id
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Excluído!',
                            text: data.message
                        }).then(() => location.reload());
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro',
                            text: data.message
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: 'Ocorreu um erro ao excluir a imagem.'
                    });
                });
            }
        });
    }
</script>