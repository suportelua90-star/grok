<?php

error_reporting(E_ALL);

ini_set('display_errors', 1);

ini_set('display_startup_errors', 1);

$static_url = dirname($_SERVER['SCRIPT_NAME']) . '/assets/';

$current_page = basename($_SERVER['PHP_SELF']);

$accordion_active = ($current_page == 'themes.php') ? 'show' : '';

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
                            <span class="menu-heading">📊 Dashboard</span>
                        </div>
                    </div>
                    <div class="separator mb-4 mx-4"></div>
                    <div class="menu-item">
                        <a class="menu-link <?php echo ($current_page == 'dns.php') ? 'active' : ''; ?>" href="dns.php">
                            <span class="menu-title">🌐 Adicionar DNS</span>
                        </a>
                    </div>
                    <div class="menu-item">
                        <a class="menu-link <?php echo ($current_page == 'mac.php') ? 'active' : ''; ?>" href="mac.php">
                            <span class="menu-title">👥 Usuarios Ativo</span>
                        </a>
                    </div>
                     <div class="menu-item">
                        <a class="menu-link <?php echo ($current_page == 'ads.php') ? 'active' : ''; ?>" href="ads.php">
                            <span class="menu-title">📤 Anuncio Banner</span>
                        </a>
                    </div>
                        <div class="menu-item">
                        <a class="menu-link <?php echo ($current_page == 'modobanner.php') ? 'active' : ''; ?>" href="modobanner.php">
                            <span class="menu-title">⚙️ Modelo de Banner</span>
                        </a>
                    </div>
                    <div class="menu-item">
                        <a class="menu-link <?php echo ($current_page == 'ads.php') ? 'active' : ''; ?>" href="teste.php">
                            <span class="menu-title">📤 Teste Automatico</span>
                        </a>
                    </div>
                    <div class="menu-item">
                        <a class="menu-link <?php echo ($current_page == 'note.php') ? 'active' : ''; ?>" href="note.php">
                            <span class="menu-title">📢 Menssagem Serv</span>
                        </a>
                    </div>
                    <div data-kt-menu-trigger="click" class="menu-item <?php echo $accordion_active ? 'here ' . $accordion_active : ''; ?> menu-accordion">
                        <span class="menu-link">
                            <span class="menu-title">🎨 Pesonalização</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <div class="menu-sub menu-sub-accordion">
                            <div class="menu-item">
                                <a class="menu-link <?php echo ($current_page == 'themes.php') ? 'active' : ''; ?>" href="themes.php">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">🎭 Temas Aplicativo</span>
                                </a>
                            </div>
                            <div class="menu-item">
                                <a class="menu-link <?php echo ($current_page == 'logo.php') ? 'active' : ''; ?>" href="logo.php">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">🖼️ Sua Logotipo</span>
                                </a>
                            </div>
                            
                             <div class="menu-item">
                                <a class="menu-link <?php echo ($current_page == 'bg.php') ? 'active' : ''; ?>" href="bg.php">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">🖼️ Backgroud App</span>
                                </a>
                            </div>
                            
                            <div class="menu-item">
                                <a class="menu-link <?php echo ($current_page == 'ajustescores.php') ? 'active' : ''; ?>" href="ajustescores.php">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">🎨 Cores Botões</span>
                                </a>
                            </div>
                        </div>
                        
                        
                        <div class="menu-item">
                            <a class="menu-link <?php echo ($current_page == 'qrcode.php') ? 'active' : ''; ?>" href="qrcode.php">
                                <span class="menu-title">🔳 Enviar QR Code</span>
                            </a>
                        </div>
                        
                         <div class="menu-item">
                            <a class="menu-link <?php echo ($current_page == 'manutencao.php') ? 'active' : ''; ?>" href="manutencao.php">
                                <span class="menu-title"> 🛠️ Manutenção Servidor</span>
                            </a>
                        </div>
                        
                         <div class="menu-item">
                            <a class="menu-link <?php echo ($current_page == 'alerta_vencimento.php') ? 'active' : ''; ?>" href="alerta_vencimento.php">
                                <span class="menu-title"> 🔔 Alerta Vencimento</span>
                            </a>
                        </div>

                        <div class="menu-item">
                            <a class="menu-link <?php echo ($current_page == 'users.php') ? 'active' : ''; ?>" href="users.php">
                                <span class="menu-title">👤 Meu Perfil</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex flex-stack px-10 px-lg-15 pb-8" id="kt_app_sidebar_footer">
        <span class="d-flex flex-center gap-1 text-white theme-light-show fs-5 px-0">
            <i class="ki-outline ki-night-day text-gray-500 fs-2"></i>🌙 Dark Mode</span>
        <span class="d-flex flex-center gap-1 text-white theme-dark-show fs-5 px-0">
            <i class="ki-outline ki-moon text-gray-500 fs-2"></i>☀️ Light Mode</span>
        <div data-bs-theme="dark">
            <div class="form-check form-switch form-check-custom form-check-solid">
                <input class="form-check-input h-25px w-45px" type="checkbox" value="1"
                    id="kt_sidebar_theme_mode_toggle" />
            </div>
        </div>
    </div>
</div>