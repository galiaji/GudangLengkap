<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Gudang Kayu')</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    @stack('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            overflow-x: hidden;
            background: #F1F8E9;
        }
        .navbar {
            background: #33691E;
            border-bottom: 2px solid #A5D6A7;
            min-height: 60px;
        }
        .navbar-brand {
            color: #fff !important;
            font-size: 1.7rem;
            font-weight: 700;
            letter-spacing: 1px;
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
            .sidebar-toggle-header {
                display: inline-block;
            }
        }
        #sidebar {
            width: 140px;
            min-width: 64px;
            max-width: 140px;
            background: #388E3C;
            color: #fff;
            min-height: 100vh;
            transition: width 0.3s, min-width 0.3s, max-width 0.3s;
            box-shadow: 2px 0 8px #e0f2f1;
            padding: 0;
            z-index: 100;
            display: flex;
            flex-direction: column;
        }
        #sidebar.collapsed {
            width: 64px !important;
            min-width: 64px !important;
            max-width: 64px !important;
        }
        #sidebar .sidebar-content, #sidebar .sidebar-bottom {
            padding: 18px 6px 6px 6px;
            display: flex;
            flex-direction: column;
            align-items: stretch;
            transition: padding 0.3s;
            gap: 4px;
        }
        .btn-sidebar {
            background-color: #A5D6A7;
            color: #33691E;
            border: none;
            font-weight: 600;
            border-radius: 6px;
            margin-bottom: 6px;
            width: 100%;
            min-width: 48px;
            height: 48px;
            padding: 0 12px;
            display: flex;
            align-items: center;
            font-size: 1.3rem;
            transition: background 0.2s, color 0.2s;
            position: relative;
            overflow: hidden;
            gap: 12px;
        }
        .btn-sidebar .sidebar-label {
            display: inline;
            font-size: 1rem;
            color: #33691E;
            font-weight: 600;
            margin-left: 10px;
            line-height: 1;
            white-space: nowrap;
            transition: opacity 0.3s, width 0.3s;
            opacity: 1;
            width: auto;
        }
        .btn-sidebar:hover, .btn-sidebar:focus {
            background-color: #689F38;
            color: #fff;
        }
        .btn-sidebar:hover .sidebar-label,
        .btn-sidebar:focus .sidebar-label {
            color: #fff;
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
        #sidebar .sidebar-bottom {
            margin-top: auto;
            padding-bottom: 12px;
        }
        .sidebar-toggle-btn {
            background: #33691E;
            color: #fff;
            border: none;
            width: 100%;
            height: 48px;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding: 0 12px;
            cursor: pointer;
            transition: background 0.2s;
            border-radius: 0;
        }
        #sidebar.collapsed .sidebar-toggle-btn {
            justify-content: center;
        }
        .sidebar-toggle-btn:focus {
            outline: none;
            background: #689F38;
        }
        @media (max-width: 991.98px) {
            #sidebar {
                position: fixed;
                left: -220px;
                top: 0;
                height: 100vh;
                width: 220px !important;
                min-width: 64px !important;
                max-width: 220px !important;
                z-index: 1050;
                transition: left 0.3s;
            }
            #sidebar.show {
                left: 0;
            }
            .main-content {
                margin-left: 0 !important;
            }
            .sidebar-toggle-btn {
                display: none;
            }
        }
        .btn-sidebar.logout-btn {
            background-color: #ffebee;
            color: #c62828;
            border: none;
            font-weight: 600;
            border-radius: 6px;
            margin-bottom: 6px;
            width: 100%;
            min-width: 48px;
            height: 48px;
            padding: 0 12px;
            display: flex;
            align-items: center;
            font-size: 1.3rem;
            transition: background 0.2s, color 0.2s;
            position: relative;
            overflow: hidden;
            gap: 12px;
        }
        .btn-sidebar.logout-btn:hover, .btn-sidebar.logout-btn:focus {
            background-color: #ffcdd2;
            color: #b71c1c;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <nav class="navbar navbar-expand-lg">
            <div class="container-fluid justify-content-center align-items-center">
                <button class="sidebar-toggle-header d-lg-none" id="sidebarToggleHeader" aria-label="Toggle sidebar">
                    <i class="bi bi-list"></i>
                </button>
                <span class="navbar-brand mx-auto">
                    UD. Tiga PutraKarya Sentosa
                </span>
            </div>
        </nav>
    </header>
    <!-- Layout: Sidebar & Konten Utama -->
    <div class="container-fluid">
      <div class="row flex-nowrap">
        <!-- Sidebar -->
        <div id="sidebar" class="border-end d-flex flex-column col-auto p-0">
            <button class="sidebar-toggle-btn d-none d-lg-flex" id="sidebarToggle" aria-label="Toggle sidebar">
                <i class="bi bi-chevron-double-left"></i>
            </button>
            <div class="sidebar-content flex-grow-1">
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
                <a href="{{ route('wood_prices.index') }}" class="btn btn-sidebar" title="Stel Harga">
                    <i class="bi bi-cash-coin"></i>
                    <span class="sidebar-label">Harga</span>
                </a>
            </div>
            <div class="sidebar-bottom mt-auto p-2">
                <a href="{{ route('account.index') }}" class="btn btn-sidebar" title="Manage Akun">
                    <i class="bi bi-person-circle"></i>
                    <span class="sidebar-label">Akun</span>
                </a>
                <form action="{{ route('logout') }}" method="POST" class="w-100 mt-2">
                    @csrf
                    <button type="submit" class="btn btn-sidebar logout-btn" title="Logout">
                        <i class="bi bi-box-arrow-right"></i>
                        <span class="sidebar-label">Logout</span>
                    </button>
                </form>
            </div>
        </div>
        <!-- Konten Utama -->
        <div class="main-content flex-grow-1 col p-0">
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
            if(toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    sidebar.classList.toggle('collapsed');
                    if (sidebar.classList.contains('collapsed')) {
                        icon.classList.remove('bi-chevron-double-left');
                        icon.classList.add('bi-chevron-double-right');
                    } else {
                        icon.classList.remove('bi-chevron-double-right');
                        icon.classList.add('bi-chevron-double-left');
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
        });
    </script>
    @yield('scripts')
</body>
</html>