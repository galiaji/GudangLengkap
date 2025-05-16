@extends('layouts.app')

@section('content')
<style>
    body, html { background: #fff !important; }
    .kop-usaha {
        font-size: 1.15rem;
        font-weight: bold;
        text-align: center;
        color: #2d5c88;
        margin-bottom: 0.2rem;
        letter-spacing: 1px;
    }
    .meta-table {
        font-size: 0.90rem;
        margin-bottom: 0.5rem;
        margin-left: 0.2rem;
    }
    .meta-table td {
        padding: 1px 4px 1px 0;
        vertical-align: top;
    }
    .meta-table .label {
        color: #222;
        font-weight: 500;
        min-width: 90px;
        display: inline-block;
    }
    .transaksi-table {
        border-collapse: collapse;
        width: 100%;
        font-size: 0.78rem;
        margin: 0.5rem 0 0.7rem 0;
        table-layout: fixed;
    }
    .transaksi-table th, .transaksi-table td {
        border: 1px solid #222;
        padding: 2px 3px;
        text-align: center;
        min-width: 36px;
        max-width: 80px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .transaksi-table th {
        background: #e0e0e0;
        font-weight: 600;
        font-size: 0.80rem;
    }
    .transaksi-table td {
        font-size: 0.78rem;
    }
    .transaksi-summary {
        font-size: 0.95rem;
        margin: 0.5rem 0 0.2rem 0.2rem;
        text-align: left;
    }
    .print-btn {
        display: inline-block;
        margin: 0.2rem 0 0.5rem 0.2rem;
        color: #1976d2;
        text-decoration: underline;
        cursor: pointer;
        font-size: 0.9rem;
    }
    #transaksi-area {
        background: #fff !important;
        overflow: visible !important;
        width: 700px;
        max-width: 98vw;
        margin: 24px auto 0 auto;
        box-shadow: 0 2px 8px rgba(0,0,0,0.07);
        border-radius: 6px;
        padding: 16px 18px 18px 18px;
    }
    @media print {
        body, html { background: #fff !important; }
        .print-btn, nav, .navbar, .sidebar, .menu, #sidebar, .btn, .alert { display: none !important; }
        .kop-usaha { font-size: 1rem; }
        .meta-table, .transaksi-summary { font-size: 0.80rem; }
        .transaksi-table th, .transaksi-table td { font-size: 0.75rem; padding: 1px 3px; }
        .container, .container-fluid { width: 100% !important; margin: 0; padding: 0; }
        #transaksi-area { box-shadow: none !important; border-radius: 0 !important; padding: 0 !important; }
    }
</style>

<div id="transaksi-area" class="container-fluid">
    <div class="kop-usaha">UD. Tiga Putra Karya Sentosa</div>
    <table class="meta-table">
        <tr>
            <td class="label"><b>Tipe transaksi</b></td>
            <td>: Pembelian</td>
        </tr>
        <tr>
            <td class="label"><b>Atas nama</b></td>
            <td>: {{ $transaksi->atas_nama }}</td>
        </tr>
        <tr>
            <td class="label"><b>Tanggal dan waktu</b></td>
            <td>: {{ \Carbon\Carbon::parse($transaksi->tanggal_transaksi)->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td class="label"><b>Jenis Barang</b></td>
            <td>: {{ $transaksi->details->first()->wood_type ?? '-' }}</td>
        </tr>
    </table>

    <div style="font-size:0.93rem; margin-left:0.2rem; margin-bottom:0.2rem;"></div>
    @php
        $panjangs = $transaksi->details->pluck('panjang')->unique()->sort()->values();
        $grades = $transaksi->details->pluck('grade')->unique()->values();
        $diameters = [];
        foreach ($grades as $grade) {
            $diameters[$grade] = $transaksi->details->where('grade', $grade)->pluck('diameter')->unique()->sort()->values();
        }
    @endphp
    <div class="tabel-scroll">
    <table class="transaksi-table">
        <thead>
            <tr>
                <th rowspan="2">Grade</th>
                <th rowspan="2">Diameter</th>
                @foreach($panjangs as $p)
                    <th colspan="3">{{ $p }}</th>
                @endforeach
            </tr>
            <tr>
                @foreach($panjangs as $p)
                    <th>Jml</th>
                    <th>Vol</th>
                    <th>Rp</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($grades as $grade)
                @foreach($diameters[$grade] as $i => $diameter)
                <tr>
                    @if($i == 0)
                        <td rowspan="{{ count($diameters[$grade]) }}">{{ $grade }}</td>
                    @endif
                    <td>{{ number_format($diameter, 2, ',', '.') }}</td>
                    @foreach($panjangs as $p)
                        @php
                            $d = $transaksi->details->where('grade', $grade)->where('diameter', $diameter)->where('panjang', $p)->first();
                        @endphp
                        <td>{{ $d ? $d->pcs : 0 }}</td>
                        <td>{{ $d ? number_format($d->volume, 3, ',', '.') : '0,000' }}</td>
                        <td>{{ $d ? 'Rp'.number_format($d->harga,0,',','.') : 'Rp0' }}</td>
                    @endforeach
                </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
    </div>
    <div class="transaksi-summary">
        Grand total Volume : {{ number_format($transaksi->total_volume, 3, ',', '.') }} m³ <br>
        Grand total Pembayaran : Rp{{ number_format($transaksi->total_pembayaran, 0, ',', '.') }}
    </div>
    <a href="#" class="print-btn no-capture" onclick="window.print()">Print / PDF</a>
    <a href="#" class="print-btn no-capture" id="download-img-btn">Download Gambar</a>
</div>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script>
document.getElementById('download-img-btn').addEventListener('click', function(e) {
    e.preventDefault();
    // Sembunyikan tombol sebelum capture
    document.querySelectorAll('.no-capture').forEach(el => el.style.display = 'none');
    html2canvas(document.getElementById('transaksi-area'), {
        backgroundColor: '#fff',
        scale: 2
    }).then(function(canvas) {
        // Tampilkan kembali tombol setelah capture
        document.querySelectorAll('.no-capture').forEach(el => el.style.display = '');
        var link = document.createElement('a');
        link.download = 'transaksi.png';
        link.href = canvas.toDataURL();
        link.click();
    });
});
</script>
@endsection