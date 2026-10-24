<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 15mm 12mm 12mm 15mm; }
        body { font-family: "Times New Roman", Times, serif; font-size: 10.5pt; }
        .judul { font-size: 15pt; font-weight: bold; text-align: center; margin-bottom: 2px; }
        .sub { text-align: center; margin-bottom: 12px; }
        .info td { padding: 1px 8px 1px 0; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data th, table.data td { border: 1px solid #555; padding: 4px 6px; }
        table.data th { background: #e8f5e9; }
        .no { width: 34px; text-align: center; }
        .isi { width: 120px; }
        .catatan { width: 150px; }
        .ttd { width: 100%; margin-top: 28px; }
        .ttd td { width: 50%; text-align: center; vertical-align: top; }
        .petunjuk { font-size: 9pt; margin-top: 6px; }
    </style>
</head>
<body>
    <div class="judul">LEMBAR HITUNG STOCK OPNAME</div>
    <div class="sub">{{ $opname->cakupan }}</div>

    <table class="info">
        <tr><td>Kode</td><td>: {{ $opname->kode }}</td></tr>
        <tr><td>Tanggal</td><td>: {{ $opname->tanggal->format('d-m-Y') }}</td></tr>
        <tr><td>Petugas</td><td>: ...........................................</td></tr>
    </table>

    <div class="petunjuk">
        Hitung jumlah fisik setiap item (pcs) lalu tulis di kolom "Jumlah Fisik". Tulis 0 jika barang tidak ada.
    </div>

    <table class="data">
        <thead>
            <tr>
                <th class="no">No</th>
                <th>Item</th>
                <th class="isi">Jumlah Fisik (pcs)</th>
                <th class="catatan">Catatan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($opname->items as $item)
                <tr>
                    <td class="no">{{ $loop->iteration }}</td>
                    <td>{{ $item->label }}</td>
                    <td></td>
                    <td></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="ttd">
        <tr>
            <td>Petugas Penghitung<br><br><br><br>(.................................)</td>
            <td>Mengetahui<br><br><br><br>(.................................)</td>
        </tr>
    </table>
</body>
</html>
