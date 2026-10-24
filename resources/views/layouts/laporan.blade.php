<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Laporan Keuangan') | UD. TPKS</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon-tpks.png') }}">
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; }
        h2, h3 { margin-bottom: 20px; }
        .btn-print { display: inline-block; margin-bottom: 20px; padding: 8px 16px; background: #198754; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
        .btn-print:active { background: #145c32; }
        @media print {
            .btn-print { display: none !important; }
        }
        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }
        table { border-collapse: collapse; width: 100%; min-width: 600px; }
        th, td { border: 1px solid #aaa; padding: 8px; text-align: left; }
        th { background: #f3f3f3; }
        @media (max-width: 767.98px) {
            body { margin: 6px; font-size: 0.95rem; }
            h2, h3 { font-size: 1.1rem; margin-bottom: 12px; }
            .btn-print { padding: 6px 10px; font-size: 0.95rem; }
            th, td { padding: 5px; font-size: 0.90rem; }
            table { min-width: 480px; font-size: 0.95rem; }
        }
        @media (max-width: 480px) {
            body { margin: 2px; font-size: 0.90rem; }
            th, td { padding: 3px; font-size: 0.85rem; }
            table { min-width: 360px; font-size: 0.90rem; }
        }
    </style>
    @stack('head')
</head>
<body>
    <button class="btn-print" onclick="window.print()">Print</button>
    @yield('content')
</body>
</html>