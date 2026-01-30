<?php
/**
 * Sistema de Busca de Dispositivos - IBO Panel
 * Arquivo: revenda.php
 * Descrição: Página completa com interface web e API para busca de dispositivos por MAC
 */

// Ativa modo de exibição de erros (desativar em produção)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Verifica se é uma requisição AJAX/API
$is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// Se for requisição AJAX/API, retorna JSON
if ($is_ajax || isset($_GET['api']) || isset($_POST['api'])) {
    header('Content-Type: application/json');
    handleApiRequest();
    exit;
}

// Se não for AJAX, exibe a interface HTML
showWebInterface();

// ================= FUNÇÕES DA API =================

function handleApiRequest() {
    $response = processRequest();
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}

function formatarMacAddress($mac) {
    // Remove caracteres não hexadecimais
    $mac_limpo = preg_replace('/[^A-Fa-f0-9]/', '', $mac);
    
    // Verifica se tem comprimento válido
    if (strlen($mac_limpo) != 12) {
        return $mac;
    }
    
    // Formata com : a cada 2 caracteres
    $partes = str_split($mac_limpo, 2);
    return strtoupper(implode(':', $partes));
}

function processRequest() {
    // Obtém o MAC address da requisição
    $mac_input = '';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $mac_input = trim($_POST['mac'] ?? $_POST['mac_address'] ?? '');
    } else {
        $mac_input = trim($_GET['mac'] ?? $_GET['mac_address'] ?? '');
    }
    
    // Se não forneceu MAC, retorna instruções
    if (empty($mac_input)) {
        return [
            'status' => 'info',
            'codigo' => 100,
            'mensagem' => 'Bem-vindo ao IBO MAX PLAYER',
            'instrucoes' => [
                'como_usar' => 'Para buscar um dispositivo, envie o parâmetro "mac" via GET ou POST',
                'exemplo_get' => 'GET: /revenda.php?mac=E640E789DF3B',
                'formatos_aceitos' => [
                    'E640E789DF3B',
                    'E6:40:E7:89:DF:3B',
                    'e6-40-e7-89-df-3b'
                ]
            ],
            'timestamp' => time()
        ];
    }
    
    // Formata o MAC
    $mac_formatado = formatarMacAddress($mac_input);
    
    // Verifica se o MAC formatado tem comprimento correto
    if (strlen(str_replace(':', '', $mac_formatado)) !== 12) {
        return [
            'status' => 'erro',
            'codigo' => 400,
            'mensagem' => 'Formato de MAC address inválido',
            'mac_recebido' => $mac_input,
            'mac_formatado' => $mac_formatado,
            'timestamp' => time()
        ];
    }
    
    try {
        // Caminho do banco de dados
        $db_file = __DIR__ . "/ibo_panel.db";
        
        // Verifica se o banco existe
        if (!file_exists($db_file)) {
            throw new Exception("Banco de dados não encontrado: " . basename($db_file));
        }
        
        // Conecta ao banco de dados SQLite
        $db = new PDO("sqlite:" . $db_file);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Busca o dispositivo pelo MAC - APENAS OS CAMPOS SOLICITADOS
        $query = "SELECT 
                    mac_address,
                    username,
                    password
                  FROM playlist 
                  WHERE UPPER(REPLACE(mac_address, ':', '')) = UPPER(REPLACE(:mac, ':', ''))
                  LIMIT 1";
        
        $stmt = $db->prepare($query);
        $stmt->bindValue(':mac', $mac_formatado, PDO::PARAM_STR);
        $stmt->execute();
        
        $dispositivo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($dispositivo) {
            return [
                'status' => 'sucesso',
                'codigo' => 200,
                'mensagem' => 'Dispositivo encontrado com sucesso',
                'mac_address' => $mac_formatado,
                'dados_dispositivo' => [
                    'mac_address' => $dispositivo['mac_address'],
                    'username' => $dispositivo['username'],
                    'password' => $dispositivo['password']
                ],
                'timestamp' => time()
            ];
        } else {
            // Sugere dispositivos similares
            $mac_sem_formatacao = str_replace(':', '', $mac_formatado);
            $query_like = "SELECT mac_address FROM playlist 
                          WHERE REPLACE(mac_address, ':', '') LIKE '%' || :mac || '%'
                          LIMIT 5";
            
            $stmt_like = $db->prepare($query_like);
            $stmt_like->bindValue(':mac', $mac_sem_formatacao, PDO::PARAM_STR);
            $stmt_like->execute();
            $sugestoes = $stmt_like->fetchAll(PDO::FETCH_COLUMN);
            
            return [
                'status' => 'nao_encontrado',
                'codigo' => 404,
                'mensagem' => 'Dispositivo não encontrado na base de dados',
                'mac_buscado' => $mac_formatado,
                'sugestoes' => $sugestoes,
                'timestamp' => time()
            ];
        }
        
    } catch (Exception $e) {
        return [
            'status' => 'erro',
            'codigo' => 500,
            'mensagem' => 'Erro no banco de dados',
            'erro_tecnico' => $e->getMessage(),
            'timestamp' => time()
        ];
    }
}

// ================= INTERFACE WEB =================

function showWebInterface() {
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IBO MAX PLAYER - Busca de Dispositivos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .header {
            background: white;
            border-radius: 15px 15px 0 0;
            padding: 30px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .header h1 {
            color: #2c3e50;
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .header h1 i {
            color: #667eea;
            margin-right: 15px;
        }

        .header p {
            color: #7f8c8d;
            font-size: 1.1em;
        }

        .main-content {
            background: white;
            border-radius: 0 0 15px 15px;
            padding: 40px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .search-section {
            margin-bottom: 40px;
        }

        .search-box {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .mac-input {
            flex: 1;
            padding: 15px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 18px;
            transition: all 0.3s;
            font-family: monospace;
            text-transform: uppercase;
        }

        .mac-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
        }

        .search-btn {
            background: linear-gradient(to right, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 0 30px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .search-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .search-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .info-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            border-left: 4px solid #667eea;
            margin-top: 20px;
        }

        .info-box h3 {
            color: #2c3e50;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .examples {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }

        .example-chip {
            background: white;
            border: 1px solid #ddd;
            padding: 8px 15px;
            border-radius: 20px;
            font-family: monospace;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .example-chip:hover {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        .results-section {
            display: none;
        }

        .results-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border: 1px solid #e0e0e0;
        }

        .result-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .result-header h2 {
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }

        .status-success {
            background: #d4edda;
            color: #155724;
        }

        .status-error {
            background: #f8d7da;
            color: #721c24;
        }

        .status-info {
            background: #d1ecf1;
            color: #0c5460;
        }

        .device-info {
            margin-top: 20px;
        }

        .info-row {
            display: flex;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .info-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        .info-label {
            width: 200px;
            font-weight: 600;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-value {
            flex: 1;
            font-family: monospace;
            color: #34495e;
            word-break: break-all;
        }

        .loading {
            text-align: center;
            padding: 40px;
            display: none;
        }

        .loading-spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .footer {
            text-align: center;
            color: white;
            padding: 20px;
            font-size: 0.9em;
        }

        .footer a {
            color: white;
            text-decoration: none;
        }

        .footer a:hover {
            text-decoration: underline;
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .header h1 {
                font-size: 2em;
            }
            
            .search-box {
                flex-direction: column;
            }
            
            .search-btn {
                padding: 15px;
                justify-content: center;
            }
            
            .info-row {
                flex-direction: column;
                gap: 5px;
            }
            
            .info-label {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-search"></i> IBO MAX PLAYER</h1>
            <p>Busque dispositivos pelo endereço MAC</p>
        </div>
        
        <div class="main-content">
            <div class="search-section">
                <div class="search-box">
                    <input type="text" 
                           class="mac-input" 
                           id="macInput" 
                           placeholder="Digite o MAC address (ex: E640E789DF3B)"
                           maxlength="17">
                    <button class="search-btn" id="searchBtn" onclick="searchDevice()">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                </div>
                
                <div class="info-box">
                    <h3><i class="fas fa-info-circle"></i> Como usar</h3>
                    <p>Digite o endereço MAC do dispositivo que deseja buscar. Você pode digitar com ou sem os dois pontos (:).</p>
                    
                    <div class="examples">
                        <div class="example-chip" onclick="setExample('E640E789DF3B')">E640E789DF3B</div>
                        <div class="example-chip" onclick="setExample('E6:40:E7:89:DF:3B')">E6:40:E7:89:DF:3B</div>
                        <div class="example-chip" onclick="setExample('e640e789df3b')">e640e789df3b</div>
                        <div class="example-chip" onclick="setExample('E6-40-E7-89-DF-3B')">E6-40-E7-89-DF-3B</div>
                    </div>
                </div>
            </div>
            
            <div class="loading" id="loading">
                <div class="loading-spinner"></div>
                <p>Buscando dispositivo...</p>
            </div>
            
            <div class="results-section" id="resultsSection">
                <!-- Os resultados serão inseridos aqui via JavaScript -->
            </div>
        </div>
        
        <div class="footer">
            <p>Sistema IBO MAX PLAYER &copy; <?php echo date('Y'); ?> - Todos os direitos reservados</p>
        </div>
    </div>

    <script>
        // Formata o MAC address enquanto digita
        document.getElementById('macInput').addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^A-Fa-f0-9]/g, '');
            let formatted = '';
            
            for (let i = 0; i < value.length; i += 2) {
                if (i > 0 && i < value.length) {
                    formatted += ':';
                }
                formatted += value.substr(i, 2);
            }
            
            e.target.value = formatted.toUpperCase();
        });
        
        // Permite usar a tecla Enter para buscar
        document.getElementById('macInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchDevice();
            }
        });
        
        // Preenche o campo com exemplo
        function setExample(mac) {
            document.getElementById('macInput').value = mac;
            document.getElementById('macInput').focus();
        }
        
        // Função principal de busca
        function searchDevice() {
            const mac = document.getElementById('macInput').value.trim();
            const searchBtn = document.getElementById('searchBtn');
            const loading = document.getElementById('loading');
            const resultsSection = document.getElementById('resultsSection');
            
            if (!mac) {
                alert('Por favor, digite um MAC address para buscar.');
                return;
            }
            
            // Desabilita botão e mostra loading
            searchBtn.disabled = true;
            loading.style.display = 'block';
            resultsSection.style.display = 'none';
            
            // Faz a requisição AJAX
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'revenda.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    loading.style.display = 'none';
                    searchBtn.disabled = false;
                    resultsSection.style.display = 'block';
                    
                    try {
                        const response = JSON.parse(xhr.responseText);
                        displayResults(response);
                    } catch (e) {
                        resultsSection.innerHTML = `
                            <div class="results-card">
                                <div class="result-header">
                                    <h2><i class="fas fa-exclamation-triangle"></i> Erro</h2>
                                    <div class="status-badge status-error">ERRO</div>
                                </div>
                                <p>Erro ao processar resposta do servidor.</p>
                            </div>
                        `;
                    }
                }
            };
            
            xhr.onerror = function() {
                loading.style.display = 'none';
                searchBtn.disabled = false;
                resultsSection.style.display = 'block';
                resultsSection.innerHTML = `
                    <div class="results-card">
                        <div class="result-header">
                            <h2><i class="fas fa-exclamation-triangle"></i> Erro de Conexão</h2>
                            <div class="status-badge status-error">ERRO</div>
                        </div>
                        <p>Não foi possível conectar ao servidor. Verifique sua conexão com a internet.</p>
                    </div>
                `;
            };
            
            xhr.send('mac=' + encodeURIComponent(mac));
        }
        
        // Exibe os resultados na tela
        function displayResults(data) {
            const resultsSection = document.getElementById('resultsSection');
            
            if (data.status === 'sucesso') {
                const dispositivo = data.dados_dispositivo;
                resultsSection.innerHTML = `
                    <div class="results-card">
                        <div class="result-header">
                            <h2><i class="fas fa-desktop"></i> Dispositivo Encontrado</h2>
                            <div class="status-badge status-success">ENCONTRADO</div>
                        </div>
                        
                        <div class="device-info">
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="fas fa-fingerprint"></i> MAC Address:
                                </div>
                                <div class="info-value">${data.mac_address}</div>
                            </div>
                            
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="fas fa-user"></i> Usuário:
                                </div>
                                <div class="info-value">${dispositivo.username}</div>
                            </div>
                            
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="fas fa-key"></i> Senha:
                                </div>
                                <div class="info-value">${dispositivo.password}</div>
                            </div>
                        </div>
                    </div>
                `;
            } else if (data.status === 'nao_encontrado') {
                let sugestoesHTML = '';
                if (data.sugestoes && data.sugestoes.length > 0) {
                    sugestoesHTML = `
                        <div class="info-box" style="margin-top: 20px;">
                            <h3><i class="fas fa-lightbulb"></i> Sugestões:</h3>
                            <div class="examples">
                                ${data.sugestoes.map(mac => 
                                    `<div class="example-chip" onclick="setExample('${mac}')">${mac}</div>`
                                ).join('')}
                            </div>
                        </div>
                    `;
                }
                
                resultsSection.innerHTML = `
                    <div class="results-card">
                        <div class="result-header">
                            <h2><i class="fas fa-search-minus"></i> Dispositivo Não Encontrado</h2>
                            <div class="status-badge status-error">NÃO ENCONTRADO</div>
                        </div>
                        <p>Nenhum dispositivo encontrado com o MAC: <strong>${data.mac_buscado}</strong></p>
                        ${sugestoesHTML}
                    </div>
                `;
            } else if (data.status === 'erro') {
                resultsSection.innerHTML = `
                    <div class="results-card">
                        <div class="result-header">
                            <h2><i class="fas fa-exclamation-triangle"></i> Erro na Busca</h2>
                            <div class="status-badge status-error">ERRO</div>
                        </div>
                        <p><strong>${data.mensagem}</strong></p>
                        <p>MAC informado: ${data.mac_recebido || 'N/A'}</p>
                        ${data.erro_tecnico ? `<p class="error-detail">Detalhes: ${data.erro_tecnico}</p>` : ''}
                    </div>
                `;
            } else {
                resultsSection.innerHTML = `
                    <div class="results-card">
                        <div class="result-header">
                            <h2><i class="fas fa-info-circle"></i> Informações do Sistema</h2>
                            <div class="status-badge status-info">INFO</div>
                        </div>
                        <p><strong>${data.mensagem}</strong></p>
                        <div class="info-box" style="margin-top: 20px;">
                            <h3><i class="fas fa-question-circle"></i> Instruções:</h3>
                            <p>Para buscar um dispositivo, use um dos seguintes formatos:</p>
                            <div class="examples">
                                <div class="example-chip" onclick="setExample('E640E789DF3B')">E640E789DF3B</div>
                                <div class="example-chip" onclick="setExample('E6:40:E7:89:DF:3B')">E6:40:E7:89:DF:3B</div>
                            </div>
                        </div>
                    </div>
                `;
            }
        }
    </script>
</body>
</html>
<?php
}
?>