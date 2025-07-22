{{-- filepath: resources/views/exports/pallet_stock_pdf.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Stok Pallet</title>
    <style>
        body { font-family: "Times New Roman", Times, serif; font-size: 12pt; }
        .judul { font-size: 16pt; font-weight: bold; text-align: center; margin-bottom: 10px; }
        .subjudul { font-size: 13pt; font-weight: bold; margin: 10px 0 6px 0; }
        .tabel-pallet { border-collapse: collapse; width: 100%; margin-bottom: 12px; }
        .tabel-pallet th, .tabel-pallet td { border: 1px solid #888; padding: 4px 8px; text-align: center; font-size: 11pt; }
        .tabel-pallet th { background: #f0f0f0; }
        .right { text-align: right; }
        .total { font-weight: bold; background: #f5f5f5; }
        .batch-title { margin-top: 10px; margin-bottom: 2px; font-size: 12pt; font-weight: bold; }
        .batch-date { margin-bottom: 6px; font-size: 10.5pt; color: #444; }
    </style>
</head>
<body>
    <div class="judul">LAPORAN STOK PALLET</div>
    <div style="margin-bottom: 10px; font-size:11pt;">
        Dicetak pada: {{ \Carbon\Carbon::now()->format('d-m-Y H:i') }}
    </div>

    @foreach($pallets as $status => $batches)
        <div class="subjudul">Status: {{ ucfirst($status) }}</div>
        @foreach($batches as $input_code => $items)
            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2px;">
                <div class="batch-title" style="margin:0;">
                    Batch/Input Code: {{ $input_code }}
                </div>
                <div class="batch-date" style="margin:0;">
                    Tanggal Batch: 
                    {{ optional($items->first()->tanggal ?? $items->first()->created_at)->format('d-m-Y') }}
                </div>
            </div>
            <table class="tabel-pallet">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Ukuran</th>
                        <th>Pcs</th>
                        <th>Volume</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $i => $item)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td>{{ $item->ukuran ?? '-' }}</td>
                        <td>{{ $item->pcs ?? 0 }}</td>
                        <td>{{ number_format($item->volume ?? 0, 3, ',', '.') }}</td>
                    </tr>
                    @endforeach
                    <tr class="total">
                        <td colspan="2" class="right">TOTAL Batch</td>
                        <td><b>{{ $rekap[$status][$input_code]['total_pcs'] ?? 0 }}</b></td>
                        <td><b>{{ number_format($rekap[$status][$input_code]['total_volume'] ?? 0, 3, ',', '.') }}</b></td>
                    </tr>
                </tbody>
            </table>
        @endforeach
    @endforeach

</body>
</html>