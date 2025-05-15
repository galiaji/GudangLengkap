<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Gudang Kayu')</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    
    <style>
    /* Hilangkan scroll horizontal */
    body {
        overflow-x: hidden;
    }

    /* Navbar: hijau sangat terang untuk kesan natural */
    .navbar {
        background: #F1F8E9;
        border-bottom: 1px solid #C8E6C9;
    }
    .navbar-brand,
    .navbar-nav .nav-link {
        color: #2E7D32 !important;
    }
    .navbar-toggler {
        border-color: #A5D6A7;
    }

    /* Sidebar */
    #sidebar {
        width: 250px;
        background: #2E7D32;
        transition: width 0.3s ease;
        overflow: hidden;
        color: white;
    }
    #sidebar.collapsed {
        width: 0;
    }
    #sidebar .sidebar-content {
        padding: 20px;
    }
    #sidebar h5 {
        color: white;
        margin-bottom: 15px;
    }
    #sidebar .nav-link {
        color: white;
        padding: 8px 0;
    }
    #sidebar .nav-link:hover {
        color: #C8E6C9;
    }

    /* Konten utama */
    main.flex-grow-1 {
        background: #ffffff;
        min-height: 100vh;
    }

    /* Tombol sidebar */
    .btn-sidebar {
        background-color: #81C784;
        color: #2E2E2E;
        border: 2px solid #2E2E2E;
    }
    .btn-sidebar:hover {
        background-color: #66BB6A;
        color: #ffffff;
        border-color: #2E7D32;
    }

    /* Tombol toggle sidebar */
    #sidebarToggle {
        background-color: #81C784;
        color: #1B5E20;
        border: none;
    }

    #sidebarToggle:hover {
        background-color: #66BB6A;
        color: white;
    }

    /* Tombol lingkaran pengaturan */
    .setting-button {
        background-color: #4CAF50;
        color: white;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        transition: transform 0.2s ease;
        cursor: pointer;
    }

    .setting-button:hover {
        transform: rotate(15deg) scale(1.05);
        background-color: #388E3C;
    }
</style>


    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <nav class="navbar navbar-expand-lg navbar-light">
            <div class="container-fluid">
                <a class="navbar-brand" href="/dashboard">Gudang Kayu</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavHeader"
                    aria-controls="navbarNavHeader" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNavHeader">
                    <!-- Tombol toggle sidebar -->
                    <button id="sidebarToggle" class="btn btn-outline-secondary me-2" onclick="toggleSidebar()">Toggle Sidebar</button>
                    <!-- Menu navigasi header -->
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('transaksi.index') }}">Transaksi</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('wood_transactions.sell') }}">Jual Stok</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('wood_prices.index') }}">Harga Kayu</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    
    <!-- Layout: Sidebar & Konten Utama -->
    <div class="d-flex">
        <!-- Sidebar -->
        <div id="sidebar" class="border-end">
            <div class="sidebar-content">
              <h5>Menu Sidebar</h5>
<div class="d-grid gap-2">
    <a href="{{ route('wood_transactions.buy') }}" class="btn btn-sidebar">Beli Stok</a>
    <a href="{{ route('wood_transactions.sell') }}" class="btn btn-sidebar">Jual Stok</a>
    <a href="{{ route('wood_prices.index') }}" class="btn btn-sidebar">Harga Kayu</a>
    <!-- Tambahkan button lain sesuai kebutuhan -->
</div>
                       </div>
        </div>
        <!-- Konten Utama -->
        <main class="flex-grow-1 p-3">
            @yield('content')
        </main>
    </div>
    
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Script Toggle Sidebar -->
    <script>
        function toggleSidebar() {
            var sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('collapsed');
        }
    </script>
    @yield('scripts')
</body>
</html>