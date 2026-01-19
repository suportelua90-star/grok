<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Balloon page</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body, html {
            height: 100%;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            background: transparent;
        }
        .baloon-container {
            width: 100%;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            overflow: hidden;
        }
        .baloon {
            width: 100%;
            max-width: 90%;
            padding: 20px;
            background: linear-gradient(135deg, var(--color1), var(--color2), var(--color3));
            border-radius: 20px;
            box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.3);
            font-size: 16px;
            color: white;
            text-align: center;
            word-wrap: break-word;
        }
        .hidden-message {
            visibility: hidden;
        }
    </style>
</head>
<body>
    <div class="baloon-container">
        <div class="baloon" id="baloon">
            <span class="hidden-message" id="hidden-message"></span>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            function exibirMensagem(mensagem, cor1, cor2, cor3) {
                var baloonDiv = $('#baloon');
                var hiddenMessageDiv = $('#hidden-message');
                document.documentElement.style.setProperty('--color1', cor1);
                document.documentElement.style.setProperty('--color2', cor2);
                document.documentElement.style.setProperty('--color3', cor3);
                hiddenMessageDiv.html('<strong>' + mensagem + '</strong>');
            }
            
            <?php
                $file_path = "cores.txt";
                $content = file_get_contents($file_path);
                $lines = explode("\n", $content);
                
                if(count($lines) >= 4) {
                    $mensagem = trim($lines[0]);
                    $cor1 = trim($lines[1]);
                    $cor2 = trim($lines[2]);
                    $cor3 = trim($lines[3]);
                    
                    echo 'exibirMensagem("' . $mensagem . '", "' . $cor1 . '", "' . $cor2 . '", "' . $cor3 . '");';
                } else {
                    echo 'console.error("The description file does not contain enough information.");';
                }
            ?>
        });
    </script>
</body>
</html>
