<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QrCode Banners</title>
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
        }

        .banner-slide {
            width: 100%;
            height: 100%;
            position: absolute;
            top: 0;
            left: 0;
            opacity: 0;
            transition: opacity 1s ease-in-out;
        }

        .banner-slide.active {
            opacity: 1;
        }
    </style>
</head>
<body>
    <div class="banner-carousel">
    <?php
        $directory = __DIR__ . '/../assets/media/qrcode/';
        
        if (is_dir($directory) && is_readable($directory)) {
            $banner_files = glob($directory . "*.{jpg,jpeg,png,gif}", GLOB_BRACE);
        
            foreach ($banner_files as $index => $file) {
                // Fiziksel yolu web yoluna dönüştür
                $web_path = str_replace($_SERVER['DOCUMENT_ROOT'], '', $file);
                $class = $index === 0 ? 'banner-slide active' : 'banner-slide';
                echo "<img class='$class' src='$web_path' alt='" . pathinfo($file, PATHINFO_FILENAME) . "'>";
            }
        } else {
            echo "<p>The installation directory does not exist or is not accessible.</p>";
        }
    ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const slides = document.querySelectorAll('.banner-slide');
            let currentSlide = 0;

            function showSlide(index) {
                slides.forEach(slide => slide.classList.remove('active'));
                slides[index].classList.add('active');
            }

            function nextSlide() {
                const previousSlide = currentSlide;
                currentSlide = (currentSlide + 1) % slides.length;
                slides[previousSlide].style.opacity = 0;
                slides[currentSlide].style.opacity = 1;
            }

            setInterval(nextSlide, 5000);
        });
    </script>
</body>
</html>
