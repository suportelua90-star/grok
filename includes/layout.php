<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
$static_url = dirname($_SERVER['SCRIPT_NAME']) . '/assets/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title><?php echo isset($page_title) ? $page_title : "IBO Panel PWRebrands"; ?></title>
<meta charset="utf-8" />
<meta name="description" content="İş Güvenlik Kontrol Sistemi" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link rel="shortcut icon" href="<?php echo $static_url; ?>media/logos/logo.png" />
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
<link href="<?php echo $static_url; ?>plugins/custom/fullcalendar/fullcalendar.bundle.css" rel="stylesheet" />
<link href="<?php echo $static_url; ?>plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" />
<link href="<?php echo $static_url; ?>plugins/global/plugins.bundle.css" rel="stylesheet" />
<link href="<?php echo $static_url; ?>css/style.bundle.css" rel="stylesheet" />
<!-- CORREÇÃO DO SIDEBAR: Força tema light no sidebar -->
<style>
/* ====== CORREÇÃO DEFINITIVA DO SIDEBAR ====== */
/* Remove todos os estilos indesejados */
html[data-bs-theme="light"] .app-sidebar,
html[data-bs-theme="dark"] .app-sidebar {
    background-color: #ffffff !important;
    border-right: 1px solid #e5e7eb !important;
}

/* Correção para o light mode */
html[data-bs-theme="light"] .app-sidebar {
    background-color: #ffffff !important;
    border-right: 1px solid #e5e7eb !important;
}

html[data-bs-theme="light"] .app-sidebar .menu-item {
    color: #4b5563 !important;
    padding: 0.75rem 1rem !important;
}

html[data-bs-theme="light"] .app-sidebar .menu-item:hover:not(.menu-item-active) {
    background-color: #f3f4f6 !important;
    color: #1f2937 !important;
}

/* CORREÇÃO CRÍTICA: Texto legível no item selecionado */
html[data-bs-theme="light"] .app-sidebar .menu-item-active {
    background-color: #e0f2fe !important;
    color: #0d9488 !important;
    border-left: 3px solid #0d9488 !important;
    font-weight: 600 !important;
}

html[data-bs-theme="light"] .app-sidebar .menu-icon {
    color: #6b7280 !important;
}

html[data-bs-theme="light"] .app-sidebar .menu-icon:hover {
    color: #1f2937 !important;
}

/* Correção para o dark mode */
html[data-bs-theme="dark"] .app-sidebar {
    background-color: #111827 !important;
    border-right: 1px solid #2a3042 !important;
}

html[data-bs-theme="dark"] .app-sidebar .menu-item {
    color: #cbd5db !important;
    padding: 0.75rem 1 !important;
}

html[data-bs-theme="dark"] .app-sidebar .menu-item:hover:not(.menu-item-active) {
    background-color: #1f2937 !important;
    color: #ffffff !important;
}

/* CORREÇÃO CRÍTICA: Texto legível no dark mode */
html[data-bs-theme="dark"] .app-sidebar .menu-item-active {
    background-color: #1e2435 !important;
    color: #00d9ff !important;
    border-left: 3px solid #00d9ff !important;
    font-weight: 600 !important;
}

html[data-bs-theme="dark"] .app-sidebar .menu-icon {
    color: #6b7280 !important;
}

html[data-bs-theme="dark"] .app-sidebar .menu-icon:hover {
    color: #ffffff !important;
}

/* Correção para o tema do sidebar */
body[data-kt-app-sidebar-bg="light"] .app-sidebar {
    background-color: #ffffff !important;
    border-right: 1px solid #e5e7eb !important;
}

body[data-kt-app-sidebar-bg="dark"] .app-sidebar {
    background-color: #111827 !important;
    border-right: 1px solid #2a3042 !important;
}

/* Correção para o item ativo no dark mode */
body[data-kt-app-sidebar-bg="dark"] .app-sidebar .menu-item-active {
    background-color: #1e2435 !important;
    color: #00d9ff !important;
    border-left: 3px solid #00d9ff !important;
}
</style>
</head>
<body id="kt_app_body" 
      data-kt-app-sidebar-enabled="true" 
      data-kt-app-sidebar-fixed="true"
      data-kt-app-sidebar-push-header="true" 
      data-kt-app-sidebar-push-toolbar="true"
      data-kt-app-sidebar-push-footer="true" 
      data-kt-app-sidebar-bg="light" <!-- CORREÇÃO AQUI -->
      class="app-default">
      
<script>
// CORREÇÃO: Sincroniza o tema do sidebar com o tema global
var defaultThemeMode = "light"; 
var themeMode; 

if (document.documentElement) { 
    if (document.documentElement.hasAttribute("data-bs-theme-mode")) { 
        themeMode = document.documentElement.getAttribute("data-bs-theme-mode"); 
    } else { 
        if (localStorage.getItem("data-bs-theme") !== null) { 
            themeMode = localStorage.getItem("data-bs-theme"); 
        } else { 
            themeMode = defaultThemeMode; 
        } 
    } 
    
    if (themeMode === "system") { 
        themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light"; 
    } 
    
    document.documentElement.setAttribute("data-bs-theme", themeMode);
    
    // CORREÇÃO CRÍTICA: Define o atributo do sidebar dinamicamente
    if (themeMode === 'light') {
        document.body.setAttribute('data-kt-app-sidebar-bg', 'light');
    } else {
        document.body.setAttribute('data-kt-app-sidebar-bg', 'dark');
    }
}

// Observador para mudanças de tema
const themeObserver = new MutationObserver((mutations) => {
    mutations.forEach((mutation) => {
        if (mutation.attributeName === 'data-bs-theme') {
            const currentTheme = document.documentElement.getAttribute('data-bs-theme');
            document.body.setAttribute(
                'data-kt-app-sidebar-bg', 
                currentTheme === 'light' ? 'light' : 'dark'
            );
        }
    });
});

themeObserver.observe(document.documentElement, {
    attributes: true,
    attributeFilter: ['data-bs-theme']
});
</script>

<div class="d-flex flex-column flex-root app-root" id="kt_app_root">
<div class="app-page flex-column flex-column-fluid" id="kt_app_page">
<?php include 'includes/header.php'; ?>
<div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">
<?php include 'includes/sidebar.php'; ?>
<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
<div class="d-flex flex-column flex-column-fluid">
<div id="kt_app_content" class="app-content flex-column-fluid">
<div id="kt_app_content_container" class="app-container container-fluid">
<?php
echo isset($page_content) ? $page_content : "<h1>İçerik bulunamadı</h1>";
?>
</div>
</div>
</div>
<div id="kt_app_footer" class="app-footer">
<div class="app-container container-fluid d-flex flex-column flex-md-row flex-center flex-md-stack py-3">
<div class="text-gray-900 order-2 order-md-1">
<span class="text-muted fw-semibold me-1">2025&copy;</span>
<a href="https://t.me/Rain_Bow65" target="_blank" class="text-gray-800 text-hover-primary">PWRebrands</a>
</div>
<ul class="menu menu-gray-600 menu-hover-primary fw-semibold order-1">
<a href='https://ko-fi.com/P5P51B6AZT' target='_blank'><img height='36' style='border:0px;height:36px;' src='https://storage.ko-fi.com/cdn/kofi5.png?v=6' border='0' alt='Buy Me a Coffee at ko-fi.com' /></a>
</ul>
</div>
</div>
</div>
</div>
</div>
</div>
<div id="kt_scrolltop" class="scrolltop" data-kt-scrolltop="true">
<span class="svg-icon">
<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<rect opacity="0.5" x="13" y="6" width="13" height="2" rx="1" transform="rotate(90 13 6)" fill="currentColor"></rect>
<path d="M12.5657 8.56569L16.75 12.75C17.1642 13.1642 17.8358 13.1642 18.25 12.75C18.6642 12.3358 18.6642 11.6642 18.25 11.25L12.7071 5.70711C12.3166 5.31658 11.6834 5.31658 11.2929 5.70711L5.75 11.25C5.33579 11.6642 5.33579 12.3358 5.75 12.75C6.16421 13.1642 6.83579 13.1642 7.25 12.75L11.4343 8.56569C11.7467 8.25327 12.2533 8.25327 12.5657 8.56569Z" fill="currentColor"></path>
</svg>
</span>
</div>
<script src="<?php echo $static_url; ?>plugins/global/plugins.bundle.js"></script>
<script src="<?php echo $static_url; ?>js/scripts.bundle.js"></script>
<script src="<?php echo $static_url; ?>plugins/custom/datatables/datatables.bundle.js"></script>
<script src="<?php echo $static_url; ?>js/widgets.js"></script>
</body>
</html>