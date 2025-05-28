<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Gudang Kayu')</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    @stack('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.png') }}">
    <style>
        body {
            overflow-x: hidden;
            background: #F1F8E9;
        }
        .navbar {
            background: #33691E;
            border-bottom: 2px solid #A5D6A7;
            min-height: 60px;
            padding: 0 16px;
        }
        .navbar-brand {
            color: #fff !important;
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: 1px;
            padding: 0;
            margin: 0;
            white-space: nowrap;
            display: flex;
            align-items: center;
        }
        .sidebar-toggle-header {
            background: none;
            border: none;
            color: #fff;
            font-size: 2rem;
            margin-right: 16px;
            display: none;
        }
        @media (max-width: 991.98px) {
            .navbar {
                min-height: 44px;
                padding: 0 8px !important;
            }
            .navbar-brand {
                font-size: 1.1rem !important;
                letter-spacing: 0.5px;
            }
            .sidebar-toggle-header {
                display: inline-block !important;
                font-size: 1.3rem !important;
                margin-right: 4px !important;
                padding: 0 !important;
            }
            .container-fluid.d-flex {
                flex-direction: row !important;
                padding: 0 !important;
            }
            .navbar .dropdown {
                margin-left: auto !important;
            }
            .navbar .dropdown-toggle {
                font-size: 0.85rem !important;
                padding: 1px 4px !important;
            }
            .navbar .dropdown-menu {
                font-size: 0.85rem !important;
                min-width: 160px;
                right: 0;
                left: auto;
            }
            .navbar .dropdown img {
                height: 24px !important;
                width: 24px !important;
            }
            #sidebar {
                position: fixed;
                left: -220px;
                top: 0;
                height: 100vh;
                width: 200px !important;
                min-width: 64px !important;
                max-width: 220px !important;
                z-index: 1050;
                transition: left 0.3s;
                box-shadow: 2px 0 8px #e0f2f1;
            }
            #sidebar.show {
                left: 0;
            }
            .main-content {
                margin-left: 0 !important;
                padding: 12px 4px 4px 4px !important;
            }
            .sidebar-toggle-btn {
                display: none;
            }
            body {
                overflow-x: hidden;
            }
        }
        @media (max-width: 575.98px) {
            .navbar {
                min-height: 36px;
                padding: 0 4px !important;
            }
            .navbar-brand {
                font-size: 0.95rem !important;
                letter-spacing: 0.2px;
            }
            .sidebar-toggle-header {
                font-size: 1.1rem !important;
                margin-right: 2px !important;
            }
            .navbar .dropdown-toggle {
                font-size: 0.85rem !important;
                padding: 1px 2px !important;
            }
            .navbar .dropdown-menu {
                font-size: 0.85rem !important;
                min-width: 120px;
                right: 0;
                left: auto;
            }
            .navbar .dropdown img {
                height: 24px !important;
                width: 24px !important;
            }
            #sidebar {
                width: 75vw !important;
                min-width: 0 !important;
                max-width: 80vw !important;
                left: -80vw;
                border-right: 2px solid #A5D6A7;
            }
            #sidebar.show {
                left: 0;
            }
            .btn-sidebar {
                font-size: 0.93rem;
                height: 38px;
                padding: 0 4px;
                border-radius: 6px;
            }
            #sidebar .sidebar-content, #sidebar .sidebar-bottom {
                padding: 8px 2px 2px 2px !important;
                gap: 2px;
            }
            .main-content {
                padding-left: 0 !important;
                padding-right: 0 !important;
            }
        }
        /* --- Sidebar Desktop --- */
        #sidebar {
            width: 160px;
            min-width: 120px;
            max-width: 180px;
            background: #388E3C;
            color: #fff;
            min-height: 100vh;
            transition: width 0.3s, min-width 0.3s, max-width 0.3s, left 0.3s;
            box-shadow: 2px 0 8px #e0f2f1;
            padding: 0;
            z-index: 100;
            display: flex;
            flex-direction: column;
            position: relative;
            border-right: 2px solid #A5D6A7;
        }
        #sidebar .sidebar-content, #sidebar .sidebar-bottom {
            padding: 14px 6px 6px 6px;
            display: flex;
            flex-direction: column;
            align-items: stretch;
            transition: padding 0.3s;
            gap: 7px;
        }
        #sidebar .sidebar-bottom {
            margin-top: auto;
            padding-bottom: 12px;
        }
        /* --- Sidebar Collapsed Desktop --- */
        #sidebar.collapsed {
            width: 56px !important;
            min-width: 56px !important;
            max-width: 56px !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
            align-items: center;
        }
        #sidebar.collapsed .sidebar-content,
        #sidebar.collapsed .sidebar-bottom {
            padding-left: 2px !important;
            padding-right: 2px !important;
            align-items: center;
            gap: 4px;
        }
        #sidebar.collapsed .btn-sidebar {
            justify-content: center;
            padding: 0 !important;
            min-width: 40px;
            width: 40px;
            height: 40px;
            border-radius: 8px;
        }
        #sidebar.collapsed .sidebar-label {
            display: none !important;
        }
        #sidebar.collapsed .mt-3.mb-2.px-2.text-uppercase {
            display: none !important;
        }
        /* Main content margin adjustment */
        @media (min-width: 992px) {
            .main-content {
                transition: margin-left 0.3s;
                margin-left: 160px;
            }
            #sidebar.collapsed ~ .main-content {
                margin-left: 56px !important;
            }
        }
        /* --- Sidebar Tablet --- */
        @media (max-width: 991.98px) {
            #sidebar {
                position: fixed;
                left: -75vw;
                top: 0;
                height: 100vh;
                width: 70vw !important;
                min-width: 120px !important;
                max-width: 80vw !important;
                z-index: 1050;
                transition: left 0.3s, width 0.3s;
                box-shadow: 2px 0 8px #e0f2f1;
            }
            #sidebar.show {
                left: 0;
            }
        }
        /* --- Sidebar Mobile --- */
        @media (max-width: 575.98px) {
            #sidebar {
                width: 75vw !important;
                min-width: 0 !important;
                max-width: 80vw !important;
                left: -80vw;
                border-right: 2px solid #A5D6A7;
            }
            #sidebar.show {
                left: 0;
            }
            .btn-sidebar {
                font-size: 0.93rem;
                height: 38px;
                padding: 0 4px;
                border-radius: 6px;
            }
            #sidebar .sidebar-content, #sidebar .sidebar-bottom {
                padding: 8px 2px 2px 2px !important;
                gap: 2px;
            }
            .main-content {
                padding-left: 0 !important;
                padding-right: 0 !important;
            }
        }
        .btn-sidebar {
            background-color: #A5D6A7;
            color: #33691E;
            border: none;
            font-weight: 600;
            border-radius: 8px;
            margin-bottom: 0;
            width: 100%;
            min-width: 48px;
            height: 40px;
            padding: 0 8px;
            display: flex;
            align-items: center;
            font-size: 1rem;
            transition: background 0.2s, color 0.2s;
            gap: 8px;
        }
        .btn-sidebar .sidebar-label {
            display: inline;
            font-size: 0.98rem;
            color: #33691E;
            font-weight: 600;
            margin-left: 8px;
            line-height: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
            transition: opacity 0.3s, width 0.3s;
            opacity: 1;
            width: auto;
        }
        #sidebar.collapsed .sidebar-label {
            opacity: 0;
            width: 0;
            margin: 0;
            padding: 0;
            overflow: hidden;
            display: inline-block;
            transition: opacity 0.3s, width 0.3s;
        }
        .btn-sidebar:hover, .btn-sidebar:focus {
            background-color: #689F38;
            color: #fff;
        }
        .btn-sidebar:hover .sidebar-label,
        .btn-sidebar:focus .sidebar-label {
            color: #fff;
        }
        .mt-3.mb-2.px-2.text-uppercase {
            margin-top: 18px !important;
            margin-bottom: 10px !important;
            padding-left: 8px !important;
            font-size: 0.85rem;
            letter-spacing: 1px;
        }
        @media (max-width: 991.98px) {
            #sidebar {
                width: 70vw !important;
                min-width: 120px !important;
                max-width: 90vw !important;
                left: -90vw;
                border-right: 2px solid #A5D6A7;
            }
            #sidebar .sidebar-content, #sidebar .sidebar-bottom {
                padding: 12px 4px 4px 4px !important;
                gap: 4px;
            }
            .btn-sidebar {
                font-size: 0.98rem;
                height: 40px;
                padding: 0 7px;
                border-radius: 7px;
            }
            .mt-3.mb-2.px-2.text-uppercase {
                margin-top: 12px !important;
                margin-bottom: 6px !important;
                padding-left: 4px !important;
                font-size: 0.8rem;
            }
        }
        @media (max-width: 575.98px) {
            #sidebar {
                width: 75vw !important;
                min-width: 0 !important;
                max-width: 80vw !important;
                left: -80vw;
                border-right: 2px solid #A5D6A7;
            }
            #sidebar .sidebar-content, #sidebar .sidebar-bottom {
                padding: 8px 2px 2px 2px !important;
                gap: 2px;
            }
            .btn-sidebar {
                font-size: 0.93rem;
                height: 38px;
                padding: 0 4px;
                border-radius: 6px;
            }
            .mt-3.mb-2.px-2.text-uppercase {
                margin-top: 8px !important;
                margin-bottom: 4px !important;
                padding-left: 2px !important;
                font-size: 0.75rem;
            }
        }
        #sidebarOverlay {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(44, 62, 80, 0.35);
            z-index: 1049;
            transition: opacity 0.3s;
            opacity: 0;
        }
        #sidebarOverlay.active {
            display: block;
            opacity: 1;
        }
        @media (min-width: 992px) {
            #sidebarOverlay { display: none !important; }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <nav class="navbar navbar-expand-lg shadow-sm" style="background: #33691E; border-bottom: 2px solid #A5D6A7; min-height: 56px;">
            <div class="container-fluid d-flex justify-content-between align-items-center flex-nowrap">
                <div class="d-flex align-items-center flex-shrink-1">
                    <button class="sidebar-toggle-header d-lg-none me-2" id="sidebarToggleHeader" aria-label="Toggle sidebar">
                        <i class="bi bi-list"></i>
                    </button>
                    <span class="navbar-brand mb-0">
                        <i class="bi bi-tree-fill me-2"></i> UD. Tiga PutraKarya Sentosa
                    </span>
                </div>
                <div class="d-flex align-items-center justify-content-end flex-shrink-0 ms-auto">
                    <!-- Dropdown Profil -->
                    <div class="dropdown">
                        <button class="btn btn-link dropdown-toggle d-flex align-items-center text-white text-decoration-none" type="button" id="dropdownProfile" data-bs-toggle="dropdown" aria-expanded="false" style="font-weight:600;">
                            <span class="me-2 d-none d-sm-inline">{{ auth()->user() ? auth()->user()->name : '' }}</span>
                            <img src="{{ asset('favicon.png') }}" alt="Logo" style="height:28px; width:28px; border-radius:6px; object-fit:cover;">
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownProfile">
                            <li class="px-3 py-2">
                                <div style="font-weight:600;">{{ auth()->user() ? auth()->user()->name : '' }}</div>
                                <div style="font-size:0.93rem; color:#33691E;">{{ auth()->user() ? auth()->user()->email : '' }}</div>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="{{ route('account.profile') }}">
                                    <i class="bi bi-person-circle me-2"></i> Profil Saya
                                </a>
                            </li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>
    </header>
    <!-- Layout: Sidebar & Konten Utama -->
    <div class="container-fluid">
      <div class="row flex-nowrap">
        <!-- Sidebar -->
        <div id="sidebar" class="border-end d-flex flex-column col-auto p-0">
            <button class="sidebar-toggle-btn d-none d-lg-flex" id="sidebarToggle" aria-label="Toggle sidebar">
                <i class="bi bi-list"></i>
            </button>
            <div class="sidebar-content flex-grow-1">
                <!-- Section Wood -->
                <div class="mt-3 mb-2 px-2 text-uppercase fw-bold small" style="color:#A5D6A7; letter-spacing:1px;">Log Kayu</div>
                <a href="{{ route('dashboard') }}" class="btn btn-sidebar" title="Laporan Stock">
                    <i class="bi bi-clipboard-data"></i>
                    <span class="sidebar-label">Stock</span>
                </a>
                <a href="{{ route('wood_transactions.buy') }}" class="btn btn-sidebar" title="Pembelian">
                    <i class="bi bi-cart-plus"></i>
                    <span class="sidebar-label">Beli</span>
                </a>
                <a href="{{ route('wood_transactions.sell') }}" class="btn btn-sidebar" title="Penjualan">
                    <i class="bi bi-cart-check"></i>
                    <span class="sidebar-label">Jual</span>
                </a>
                <a href="{{ route('transaksi.index') }}" class="btn btn-sidebar" title="List Transaksi">
                    <i class="bi bi-list-ul"></i>
                    <span class="sidebar-label">Transaksi</span>
                </a>
                @if(auth()->user() && auth()->user()->role === 'admin')
                    <a href="{{ route('wood_prices.index') }}" class="btn btn-sidebar" title="Harga Kayu">
                        <i class="bi bi-cash-coin"></i>
                        <span class="sidebar-label">Harga Kayu</span>
                    </a>
                @endif

                <!-- Divider -->
                <div class="sidebar-divider my-2"></div>
               
                <!-- Section Palet -->
                <div class="mt-3 mb-2 px-2 text-uppercase fw-bold small" style="color:#A5D6A7; letter-spacing:1px;">Palet</div>
                 <a href="{{ route('pallet_stocks.index') }}" class="btn btn-sidebar" title="Stok Palet">
                    <i class="bi bi-clipboard-data"></i>
                    <span class="sidebar-label">Stock Palet</span>
                </a>
                
               
                <a href="{{ route('pallet_stocks.create') }}" class="btn btn-sidebar" title="Tambah Stok Palet">
                    <i class="bi bi-plus-square"></i>
                    <span class="sidebar-label">Stok Palet</span>
                </a>
                <a href="{{ route('pallet-prices.index') }}" class="btn btn-sidebar" title="Harga Palet">
                         <i class="bi bi-cash-coin"></i>
                    <span class="sidebar-label">Harga Palet</span>
                </a>
                <a href="{{ route('pallet-sales.index') }}" class="btn btn-sidebar" title="Riwayat Penjualan Palet">
                    <i class="bi bi-receipt"></i>
                    <span class="sidebar-label">Penjualan Palet</span>
                </a>
            </div>
            <div class="sidebar-bottom mt-auto p-2">
                @if(auth()->user() && auth()->user()->role === 'admin')
                    <a href="{{ route('account.index') }}" class="btn btn-sidebar" title="Manage Akun">
                        <i class="bi bi-person-circle"></i>
                        <span class="sidebar-label">Akun</span>
                    </a>
                @endif
                <form action="{{ route('logout') }}" method="POST" class="w-100 mt-2">
                    @csrf
                    <button type="submit" class="btn btn-sidebar logout-btn" title="Logout">
                        <i class="bi bi-box-arrow-right"></i>
                        <span class="sidebar-label">Logout</span>
                    </button>
                </form>
            </div>
        </div>
        <!-- Overlay for mobile sidebar -->
        <div id="sidebarOverlay"></div>
        <!-- Konten Utama -->
        <div class="main-content flex-grow-1 col p-0 ps-4">
            @yield('content')
        </div>
      </div>
    </div>
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sidebar collapse/expand (desktop)
        document.addEventListener('DOMContentLoaded', function() {
            var sidebar = document.getElementById('sidebar');
            var toggleBtn = document.getElementById('sidebarToggle');
            var icon = toggleBtn ? toggleBtn.querySelector('i') : null;
            var mainContent = document.querySelector('.main-content');

            if(toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    sidebar.classList.toggle('collapsed');
                    if (sidebar.classList.contains('collapsed')) {
                        icon.classList.remove('bi-list');
                        icon.classList.add('bi-chevron-double-right');
                        if(mainContent) mainContent.style.marginLeft = '56px';
                    } else {
                        icon.classList.remove('bi-chevron-double-right');
                        icon.classList.add('bi-list');
                        if(mainContent) mainContent.style.marginLeft = '160px';
                    }
                });
            }
            // Sidebar show/hide (mobile)
            var sidebarToggleHeader = document.getElementById('sidebarToggleHeader');
            function toggleMobileSidebar() {
                sidebar.classList.toggle('show');
            }
            if(sidebarToggleHeader) {
                sidebarToggleHeader.addEventListener('click', toggleMobileSidebar);
            }
            // Klik di luar sidebar untuk menutup di mobile
            document.addEventListener('click', function(e) {
                if(window.innerWidth <= 991.98) {
                    if(sidebar.classList.contains('show') && !sidebar.contains(e.target) && !sidebarToggleHeader.contains(e.target)) {
                        sidebar.classList.remove('show');
                    }
                }
            });
            // Tutup sidebar setelah klik menu di mobile
            sidebar.querySelectorAll('.btn-sidebar').forEach(function(link) {
                link.addEventListener('click', function() {
                    if(window.innerWidth <= 991.98) {
                        sidebar.classList.remove('show');
                    }
                });
            });
        });
    </script>
    @yield('scripts')
    <!-- Footer -->
    <footer class="text-center py-3 mt-auto" style="background: #33691E; color: #fff; font-size: 1rem; letter-spacing: 1px;">
        &copy; {{ date('Y') }} UD. Tiga PutraKarya Sentosa &mdash; Sistem Pembukuan Stok Kayu
    </footer>
</body>
</html>