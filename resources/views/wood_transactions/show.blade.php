@extends('layouts.app')

@section('title', 'Detail Transaksi')

@section('content')

<style>
    @page {
        size: A4 landscape;
        margin: 8mm;
    }

    body,
    html {
        background: #F1F8E9 !important;
        font-family: Arial, sans-serif;
    }

    #transaksi-area {
        background: #fff;
        width: 1123px;
        min-height: 794px;
        margin: 20px auto;
        padding: 15px;
        border: 1px solid #222;
        border-radius: 6px;
        box-shadow: 0 2px 8px #ddd;
    }

    .kop-usaha {
        font-size: 19px;
        font-weight: bold;
        text-align: center;
        color: #111;
        margin-bottom: 8px;
        letter-spacing: 1px;
    }

    .meta-table {
        width: 100%;
        font-size: 14px;
        margin-bottom: 10px;
    }

    .meta-table td {
        padding: 2px 4px 2px 0;
        vertical-align: top;
    }

    .meta-table .label {
        width: 145px;
    min-width: 145px;
    font-weight: 600;
    white-space: nowrap;
    padding-right: 5px;
    }

    .tabel-scroll {
        width: 100%;
        overflow-x: auto;
    }

    .transaksi-table {
        width: 100%;
        table-layout: auto;
        border-collapse: separate;
        border-spacing: 0;
        background: #fff;
        font-size: 14px;
    }

    .transaksi-table th,
        
    /* ===== TABEL TRANSAKSI ===== */
    .transaksi-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        background: #fff;
        font-size: 12px;
    }

    /* ===== HEADER ===== */
    .transaksi-table th {
        background: #E8F5E9;
        font-weight: 700;
        font-size: 14px;
        text-align: center;
        vertical-align: middle;
        border: 1px solid #222;
        padding: 4px 5px;
        white-space: normal;
        overflow-wrap: break-word;
    }

    /* ===== CELL UMUM ===== */
    .transaksi-table td {
        border: 1px solid #222;
        padding: 4px 5px;
        text-align: center;
        vertical-align: middle;
        white-space: nowrap;
        overflow: visible;
        text-overflow: clip;
        font-variant-numeric: tabular-nums;
        position: relative;
    }

    /* ===== WARNA BARIS SELANG-SELING ===== */
    .transaksi-table tbody tr:nth-child(even) {
        background: #fafafa;
    }

    /* ===== KOLOM GRADE ===== */
    .transaksi-table th:first-child,
    .transaksi-table td:first-child {
        width: 90px;
    }

    .transaksi-table .grade-cell {
        width: 90px;
        min-width: 90px;
        background: #f0f0f0 !important;
        font-weight: bold;
        text-align: center !important;
        vertical-align: middle !important;
        white-space: normal !important;
        overflow-wrap: break-word;
        padding: 8px 6px !important;
        position: relative;
        z-index: 2;
    }

    /* ===== CELL DENGAN ROWSPAN ===== */
    .transaksi-table td[rowspan] {
        background: #f0f0f0 !important;
        vertical-align: middle !important;
        position: relative;
        z-index: 2;
    }

    /* ===== KOLOM DIAMETER ===== */
    .transaksi-table th:nth-child(2),
    .transaksi-table td:nth-child(2) {
        width: 55px;
        text-align: center;
    }

    /* ===== HEADER KELOMPOK ===== */
    .transaksi-table th[colspan="3"] {
        text-align: center;
    }

    /* ===== KOLOM JUMLAH ===== */
    .transaksi-table .col-jml {
        width: 45px;
        text-align: center;
    }

    /* ===== KOLOM VOLUME ===== */
    .transaksi-table .col-vol {
        width: 70px;
        text-align: center;
    }

    /* ===== KOLOM RUPIAH ===== */
    .transaksi-table .col-rp {
        width: 95px;
        text-align: left;
    }

    /* ===== FORMAT NILAI UANG ===== */
    .transaksi-table td.money,
    .transaksi-table td.money * {
        text-align: left !important;
    }

    .transaksi-table .money {
        padding-left: 8px !important;
        white-space: nowrap;
    }

    /* ===== RINGKASAN TOTAL ===== */
    .transaksi-summary {
        margin-top: 12px;
        font-size: 16px;
        font-weight: 600;
        color: #111;
    }
    @media print {

        html,
        body {
            width: 297mm;
            height: 210mm;
            background: #fff !important;
        }

        .print-btn,
        nav,
        .navbar,
        .sidebar,
        .menu,
        #sidebar,
        .btn,
        .alert,
        .no-capture {
            display: none !important;
        }

        #transaksi-area {
            width: 100%;
            min-height: auto;
            margin: 0;
            padding: 0;
            border: none;
            box-shadow: none;
        }

        .transaksi-table tr {
            page-break-inside: avoid;
        }

        .transaksi-table th,
        .transaksi-table td {
            font-size: 14px;
            padding: 2px 3px;
        }

        .kop-usaha {
            font-size: 16px;
        }

        .meta-table,
        .transaksi-summary {
            font-size: 16px;
        }
    }
</style>
<div id="transaksi-area" class="container-fluid">
    <div class="kop-usaha">UD. Tiga Putra Karya Sentosa</div>
    <table class="meta-table">
        <tr>
            <td class="label"><b>Tipe Transaksi</b></td>
            <td>: {{ $transaksi->jenis_transaksi }}</td>
        </tr>
        <tr>
            <td class="label"><b>Atas Nama</b></td>
            <td>: {{ $transaksi->atas_nama }}</td>
        </tr>
        <tr>
            <td class="label"><b>Tanggal dan Waktu</b></td>
            <td>: {{ \Carbon\Carbon::parse($transaksi->tanggal_transaksi)->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td class="label"><b>Jenis Barang</b></td>
            <td>: 
                @php
                    $jenisBarang = $transaksi->details->pluck('wood_type_name')->unique()->implode(', ');
                @endphp
                {{ $jenisBarang ?: '-' }}
            </td>
        </tr>
    </table>

    <div style="font-size:1rem; margin-left:0.2rem; margin-bottom:0.2rem;"></div>
    @php
        $panjangs = $transaksi->details->pluck('panjang')->unique()->sort()->values();
        $grades = $transaksi->details->pluck('grade')->unique()->values();
        $woodTypes = $transaksi->details->pluck('wood_type_name')->unique()->values();
        $diameters = [];
        foreach ($grades as $grade) {
            $diameters[$grade] = $transaksi->details->where('grade', $grade)->pluck('diameter')->unique()->sort()->values();
        }
    @endphp
    <div class="tabel-scroll">
    <table class="transaksi-table">
        <!-- <thead>
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
        </thead> -->
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
                    <th class="col-jml">Jml</th>
                    <th class="col-vol">Vol</th>
                    <th class="col-rp">Nilai</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($grades as $grade)

                @php
                    $rowspan = count($diameters[$grade]);
                @endphp

                @foreach($diameters[$grade] as $i => $diameter)

                <tr>

                    @if($i == 0)
                        <td rowspan="{{ $rowspan }}" class="grade-cell">
                            {{ $grade }}
                        </td>
                    @endif

                    <td>{{ number_format($diameter, 0, ',', '.') }}</td>

                    @foreach($panjangs as $p)

                        @php
                            $d = $transaksi->details
                                ->where('grade', $grade)
                                ->where('diameter', $diameter)
                                ->where('panjang', $p)
                                ->first();
                        @endphp

                        <td>{{ $d ? $d->pcs : 0 }}</td>

                        <td>
                            {{ $d
                                ? number_format($d->volume, 3, ',', '.')
                                : '0,000' }}
                        </td>

                        <td class="money">
                            Rp. {{ number_format(
                                $d->total_bayar_per_diameter ?? $d->harga ?? 0,
                                0,',','.'
                            ) }}
                        </td>

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
<script src="{{ asset('js/html2canvas.min.js') }}"></script>
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

        var isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);
        if (isMobile) {
            // Tampilkan gambar hasil capture di bawah tombol
            var img = document.createElement('img');
            img.src = canvas.toDataURL();
            img.style.maxWidth = '100%';
            img.style.margin = '12px 0';
            img.alt = 'Hasil Capture';
            img.id = 'hasil-capture-img';

            // Hapus gambar sebelumnya jika ada
            var prevImg = document.getElementById('hasil-capture-img');
            if (prevImg) prevImg.remove();

            // Tampilkan instruksi
            var instruksi = document.createElement('div');
            instruksi.innerText = 'Tekan & tahan gambar lalu pilih "Download" atau "Simpan Gambar".';
            instruksi.style.fontSize = '0.95rem';
            instruksi.style.marginBottom = '8px';
            instruksi.style.color = '#33691E';
            instruksi.id = 'instruksi-capture';

            var prevInstruksi = document.getElementById('instruksi-capture');
            if (prevInstruksi) prevInstruksi.remove();

            var btn = document.getElementById('download-img-btn');
            btn.parentNode.insertBefore(instruksi, btn.nextSibling);
            btn.parentNode.insertBefore(img, instruksi.nextSibling);
        } else {
            // Desktop: download otomatis
            var link = document.createElement('a');
            link.download = 'transaksi.png';
            link.href = canvas.toDataURL();
            link.click();
        }
    });
});
</script>
@endsection