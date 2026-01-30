<?php
// Especificar o URL do site que você deseja exibir no WebView
$websiteUrl = 'https://xsender.painelmaster.lol/card3.php';

// Gerar a página HTML com o WebView e o script de scroll
$htmlContent = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WebView</title>
    <style>
        body {
            margin: 0;
            overflow: hidden;
        }
        iframe {
            width: 100%;
            height: 100vh; /* Defina uma altura fixa, ajuste conforme necessário */
            border: none;
        }
    </style>
</head>
<body>
    <iframe id="webview" src="$websiteUrl" frameborder="0" allowfullscreen></iframe>
    <script>
        // Adicionar um listener para aguardar o carregamento completo da página
        document.getElementById('webview').onload = function() {
            // Definir um intervalo para rolar para cima a cada 3 segundos (3000 milissegundos)
            setInterval(function() {
                window.scrollBy(0, -10); // Modifique o valor -10 conforme necessário para ajustar a velocidade do scroll
            }, 3000);
        };
    </script>
</body>
</html>
HTML;

// Definir o tipo de conteúdo como HTML
header("Content-Type: text/html");

// Saída do conteúdo HTML
echo $htmlContent;
?>
