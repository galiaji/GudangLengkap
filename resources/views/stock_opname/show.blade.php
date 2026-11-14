@extends('layouts.app')

@section('title', 'Stock Opname ' . $opname->kode)

@php
    $vol    = fn ($n) => number_format((float) $n, 3, ',', '.');
    $tandai = fn ($n) => ($n > 0 ? '+' : '') . number_format($n, 0, ',', '.');
    $satuan = $opname->jenis === 'kayu' ? 'btg' : 'pcs';
    $selisih = $opname->itemSelisih();
@endphp

@section('content')
<div class="container py-3">
    @include('stock_opname._pesan')

    <div class="card mb-3">
        <div class="card-header">Hasil Stock Opname {{ $opname->kode }}</div>
        <div class="card-body">
            <div class="row g-2 small">
                <div class="col-sm-6 col-lg-3"><span class="text-muted">Cakupan</span><br><strong>{{ $opname->cakupan }}</strong></div>
                <div class="col-sm-6 col-lg-3"><span class="text-muted">Tanggal</span><br><strong>{{ $opname->tanggal->format('d-m-Y') }}</strong></div>
                <div class="col-sm-6 col-lg-3"><span class="text-muted">Disimpan oleh</span><br><strong>{{ $opname->pembuat->name ?? '-' }}</strong>
                    <span class="text-muted">({{ $opname->created_at->format('d-m-Y H:i') }})</span></div>
                <div class="col-sm-6 col-lg-3"><span class="text-muted">Keterangan</span><br>{{ $opname->catatan ?: '-' }}</div>
            </div>
        </div>
    </div>

    <div class="row g-2 mb-3">
        <div class="col-6 col-lg-3">
            <div class="border rounded p-2 h-100 bg-white">
                <div class="small text-muted">Baris selisih</div>
                <div class="fs-5 fw-bold">{{ $selisih->count() }} <span class="fs-6 fw-normal text-muted">/ {{ $opname->items->count() }}</span></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="border rounded p-2 h-100 bg-white">
                <div class="small text-muted">Total kurang</div>
                <div class="fs-5 fw-bold text-danger">{{ $tandai($selisih->where('selisih', '<', 0)->sum('selisih')) }} {{ $satuan }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="border rounded p-2 h-100 bg-white">
                <div class="small text-muted">Total lebih</div>
                <div class="fs-5 fw-bold text-success">{{ $tandai($selisih->where('selisih', '>', 0)->sum('selisih')) }} {{ $satuan }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="border rounded p-2 h-100 bg-white">
                <div class="small text-muted">Total selisih volume</div>
                <div class="fs-5 fw-bold">{{ $vol($opname->items->sum('volume_selisih')) }} m³</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span>Rincian Hasil Hitung</span>
            <div class="form-check form-switch mb-0 small">
                <input class="form-check-input" type="checkbox" id="hanya-selisih">
                <label class="form-check-label" for="hanya-selisih">Hanya tampilkan yang selisih</label>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive" style="max-height: 65vh;">
                <table class="table table-sm table-bordered align-middle mb-0">
                    <thead class="table-light" style="position: sticky; top: 0; z-index: 1;">
                        <tr>
                            <th>No</th>
                            <th>{{ $opname->jenis === 'kayu' ? 'Grade · Diameter · Panjang' : 'Ukuran (L x T x P)' }}</th>
                            <th class="text-end">Stok Tercatat</th>
                            <th class="text-end">Fisik</th>
                            <th class="text-end">Selisih ({{ $satuan }})</th>
                            <th class="text-end">Selisih Volume (m³)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($opname->items as $item)
                            @php $ada = (int) $item->selisih !== 0; @endphp
                            <tr class="baris-hasil {{ $ada ? ($item->selisih < 0 ? 'table-danger' : 'table-success') : 'tanpa-selisih' }}">
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->label }}</td>
                                <td class="text-end">{{ number_format($item->qty_sistem, 0, ',', '.') }}</td>
                                <td class="text-end">{{ $item->qty_fisik === null ? '-' : number_format($item->qty_fisik, 0, ',', '.') }}</td>
                                <td class="text-end fw-semibold">{{ $item->selisih === null ? '-' : $tandai($item->selisih) }}</td>
                                <td class="text-end">{{ $item->volume_selisih === null ? '-' : $vol($item->volume_selisih) }}</td>
                            </tr>
                        @endforeach
                        <tr id="baris-kosong" class="d-none">
                            <td colspan="6" class="text-center text-muted">Tidak ada item yang selisih.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <a href="{{ route('stock-opname.index') }}" class="btn btn-link px-0 mt-2">&larr; Kembali ke Riwayat Stock Opname</a>
</div>
@endsection

@section('scripts')
<script>
    (function () {
        const sw = document.getElementById('hanya-selisih');
        sw.addEventListener('change', function () {
            let tampil = 0;
            document.querySelectorAll('.baris-hasil').forEach(tr => {
                const sembunyi = sw.checked && tr.classList.contains('tanpa-selisih');
                tr.classList.toggle('d-none', sembunyi);
                if (!sembunyi) tampil++;
            });
            document.getElementById('baris-kosong').classList.toggle('d-none', tampil > 0);
        });
    })();
</script>
@endsection
