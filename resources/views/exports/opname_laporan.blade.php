@php
    $rp  = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
    $vol = fn ($n) => number_format((float) $n, 3, ',', '.');
    $tandai = fn ($n) => $n > 0 ? '+' . number_format($n, 0, ',', '.') : number_format($n, 0, ',', '.');
    $items  = $opname->items;
    $selisihItems = $items->filter(fn ($i) => $i->selisih !== null && $i->selisih !== 0);
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 12mm 12mm 10mm 12mm; }
        body { font-family: "Times New Roman", Times, serif; font-size: 10pt; }
        .judul { font-size: 15pt; font-weight: bold; text-align: center; margin-bottom: 2px; }
        .sub { text-align: center; margin-bottom: 10px; }
        .info td { padding: 1px 10px 1px 0; vertical-align: top; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.data th, table.data td { border: 1px solid #555; padding: 3px 5px; }
        table.data th { background: #e8f5e9; }
        .r { text-align: right; }
        .c { text-align: center; }
        .kurang { color: #b71c1c; }
        .lebih { color: #1b5e20; }
        .total td { font-weight: bold; background: #f5f5f5; }
        h3 { font-size: 11.5pt; margin: 14px 0 0 0; }
        .ttd { width: 100%; margin-top: 24px; }
        .ttd td { width: 50%; text-align: center; vertical-align: top; }
    </style>
</head>
<body>
    <div class="judul">LAPORAN STOCK OPNAME</div>
    <div class="sub">{{ $opname->cakupan }}</div>

    <table class="info">
        <tr>
            <td>Kode</td><td>: {{ $opname->kode }}</td>
            <td>Dibuat oleh</td><td>: {{ $opname->pembuat->name ?? '-' }}</td>
        </tr>
        <tr>
            <td>Tanggal</td><td>: {{ $opname->tanggal->format('d-m-Y') }}</td>
            <td>Disetujui oleh</td>
            <td>: {{ $opname->penyetuju->name ?? '-' }}{{ $opname->disetujui_at ? ' (' . $opname->disetujui_at->format('d-m-Y H:i') . ')' : '' }}</td>
        </tr>
        <tr>
            <td>Status</td><td>: {{ $opname->statusLabel() }}</td>
            <td>Item dihitung</td><td>: {{ $items->count() }} item, {{ $selisihItems->count() }} selisih</td>
        </tr>
        @if ($opname->catatan)
            <tr><td>Catatan</td><td colspan="3">: {{ $opname->catatan }}</td></tr>
        @endif
    </table>

    <h3>Rincian Selisih</h3>
    <table class="data">
        <thead>
            <tr>
                <th class="c">No</th>
                <th>Item</th>
                <th class="r">Sistem</th>
                <th class="r">Fisik</th>
                <th class="r">Selisih</th>
                <th class="r">Volume (m³)</th>
                <th class="r">Nilai</th>
                <th>Alasan</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($selisihItems as $item)
                <tr>
                    <td class="c">{{ $loop->iteration }}</td>
                    <td>{{ $item->label }}</td>
                    <td class="r">{{ number_format($item->qty_sistem, 0, ',', '.') }}</td>
                    <td class="r">{{ number_format($item->qty_fisik, 0, ',', '.') }}</td>
                    <td class="r {{ $item->selisih < 0 ? 'kurang' : 'lebih' }}">{{ $tandai($item->selisih) }}</td>
                    <td class="r">{{ $vol($item->volume_selisih) }}</td>
                    <td class="r">{{ $rp($item->nilai_selisih) }}</td>
                    <td>{{ $item->alasan ?? '-' }}</td>
                    <td>{{ $item->keterangan ?? '' }}</td>
                </tr>
            @empty
                <tr><td colspan="9" class="c">Tidak ada selisih. Semua stok sistem sesuai dengan jumlah fisik.</td></tr>
            @endforelse
            @if ($selisihItems->isNotEmpty())
                <tr class="total">
                    <td colspan="4" class="r">Total</td>
                    <td class="r">{{ $tandai($selisihItems->sum('selisih')) }}</td>
                    <td class="r">{{ $vol($selisihItems->sum('volume_selisih')) }}</td>
                    <td class="r">{{ $rp($selisihItems->sum('nilai_selisih')) }}</td>
                    <td colspan="2"></td>
                </tr>
            @endif
        </tbody>
    </table>

    <h3>Rekap Seluruh Item</h3>
    <table class="data">
        <thead>
            <tr>
                <th class="c">No</th>
                <th>Item</th>
                <th class="r">Sistem</th>
                <th class="r">Fisik</th>
                <th class="r">Selisih</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $item)
                <tr>
                    <td class="c">{{ $loop->iteration }}</td>
                    <td>{{ $item->label }}</td>
                    <td class="r">{{ number_format($item->qty_sistem, 0, ',', '.') }}</td>
                    <td class="r">{{ $item->qty_fisik === null ? '-' : number_format($item->qty_fisik, 0, ',', '.') }}</td>
                    <td class="r">{{ $item->selisih === null ? '-' : $tandai($item->selisih) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="ttd">
        <tr>
            <td>Petugas Penghitung<br><br><br><br>( {{ $opname->pembuat->name ?? '.................................' }} )</td>
            <td>Disetujui<br><br><br><br>( {{ $opname->penyetuju->name ?? '.................................' }} )</td>
        </tr>
    </table>
</body>
</html>
