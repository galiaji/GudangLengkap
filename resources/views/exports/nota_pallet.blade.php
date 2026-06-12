<!-- filepath: resources/views/exports/nota_pallet.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Nota Penjualan Pallet</title>
    <style>
        @page {
            margin-top: 10px;
            margin-bottom: 10px;
        }
        body {
            margin-top: 10px !important;
            margin-bottom: 10px !important;
        }
        body { font-family: "Times New Roman", Times, serif; font-size: 12pt; }
        .judul { font-size: 16pt; font-weight: bold; text-align: center; margin-bottom: 10px; }
        .info-table { width: 100%; border: none; margin-bottom: 10px; }
        .info-table td { border: none; padding: 0; vertical-align: top; }
        .info-kiri { text-align: left; }
        .info-kanan { text-align: right; }
        .subjudul { font-size: 13pt; font-weight: bold; margin: 10px 0 6px 0; }
        .tabel-flex { display: flex; gap: 18px; justify-content: center; align-items: flex-start; margin-bottom: 20px; }
        .tabel-pallet { border-collapse: collapse; min-width: 320px; margin-bottom: 0; }
        .tabel-pallet th, .tabel-pallet td { border: 1px solid #888; padding: 4px 8px; text-align: center; font-size: 12pt; }
        .tabel-pallet th { background: #f0f0f0; }
        .right { text-align: right; }
        .total { font-weight: bold; background: #f5f5f5; }
    </style>
</head>
<body>
    <div class="judul">NOTA PENJUALAN PALLET</div>
    <table class="info-table" style="width:100%; margin-bottom:10px;">
        <tr>
            <td class="info-kiri" style="width:60%; vertical-align:top;">
                <!-- <strong>No. Transaksi:</strong> {{ $sale->kode_transaksi ?? '-' }}<br> -->
                <strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($sale->tanggal)->format('d-m-Y') ?? '-' }}<br>
                <strong>Pelanggan:</strong> {{ $sale->customer ?? '-' }}
            </td>
            <td class="info-kanan" style="width:40%; vertical-align:top; text-align:right;">
                <strong>Total Volume:</strong> {{ number_format($total_volume, 3, ',', '.') }}<br>
                <strong>Grand Total:</strong> Rp{{ number_format($grand_total, 0, ',', '.') }}
            </td>
        </tr>
    </table>

    <table width="100%" style="margin-bottom:20px;">
        <tr>
            {{-- Pallet Basah --}}
            <td valign="top" style="width:50%;">
                @if($basah->count())
                <div class="subjudul">Pallet Basah</div>
                <table class="tabel-pallet" style="width:100%;">
                    <thead>
                        <tr>
                            <th>Ukuran</th>
                            <th>Pcs</th>
                            <th>Volume</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($basah as $item)
                        <tr>
                            <td>{{ $item->ukuran ?? '-' }}</td>
                            <td>{{ $item->pcs ?? 0 }}</td>
                            <td>{{ number_format($item->volume ?? 0, 3, ',', '.') }}</td>
                            <td class="right">Rp{{ number_format($item->subtotal ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                        <tr class="total">
                            <td colspan="2" class="right">TOTAL Basah</td>
                            <td><b>{{ number_format($total_volume_basah, 3, ',', '.') }}</b></td>
                            <td class="right"><b>Rp{{ number_format($grand_total_basah, 0, ',', '.') }}</b></td>
                        </tr>
                    </tbody>
                </table>
                @endif
            </td>
            {{-- Pallet Kering --}}
            <td valign="top" style="width:50%;">
                @if($kering->count())
                <div class="subjudul">Pallet Kering</div>
                <table class="tabel-pallet" style="width:100%;">
                    <thead>
                        <tr>
                            <th>Ukuran</th>
                            <th>Pcs</th>
                            <th>Volume</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($kering as $item)
                        <tr>
                            <td>{{ $item->ukuran ?? '-' }}</td>
                            <td>{{ $item->pcs ?? 0 }}</td>
                            <td>{{ number_format($item->volume ?? 0, 3, ',', '.') }}</td>
                            <td class="right">Rp{{ number_format($item->subtotal ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                        <tr class="total">
                            <td colspan="2" class="right">TOTAL Kering</td>
                            <td><b>{{ number_format($total_volume_kering, 3, ',', '.') }}</b></td>
                            <td class="right"><b>Rp{{ number_format($grand_total_kering, 0, ',', '.') }}</b></td>
                        </tr>
                    </tbody>
                </table>
                @endif
            </td>
        </tr>
    </table>

    <div style="margin-top:40px; text-align:right;">
        <span style="font-size:11pt;">Dicetak pada: {{ \Carbon\Carbon::now()->format('d-m-Y H:i') }}</span>
    </div>
</body>
</html>