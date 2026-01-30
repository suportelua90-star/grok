<?php

error_reporting(E_ALL);

ini_set('display_errors', 1);

ini_set('display_startup_errors', 1);

$static_url = dirname($_SERVER['SCRIPT_NAME']) . '/assets/';

?>



<!DOCTYPE html>

<html lang="en">



    <head>

        <title><?php echo isset($page_title) ? $page_title : "IBO Panel"; ?></title>

        <meta charset="utf-8" />

        <meta name="description" content="MaxRebrands IBO Panel" />

        <meta name="viewport" content="width=device-width, initial-scale=1" />

        <link rel="shortcut icon" href="<?php echo $static_url; ?>media/logos/logo.png" />

		<link href="<?php echo $static_url; ?>plugins/global/plugins.bundle.css" rel="stylesheet" />

		<link href="<?php echo $static_url; ?>css/style.bundle.css" rel="stylesheet" />

    </head>



    <body id="kt_body" class="app-blank">

		<script>var defaultThemeMode = "light"; var themeMode; if ( document.documentElement ) { if ( document.documentElement.hasAttribute("data-bs-theme-mode")) { themeMode = document.documentElement.getAttribute("data-bs-theme-mode"); } else { if ( localStorage.getItem("data-bs-theme") !== null ) { themeMode = localStorage.getItem("data-bs-theme"); } else { themeMode = defaultThemeMode; } } if (themeMode === "system") { themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light"; } document.documentElement.setAttribute("data-bs-theme", themeMode); }</script>

		<div class="d-flex flex-column flex-root" id="kt_app_root">

			<div class="d-flex flex-column flex-lg-row flex-column-fluid">

				<div class="d-flex flex-column flex-lg-row-fluid w-lg-50 p-10 order-2 order-lg-1">

					<div class="d-flex flex-center flex-column flex-lg-row-fluid">

						<div class="w-lg-500px p-10">

							<form class="form w-100" novalidate="novalidate" id="kt_sign_in_form" data-kt-redirect-url="index.html" action="#">

								<div class="text-center mb-11">

                                    <img alt="Logo" src="<?php echo $static_url; ?>media/logos/logo.png" class="h-200px h-lg-200px" />

								</div>

								<div class="fv-row mb-8">

									<input type="text" placeholder="Username" name="username" autocomplete="off" class="form-control bg-transparent" />

								</div>

								<div class="fv-row mb-3">

									<input type="password" placeholder="Password" name="password" autocomplete="off" class="form-control bg-transparent" />

								</div>

								<div class="d-grid mb-10">

									<button type="submit" id="kt_sign_in_submit" class="btn btn-primary">

										<span class="indicator-label">ENTRAR</span>

										<span class="indicator-progress">Please wait... 

										<span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>

									</button>

								</div>

							</form>

						</div>

					</div>

				</div>

				<div class="d-flex flex-lg-row-fluid w-lg-50 bgi-size-cover bgi-position-center order-1 order-lg-2" style="background-image: url(<?php echo $static_url; ?>media/images/auth-bg.png)">

					<div class="d-flex flex-column flex-center py-7 py-lg-15 px-5 px-md-15 w-100">

						<img class="d-none d-lg-block mx-auto w-275px w-md-50 w-xl-500px mb-5 mb-lg-10" src="<?php echo $static_url; ?>media/images/auth-screens5.png" alt="" />

						<h1 class="d-none d-lg-block text-white fs-2qx fw-bolder text-center mb-5">BEM VINDO</h1>

						<div class="d-none d-lg-block text-white fs-base text-center mb-5">

						</div>

						<div class="mt-auto d-flex flex-row align-items-center justify-content-between px-10 px-lg-15 pb-8" id="kt_app_sidebar_footer">

							<span class="d-flex flex-center gap-1 text-white theme-light-show fs-5 px-0">

								<i class="ki-outline ki-night-day text-gray-900 fs-1"></i>Dark Mode

							</span>

							<span class="d-flex flex-center gap-1 text-white theme-dark-show fs-5 px-0">

								<i class="ki-outline ki-moon text-gray-900 fs-1"></i>Light Mode

							</span>

							<div data-bs-theme="dark" class="ms-3">

								<div class="form-check form-switch form-check-custom form-check-solid">

									<input class="form-check-input h-25px w-45px" type="checkbox" value="1" id="kt_sidebar_theme_mode_toggle" />

								</div>

							</div>

						</div>

					</div>

				</div>

			</div>

		</div>

        <script src="<?php echo $static_url; ?>plugins/global/plugins.bundle.js"></script>

        <script src="<?php echo $static_url; ?>js/scripts.bundle.js"></script>

        <script src="<?php echo $static_url; ?>js/users.js"></script>

	</body>

</html>