<!DOCTYPE html>
<html>
<head>
    <title>Laporan Penjualan Pallet Mingguan</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12pt; }
        table { border-collapse: collapse; width: 100%; margin-top: 16px; }
        th, td { border: 1px solid #888; padding: 6px 10px; text-align: center; }
        th { background: #f0f0f0; }
        .total-row { font-weight: bold; background: #f5f5f5; }
    </style>
</head>
<body>
    <h3>Laporan Penjualan Pallet Mingguan</h3>
    <div>Periode: {{ $start->format('d/m/Y') }} - {{ $end->format('d/m/Y') }}</div>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Customer</th>
                <th>Total PCS</th>
                <th>Total Volume (m³)</th>
                <th>Grand Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $total_pcs_all = 0;
                $total_volume_all = 0;
                $grand_total_all = 0;
            @endphp
            @foreach($sales as $i => $sale)
            @php
                // Volume item sudah total (pcs x volume per pcs, m³), jadi cukup dijumlahkan
                $total_pcs = $sale->items->sum('pcs');
                $total_volume = $sale->items->sum('volume');
                $grand_total = $sale->items->sum('subtotal');
                $total_pcs_all += $total_pcs;
                $total_volume_all += $total_volume;
                $grand_total_all += $grand_total;
            @endphp
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ date('d/m/Y', strtotime($sale->tanggal)) }}</td>
                <td>{{ $sale->customer }}</td>
                <td>{{ number_format($total_pcs, 0, ',', '.') }}</td>
                <td>{{ number_format($total_volume, 3, ',', '.') }}</td>
                <td>Rp{{ number_format($grand_total,0,',','.') }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="3">TOTAL</td>
                <td>{{ number_format($total_pcs_all, 0, ',', '.') }}</td>
                <td>{{ number_format($total_volume_all, 3, ',', '.') }}</td>
                <td>Rp{{ number_format($grand_total_all,0,',','.') }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>