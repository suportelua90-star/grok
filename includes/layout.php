<?php
// Evita erros de session duplicada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Static URL ajustado para evitar problemas de subdiretório
$static_url = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/assets/';
?>

<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark"> <!-- Tema default dark para painel IPTV -->
<head>
    <meta charset="utf-8" />
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : "PW REBRANDS – Plataforma Profissional IPTV"; ?></title>
    <meta name="description" content="PW REBRANDS – Plataforma Profissional IPTV" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="shortcut icon" href="<?php echo $static_url; ?>media/logos/favicon.ico" />

    <!-- Google Fonts (Inter) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Plugins (mantenha locais se preferir, mas CDN como fallback) -->
    <link href="<?php echo $static_url; ?>plugins/global/plugins.bundle.css" rel="stylesheet" />
    <link href="<?php echo $static_url; ?>plugins/custom/fullcalendar/fullcalendar.bundle.css" rel="stylesheet" />
    <link href="<?php echo $static_url; ?>plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" />
    <link href="<?php echo $static_url; ?>css/style.bundle.css" rel="stylesheet" />
    <link href="<?php echo $static_url; ?>css/theme-colors.css" rel="stylesheet" />
    <link href="<?php echo $static_url; ?>css/iptv-theme.css" rel="stylesheet" />

    <!-- Tema dark/light dinâmico -->
    <script>
        const defaultThemeMode = "dark"; // Painel admin fica melhor em dark
        let themeMode = localStorage.getItem("theme-mode") || defaultThemeMode;
        if (themeMode === "system") {
            themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
        }
        document.documentElement.setAttribute("data-bs-theme", themeMode);
        document.documentElement.classList.add("theme-" + themeMode);
    </script>

    <!-- Tema de cor (paletas) -->
    <script>
        (function () {
            const DEFAULT_COLOR_THEME = "pw-rebrands";
            const saved = localStorage.getItem("theme-color") || DEFAULT_COLOR_THEME;
            document.documentElement.setAttribute("data-theme-color", saved);
        })();
    </script>
</head>

<body id="kt_app_body" class="app-default" data-kt-app-sidebar-enabled="true" data-kt-app-sidebar-fixed="true"
      data-kt-app-sidebar-push-header="true" data-kt-app-sidebar-push-toolbar="true"
      data-kt-app-sidebar-push-footer="true">

    <div class="d-flex flex-column flex-root app-root" id="kt_app_root">
        <div class="app-page flex-column flex-column-fluid" id="kt_app_page">

            <!-- Header -->
            <?php include 'header.php'; ?>

            <div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">

                <!-- Sidebar -->
                <?php include 'sidebar.php'; ?>

                <!-- Conteúdo Principal -->
                <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
                    <div class="d-flex flex-column flex-column-fluid">
                        <div id="kt_app_content" class="app-content flex-column-fluid">
                            <div id="kt_app_content_container" class="app-container container-fluid">
                                <?php
                                // Conteúdo da página atual
                                echo isset($page_content) ? $page_content : '<div class="alert alert-warning text-center py-5">
                                    <h3>Conteúdo não encontrado</h3>
                                    <p>Verifique se a variável $page_content foi definida corretamente.</p>
                                </div>';
                                ?>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div id="kt_app_footer" class="app-footer bg-dark-subtle">
                        <div class="app-container container-fluid d-flex flex-column flex-md-row flex-center flex-md-stack py-4">
                            <!-- Copyright -->
                            <div class="text-gray-700 order-2 order-md-1">
                                <span class="fw-semibold me-1">© 2024 - 2026</span>
                                <a href="#" class="text-primary text-hover-light fw-bold">PW REBRANDS</a>
                            </div>

                            <!-- Links / Doações -->
                            <!-- Links removidos (white-label) -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scroll to Top -->
    <div id="kt_scrolltop" class="scrolltop" data-kt-scrolltop="true">
        <span class="svg-icon">
            <i class="bi bi-arrow-up fs-2"></i>
        </span>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <!-- Seus scripts locais (mantidos) -->
    <script src="<?php echo $static_url; ?>plugins/global/plugins.bundle.js"></script>
    <script src="<?php echo $static_url; ?>js/scripts.bundle.js"></script>
    <script src="<?php echo $static_url; ?>plugins/custom/datatables/datatables.bundle.js"></script>
    <script src="<?php echo $static_url; ?>js/widgets.js"></script>

    <!-- Script para toggle tema (light/dark) -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const toggle = document.getElementById("kt_sidebar_theme_mode_toggle");
            if (toggle) {
                toggle.addEventListener("change", function () {
                    const newMode = this.checked ? "dark" : "light";
                    document.documentElement.setAttribute("data-bs-theme", newMode);
                    localStorage.setItem("theme-mode", newMode);
                });
            }

            // Paletas de cor
            document.querySelectorAll('[data-set-theme-color]').forEach((el) => {
                el.addEventListener('click', (e) => {
                    e.preventDefault();
                    const theme = el.getAttribute('data-set-theme-color');
                    if (!theme) return;
                    document.documentElement.setAttribute('data-theme-color', theme);
                    localStorage.setItem('theme-color', theme);

                    // marcar ativo
                    document.querySelectorAll('[data-set-theme-color] .theme-color-check').forEach((c) => c.classList.add('d-none'));
                    const check = el.querySelector('.theme-color-check');
                    if (check) check.classList.remove('d-none');
                });
            });

            // definir check inicial
            const current = document.documentElement.getAttribute('data-theme-color') || localStorage.getItem('theme-color');
            if (current) {
                const active = document.querySelector(`[data-set-theme-color="${current}"] .theme-color-check`);
                if (active) active.classList.remove('d-none');
            }
        });
    </script>
</body>
</html>