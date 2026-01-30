<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
$static_url = dirname($_SERVER['SCRIPT_NAME']) . '/rtx/';
include 'session_check.php';

$adTypeFile = __DIR__ . '/api/ad_type.json';
$currentAdType = json_decode(file_get_contents($adTypeFile), true)['adType'] ?? 'manual';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $newAdType = $_POST['ad_type'] ?? '';
    $allowedTypes = ['manual', 'tmdb', 'sport'];
    
    if (in_array($newAdType, $allowedTypes)) {
        file_put_contents($adTypeFile, json_encode(['adType' => $newAdType], JSON_PRETTY_PRINT));
        echo json_encode(['status' => 'success', 'message' => 'Tipo de anúncio atualizado com sucesso!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Tipo de anúncio inválido']);
    }
    exit;
}

$page_title = "Seleção de Tipo de Anúncio";

$page_content = '
<!-- Adicionando as bibliotecas necessárias no cabeçalho -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Selecione o Tipo de Anúncio</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <!-- Banner Manual -->
            <div class="col-md-4 mb-5 mb-md-0">
                <div class="card card-custom ad-type-card ' . ($currentAdType === 'manual' ? 'card-active' : '') . '" data-type="manual">
                    <div class="card-body text-center">
                        <div class="symbol symbol-100px mb-7">
                            <img src="' . $static_url . 'img/manual.png" class="img-fluid" alt="Banner Manual">
                        </div>
                        <h4 class="text-dark mb-4">Banner Manual</h4>
                        <p class="text-muted">Anúncios personalizados criados manualmente</p>
                        <button class="btn btn-primary select-ad-type" data-type="manual">
                            ' . ($currentAdType === 'manual' ? 'Ativo' : 'Selecionar') . '
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Filmes Automáticos (TMDB) -->
            <div class="col-md-4 mb-5 mb-md-0">
                <div class="card card-custom ad-type-card ' . ($currentAdType === 'tmdb' ? 'card-active' : '') . '" data-type="tmdb">
                    <div class="card-body text-center">
                        <div class="symbol symbol-100px mb-7">
                            <img src="' . $static_url . 'img/tmdb.png" class="img-fluid" alt="Filmes Automáticos">
                        </div>
                        <h4 class="text-dark mb-4">Filmes Automáticos</h4>
                        <p class="text-muted">Anúncios baseados no banco de dados do TMDB</p>
                        <button class="btn btn-primary select-ad-type" data-type="tmdb">
                            ' . ($currentAdType === 'tmdb' ? 'Ativo' : 'Selecionar') . '
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Guia Esportivo -->
            <div class="col-md-4">
                <div class="card card-custom ad-type-card ' . ($currentAdType === 'sport' ? 'card-active' : '') . '" data-type="sport">
                    <div class="card-body text-center">
                        <div class="symbol symbol-100px mb-7">
                            <img src="' . $static_url . 'img/sport.png" class="img-fluid" alt="Guia Esportivo">
                        </div>
                        <h4 class="text-dark mb-4">Guia Esportivo</h4>
                        <p class="text-muted">Anúncios relacionados a eventos esportivos</p>
                        <button class="btn btn-primary select-ad-type" data-type="sport">
                            ' . ($currentAdType === 'sport' ? 'Ativo' : 'Selecionar') . '
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .ad-type-card {
        transition: all 0.3s ease;
        cursor: pointer;
        height: 100%;
    }
    
    .ad-type-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .card-active {
        border: 2px solid #3699FF;
        background-color: #F3F6F9;
    }
    
    .select-ad-type {
        width: 120px;
    }
</style>

<script>
// Verificação de dependências
if (typeof $ === "undefined") {
    console.error("jQuery não está carregado!");
}
if (typeof Swal === "undefined") {
    console.error("SweetAlert2 não está carregado!");
}

$(document).ready(function() {
    console.log("Documento pronto - jQuery funcionando");
    
    $(".ad-type-card").click(function() {
        console.log("Card clicado:", $(this).data("type"));
        const adType = $(this).data("type");
        selectAdType(adType);
    });
    
    $(".select-ad-type").click(function(e) {
        console.log("Botão clicado:", $(this).data("type"));
        e.stopPropagation();
        const adType = $(this).data("type");
        selectAdType(adType);
    });
    
    function selectAdType(adType) {
        console.log("Tentando selecionar:", adType);
        
        // Verificação simplificada para teste
        if (typeof Swal === "undefined") {
            alert("Deseja alterar para " + getAdTypeName(adType) + "?");
            return;
        }
        
        Swal.fire({
            title: "Confirmar alteração",
            text: "Deseja realmente alterar o tipo de anúncio para " + getAdTypeName(adType) + "?",
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Sim, alterar",
            cancelButtonText: "Cancelar"
        }).then((result) => {
            if (result.isConfirmed) {
                console.log("Enviando requisição AJAX para:", window.location.href);
                
                $.ajax({
                    url: window.location.href,
                    method: "POST",
                    data: { ad_type: adType },
                    dataType: "json",
                    success: function(response) {
                        console.log("Resposta recebida:", response);
                        if (response.status === "success") {
                            Swal.fire({
                                title: "Sucesso!",
                                text: response.message,
                                icon: "success"
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: "Erro",
                                text: response.message,
                                icon: "error"
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Erro na requisição:", status, error);
                        Swal.fire({
                            title: "Erro",
                            text: "Ocorreu um erro ao tentar atualizar o tipo de anúncio: " + error,
                            icon: "error"
                        });
                    }
                });
            }
        });
    }
    
    function getAdTypeName(adType) {
        switch(adType) {
            case "manual": return "Banner Manual";
            case "tmdb": return "Filmes Automáticos";
            case "sport": return "Guia Esportivo";
            default: return adType;
        }
    }
});
</script>
';

include 'includes/layout.php';
?>