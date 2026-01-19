<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
$static_url = dirname($_SERVER['SCRIPT_NAME']) . '/assets/';
$current_page = basename($_SERVER['PHP_SELF']);
$accordion_active = ($current_page == 'themes.php' || $current_page == 'banner.php' || $current_page == 'banner_type.php' || $current_page == 'logo_and_background.php') ? 'show' : '';
?>

<div id="kt_app_sidebar" class="app-sidebar" data-kt-drawer="true" data-kt-drawer-name="app-sidebar"
    data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="250px"
    data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_app_sidebar_toggle">
    <div class="d-none d-lg-flex flex-center px-6 pt-10 pb-10" id="kt_app_sidebar_header">
        <a href="dns.php">
            <img alt="Logo" src="<?php echo $static_url; ?>media/logos/logo.png" class=" h-50px" />
        </a>
    </div>
    <div class="flex-grow-1">
        <div id="kt_app_sidebar_menu_wrapper" class="hover-scroll-y" data-kt-scroll="true" data-kt-scroll-height="auto"
            data-kt-scroll-dependencies="#kt_app_sidebar_header, #kt_app_sidebar_footer" data-kt-scroll-offset="20px">
            <div class="app-sidebar-navs-default px-5 mb-10">
                <div id="#kt_app_sidebar_menu" data-kt-menu="true" data-kt-menu-expand="false"
                    class="menu menu-column menu-rounded menu-sub-indention">
                    <div class="menu-item pb-0 pt-0">
                        <div class="menu-content">
                            <span class="menu-heading">PW REBRANDS</span>
                            <div class="text-white-50 fs-8 mt-1">Plataforma Profissional IPTV</div>
                        </div>
                    </div>
                    <div class="separator mb-4 mx-4"></div>
                    <div class="menu-item">
                        <a class="menu-link <?php echo ($current_page == 'dns.php') ? 'active' : ''; ?>" href="dns.php">
                            <span class="menu-title">Servidores/DNS</span>
                        </a>
                    </div>
                    <div class="menu-item">
                        <a class="menu-link <?php echo ($current_page == 'mac.php') ? 'active' : ''; ?>" href="mac.php">
                            <span class="menu-title">Usuários MAC</span>
                        </a>
                    </div>
                    <div class="menu-item">
                        <a class="menu-link <?php echo ($current_page == 'mac_message.php') ? 'active' : ''; ?>" href="mac_message.php">
                            <span class="menu-title">Mensagem MAC</span>
                        </a>
                    </div>
                    <div class="menu-item">
                        <a class="menu-link <?php echo ($current_page == 'note.php') ? 'active' : ''; ?>" href="note.php">
                            <span class="menu-title">Notas</span>
                        </a>
                    </div>
                    <div data-kt-menu-trigger="click" class="menu-item <?php echo $accordion_active ? 'here ' . $accordion_active : ''; ?> menu-accordion">
                        <span class="menu-link">
                            <span class="menu-title">Configurações</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <div class="menu-sub menu-sub-accordion">
                            <div class="menu-item">
                                <a class="menu-link <?php echo ($current_page == 'themes.php') ? 'active' : ''; ?>" href="themes.php">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">Temas</span>
                                </a>
                            </div>
                            <div class="menu-item">
                                <a class="menu-link <?php echo ($current_page == 'banner.php') ? 'active' : ''; ?>" href="banner.php">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">Banners</span>
                                </a>
                            </div>
                            <div class="menu-item">
                                <a class="menu-link <?php echo ($current_page == 'banner_type.php') ? 'active' : ''; ?>" href="banner_type.php">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">Tipos de Banner</span>
                                </a>
                            </div>
                            <div class="menu-item">
                                <a class="menu-link <?php echo ($current_page == 'logo_and_background.php') ? 'active' : ''; ?>" href="logo_and_background.php">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">Logo e Fundo</span>
                                </a>
                            </div>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link <?php echo ($current_page == 'messages.php') ? 'active' : ''; ?>" href="messages.php">
                                <span class="menu-title">Mensagens</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link <?php echo ($current_page == 'web_url.php') ? 'active' : ''; ?>" href="web_url.php">
                                <span class="menu-title">Página Web</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link <?php echo ($current_page == 'qr.php') ? 'active' : ''; ?>" href="qr.php">
                                <span class="menu-title">Qr Code</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link <?php echo ($current_page == 'qrlogin.php') ? 'active' : ''; ?>" href="qrlogin.php">
                                <span class="menu-title">Login QR</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link <?php echo ($current_page == 'users.php') ? 'active' : ''; ?>" href="users.php">
                                <span class="menu-title">Usuários</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="d-flex flex-stack px-10 px-lg-15 pb-8" id="kt_app_sidebar_footer">
        <span class="d-flex flex-center gap-1 text-white theme-light-show fs-5 px-0">
            <i class="ki-outline ki-night-day text-gray-500 fs-2"></i>Modo Escuro</span>
        <span class="d-flex flex-center gap-1 text-white theme-dark-show fs-5 px-0">
            <i class="ki-outline ki-moon text-gray-500 fs-2"></i>Modo Claro</span>
        <div data-bs-theme="dark">
            <div class="form-check form-switch form-check-custom form-check-solid">
                <input class="form-check-input h-25px w-45px" type="checkbox" value="1"
                    id="kt_sidebar_theme_mode_toggle" />
            </div>
        </div>
    </div>
</div>