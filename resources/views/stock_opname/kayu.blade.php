@extends('layouts.app')

@section('title', 'Stock Opname Log Kayu')

@section('content')
{{-- Format tabel mengikuti form pembelian/penjualan log kayu: satu tabel per grade, baris diameter x kolom panjang --}}
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
        padding: 6px 8px;
        font-size: 0.95rem;
        vertical-align: middle;
        word-break: break-word;
    }
    .grade-table th {
        background: #33691E !important;
        color: #fff !important;
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
    .grade-table td.sel-kosong { background: #eeeeee; color: #9e9e9e; }
    .grade-table td.kurang { background: #f8d7da; }
    .grade-table td.lebih  { background: #d1e7dd; }
    .grade-table .tercatat { font-size: 0.75rem; color: #5f6f52; line-height: 1.1; }
    .grade-table .selisih  { font-size: 0.78rem; font-weight: 700; line-height: 1.1; min-height: 1em; }
    .grade-table-total th, .grade-table-total td {
        padding: 6px 10px;
        font-size: 1rem;
        vertical-align: middle;
    }
    .grade-table-total td { text-align: right; font-weight: bold; }
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
        .grade-table { font-size: 0.92rem; min-width: 420px; }
        .card-body { padding: 10px 2px !important; overflow-x: auto; }
        h2 { font-size: 1.1rem; }
    }
    @media (max-width: 575.98px) {
        .grade-table th, .grade-table td { font-size: 0.78rem; padding: 2px 2px; }
        h2 { font-size: 1rem; }
        .card-body { padding: 6px 0 !important; }
        .form-control, select { font-size: 0.95rem; }
    }
</style>

<div class="d-flex justify-content-center">
    <div style="width:100%; max-width: 98vw;">
        <div class="card shadow-sm mb-3" style="border-radius:10px; overflow-x:auto; padding:0; background:#f5fbe8;">
            <div class="card-body" style="padding:18px 12px;">
                <h2 class="mb-4">Stock Opname Log Kayu{{ $woodType ? ' - ' . $woodType->name : '' }}</h2>

                {{-- Pilih jenis kayu --}}
                <form method="GET" action="{{ route('stock-opname.kayu') }}" class="mb-3 d-flex align-items-center" style="max-width:320px;">
                    <label for="wood_type_id" class="mb-0 me-2" style="min-width:90px;">Jenis Kayu:</label>
                    <select name="wood_type_id" id="wood_type_id" class="form-control" style="width:180px;" onchange="this.form.submit()">
                        <option value="" disabled @selected(!$woodType)>Pilih jenis kayu</option>
                        @foreach ($woodTypes as $type)
                            <option value="{{ $type->id }}" @selected($woodType && $woodType->id === $type->id)>{{ $type->name }}</option>
                        @endforeach
                    </select>
                    <noscript><button class="btn btn-secondary btn-sm ms-2">Tampilkan</button></noscript>
                </form>

                @if (!$woodType)
                    <p class="text-muted mb-0">Pilih jenis kayu untuk menampilkan stok tercatat.</p>
                @elseif ($stocks->isEmpty())
                    <p class="text-muted mb-0">Belum ada kombinasi stok untuk jenis kayu ini.</p>
                @else
                    <p class="small text-muted mb-3">
                        Isi jumlah batang hasil hitung fisik pada setiap kotak. Angka <em>Tercatat</em> adalah stok di sistem;
                        kotak yang stok tercatatnya 0 sudah terisi 0, ubah jika ternyata ada barangnya.
                    </p>

                    <form method="POST" action="{{ route('stock-opname.kayu.store') }}" id="form-opname" autocomplete="off"
                          data-confirm="Simpan stock opname {{ $woodType->name }}? Stok log kayu yang selisih akan disesuaikan dengan hasil hitung fisik."
                          data-confirm-judul="Simpan Stock Opname" data-confirm-tombol="Ya, Simpan">
                        @csrf
                        <input type="hidden" name="wood_type_id" value="{{ $woodType->id }}">

                        <div class="row">
                            @foreach ($grid as $grade => $g)
                                @php $gradeSafe = str_replace([' ', '/', '\\', '.', ','], '_', $grade); @endphp
                                <div class="col-lg-6 col-xl-4 mb-4">
                                    <table class="grade-table w-100 mb-3" data-grade="{{ $gradeSafe }}">
                                        <thead>
                                            <tr>
                                                <th rowspan="2" class="row-label">Grade</th>
                                                <th rowspan="2" class="row-label">Diameter</th>
                                                <th colspan="{{ $g['panjang']->count() }}">Panjang</th>
                                            </tr>
                                            <tr>
                                                @foreach ($g['panjang'] as $p)
                                                    <th class="row-label">{{ $p }}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($g['diameter'] as $d)
                                                <tr>
                                                    @if ($loop->first)
                                                        <td class="row-label" rowspan="{{ $g['diameter']->count() }}">{{ $grade }}</td>
                                                    @endif
                                                    <td>{{ $d }}</td>
                                                    @foreach ($g['panjang'] as $p)
                                                        @php $s = $g['sel'][$d . '|' . $p] ?? null; @endphp
                                                        @if ($s)
                                                            @php $sistem = (int) ($s->quantity ?? 0); @endphp
                                                            <td class="sel-opname" data-panjang="{{ $p }}" data-sistem="{{ $sistem }}" data-volume="{{ (float) $s->volume }}">
                                                                <div class="tercatat">Tercatat: {{ number_format($sistem, 0, ',', '.') }}</div>
                                                                <input type="hidden" name="sistem[{{ $s->id }}]" value="{{ $sistem }}">
                                                                <input type="number" min="0" step="1" inputmode="numeric" pattern="[0-9]*"
                                                                       name="fisik[{{ $s->id }}]"
                                                                       class="form-control form-control-sm pcs-input text-center @error('fisik.' . $s->id) is-invalid @enderror"
                                                                       value="{{ old('fisik.' . $s->id, $sistem === 0 ? 0 : '') }}"
                                                                       title="Grade {{ $grade }}, diameter {{ $d }}, panjang {{ $p }}">
                                                                <div class="selisih"></div>
                                                            </td>
                                                        @else
                                                            <td class="sel-kosong">-</td>
                                                        @endif
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                            <tr class="summary">
                                                <td colspan="2">Total Tercatat</td>
                                                @foreach ($g['panjang'] as $p)
                                                    <td class="total-tercatat" data-panjang="{{ $p }}">0</td>
                                                @endforeach
                                            </tr>
                                            <tr class="summary">
                                                <td colspan="2">Total Fisik</td>
                                                @foreach ($g['panjang'] as $p)
                                                    <td class="total-fisik" data-panjang="{{ $p }}">0</td>
                                                @endforeach
                                            </tr>
                                            <tr class="summary">
                                                <td colspan="2">Selisih (btg)</td>
                                                @foreach ($g['panjang'] as $p)
                                                    <td class="total-selisih" data-panjang="{{ $p }}">0</td>
                                                @endforeach
                                            </tr>
                                            <tr class="summary">
                                                <td colspan="2">Selisih Volume</td>
                                                @foreach ($g['panjang'] as $p)
                                                    <td class="total-volume" data-panjang="{{ $p }}">0,000</td>
                                                @endforeach
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            @endforeach

                            {{-- Kolom total, keterangan, dan simpan --}}
                            <div class="col-lg-6 col-xl-4 mb-4 d-flex flex-column align-items-start">
                                <table class="grade-table grade-table-total w-100 mb-3">
                                    <thead>
                                        <tr>
                                            <th>Total Selisih</th>
                                            <th>Total Selisih Volume</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><span id="grandSelisih">0</span> btg</td>
                                            <td><span id="grandVolume">0,000</span> m³</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="small text-muted mb-2">
                                    <span id="jumlahTerisi">0</span> dari {{ $stocks->count() }} kotak terisi,
                                    <span id="jumlahSelisih">0</span> kotak selisih.
                                </div>
                                <label for="keterangan" class="mb-1">Keterangan:</label>
                                <textarea name="keterangan" id="keterangan" rows="3" maxlength="1000"
                                          class="form-control mb-2 @error('keterangan') is-invalid @enderror"
                                          placeholder="Contoh: hitung ulang gudang A, 2 batang busuk dibuang">{{ old('keterangan') }}</textarea>
                                <button type="submit" class="btn btn-primary mt-2 w-100">Simpan</button>
                            </div>
                        </div>
                    </form>
                @endif
            </div>
        </div>

        <a href="{{ route('stock-opname.index') }}" class="btn btn-link px-0">&larr; Kembali ke Riwayat Stock Opname</a>
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
      <div class="modal-body">{{ session('success') }}</div>
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
      <div class="modal-body">
        @foreach (collect($errors->all())->unique() as $error)
            <div>{{ $error }}</div>
        @endforeach
        {{ session('error') }}
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Pesan validasi / berhasil tampil sebagai popup, sama seperti form pembelian & penjualan
        @if ($errors->any() || session('error'))
            new bootstrap.Modal(document.getElementById('errorModal')).show();
        @elseif (session('success'))
            new bootstrap.Modal(document.getElementById('successModal')).show();
        @endif

        const fmt = (n, d) => n.toLocaleString('id-ID', { minimumFractionDigits: d, maximumFractionDigits: d });
        const tanda = (n, d) => (n > 0 ? '+' : '') + fmt(n, d);

        // Hitung selisih per kotak, total per panjang, dan total keseluruhan
        function updateSummary() {
            let grandSelisih = 0, grandVolume = 0, terisi = 0, jumlahSelisih = 0;

            document.querySelectorAll('.grade-table[data-grade]').forEach(function (tabel) {
                const total = {};
                tabel.querySelectorAll('.sel-opname').forEach(function (td) {
                    const p = td.dataset.panjang;
                    const sistem = Number(td.dataset.sistem);
                    const input = td.querySelector('.pcs-input');
                    const label = td.querySelector('.selisih');
                    total[p] = total[p] || { tercatat: 0, fisik: 0, selisih: 0, volume: 0 };
                    total[p].tercatat += sistem;
                    td.classList.remove('kurang', 'lebih');

                    if (input.value === '' || isNaN(Number(input.value))) {
                        label.textContent = '';
                        return;
                    }
                    const fisik = parseInt(input.value, 10);
                    const selisih = fisik - sistem;
                    const volume = selisih * Number(td.dataset.volume);
                    terisi++;
                    total[p].fisik += fisik;
                    total[p].selisih += selisih;
                    total[p].volume += volume;
                    if (selisih !== 0) {
                        jumlahSelisih++;
                        td.classList.add(selisih < 0 ? 'kurang' : 'lebih');
                        label.textContent = 'Selisih ' + tanda(selisih, 0);
                    } else {
                        label.textContent = '';
                    }
                });

                Object.keys(total).forEach(function (p) {
                    const t = total[p];
                    tabel.querySelector(`.total-tercatat[data-panjang="${p}"]`).textContent = fmt(t.tercatat, 0);
                    tabel.querySelector(`.total-fisik[data-panjang="${p}"]`).textContent = fmt(t.fisik, 0);
                    tabel.querySelector(`.total-selisih[data-panjang="${p}"]`).textContent = tanda(t.selisih, 0);
                    tabel.querySelector(`.total-volume[data-panjang="${p}"]`).textContent = tanda(t.volume, 3);
                    grandSelisih += t.selisih;
                    grandVolume += t.volume;
                });
            });

            const el = id => document.getElementById(id);
            if (!el('grandSelisih')) return;
            el('grandSelisih').textContent = tanda(grandSelisih, 0);
            el('grandVolume').textContent = tanda(grandVolume, 3);
            el('jumlahTerisi').textContent = terisi;
            el('jumlahSelisih').textContent = jumlahSelisih;
        }

        document.querySelectorAll('.pcs-input').forEach(input => input.addEventListener('input', updateSummary));
        updateSummary();
    });
</script>
@endsection
