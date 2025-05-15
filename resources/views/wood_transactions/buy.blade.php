@extends('layouts.app')

@section('title', 'Input Kayu')

@section('content')
<style>
    .grade-table th, .grade-table td {
        border: 1px solid #888;
        text-align: center;
        padding: 4px 6px;
    }
    .grade-table th {
        background: #888;
        color: #fff;
    }
    .grade-table .row-label {
        background: #eee;
        font-weight: bold;
    }
    .grade-table .summary {
        background: #b2ffb2;
        font-weight: bold;
    }
</style>

<div class="container-fluid">
    <h2 class="mb-4">Input Kayu - {{ $jenisKayu }}</h2>
    <form>
        <div class="row">
            @foreach ($grades as $grade => $diameters)
                @php
                    $prefix = explode(' ', $grade)[0];
                    $allowed = $allowedLengths[$prefix] ?? [];
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
                                    <td class="total-pcs" data-grade="{{ $grade }}" data-panjang="{{ $p }}">0</td>
                                @endforeach
                            </tr>
                            <tr class="summary">
                                <td colspan="2">Total Volume</td>
                                @foreach ($allowed as $p)
                                    <td class="total-volume" data-grade="{{ $grade }}" data-panjang="{{ $p }}">0.000</td>
                                @endforeach
                            </tr>
                            <tr class="summary">
                                <td colspan="2">Total Pembayaran</td>
                                @foreach ($allowed as $p)
                                    <td class="total-bayar" data-grade="{{ $grade }}" data-panjang="{{ $p }}">Rp0</td>
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
    // Data volume per batang dari backend
    const volumes = @json($volumes);

    // Fungsi hitung volume: jumlah pcs * volume per batang
    function hitungVolume(grade, diameter, pcs) {
        let volPerBatang = (volumes[grade] && volumes[grade][diameter]) ? parseFloat(volumes[grade][diameter]) : 0;
        return pcs * volPerBatang;
    }

    function updateSummary() {
        let grandVolume = 0;
        let grandBayar = 0;

        @foreach ($grades as $grade => $diameters)
            @php $prefix = explode(' ', $grade)[0]; $allowed = $allowedLengths[$prefix] ?? []; @endphp
            @foreach ($allowed as $p)
                let totalPcs = 0, totalVol = 0, totalBayar = 0;
                @foreach ($diameters as $d)
                    let input = document.querySelector('input[name="pcs[{{ $grade }}][{{ $d }}][{{ $p }}]"]');
                    let pcs = input ? parseInt(input.value) || 0 : 0;
                    totalPcs += pcs;
                    // Ambil volume per batang dari tabel volumes
                    let vol = hitungVolume("{{ $grade }}", {{ $d }}, pcs);
                    totalVol += vol;
                    totalBayar += vol * (hargaPerM3["{{ $grade }}"] || 0);
                @endforeach
                document.querySelector('.total-pcs[data-grade="{{ $grade }}"][data-panjang="{{ $p }}"]').textContent = totalPcs;
                document.querySelector('.total-volume[data-grade="{{ $grade }}"][data-panjang="{{ $p }}"]').textContent = totalVol.toLocaleString('id-ID', {minimumFractionDigits:3, maximumFractionDigits:3});
                document.querySelector('.total-bayar[data-grade="{{ $grade }}"][data-panjang="{{ $p }}"]').textContent = 'Rp' + totalBayar.toLocaleString('id-ID');
                grandVolume += totalVol;
                grandBayar += totalBayar;
            @endforeach
        @endforeach

        document.getElementById('grandTotalVolume').textContent = grandVolume.toLocaleString('id-ID', {minimumFractionDigits:3, maximumFractionDigits:3});
        document.getElementById('grandTotalBayar').textContent = grandBayar.toLocaleString('id-ID');
    }

    document.querySelectorAll('.pcs-input').forEach(input => {
        input.addEventListener('input', updateSummary);
    });

    // Inisialisasi pertama
    updateSummary();
</script>
@endsection
