<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Message</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: transparent;
        }
        .mensagem {
            position: fixed;
            top: 17%;
            right: -100%;
            white-space: nowrap;
            z-index: 1000;
            font-size: 20px;
            line-height: 1.6;
            color: red;
        }
    </style>
</head>
<body>
    <div class="mensagem" id="mensagem"></div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            function exibirMensagem(mensagem, cor) {
                var mensagemDiv = $('#mensagem');
                mensagemDiv.html('<strong style="color: ' + cor + '">' + mensagem + '</strong>');

                function animaMensagem() {
                    mensagemDiv.css('right', '-100%');
                    mensagemDiv.animate({ right: '100%' }, 30000, 'linear', function() {
                        animaMensagem();
                    });
                }

                animaMensagem();
            }
            
        <?php
        $file_path = __DIR__ . "/ad_descriptions.txt";
        
        if (file_exists($file_path) && is_readable($file_path)) {
            $content = file_get_contents($file_path);
            $lines = explode("\n", $content);
        
            if (count($lines) >= 2) {
                $mensagem = trim($lines[0]);
                $cor = trim($lines[1]) ?: "red";
                echo 'exibirMensagem("' . addslashes($mensagem) . '", "' . addslashes($cor) . '");';
            } else {
                echo 'console.error("The description file does not contain enough information.");';
            }
        } else {
            echo 'console.error("The description file is not accessible or does not exist.");';
        }
        ?>
        });
    </script>
</body>
</html>
