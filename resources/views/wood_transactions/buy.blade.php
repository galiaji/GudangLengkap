@extends('layouts.app')

@section('title', 'Input Kayu')

@section('content')
@php use Illuminate\Support\Str; @endphp
<style>
    .grade-table {
        border-radius: 8px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 2px 8px #e0f2f1;
    }
    .grade-table th, .grade-table td {
        border: 1px solid #c5e1a5;
        text-align: center;
        padding: 4px 6px;
        font-size: 0.97rem;
    }
    .grade-table th {
        background: #33691E;
        color: #fff;
        font-weight: 600;
    }
    .grade-table .row-label {
        background: #f1f8e9;
        font-weight: bold;
        color: #33691E;
    }
    .grade-table .summary {
        background: #A5D6A7;
        font-weight: bold;
        color: #33691E;
    }
    .grade-table input[type="number"] {
        min-width: 60px;
        text-align: right;
    }
    .form-control:focus {
        border-color: #689F38;
        box-shadow: 0 0 0 0.1rem #c5e1a5;
    }
    .btn-primary {
        background-color: #33691E;
        border-color: #33691E;
        font-weight: 600;
        border-radius: 6px;
    }
    .btn-primary:hover, .btn-primary:focus {
        background-color: #558B2F;
        border-color: #558B2F;
    }
    h2 {
        color: #33691E;
        font-weight: 700;
        letter-spacing: 1px;
    }
    label {
        color: #33691E;
        font-weight: 600;
    }
    @media (max-width: 768px) {
        .grade-table th, .grade-table td { font-size: 0.89rem; }
        h2 { font-size: 1.2rem; }
    }
</style>

<div class="container-fluid">
    <h2 class="mb-4">Input Kayu - {{ $jenisKayu }}</h2>
    <form>
        <div class="row">
            @foreach ($grades as $grade => $diameters)
                @php
                    $allowed = $allowedLengths[$grade] ?? [];
                    $gradeSafe = str_replace([' ', '/', '\\', '.', ','], '_', $grade);
                @endphp
                <div class="col-md-6 mb-4">
                    <table class="grade-table w-100 mb-3">
                        <thead>
                            <tr>
                                <th rowspan="2" class="row-label">Grade</th>
                                <th rowspan="2" class="row-label">Diameter</th>
                                <th colspan="{{ count($allowed) }}">Panjang</th>
                            </tr>
                            <tr>
                                @foreach ($allowed as $p)
                                    <th class="row-label">{{ $p }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($diameters as $d)
                                <tr>
                                    @if ($loop->first)
                                        <td class="row-label" rowspan="{{ count($diameters) }}">{{ $grade }}</td>
                                    @endif
                                    <td>{{ $d }}</td>
                                    @foreach ($allowed as $p)
                                        <td>
                                            <input type="number" min="0" class="form-control form-control-sm pcs-input"
                                                name="pcs[{{ $grade }}][{{ $d }}][{{ $p }}]" value="0">
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                            <!-- Summary rows -->
                            <tr class="summary">
                                <td colspan="2">Total PCS</td>
                                @foreach ($allowed as $p)
                                    <td class="total-pcs" data-grade="{{ $gradeSafe }}" data-panjang="{{ $p }}">0</td>
                                @endforeach
                            </tr>
                            <tr class="summary">
                                <td colspan="2">Total Volume</td>
                                @foreach ($allowed as $p)
                                    <td class="total-volume" data-grade="{{ $gradeSafe }}" data-panjang="{{ $p }}">0.000</td>
                                @endforeach
                            </tr>
                            <tr class="summary">
                                <td colspan="2">Total Pembayaran</td>
                                @foreach ($allowed as $p)
                                    <td class="total-bayar" data-grade="{{ $gradeSafe }}" data-panjang="{{ $p }}">Rp0</td>
                                @endforeach
                            </tr>
                        </tbody>
                    </table>
                </div>
            @endforeach
        </div>
        <div class="mb-3">
            <label>Atas Nama:</label>
            <input type="text" name="atas_nama" class="form-control w-auto d-inline-block" style="width:200px;">
        </div>
        <div>
            <strong>Total Volume:</strong> <span id="grandTotalVolume">0.000</span> m³<br>
            <strong>Total Pembayaran:</strong> Rp<span id="grandTotalBayar">0</span>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Simpan</button>
    </form>
</div>

<script>
    const volumes = @json($volumes);
    const hargaPerM3 = @json($hargaPerM3);
    const grades = @json($grades);
    const allowedLengths = @json($allowedLengths);

    // Fungsi JS untuk samakan gradeSafe dengan Blade
    function gradeSafe(str) {
        return str.replace(/[\s\/\\\.,]/g, '_');
    }

    function updateSummary() {
        Object.keys(grades).forEach(function(grade) {
            let gradeSafeStr = gradeSafe(grade);
            let diameters = grades[grade];
            let allowed = allowedLengths[grade] || [];

            allowed.forEach(function(p) {
                let totalPcs = 0, totalVol = 0, totalBayar = 0;

                diameters.forEach(function(d) {
                    let input = document.querySelector('input[name="pcs['+grade+']['+d+']['+p+']"]');
                    let pcs = input ? parseInt(input.value) || 0 : 0;
                    totalPcs += pcs;

                    // Ambil volume per batang dari volumes[diameter][panjang]
                    let volPerBatang = (volumes[d] && volumes[d][p]) ? parseFloat(volumes[d][p]) : 0;
                    totalVol += pcs * volPerBatang;
                });

                // Hitung total bayar per panjang di grade ini
                let harga = (hargaPerM3[grade] && hargaPerM3[grade][p]) ? hargaPerM3[grade][p] : 0;
                totalBayar = totalVol * harga;

                // Update ke tabel
                let pcsCell = document.querySelector('.total-pcs[data-grade="'+gradeSafeStr+'"][data-panjang="'+p+'"]');
                let volCell = document.querySelector('.total-volume[data-grade="'+gradeSafeStr+'"][data-panjang="'+p+'"]');
                let bayarCell = document.querySelector('.total-bayar[data-grade="'+gradeSafeStr+'"][data-panjang="'+p+'"]');
                if (pcsCell) pcsCell.textContent = totalPcs;
                if (volCell) volCell.textContent = totalVol.toLocaleString('id-ID', {minimumFractionDigits:3, maximumFractionDigits:3});
                if (bayarCell) bayarCell.textContent = 'Rp' + totalBayar.toLocaleString('id-ID');
            });
        });
    }

    // Jalankan saat input berubah
    document.querySelectorAll('.pcs-input').forEach(input => {
        input.addEventListener('input', updateSummary);
    });

    // Jalankan saat halaman pertama kali dimuat
    document.addEventListener('DOMContentLoaded', updateSummary);
</script>
@endsection
