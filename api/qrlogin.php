<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QrLogin Banners</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            overflow: hidden;
        }

        .banner-carousel {
            width: 100%;
            height: 100%;
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .banner-slide {
            width: 100%;
            height: 100%;
            position: absolute;
            top: 0;
            left: 0;
            opacity: 0;
            display: flex;
            transition: opacity 1s ease-in-out;
            align-items: center;
        }

        .banner-slide.active {
            opacity: 1;
        }

        .banner-image {
            flex: 1;
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .banner-text {
            flex: 1;
            padding: 20px;
            font-size: 1.5rem;
            color: white;
            height: auto;
            box-sizing: border-box;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="banner-carousel">
    <?php
        $directory = __DIR__ . '/../assets/media/qrlogin/';
        
        if (is_dir($directory) && is_readable($directory)) {
            $banner_files = glob($directory . "*.{jpg,jpeg,png,gif}", GLOB_BRACE);
        
            foreach ($banner_files as $index => $file) {
                $web_path = str_replace($_SERVER['DOCUMENT_ROOT'], '', $file);
                
                $class = $index === 0 ? 'banner-slide active' : 'banner-slide';
                $text_file = $directory . pathinfo($file, PATHINFO_FILENAME) . ".txt";
        
                echo "<div class='$class'>";
                echo "<img class='banner-image' src='$web_path' alt='" . pathinfo($file, PATHINFO_FILENAME) . "'>";
        
                if (file_exists($text_file)) {
                    $banner_text = file_get_contents($text_file);
                    echo "<div class='banner-text'>" . htmlspecialchars($banner_text) . "</div>";
                } else {
                    echo "<div class='banner-text'>Sem texto</div>";
                }
        
                echo "</div>";
            }
        } else {
            echo "<p>The installation directory does not exist or is not accessible.</p>";
        }
    ?>
    </div>

    <script>
        let currentSlide = 0;
        const slides = document.querySelectorAll('.banner-slide');
        const totalSlides = slides.length;

        function showNextSlide() {
            slides[currentSlide].classList.remove('active');
            currentSlide = (currentSlide + 1) % totalSlides;
            slides[currentSlide].classList.add('active');
        }

        setInterval(showNextSlide, 5000); // Mudar slide a cada 5 segundos
    </script>
</body>
</html>
