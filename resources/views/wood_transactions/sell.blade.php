@extends('layouts.app')

@section('title', 'Jual Kayu')

@section('content')
@php use Illuminate\Support\Str; @endphp
<style>
    .grade-table {
        border-radius: 8px;
        overflow: hidden;
        background: #f5fbe8;
        box-shadow: 0 2px 8px #e0f2f1;
        width: 100%;
        table-layout: fixed;
    }
    .grade-table th, .grade-table td {
        border: 1px solid #222;
        text-align: center;
        padding: 6px 10px;
        font-size: 0.97rem;
        vertical-align: middle;
        word-break: break-word;
    }
    .grade-table th {
        background: #33691E !important;
        color: #fff !important;
        font-weight: 600;
    }
    .grade-table-total th, .grade-table-total td {
        padding: 6px 10px;
        font-size: 1rem;
        vertical-align: middle;
    }
    .grade-table-total th {
        background: #33691E !important;
        color: #fff !important;
        font-weight: 600;
    }
    .grade-table-total td:first-child {
        text-align: left;
        font-weight: bold;
        width: 60%;
    }
    .grade-table-total td:last-child {
        text-align: right;
        width: 40%;
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
    .form-control:focus {
        border-color: #689F38;
        box-shadow: 0 0 0 0.1rem #c5e1a5;
    }
    .btn-primary {
        background-color: #8BC34A !important;
        border-color: #689F38 !important;
        color: #fff !important;
        font-weight: 700;
        border-radius: 6px;
        transition: background 0.2s;
    }
    .btn-primary:hover, .btn-primary:focus {
        background-color: #689F38 !important;
        border-color: #558B2F !important;
        color: #fff !important;
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
    @media (max-width: 991.98px) {
        .grade-table th, .grade-table td { font-size: 0.85rem; padding: 4px 4px; }
        .grade-table { font-size: 0.92rem; min-width: 520px; }
        .grade-table-total { min-width: 320px; }
        .card-body { padding: 10px 2px !important; overflow-x: auto; }
        h2 { font-size: 1.1rem; }
        .col-md-3 { flex: 0 0 100%; max-width: 100%; }
    }
    @media (max-width: 575.98px) {
        .grade-table th, .grade-table td { font-size: 0.78rem; padding: 2px 2px; }
        .grade-table { font-size: 0.88rem; min-width: 420px; }
        .grade-table-total { min-width: 220px; }
        h2 { font-size: 1rem; }
        .card-body { padding: 6px 0 !important; }
        .form-control, select { font-size: 0.95rem; }
    }
    @media (max-width: 400px) {
        .grade-table { min-width: 320px; }
        .grade-table-total { min-width: 160px; }
    }
</style>

<div class="d-flex justify-content-center">
    <div style="width:100%; max-width: 98vw;">
        <div class="card shadow-sm" style="border-radius:10px; overflow-x:auto; padding:0; background:#f5fbe8;">
            <div class="card-body" style="padding:18px 12px;">
                <h2 class="mb-4">Jual Kayu - {{ $jenisKayu }}</h2>
                <form id="form-transaksi" autocomplete="off">
                    <div class="mb-3 d-flex align-items-center" style="max-width:320px;">
                        <label class="mb-0 me-2" style="min-width:90px;">Atas Nama:</label>
                        <input type="text" name="atas_nama" class="form-control" style="width:180px;" placeholder="(wajib diisi)" autocomplete="off">
                    </div>
                    <div class="mb-3 d-flex align-items-center" style="max-width:320px;">
                        <label for="wood_type" class="mb-0 me-2" style="min-width:90px;">Jenis Kayu:</label>
                        <select name="wood_type" id="wood_type" class="form-control" style="width:180px;" required>
                            <option value="" disabled selected>Pilih jenis kayu</option>
                            @foreach ($woodTypes as $type)
                                <option value="{{ $type }}">{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        @foreach ($grades as $grade => $diameters)
                            @php
                                $allowed = $allowedLengths[$grade] ?? [];
                                $gradeSafe = str_replace([' ', '/', '\\', '.', ','], '_', $grade);
                            @endphp
                            <div class="col-md-3 mb-4">
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
                                                        <input type="number" inputmode="numeric" pattern="[0-9]*"
                                                            class="form-control form-control-sm pcs-input"
                                                            name="pcs[{{ $grade }}][{{ $d }}][{{ $p }}]"
                                                            value=""
                                                            placeholder="0"
                                                            autocomplete="off"
                                                            onfocus="this.placeholder=''"
                                                            onblur="if(this.value===''){this.placeholder='0'}">
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
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

                        <!-- Kolom total, letakkan setelah loop -->
                        <div class="col-md-3 mb-4 d-flex flex-column align-items-start">
                            <table class="grade-table grade-table-total w-100 mb-3">
                                <thead>
                                    <tr>
                                        <th>Total Volume</th>
                                        <th>Total Pembayaran</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><span id="grandTotalVolume">0.000</span> m³</td>
                                        <td>Rp<span id="grandTotalBayar">0</span></td>
                                    </tr>
                                </tbody>
                            </table>
                            <button type="submit" class="btn btn-primary mt-2 w-100">Simpan</button>
                        </div>
                    </div>
                    <input type="hidden" name="total_volume" id="inputTotalVolume">
                    <input type="hidden" name="total_pembayaran" id="inputTotalBayar">
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Sukses -->
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-success">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="successModalLabel">Berhasil</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body" id="successModalMsg">
        Transaksi penjualan berhasil disimpan!
      </div>
    </div>
  </div>
</div>

<!-- Modal Error -->
<div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-danger">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="errorModalLabel">Terjadi Kesalahan</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body" id="errorModalMsg">
        <!-- Pesan error akan muncul di sini -->
      </div>
    </div>
  </div>
</div>

<script>
    const volumes = @json($volumes);
    const hargaPerM3 = @json($hargaPerM3);
    const grades = @json($grades);
    const allowedLengths = @json($allowedLengths);

    function gradeSafe(str) {
        return str.replace(/[\s\/\\\.,]/g, '_');
    }

    function parseNumber(str) {
        // Hilangkan semua karakter selain angka dan titik/koma
        if (typeof str === 'string') {
            str = str.replace(/[^0-9.,-]/g, '').replace(/\./g, '').replace(',', '.');
        }
        return parseFloat(str) || 0;
    }

    function updateSummary() {
        let grandVolume = 0;
        let grandBayarJual = 0;
        const selectedWoodType = document.getElementById('wood_type').value;

        Object.keys(grades).forEach(function(grade) {
            let gradeSafeStr = gradeSafe(grade);
            let diameters = grades[grade];
            let allowed = allowedLengths[grade] || [];

            allowed.forEach(function(p) {
                let totalPcs = 0, totalVol = 0, totalBayarJual = 0;

                diameters.forEach(function(d) {
                    let input = document.querySelector('input[name="pcs['+grade+']['+d+']['+p+']"]');
                    let pcs = input ? parseInt(input.value) || 0 : 0;
                    totalPcs += pcs;

                    let volPerBatang = (volumes[d] && volumes[d][p]) ? parseFloat(volumes[d][p]) : 0;
                    totalVol += pcs * volPerBatang;
                });

                // Ambil harga JUAL sesuai wood_type terpilih
                let hargaJual = 0;
                if (
                    hargaPerM3[selectedWoodType] &&
                    hargaPerM3[selectedWoodType][grade] &&
                    hargaPerM3[selectedWoodType][grade][p]
                ) {
                    hargaJual = parseFloat(hargaPerM3[selectedWoodType][grade][p]['jual']) || 0;
                }
                totalBayarJual = totalVol * hargaJual;

                // Update ke tabel
                let pcsCell = document.querySelector('.total-pcs[data-grade="'+gradeSafeStr+'"][data-panjang="'+p+'"]');
                let volCell = document.querySelector('.total-volume[data-grade="'+gradeSafeStr+'"][data-panjang="'+p+'"]');
                let bayarCell = document.querySelector('.total-bayar[data-grade="'+gradeSafeStr+'"][data-panjang="'+p+'"]');
                if (pcsCell) pcsCell.textContent = totalPcs;
                if (volCell) volCell.textContent = totalVol.toLocaleString('id-ID', {minimumFractionDigits:3, maximumFractionDigits:3});
                if (bayarCell) bayarCell.textContent = 'Rp' + totalBayarJual.toLocaleString('id-ID');

                grandVolume += totalVol;
                grandBayarJual += totalBayarJual;
            });
        });

        document.getElementById('grandTotalVolume').textContent = grandVolume.toLocaleString('id-ID', {minimumFractionDigits:3, maximumFractionDigits:3});
        document.getElementById('grandTotalBayar').textContent = grandBayarJual.toLocaleString('id-ID');
        document.getElementById('inputTotalVolume').value = grandVolume;
        document.getElementById('inputTotalBayar').value = grandBayarJual;
    }

    // Jalankan saat input berubah
    document.querySelectorAll('.pcs-input').forEach(input => {
        input.addEventListener('input', updateSummary);
    });

    // Jalankan saat halaman pertama kali dimuat
    document.addEventListener('DOMContentLoaded', updateSummary);

    // Trigger updateSummary saat wood_type berubah
    document.getElementById('wood_type').addEventListener('change', updateSummary);

    document.querySelector('#form-transaksi').addEventListener('submit', function(e) {
        e.preventDefault();

        // Ambil data utama
        const atas_nama = document.querySelector('[name="atas_nama"]').value;
        const wood_type = document.querySelector('[name="wood_type"]').value;
        const total_volume = parseNumber(document.getElementById('grandTotalVolume').textContent);
        const total_pembayaran = parseNumber(document.getElementById('grandTotalBayar').textContent);

        // Kumpulkan detail
        let details = [];
        Object.keys(grades).forEach(function(grade) {
            let diameters = grades[grade];
            let allowed = allowedLengths[grade] || [];
            allowed.forEach(function(p) {
                diameters.forEach(function(d) {
                    let input = document.querySelector('input[name="pcs['+grade+']['+d+']['+p+']"]');
                    let pcs = input ? parseInt(input.value) || 0 : 0;
                    if (pcs > 0) {
                        // Hitung volume dan total bayar per diameter
                        let volPerBatang = (volumes[d] && volumes[d][p]) ? parseFloat(volumes[d][p]) : 0;
                        let volume = pcs * volPerBatang;
                        let harga_per_m3 = 0;
                        if (
                            hargaPerM3[wood_type] &&
                            hargaPerM3[wood_type][grade] &&
                            hargaPerM3[wood_type][grade][p]
                        ) {
                            harga_per_m3 = parseFloat(hargaPerM3[wood_type][grade][p]['jual']) || 0;
                        }
                        let total_bayar_per_diameter = volume * harga_per_m3;
                        details.push({
                            grade: grade,
                            diameter: d,
                            panjang: p,
                            pcs: pcs,
                            volume: volume,
                            harga: total_bayar_per_diameter
                        });
                    }
                });
            });
        });

        // Kirim via AJAX
        fetch("{{ route('wood_transactions.storeSell') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                atas_nama: atas_nama,
                wood_type: wood_type,
                total_volume: total_volume,
                total_pembayaran: total_pembayaran,
                details: details
            })
        })
        .then(async res => {
            let data;
            try {
                data = await res.json();
            } catch (e) {
                throw new Error('Gagal membaca respon server.');
            }
            // Selalu tampilkan error dari backend jika validasi gagal
            if (res.ok && data.success) {
                document.getElementById('successModalMsg').innerHTML = 'Transaksi berhasil disimpan!';
                var successModal = new bootstrap.Modal(document.getElementById('successModal'), {
                    backdrop: 'static',
                    keyboard: false
                });
                successModal.show();

                setTimeout(function() {
                    window.location.href = data.redirect_url;
                }, 2000);
            } else {
                let msg = data.message || 'Terjadi kesalahan.';
                // Tampilkan semua pesan jika ada array messages
                if (Array.isArray(data.messages)) {
                    msg = '<ul style="margin:8px 0 0 16px;padding:0;">';
                    data.messages.forEach(function(err) {
                        msg += '<li>' + err + '</li>';
                    });
                    msg += '</ul>';
                }
                if (data.errors) {
                    msg += '<ul style="margin:8px 0 0 16px;padding:0;">';
                    Object.values(data.errors).forEach(function(errArr) {
                        errArr.forEach(function(err) {
                            msg += '<li>' + err + '</li>';
                        });
                    });
                    msg += '</ul>';
                }
                document.getElementById('errorModalMsg').innerHTML = msg;
                var errorModal = new bootstrap.Modal(document.getElementById('errorModal'), {
                    backdrop: 'static',
                    keyboard: false
                });
                errorModal.show();
            }
        })
        .catch(error => {
            let msg = 'Gagal: ';
            if (error && error.message) {
                msg += error.message;
            } else if (typeof error === 'string') {
                msg += error;
            } else {
                msg += 'Terjadi kesalahan tidak diketahui.';
            }
            document.getElementById('errorModalMsg').innerHTML = msg;
            var errorModal = new bootstrap.Modal(document.getElementById('errorModal'), {
                backdrop: 'static',
                keyboard: false
            });
            errorModal.show();
        });
    });
</script>
@endsection