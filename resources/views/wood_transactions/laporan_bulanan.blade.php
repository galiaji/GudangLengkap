<!-- <!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan Hari Ini</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; }
        h3 { margin-bottom: 20px; }
        .btn-print { display: inline-block; margin-bottom: 20px; padding: 8px 16px; background: #198754; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
        .btn-print:active { background: #145c32; }
        @media print {
            .btn-print { display: none !important; }
        }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #aaa; padding: 8px; text-align: left; }
        th { background: #f3f3f3; }
    </style>
</head>
<body>
<h2>Laporan Tahunan: {{ $tahun }}</h2>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>No.</th>
            <th>Jenis Transaksi</th>
            <th>Atas Nama</th>
            <th>Volume (m³)</th>
            <th>Total Pembayaran</th>
            <th>Tanggal</th>
        </tr>
    </thead>
    <tbody>
        @forelse($transaksi as $index => $item)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ ucfirst($item->jenis_transaksi ?? '') }}</td>
            <td>{{ $item->atas_nama ?? '' }}</td>
            <td>{{ number_format((float)($item->total_volume ?? 0), 3, ',', '.') }}</td>
            <td>Rp{{ number_format((float)($item->total_pembayaran ?? 0), 0, ',', '.') }}</td>
            <td>{{ \Carbon\Carbon::parse($item->tanggal_transaksi ?? $item->created_at)->format('d-m-Y H:i') }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="6" class="text-center">Tidak ada transaksi tahun ini.</td>
        </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <th colspan="3" style="text-align:right;">Total</th>
            <th>{{ number_format($total_volume, 3, ',', '.') }}</th>
            <th>Rp{{ number_format($total_pembayaran, 0, ',', '.') }}</th>
            <th></th>
        </tr>
    </tfoot>
</table>
</body>
</html> -->