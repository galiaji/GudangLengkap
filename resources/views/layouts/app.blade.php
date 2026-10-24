<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- Judul tab: "Nama Halaman | UD. TPKS" (diisi tiap halaman lewat @section('title')) --}}
    <title>@hasSection('title')@yield('title') | @endif{{ 'UD. TPKS' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon-tpks.png') }}">
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @stack('styles')
    <style>
        :root {
            --hijau-tua: #33691E;
            --hijau: #388E3C;
            --hijau-muda: #A5D6A7;
            --hijau-hover: #689F38;
            --latar: #F1F8E9;
            --header-h: 56px;
            --sidebar-w: 210px;
            --sidebar-w-kecil: 64px;
        }

        /* ---------- Kerangka halaman ---------- */
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
            background: var(--latar);
            overflow-x: hidden;
        }
        .app-header {
            position: sticky;
            top: 0;
            z-index: 1030;
            height: var(--header-h);
            background: var(--hijau-tua);
            border-bottom: 2px solid var(--hijau-muda);
            box-shadow: 0 1px 4px rgba(0, 0, 0, .15);
        }
        .app-header .navbar { height: 100%; padding: 0 16px; }
        .app-logo {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: #fff;
            padding: 2px;
            object-fit: contain;
            flex-shrink: 0;
            margin-right: 10px;
        }
        .app-brand {
            display: flex;
            align-items: center;
            color: #fff;
            font-size: 1.35rem;
            font-weight: 700;
            letter-spacing: .5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            min-width: 0;
        }
        .app-shell {
            display: flex;
            flex: 1 0 auto;
            align-items: stretch;
        }
        .app-main {
            flex: 1 1 auto;
            min-width: 0; /* agar tabel lebar tidak mendorong layout */
            padding: 16px;
        }
        .app-footer {
            background: var(--hijau-tua);
            color: #fff;
            font-size: .95rem;
            letter-spacing: .5px;
            text-align: center;
            padding: 14px 8px;
        }

        /* ---------- Tombol menu di header (HP/tablet) ---------- */
        .btn-menu-header {
            display: none;
            background: none;
            border: 0;
            color: #fff;
            font-size: 1.6rem;
            line-height: 1;
            padding: 4px 8px 4px 0;
        }

        /* ---------- Sidebar ---------- */
        #sidebar {
            position: sticky;
            top: var(--header-h);
            flex: 0 0 var(--sidebar-w);
            width: var(--sidebar-w);
            height: calc(100vh - var(--header-h));
            overflow-y: auto;
            overflow-x: hidden;
            background: var(--hijau);
            border-right: 2px solid var(--hijau-muda);
            display: flex;
            flex-direction: column;
            transition: flex-basis .25s, width .25s, transform .25s;
            z-index: 1020;
        }
        .sidebar-top {
            display: flex;
            justify-content: flex-end;
            padding: 8px 8px 0;
        }
        .btn-ciutkan {
            background: transparent;
            border: 0;
            color: #fff;
            width: 36px;
            height: 36px;
            border-radius: 8px;
            font-size: 1.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .btn-ciutkan:hover { background: rgba(255, 255, 255, .15); }
        .sidebar-menu, .sidebar-bottom {
            display: flex;
            flex-direction: column;
            gap: 6px;
            padding: 4px 10px;
        }
        .sidebar-menu { flex: 1 0 auto; }
        .sidebar-bottom { padding-bottom: 14px; padding-top: 10px; }
        .sidebar-section {
            color: var(--hijau-muda);
            font-size: .78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 10px 6px 2px;
            white-space: nowrap;
        }
        .sidebar-divider {
            height: 1px;
            background: rgba(255, 255, 255, .25);
            margin: 8px 2px 0;
        }
        .btn-sidebar {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            height: 40px;
            padding: 0 10px;
            border: 0;
            border-radius: 8px;
            background: var(--hijau-muda);
            color: var(--hijau-tua);
            font-weight: 600;
            font-size: .95rem;
            text-align: left;
            text-decoration: none;
            transition: background .2s, color .2s;
        }
        .btn-sidebar i { font-size: 1.1rem; flex-shrink: 0; width: 20px; text-align: center; }
        .btn-sidebar .ikon-svg { width: 20px; height: 20px; flex-shrink: 0; }
        .sidebar-label {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .btn-sidebar:hover, .btn-sidebar:focus-visible {
            background: var(--hijau-hover);
            color: #fff;
        }
        .btn-sidebar.active {
            background: #fff;
            color: var(--hijau-tua);
            box-shadow: inset 4px 0 0 var(--hijau-tua);
        }

        /* Sidebar diciutkan (desktop) */
        #sidebar.collapsed {
            flex-basis: var(--sidebar-w-kecil);
            width: var(--sidebar-w-kecil);
        }
        #sidebar.collapsed .sidebar-top { justify-content: center; padding: 8px 0 0; }
        #sidebar.collapsed .sidebar-menu,
        #sidebar.collapsed .sidebar-bottom { padding-left: 10px; padding-right: 10px; }
        #sidebar.collapsed .btn-sidebar { justify-content: center; padding: 0; }
        #sidebar.collapsed .sidebar-label,
        #sidebar.collapsed .sidebar-section { display: none; }

        /* Latar gelap di belakang sidebar (HP/tablet) */
        #sidebarOverlay {
            display: none;
            position: fixed;
            top: var(--header-h);
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, .35);
            z-index: 1040;
        }
        #sidebarOverlay.active { display: block; }

        /* ---------- HP & tablet ---------- */
        @media (max-width: 991.98px) {
            :root { --header-h: 50px; }
            .app-header .navbar { padding: 0 10px; }
            .app-brand { font-size: 1.05rem; }
            .app-logo { width: 32px; height: 32px; margin-right: 8px; }
            .btn-menu-header { display: inline-flex; }
            .sidebar-top { display: none; }
            #sidebar {
                position: fixed;
                left: 0;
                top: var(--header-h);
                width: min(260px, 80vw);
                height: calc(100vh - var(--header-h));
                transform: translateX(-100%);
                z-index: 1045;
                box-shadow: 2px 0 10px rgba(0, 0, 0, .2);
            }
            #sidebar.show { transform: translateX(0); }
            /* mode ciut hanya untuk desktop */
            #sidebar.collapsed { width: min(260px, 80vw); }
            #sidebar.collapsed .sidebar-label,
            #sidebar.collapsed .sidebar-section { display: initial; }
            #sidebar.collapsed .btn-sidebar { justify-content: flex-start; padding: 0 10px; }
            .app-main { padding: 12px 8px; }
        }
        @media (max-width: 575.98px) {
            .app-brand { font-size: .95rem; }
            .app-footer { font-size: .8rem; }
        }

        /* ---------- Cetak: hanya konten utama ---------- */
        @media print {
            .app-header, #sidebar, #sidebarOverlay, .app-footer { display: none !important; }
            body { background: #fff; }
            .app-main { padding: 0; }
        }

        /* ---------- Card transaksi (dipakai beberapa halaman) ---------- */
        .transaksi-card {
            border-radius: 10px;
            box-shadow: 0 2px 8px #e0f2f1;
            background: #f5fbe8;
            width: 100%;
            margin-top: 32px;
            margin-bottom: 24px;
            padding: 32px 24px 24px 24px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }
        .transaksi-card .card-header {
            background: var(--hijau-tua);
            color: #fff;
            font-weight: 700;
            font-size: 1.3rem;
            letter-spacing: 1px;
            border-bottom: 1px solid var(--hijau-muda);
            text-align: center;
            padding: 22px 0;
            margin-bottom: 24px;
            border-radius: 8px 8px 0 0;
        }
        .transaksi-card .card-body {
            padding: 0;
            background: inherit;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        @media (max-width: 991.98px) {
            .transaksi-card { padding: 8px 2px 2px 2px; }
        }
        @media (max-width: 575.98px) {
            .transaksi-card { padding: 4px 0 0 0; }
        }
    </style>
</head>
<body>
    @php
        $isAdmin = auth()->user() && auth()->user()->role === 'admin';
        $aktif = fn (...$routes) => request()->routeIs(...$routes) ? 'active' : '';
    @endphp

    <!-- Header -->
    <header class="app-header">
        <nav class="navbar">
            <div class="d-flex align-items-center w-100 gap-2">
                <button class="btn-menu-header" id="sidebarToggleHeader" type="button" aria-label="Buka menu">
                    <i class="bi bi-list"></i>
                </button>
                <span class="app-brand flex-grow-1">
                    <img src="{{ asset('images/logo-emblem.png') }}" alt="Logo UD. TPKS" class="app-logo">
                    <span class="text-truncate">UD. Tiga PutraKarya Sentosa</span>
                </span>
                <!-- Dropdown Profil -->
                <div class="dropdown flex-shrink-0">
                    <button class="btn btn-link dropdown-toggle d-flex align-items-center text-white text-decoration-none fw-semibold px-1"
                            type="button" id="dropdownProfile" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="me-2 d-none d-sm-inline">{{ auth()->user()->name ?? '' }}</span>
                        <i class="bi bi-person-circle fs-4 lh-1"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownProfile">
                        <li class="px-3 py-2">
                            <div class="fw-semibold">{{ auth()->user()->name ?? '' }}</div>
                            <div class="small" style="color: var(--hijau-tua);">{{ auth()->user()->email ?? '' }}</div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="{{ route('account.profile') }}">
                                <i class="bi bi-person-circle me-2"></i> Profil Saya
                            </a>
                        </li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <!-- Sidebar & Konten Utama -->
    <div class="app-shell">
        <aside id="sidebar">
            <div class="sidebar-top">
                <button class="btn-ciutkan" id="sidebarToggle" type="button" title="Ciutkan / lebarkan menu" aria-label="Ciutkan menu">
                    <i class="bi bi-chevron-double-left"></i>
                </button>
            </div>

            <nav class="sidebar-menu">
                <!-- Log Kayu -->
                <div class="sidebar-section">Log Kayu</div>
                <a href="{{ route('dashboard') }}" class="btn-sidebar {{ $aktif('dashboard', 'stock.detail') }}" title="Stock">
                    <i class="bi bi-clipboard-data"></i><span class="sidebar-label">Stock</span>
                </a>
                <a href="{{ route('wood_transactions.buy') }}" class="btn-sidebar {{ $aktif('wood_transactions.buy') }}" title="Beli">
                    <i class="bi bi-cart-plus"></i><span class="sidebar-label">Beli</span>
                </a>
                <a href="{{ route('stock.potongGergaji.form', ['woodType' => 'kayu keras']) }}" class="btn-sidebar {{ $aktif('stock.potongGergaji.form') }}" title="Potong/Gergaji">
                    {{-- Ikon gergaji tangan (Bootstrap Icons tidak punya ikon gergaji) --}}
                    <svg class="ikon-svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <g transform="translate(12 12) scale(.86) rotate(-35) translate(-11.9 -12)">
                            <path fill-rule="evenodd" d="M0 7.5h5.2a1.3 1.3 0 0 1 1.3 1.3v6.4a1.3 1.3 0 0 1-1.3 1.3H2.4A2.9 2.9 0 0 1-.5 13.6V8a.5.5 0 0 1 .5-.5zm1.3 2.2v3.4c0 .8.6 1.4 1.4 1.4h2V9.7z"/>
                            <path d="M6.9 8.4 24.6 10.3v1.7l-.95 1.16-.95-.84l-.95 1.16-.95-.84l-.95 1.16-.95-.84l-.95 1.16-.95-.84l-.95 1.16-.95-.84l-.95 1.16-.95-.84l-.95 1.16-.95-.84l-.95 1.16-.95-.84l-.95 1.16-.95-.84l-.95 1.16-.95-.84L6.9 15.5z"/>
                        </g>
                    </svg><span class="sidebar-label">Potong/Gergaji</span>
                </a>
                <a href="{{ route('wood_transactions.sell') }}" class="btn-sidebar {{ $aktif('wood_transactions.sell') }}" title="Jual">
                    <i class="bi bi-cart-check"></i><span class="sidebar-label">Jual</span>
                </a>
                <a href="{{ route('transaksi.index') }}" class="btn-sidebar {{ $aktif('transaksi.*') }}" title="Transaksi">
                    <i class="bi bi-list-ul"></i><span class="sidebar-label">Transaksi</span>
                </a>
                @if ($isAdmin)
                    <a href="{{ route('wood_prices.index') }}" class="btn-sidebar {{ $aktif('wood_prices.*') }}" title="Harga Kayu">
                        <i class="bi bi-cash-coin"></i><span class="sidebar-label">Harga Kayu</span>
                    </a>
                    <a href="{{ route('stok.range.create') }}" class="btn-sidebar {{ $aktif('stok.range.*') }}" title="Tambah Rentang Stok">
                        <i class="bi bi-plus-square"></i><span class="sidebar-label">Tambah Rentang Stok</span>
                    </a>
                    {{-- 
                    <a href="{{ route('wood-types.index') }}" class="btn-sidebar {{ $aktif('wood-types.*') }}" title="Jenis Kayu">
                        <i class="bi bi-tree"></i><span class="sidebar-label">Jenis Kayu</span>
                    </a>
                    --}}
                @endif

                <div class="sidebar-divider"></div>

                <!-- Palet -->
                <div class="sidebar-section">Palet</div>
                <a href="{{ route('pallet_stocks.index') }}" class="btn-sidebar {{ $aktif('pallet_stocks.index') }}" title="Stock Palet">
                    <i class="bi bi-clipboard-data"></i><span class="sidebar-label">Stock Palet</span>
                </a>
                <a href="{{ route('pallet_stocks.create') }}" class="btn-sidebar {{ $aktif('pallet_stocks.create') }}" title="Tambah Stok Palet">
                    <i class="bi bi-plus-square"></i><span class="sidebar-label">Tambah Stok Palet</span>
                </a>
                @if ($isAdmin)
                    <a href="{{ route('pallet-prices.index') }}" class="btn-sidebar {{ $aktif('pallet-prices.*') }}" title="Harga Palet">
                        <i class="bi bi-cash-coin"></i><span class="sidebar-label">Harga Palet</span>
                    </a>
                @endif
                <a href="{{ route('pallet-sales.index') }}" class="btn-sidebar {{ $aktif('pallet-sales.*') }}" title="Penjualan Palet">
                    <i class="bi bi-receipt"></i><span class="sidebar-label">Penjualan Palet</span>
                </a>

                {{-- Gudang (Stock Opname) - disembunyikan dulu, hapus komentar ini untuk menampilkan
                <div class="sidebar-divider"></div>
                <div class="sidebar-section">Gudang</div>
                <a href="{{ route('stock-opname.index') }}" class="btn-sidebar {{ $aktif('stock-opname.*') }}" title="Stock Opname">
                    <i class="bi bi-clipboard-check"></i><span class="sidebar-label">Stock Opname</span>
                </a>
                --}}
            </nav>

            <div class="sidebar-bottom">
                <div class="sidebar-divider mb-2"></div>
                @if ($isAdmin)
                    <a href="{{ route('account.index') }}" class="btn-sidebar {{ $aktif('account.index', 'account.create', 'account.edit') }}" title="Manage Account">
                        <i class="bi bi-person-circle"></i><span class="sidebar-label">Manage Account</span>
                    </a>
                @endif
                <form action="{{ route('logout') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="btn-sidebar" title="Logout">
                        <i class="bi bi-box-arrow-right"></i><span class="sidebar-label">Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Latar gelap saat sidebar terbuka di HP -->
        <div id="sidebarOverlay"></div>

        <!-- Konten Utama -->
        <main class="app-main">
            @auth
                @php $opnameAktif = \App\Models\StockOpname::aktif()->orderBy('id')->get(); @endphp
                @if ($opnameAktif->isNotEmpty())
                    <div class="alert alert-warning d-flex align-items-start gap-2 py-2">
                        <i class="bi bi-lock-fill mt-1"></i>
                        <div>
                            <strong>Stock opname sedang berlangsung.</strong>
                            Transaksi untuk stok berikut dikunci sementara:
                            @foreach ($opnameAktif as $oa)
                                <a href="{{ route('stock-opname.show', $oa) }}" class="alert-link">{{ $oa->cakupan }}</a>
                                ({{ $oa->kode }}){{ $loop->last ? '.' : ',' }}
                            @endforeach
                        </div>
                    </div>
                @endif
            @endauth
            @yield('content')
        </main>
    </div>

    <!-- Footer -->
    <footer class="app-footer">
        &copy; {{ date('Y') }} UD. Tiga PutraKarya Sentosa &mdash; Sistem Pembukuan Stok Kayu
    </footer>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var sidebar = document.getElementById('sidebar');
            var overlay = document.getElementById('sidebarOverlay');
            var btnCiutkan = document.getElementById('sidebarToggle');
            var btnHeader = document.getElementById('sidebarToggleHeader');
            var layarKecil = function () { return window.innerWidth < 992; };

            // --- Desktop: ciutkan / lebarkan, diingat di browser ---
            function setCiut(ciut) {
                sidebar.classList.toggle('collapsed', ciut);
                var icon = btnCiutkan.querySelector('i');
                icon.className = ciut ? 'bi bi-chevron-double-right' : 'bi bi-chevron-double-left';
            }
            try { setCiut(localStorage.getItem('sidebarCiut') === '1'); } catch (e) {}
            btnCiutkan.addEventListener('click', function () {
                var ciut = !sidebar.classList.contains('collapsed');
                setCiut(ciut);
                try { localStorage.setItem('sidebarCiut', ciut ? '1' : '0'); } catch (e) {}
            });

            // --- HP/tablet: buka / tutup sidebar ---
            function setBuka(buka) {
                sidebar.classList.toggle('show', buka);
                overlay.classList.toggle('active', buka);
            }
            btnHeader.addEventListener('click', function () {
                setBuka(!sidebar.classList.contains('show'));
            });
            overlay.addEventListener('click', function () { setBuka(false); });
            sidebar.querySelectorAll('a.btn-sidebar').forEach(function (link) {
                link.addEventListener('click', function () {
                    if (layarKecil()) setBuka(false);
                });
            });
            window.addEventListener('resize', function () {
                if (!layarKecil()) setBuka(false);
            });
        });
    </script>
    @yield('scripts')
</body>
</html>
