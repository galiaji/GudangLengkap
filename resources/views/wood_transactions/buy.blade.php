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

<div class="d-flex justify-content-center">
    <div style="width:100%; max-width: 98vw;">
        <div class="card shadow-sm" style="border-radius:10px; overflow-x:auto; padding:0;">
            <div class="card-body" style="padding:18px 12px;">
                <h2 class="mb-4">Input Kayu - {{ $jenisKayu }}</h2>
                <form>
                    <div class="mb-3 d-flex align-items-center" style="max-width:320px;">
                        <label class="mb-0 me-2" style="min-width:90px;">Atas Nama :</label>
                        <input type="text" name="atas_nama" class="form-control" style="width:180px;" placeholder="(wajib diisi)">
                    </div>
                    <div class="mb-3 d-flex align-items-center" style="max-width:320px;">
                        <label for="wood_type" class="mb-0 me-2" style="min-width:90px;">Jenis Kayu :</label>
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
                                                        <input type="text" inputmode="numeric" pattern="[0-9]*"
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
                    <div>
                        <strong>Total Volume:</strong> <span id="grandTotalVolume">0.000</span> m³<br>
                        <strong>Total Pembayaran:</strong> Rp<span id="grandTotalBayar">0</span>
                    </div>
                    <input type="hidden" name="total_volume" id="inputTotalVolume">
                    <input type="hidden" name="total_pembayaran" id="inputTotalBayar">
                    <button type="submit" class="btn btn-primary mt-3">Simpan</button>
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
        Transaksi berhasil disimpan!
      </div>
    </div>
  </div>
</div>
<!-- Modal Gagal -->
<div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-danger">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="errorModalLabel">Gagal</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body" id="errorModalMsg">
        Terjadi kesalahan saat menyimpan transaksi.
      </div>
    </div>
  </div>
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
        let grandVolume = 0;
        let grandBayar = 0;

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

                // Akumulasi grand total
                grandVolume += totalVol;
                grandBayar += totalBayar;
            });
        });

        // Update grand total di bawah form
        document.getElementById('grandTotalVolume').textContent = grandVolume.toLocaleString('id-ID', {minimumFractionDigits:3, maximumFractionDigits:3});
        document.getElementById('grandTotalBayar').textContent = grandBayar.toLocaleString('id-ID');

        // Update nilai ke input hidden
        document.getElementById('inputTotalVolume').value = grandVolume;
        document.getElementById('inputTotalBayar').value = grandBayar;
    }

    // Jalankan saat input berubah
    document.querySelectorAll('.pcs-input').forEach(input => {
        input.addEventListener('input', updateSummary);
    });

    // Jalankan saat halaman pertama kali dimuat
    document.addEventListener('DOMContentLoaded', updateSummary);

    document.querySelector('form').addEventListener('submit', function(e) {
        e.preventDefault();

        // Ambil data utama
        const atas_nama = document.querySelector('[name="atas_nama"]').value;
        const wood_type = document.querySelector('[name="wood_type"]').value;
        const total_volume = document.getElementById('grandTotalVolume').textContent.replace(/\./g, '').replace(',', '.');
        const total_pembayaran = document.getElementById('grandTotalBayar').textContent.replace(/\./g, '').replace(',', '.').replace(/^Rp/, '');

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
                        // Volume per batang
                        let volPerBatang = (volumes[d] && volumes[d][p]) ? parseFloat(volumes[d][p]) : 0;
                        let volume = pcs * volPerBatang;
                        let harga = (hargaPerM3[grade] && hargaPerM3[grade][p]) ? hargaPerM3[grade][p] : 0;
                        let totalHarga = volume * harga;
                        details.push({
                            grade: grade,
                            diameter: d,
                            panjang: p,
                            pcs: pcs,
                            volume: volume,
                            harga: totalHarga
                        });
                    }
                });
            });
        });

        // Kirim via AJAX
        fetch("{{ route('wood_transactions.storeBuy') }}", {
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
            if (res.ok && data.success) {
                document.getElementById('successModalMsg').innerHTML = 'Transaksi berhasil disimpan!';
                var successModal = new bootstrap.Modal(document.getElementById('successModal'), {
                    backdrop: 'static',
                    keyboard: false
                });
                successModal.show();

                setTimeout(function() {
                    window.location.href = data.redirect_url;
                }, 10);
            } else {
                let msg = data.message || 'Terjadi kesalahan.';
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
