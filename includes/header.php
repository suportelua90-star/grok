<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
$static_url = dirname($_SERVER['SCRIPT_NAME']) . '/assets/';

$username = isset($_SESSION['username']) ? $_SESSION['username'] : "Guest";
$isAdmin = isset($_SESSION['admin']) && $_SESSION['admin'] ? "Admin" : (isset($_SESSION['dealer']) && $_SESSION['dealer'] ? "Dealer" : "User");
$balance = isset($_SESSION['balance']) ? $_SESSION['balance'] : 0;
?>
<div id="kt_app_header" class="app-header" data-kt-sticky="true" data-kt-sticky-activate="{default: true, lg: true}"
    data-kt-sticky-name="app-header-sticky" data-kt-sticky-offset="{default: '200px', lg: '300px'}">
    <div class="app-container container-fluid d-flex flex-stack" id="kt_app_header_container">
        <div class="d-flex d-lg-none align-items-center me-lg-20 gap-1 gap-lg-2">
            <div class="btn btn-icon btn-color-gray-500 btn-active-color-primary w-35px h-35px d-flex d-lg-none"
                id="kt_app_sidebar_toggle">
                <i class="ki-outline ki-abstract-14 lh-0 fs-1"></i>
            </div>
            <a href="dns.php">
                <img alt="Logo" src="<?php echo $static_url; ?>media/logos/logo.png" class="h-30px" />
            </a>
        </div>
        <div class="d-flex flex-stack flex-lg-row-fluid" id="kt_app_header_wrapper">
            <div class="app-page-title d-flex flex-column gap-1 me-3 mb-5 mb-lg-0" data-kt-swapper="true"
                data-kt-swapper-mode="{default: 'prepend', lg: 'prepend'}"
                data-kt-swapper-parent="{default: '#kt_app_content_container', lg: '#kt_app_header_wrapper'}">
            </div>
            <div class="app-navbar flex-shrink-0 gap-2 gap-lg-4">
                <div class="app-navbar-item ms-lg-5" id="kt_header_user_menu_toggle">
                    <div class="d-flex align-items-center" data-kt-menu-trigger="{default: 'click', lg: 'hover'}"
                        data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end">
                        <div class="text-end d-none d-sm-flex flex-column justify-content-center me-3">
                            <span class="text-gray-500 fs-8 fw-bold">Olá</span>
                            <a href="pages/user-profile/overview.html"
                                class="text-gray-800 text-hover-primary fs-7 fw-bold d-block"><?php echo htmlspecialchars($username); ?></a>
                        </div>
                        <div class="cursor-pointer symbol symbol symbol-circle symbol-35px symbol-md-40px">
                            <img class="" src="<?php echo $static_url; ?>media/logos/logo.png" alt="user" />
                            <div
                                class="position-absolute translate-middle bottom-0 mb-1 start-100 ms-n1 bg-success rounded-circle h-8px w-8px">
                            </div>
                        </div>
                    </div>
                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-color fw-semibold py-4 fs-6 w-275px"
                        data-kt-menu="true">
                        <div class="menu-item px-3">
                            <div class="menu-content d-flex align-items-center px-3">
                                <div class="symbol symbol-50px me-5">
                                    <img alt="Logo" src="<?php echo $static_url; ?>media/logos/logo.png" />
                                </div>
                                <div class="d-flex flex-column">
                                    <div class="fw-bold d-flex align-items-center fs-5"><?php echo htmlspecialchars($username); ?>
                                        <span class="badge badge-light-success fw-bold fs-8 px-2 py-1 ms-2"><?php echo htmlspecialchars($isAdmin); ?></span>
                                    </div>
                                    <span class="fw-semibold text-muted text-hover-primary fs-7">
                                        <?php echo $balance > 0 ? "Credit: " . htmlspecialchars($balance) : ""; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="separator my-2"></div>
                        <div class="menu-item px-5" data-kt-menu-trigger="{default: 'click', lg: 'hover'}"
                            data-kt-menu-placement="left-start" data-kt-menu-offset="-15px, 0">
                            <a href="#" class="menu-link px-5">
                                <span class="menu-title position-relative">Mod
                                    <span class="ms-5 position-absolute translate-middle-y top-50 end-0">
                                        <i class="ki-outline ki-night-day theme-light-show fs-2"></i>
                                        <i class="ki-outline ki-moon theme-dark-show fs-2"></i>
                                    </span></span>
                            </a>
                            <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-title-gray-700 menu-icon-gray-500 menu-active-bg menu-state-color fw-semibold py-4 fs-base w-150px"
                                data-kt-menu="true" data-kt-element="theme-mode-menu">
                                <div class="menu-item px-3 my-0">
                                    <a href="#" class="menu-link px-3 py-2" data-kt-element="mode"
                                        data-kt-value="light">
                                        <span class="menu-icon" data-kt-element="icon">
                                            <i class="ki-outline ki-night-day fs-2"></i>
                                        </span>
                                        <span class="menu-title">Light</span>
                                    </a>
                                </div>
                                <div class="menu-item px-3 my-0">
                                    <a href="#" class="menu-link px-3 py-2" data-kt-element="mode"
                                        data-kt-value="dark">
                                        <span class="menu-icon" data-kt-element="icon">
                                            <i class="ki-outline ki-moon fs-2"></i>
                                        </span>
                                        <span class="menu-title">Dark</span>
                                    </a>
                                </div>
                                <div class="menu-item px-3 my-0">
                                    <a href="#" class="menu-link px-3 py-2" data-kt-element="mode"
                                        data-kt-value="system">
                                        <span class="menu-icon" data-kt-element="icon">
                                            <i class="ki-outline ki-screen fs-2"></i>
                                        </span>
                                        <span class="menu-title">System</span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Paletas de cor (Tema) - salva no navegador (localStorage) -->
                        <div class="menu-item px-5" data-kt-menu-trigger="{default: 'click', lg: 'hover'}"
                             data-kt-menu-placement="left-start" data-kt-menu-offset="-15px, 0">
                            <a href="#" class="menu-link px-5">
                                <span class="menu-title position-relative">Temas
                                    <span class="ms-5 position-absolute translate-middle-y top-50 end-0">
                                        <i class="ki-outline ki-palette fs-2"></i>
                                    </span>
                                </span>
                            </a>
                            <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-title-gray-700 menu-icon-gray-500 menu-active-bg menu-state-color fw-semibold py-4 fs-base w-300px"
                                 data-kt-menu="true">
                                <div class="px-5 pb-3">
                                    <div class="fw-bold text-gray-800">TEMAS</div>
                                    <div class="text-muted fs-8">Escolha uma paleta de cores</div>
                                </div>
                                <div class="separator my-2"></div>
                                <div class="px-5">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <a href="#" class="d-flex align-items-center gap-3 p-3 rounded bg-light bg-opacity-50 hover-elevate-up" data-set-theme-color="pw-rebrands">
                                                <span class="rounded-circle" style="width:14px;height:14px;background:#a855f7; box-shadow:0 0 12px rgba(168,85,247,.75);"></span>
                                                <span class="fw-semibold">PW REBRANDS</span>
                                                <span class="ms-auto theme-color-check d-none"><i class="ki-outline ki-check-circle fs-2 text-success"></i></span>
                                            </a>
                                        </div>

                                        <div class="col-6">
                                            <a href="#" class="d-flex align-items-center gap-3 p-3 rounded bg-light bg-opacity-50 hover-elevate-up" data-set-theme-color="dark">
                                                <span class="rounded-circle" style="width:14px;height:14px;background:#1f2937;"></span>
                                                <span class="fw-semibold">Dark</span>
                                                <span class="ms-auto theme-color-check d-none"><i class="ki-outline ki-check-circle fs-2 text-success"></i></span>
                                            </a>
                                        </div>
                                        <div class="col-6">
                                            <a href="#" class="d-flex align-items-center gap-3 p-3 rounded bg-light bg-opacity-50 hover-elevate-up" data-set-theme-color="ocean">
                                                <span class="rounded-circle" style="width:14px;height:14px;background:#0ea5e9;"></span>
                                                <span class="fw-semibold">Ocean</span>
                                                <span class="ms-auto theme-color-check d-none"><i class="ki-outline ki-check-circle fs-2 text-success"></i></span>
                                            </a>
                                        </div>
                                        <div class="col-6">
                                            <a href="#" class="d-flex align-items-center gap-3 p-3 rounded bg-light bg-opacity-50 hover-elevate-up" data-set-theme-color="forest-green">
                                                <span class="rounded-circle" style="width:14px;height:14px;background:#22c55e;"></span>
                                                <span class="fw-semibold">Forest Green</span>
                                                <span class="ms-auto theme-color-check d-none"><i class="ki-outline ki-check-circle fs-2 text-success"></i></span>
                                            </a>
                                        </div>
                                        <div class="col-6">
                                            <a href="#" class="d-flex align-items-center gap-3 p-3 rounded bg-light bg-opacity-50 hover-elevate-up" data-set-theme-color="royal-purple">
                                                <span class="rounded-circle" style="width:14px;height:14px;background:#8b5cf6;"></span>
                                                <span class="fw-semibold">Royal Purple</span>
                                                <span class="ms-auto theme-color-check d-none"><i class="ki-outline ki-check-circle fs-2 text-success"></i></span>
                                            </a>
                                        </div>
                                        <div class="col-6">
                                            <a href="#" class="d-flex align-items-center gap-3 p-3 rounded bg-light bg-opacity-50 hover-elevate-up" data-set-theme-color="crimson-red">
                                                <span class="rounded-circle" style="width:14px;height:14px;background:#ef4444;"></span>
                                                <span class="fw-semibold">Crimson Red</span>
                                                <span class="ms-auto theme-color-check d-none"><i class="ki-outline ki-check-circle fs-2 text-success"></i></span>
                                            </a>
                                        </div>
                                        <div class="col-6">
                                            <a href="#" class="d-flex align-items-center gap-3 p-3 rounded bg-light bg-opacity-50 hover-elevate-up" data-set-theme-color="sunset-orange">
                                                <span class="rounded-circle" style="width:14px;height:14px;background:#f97316;"></span>
                                                <span class="fw-semibold">Sunset Orange</span>
                                                <span class="ms-auto theme-color-check d-none"><i class="ki-outline ki-check-circle fs-2 text-success"></i></span>
                                            </a>
                                        </div>
                                        <div class="col-12">
                                            <a href="#" class="d-flex align-items-center gap-3 p-3 rounded bg-light bg-opacity-50 hover-elevate-up" data-set-theme-color="arctic-cyan">
                                                <span class="rounded-circle" style="width:14px;height:14px;background:#06b6d4;"></span>
                                                <span class="fw-semibold">Arctic Cyan</span>
                                                <span class="ms-auto theme-color-check d-none"><i class="ki-outline ki-check-circle fs-2 text-success"></i></span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="menu-item px-5">
                            <a href="logout.php" class="menu-link px-5">Sair</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
