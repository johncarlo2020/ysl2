<!--
=========================================================
* Argon Dashboard 2 - v2.0.4
=========================================================

* Product Page: https://www.creative-tim.com/product/argon-dashboard
* Copyright 2022 Creative Tim (https://www.creative-tim.com)
* Licensed under MIT (https://www.creative-tim.com/license)
* Coded by Creative Tim

=========================================================

* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
-->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('assets/img/apple-icon.png') }}" />
    <link rel="icon" type="image/png" href="images/logo.svg" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>YSL</title>
    <!--     Fonts and icons     -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <!-- Nucleo Icons -->
    <link href="{{ asset('assets/css/nucleo-icons.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/nucleo-svg.css') }}" rel="stylesheet" />

    <!-- Font Awesome Icons -->
    <link href="{{ asset('assets/css/nucleo-svg.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.7/css/dataTables.dataTables.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" type="text/css"
        href="https://cdn.datatables.net/buttons/2.0.0/css/buttons.dataTables.min.css" />

    <!-- CSS Files -->
    <link id="pagestyle" href="{{ asset('assets/css/argon-dashboard.css?v=2.0.4') }}" rel="stylesheet" />
    <style>
        .admin-main {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            min-height: 100dvh;
        }

        .admin-main > .navbar,
        .admin-main > .footer {
            flex-shrink: 0;
        }

        .admin-page-content {
            flex: 1;
        }
        #sidenav-main { display: flex; flex-direction: column; align-items: stretch; }
        #sidenav-main .sidenav-header, #sidenav-main > hr { flex-shrink: 0; }
        #sidenav-main #sidenav-collapse-main { flex: 1; min-height: 0; height: auto; width: 100% !important; overflow-y: auto; }
        .sidebar-nav-icon { color: #67748e; flex-shrink: 0; }
        .nav-link.active .sidebar-nav-icon { color: #5e72e4; }
        .sidebar-footer { flex-shrink: 0; margin-top: auto; padding: 16px; border-top: 1px solid #edf0f5; }
        .sidebar-logout { display: flex; align-items: center; border: 0; border-radius: 10px; padding: 12px 16px; background: transparent; width: 100%; text-align: left; transition: background .2s; }
        .sidebar-logout:hover, .sidebar-logout:focus-visible { background: #fff1f2; }
        .sidebar-logout i { transition: transform .2s; }
        .sidebar-logout:hover i, .sidebar-logout:focus-visible i { transform: translateX(4px); }
        .logout-dialog { width: calc(100% - 32px); max-width: 400px; padding: 32px; border: 1px solid #edf0f5; border-radius: 20px; box-shadow: 0 24px 64px #34476730; color: #344767; text-align: center; }
        .logout-dialog[open] { animation: logout-enter .2s ease-out; }
        .logout-dialog::backdrop { background: #172b4d80; }
        .logout-dialog-icon { display: inline-flex; align-items: center; justify-content: center; width: 64px; height: 64px; margin-bottom: 20px; border-radius: 18px; background: #fff1f2; color: #ea0606; font-size: 24px; }
        .logout-dialog h2 { font-size: 20px; margin-bottom: 10px; }
        .logout-dialog p { color: #8392ab; font-size: 14px; margin-bottom: 24px; }
        .logout-actions { display: flex; gap: 12px; }
        .logout-actions .btn { flex: 1; margin: 0; padding: 12px; border-radius: 10px; }
        @keyframes logout-enter { from { opacity: 0; transform: translateY(12px) scale(.97); } to { opacity: 1; transform: translateY(0) scale(1); } }
        @media (prefers-reduced-motion: reduce) { .sidebar-logout, .sidebar-logout i { transition: none; } .logout-dialog[open] { animation: none; } }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
</head>

<body class="g-sidenav-show bg-gray-100">
    <aside
        class="sidenav bg-white navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-4"
        id="sidenav-main">
        <div class="sidenav-header">
            <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none"
                aria-hidden="true" id="iconSidenav"></i>
            <a class="navbar-brand m-0" href="" target="_blank">
                <img src="{{ asset('images/logo2.png') }}" class="navbar-brand-img h-100" alt="main_logo" />
            </a>
        </div>
        <hr class="horizontal dark mt-0" />
        <div class="collapse navbar-collapse w-auto" id="sidenav-collapse-main">
            <ul class="navbar-nav">



                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin') ? 'active' : '' }}" href="{{ route('admin') }}">
                        <div
                            class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <svg class="sidebar-nav-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
                        </div>
                        <span class="nav-link-text ms-1">Dashboard</span>
                    </a>
                </li>

                <!-- Show all links if the user has 'full' permission -->


                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('users') ? 'active' : '' }}" href="{{ route('users') }}">
                        <div
                            class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <svg class="sidebar-nav-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="8" r="3"/><path d="M3 21v-2a6 6 0 0 1 12 0v2M16 5a3 3 0 0 1 0 6M21 21v-2a6 6 0 0 0-4-5.65"/></svg>
                        </div>
                        <span class="nav-link-text ms-1">Users</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('rfid.admin') ? 'active' : '' }}"
                        href="{{ route('rfid.admin') }}">
                        <div
                            class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <svg class="sidebar-nav-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="4" width="20" height="16" rx="3"/><circle cx="8" cy="10" r="2"/><path d="M5 16a3 3 0 0 1 6 0M14 9h4M14 13h4"/></svg>
                        </div>
                        <span class="nav-link-text ms-1">RFID Cards</span>
                    </a>
                </li>



            <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('staff.*') ? 'active' : '' }}" href="{{ route('staff.index') }}">
                        <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center"><svg class="sidebar-nav-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3 20 6v6c0 5-8 9-8 9s-8-4-8-9V6z"/><circle cx="12" cy="10" r="2.5"/><path d="M8 16a4 4 0 0 1 8 0"/></svg></div>
                        <span class="nav-link-text ms-1">Staff users</span>
                    </a>
                </li>
            </ul>
        </div>
        <div class="sidebar-footer">
                    <button type="button" class="nav-link sidebar-logout" id="sidebar-logout" aria-haspopup="dialog" aria-controls="logout-dialog">
                        <span class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="fa-solid fa-right-from-bracket text-danger text-sm" aria-hidden="true"></i>
                        </span>
                        <span class="nav-link-text ms-1 text-danger">Logout</span>
                    </button>
        </div>
    </aside>
    <main class="main-content admin-main position-relative border-radius-lg">
        <!-- Navbar -->
        <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur"
            data-scroll="false">
            <div class="container-fluid py-1 px-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
                        <li class="breadcrumb-item text-sm">
                            <a class="opacity-5 text-dark" href="javascript:;">Pages</a>
                        </li>
                        <li class="breadcrumb-item text-sm text-dark active" aria-current="page">
                            Dashboard
                        </li>
                    </ol>
                    <h6 class="font-weight-bolder text-dark mb-0">
                        Dashboard
                    </h6>
                </nav>
                <ul class="navbar-nav justify-content-end">
                    <li class="nav-item d-xl-none ps-3 d-flex align-items-center">
                        <a href="javascript:;" class="nav-link text-dark p-0" id="iconNavbarSidenav">
                            <div class="sidenav-toggler-inner">
                                <i class="sidenav-toggler-line bg-dark"></i>
                                <i class="sidenav-toggler-line bg-dark"></i>
                                <i class="sidenav-toggler-line bg-dark"></i>
                            </div>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
        <!-- End Navbar -->
        <div class="container-fluid admin-page-content">@yield('content')</div>

        <footer class="footer py-3">
            <div class="container-fluid">
                <div class="row align-items-center justify-content-lg-between">
                    <div class="col-lg-6">
                        <div class="copyright text-center text-sm text-muted text-lg-start">
                            <a href="https://wowsome.com.my/" class="font-weight-bold" target="_blank">Wowsome</a>
                            © Copyright {{ now()->year }}
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </main>

    <dialog class="logout-dialog" id="logout-dialog" aria-labelledby="logout-title" aria-describedby="logout-description">
        <span class="logout-dialog-icon"><i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i></span>
        <h2 id="logout-title">Are you sure you want to log out?</h2>
        <p id="logout-description">You’ll need to sign in again to access the admin panel.</p>
        <form method="POST" action="{{ route('logout') }}" class="logout-actions">
            @csrf
            <button type="button" class="btn btn-light" id="cancel-logout" autofocus>Cancel</button>
            <button type="submit" class="btn btn-danger">Yes, log out</button>
        </form>
    </dialog>
    <script>
        const logoutDialog = document.getElementById('logout-dialog');
        document.getElementById('sidebar-logout').addEventListener('click', function () {
            logoutDialog.showModal();
        });
        document.getElementById('cancel-logout').addEventListener('click', function () {
            logoutDialog.close();
        });
    </script>
    <script>
        var win = navigator.platform.indexOf("Win") > -1;
        if (win && document.querySelector("#sidenav-scrollbar")) {
            var options = {
                damping: "0.5",
            };
            Scrollbar.init(
                document.querySelector("#sidenav-scrollbar"),
                options
            );
        }
    </script>
    <!-- Github buttons -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>
    <!-- Control Center for Soft Dashboard: parallax effects, scripts for the example pages etc -->
    <script src="{{ asset('assets/js/argon-dashboard.min.js?v=2.0.4.1') }}"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.7/js/dataTables.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>

</html>
