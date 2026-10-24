@extends('layouts.app')

@section('title', 'Beli Kayu')

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
        background: #33691E !important; /* hijau tua */
        color: #fff !important;
        font-weight: 600;
    }
    .grade-table-total {
        height: auto !important;      /* Pastikan tinggi mengikuti isi */
        min-height: unset !important; /* Hilangkan min-height jika ada */
    }
    .grade-table-total th, .grade-table-total td {
        padding: 6px 10px;            /* Perkecil padding */
        font-size: 1rem;
        vertical-align: middle;
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
    .grade-table input[type="number"] {
        min-width: 60px;
        text-align: right;
    }
    .form-control:focus {
        border-color: #689F38;
        box-shadow: 0 0 0 0.1rem #c5e1a5;
    }
    .btn-primary {
        background-color: #8BC34A !important;   /* hijau terang */
        border-color: #689F38 !important;       /* hijau tua */
        color: #fff !important;
        font-weight: 700;
        border-radius: 6px;
        transition: background 0.2s;
    }
    .btn-primary:hover, .btn-primary:focus {
        background-color: #689F38 !important;   /* hijau tua saat hover */
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
        .grade-table { font-size: 0.92rem; }
        .card-body { padding: 10px 2px !important; }
        h2 { font-size: 1.1rem; }
        .col-md-3 { flex: 0 0 100%; max-width: 100%; }
        .grade-table { min-width: 520px; }
        .grade-table-total { min-width: 320px; }
        .table-responsive, .card-body { overflow-x: auto; }
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
    .card,
    .grade-table {
        background: #f5fbe8; /* warna background halaman */
    }
    /* pastikan elemen yang disembunyikan filter jenis kayu benar-benar hilang */
    .grade-table [hidden], .grade-block[hidden] { display: none !important; }
</style>

<div class="d-flex justify-content-center">
    <div style="width:100%; max-width: 98vw;">
        <div class="card shadow-sm" style="border-radius:10px; overflow-x:auto; padding:0;">
            <div class="card-body" style="padding:18px 12px;">
                <h2 class="mb-4">Input Kayu - {{ $jenisKayu }}</h2>
                <form id="form-transaksi" autocomplete="off">
                    <div class="mb-3 d-flex align-items-center" style="max-width:320px;">
                        <label class="mb-0 me-2" style="min-width:90px;">Atas Nama :</label>
                        <input type="text" name="atas_nama" class="form-control" 
                            style="width:180px;" placeholder="(wajib diisi)">
                    </div>
                    <div class="mb-3 d-flex align-items-center" style="max-width:320px;">
                        <label for="wood_type_id" class="mb-0 me-2" style="min-width:90px;">Jenis Kayu :</label>
                        <select name="wood_type_id" id="wood_type_id" class="form-control" style="width:180px;">
                            <option value="" disabled selected>Pilih jenis kayu</option>
                            @foreach ($woodTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        @foreach ($grades as $grade => $diameters)
                            @php
                                $allowed = $allowedLengths[$grade] ?? [];
                                $gradeSafe = str_replace([' ', '/', '\\', '.', ','], '_', $grade);
                            @endphp
                            <div class="col-md-3 mb-4 grade-block" data-grade="{{ $grade }}">
                                <table class="grade-table w-100 mb-3">
                                    <thead>
                                        <tr>
                                            <th rowspan="2" class="row-label">Grade</th>
                                            <th rowspan="2" class="row-label">Diameter</th>
                                            <th colspan="{{ count($allowed) }}" class="panjang-header">Panjang</th>
                                        </tr>
                                        <tr>
                                            @foreach ($allowed as $p)
                                                <th class="row-label" data-col-panjang="{{ $p }}">{{ $p }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($diameters as $d)
                                            <tr class="diameter-row" data-diameter="{{ $d }}">
                                                @if ($loop->first)
                                                    <td class="row-label grade-label" rowspan="{{ count($diameters) }}">{{ $grade }}</td>
                                                @endif
                                                <td>{{ $d }}</td>
                                                @foreach ($allowed as $p)
                                                    <td data-col-panjang="{{ $p }}" class="pcs-cell">
                                                        <span class="pcs-na text-muted" hidden>–</span>
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
                                                <td class="total-pcs" data-col-panjang="{{ $p }}" data-grade="{{ $gradeSafe }}" data-panjang="{{ $p }}">0</td>
                                            @endforeach
                                        </tr>
                                        <tr class="summary">
                                            <td colspan="2">Total Volume</td>
                                            @foreach ($allowed as $p)
                                                <td class="total-volume" data-col-panjang="{{ $p }}" data-grade="{{ $gradeSafe }}" data-panjang="{{ $p }}">0.000</td>
                                            @endforeach
                                        </tr>
                                        <tr class="summary">
                                            <td colspan="2">Total Pembayaran</td>
                                            @foreach ($allowed as $p)
                                                <td class="total-bayar" data-col-panjang="{{ $p }}" data-grade="{{ $gradeSafe }}" data-panjang="{{ $p }}">Rp0</td>
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
                    <input type="hidden" name="total_bayar_per_diameter" id="inputTotalBayarPerDiameter">
                </form>
            </div>
        </div>
    </div>
</div>
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
    // availableCombos[wood_type_id][grade][diameter] = [panjang, ...]
    const availableCombos = @json($availableCombos);

    // Tampilkan hanya diameter & panjang yang terdaftar untuk jenis kayu terpilih.
    // Sebelum jenis kayu dipilih, semua baris tetap tampil.
    function filterByWoodType() {
        const typeId = document.getElementById('wood_type_id').value;
        const combos = typeId ? (availableCombos[typeId] || {}) : null;

        document.querySelectorAll('.grade-block').forEach(function(block) {
            const grade = block.dataset.grade;
            const gradeCombos = combos ? (combos[grade] || {}) : null;
            const rows = Array.from(block.querySelectorAll('tr.diameter-row'));
            const usedPanjang = new Set();
            let visibleRows = [];

            rows.forEach(function(row) {
                const d = row.dataset.diameter;
                const panjangList = gradeCombos ? (gradeCombos[d] || []).map(Number) : null;
                const rowVisible = !gradeCombos || panjangList.length > 0;
                row.hidden = !rowVisible;
                if (rowVisible) visibleRows.push(row);

                row.querySelectorAll('td.pcs-cell').forEach(function(cell) {
                    const p = Number(cell.dataset.colPanjang);
                    const ok = !panjangList || panjangList.includes(p);
                    const input = cell.querySelector('.pcs-input');
                    input.hidden = !ok;
                    input.disabled = !ok;
                    if (!ok) input.value = '';
                    cell.querySelector('.pcs-na').hidden = ok;
                    if (ok && rowVisible) usedPanjang.add(p);
                });
            });

            // Sembunyikan seluruh tabel grade kalau tidak ada diameter terdaftar
            block.hidden = visibleRows.length === 0;
            if (block.hidden) return;

            // Sembunyikan kolom panjang yang tidak dipakai sama sekali di grade ini
            let visibleCols = 0;
            const headerCols = block.querySelectorAll('th[data-col-panjang]');
            headerCols.forEach(function(th) {
                const p = Number(th.dataset.colPanjang);
                const show = !combos || usedPanjang.has(p);
                if (show) visibleCols++;
                block.querySelectorAll(`[data-col-panjang="${th.dataset.colPanjang}"]`).forEach(el => el.hidden = !show);
            });
            block.querySelector('.panjang-header').colSpan = Math.max(visibleCols, 1);

            // Pindahkan label grade (rowspan) ke baris pertama yang tampil
            const label = block.querySelector('.grade-label');
            visibleRows[0].insertBefore(label, visibleRows[0].firstElementChild);
            label.rowSpan = visibleRows.length;
        });

        updateSummary();
    }

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
        let grandBayarBeli = 0;
        const selectedWoodType = document.getElementById('wood_type_id').value;

        Object.keys(grades).forEach(function(grade) {
            let gradeSafeStr = gradeSafe(grade);
            let diameters = grades[grade];
            let allowed = allowedLengths[grade] || [];

            allowed.forEach(function(p) {
                let totalPcs = 0, totalVol = 0, totalBayarBeli = 0;

                diameters.forEach(function(d) {
                    let input = document.querySelector('input[name="pcs['+grade+']['+d+']['+p+']"]');
                    let pcs = input ? parseInt(input.value) || 0 : 0;
                    totalPcs += pcs;

                    let volPerBatang = (volumes[d] && volumes[d][p]) ? parseFloat(volumes[d][p]) : 0;
                    totalVol += pcs * volPerBatang;
                });

                // Ambil harga sesuai wood_type terpilih
                let hargaBeli = 0;
                if (
                    hargaPerM3[selectedWoodType] &&
                    hargaPerM3[selectedWoodType][grade] &&
                    hargaPerM3[selectedWoodType][grade][p]
                ) {
                    hargaBeli = parseFloat(hargaPerM3[selectedWoodType][grade][p]['beli']) || 0;
                }
                totalBayarBeli = totalVol * hargaBeli;

                // Update ke tabel
                let pcsCell = document.querySelector('.total-pcs[data-grade="'+gradeSafeStr+'"][data-panjang="'+p+'"]');
                let volCell = document.querySelector('.total-volume[data-grade="'+gradeSafeStr+'"][data-panjang="'+p+'"]');
                let bayarCell = document.querySelector('.total-bayar[data-grade="'+gradeSafeStr+'"][data-panjang="'+p+'"]');
                if (pcsCell) pcsCell.textContent = totalPcs;
                if (volCell) volCell.textContent = totalVol.toLocaleString('id-ID', {minimumFractionDigits:3, maximumFractionDigits:3});
                if (bayarCell) bayarCell.textContent = 'Rp' + totalBayarBeli.toLocaleString('id-ID');

                grandVolume += totalVol;
                grandBayarBeli += totalBayarBeli;
            });
        });

        document.getElementById('grandTotalVolume').textContent = grandVolume.toLocaleString('id-ID', {minimumFractionDigits:3, maximumFractionDigits:3});
        document.getElementById('grandTotalBayar').textContent = grandBayarBeli.toLocaleString('id-ID');
        document.getElementById('inputTotalVolume').value = grandVolume;
        document.getElementById('inputTotalBayar').value = grandBayarBeli;
    }

    // Jalankan saat input berubah
    document.querySelectorAll('.pcs-input').forEach(input => {
        input.addEventListener('input', updateSummary);
    });

    // Jalankan saat halaman pertama kali dimuat
    document.addEventListener('DOMContentLoaded', filterByWoodType);

    document.addEventListener('DOMContentLoaded', function() {
    function showErrorModal(message) {
        document.getElementById('errorModalMsg').innerHTML = message;
        var errorModal = new bootstrap.Modal(document.getElementById('errorModal'), {
            backdrop: 'static',
            keyboard: false
        });
        errorModal.show();
    }

    document.getElementById('form-transaksi').addEventListener('submit', function(e) {
        e.preventDefault();

        const atas_nama = document.querySelector('[name="atas_nama"]').value.trim();
        const wood_type = document.querySelector('[name="wood_type_id"]').value.trim();
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
                        let volPerBatang = (volumes[d] && volumes[d][p]) ? parseFloat(volumes[d][p]) : 0;
                        let volume = pcs * volPerBatang;
                        let harga_per_m3 = 0;
                        if (
                            hargaPerM3[wood_type] &&
                            hargaPerM3[wood_type][grade] &&
                            hargaPerM3[wood_type][grade][p]
                        ) {
                            harga_per_m3 = parseFloat(hargaPerM3[wood_type][grade][p]['beli']) || 0;
                        }
                        details.push({
                            grade: grade,
                            diameter: d,
                            panjang: p,
                            pcs: pcs,
                            volume: volume,
                            harga: volume * harga_per_m3
                        });
                    }
                });
            });
        });

        // Validasi input wajib + minimal 1 log kayu
        let errors = [];
        if (!atas_nama) errors.push('Kolom Atas Nama wajib diisi.');
        if (!wood_type) errors.push('Jenis Kayu wajib dipilih.');
        if (details.length === 0) errors.push('Minimal 1 log kayu harus dimasukkan.');

        if (errors.length > 0) {
            showErrorModal(errors.join('<br>'));
            return; // hentikan, jangan kirim AJAX
        }

        // Kirim via AJAX hanya kalau valid
        fetch("{{ route('wood_transactions.storeBuy') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                atas_nama,
                wood_type_id: wood_type,
                total_volume,
                total_pembayaran,
                details,
                jenis_transaksi: 'Pembelian'
            })
        })
        .then(async res => {
            const contentType = res.headers.get("content-type");
            if (!contentType || !contentType.includes("application/json")) {
                throw new Error("Server tidak mengembalikan JSON.");
            }
            let data = await res.json();

            if (res.ok && data.success) {
                document.getElementById('successModalMsg').innerHTML = 'Transaksi berhasil disimpan!';
                var successModal = new bootstrap.Modal(document.getElementById('successModal'), {
                    backdrop: 'static',
                    keyboard: false
                });
                successModal.show();
                setTimeout(() => window.location.href = data.redirect_url, 2000);
            } else {
                let msg = data.messages ? data.messages.join('<br>') : (data.message || 'Terjadi kesalahan.');
                if (data.errors) {
                    msg += '<ul style="margin:8px 0 0 16px;padding:0;">';
                    Object.values(data.errors).forEach(errArr => {
                        errArr.forEach(err => { msg += '<li>' + err + '</li>'; });
                    });
                    msg += '</ul>';
                }
                showErrorModal(msg);
            }
        })
        .catch(error => {
            showErrorModal('Gagal: ' + (error.message || 'Terjadi kesalahan tidak diketahui.'));
        });
    });
});


    // Trigger updateSummary saat wood_type berubah
    document.getElementById('wood_type_id').addEventListener('change', filterByWoodType);
</script>
@endsection
