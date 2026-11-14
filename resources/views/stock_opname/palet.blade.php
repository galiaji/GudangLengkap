@extends('layouts.app')

@section('title', 'Stock Opname Pallet')

@section('content')
<div class="container py-3">
    @include('stock_opname._pesan')

    <div class="card mb-3">
        <div class="card-header">Stock Opname Pallet</div>
        <div class="card-body">
            {{-- Langkah 1: pilih batch --}}
            <form method="GET" action="{{ route('stock-opname.palet') }}" class="row g-2 align-items-end">
                <div class="col-sm-8 col-lg-6">
                    <label for="input_code" class="form-label fw-semibold">Batch</label>
                    <select name="input_code" id="input_code" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Pilih batch --</option>
                        @foreach ($batches->groupBy('status') as $status => $daftar)
                            <optgroup label="{{ ucfirst($status) }}">
                                @foreach ($daftar as $b)
                                    <option value="{{ $b->input_code }}" @selected($batch && $batch->input_code === $b->input_code)>
                                        {{ $b->input_code }} · {{ \Carbon\Carbon::parse($b->tanggal_input)->format('d-m-Y') }}
                                        · {{ $b->jumlah_ukuran }} ukuran · {{ number_format($b->total_pcs, 0, ',', '.') }} pcs
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    @if ($batches->isEmpty())
                        <small class="text-muted">Belum ada stok pallet.</small>
                    @endif
                </div>
                <noscript><div class="col-auto"><button class="btn btn-secondary">Tampilkan</button></div></noscript>
            </form>
        </div>
    </div>

    @if ($batch)
        {{-- Langkah 2: isi jumlah pcs hasil hitung fisik --}}
        <form method="POST" action="{{ route('stock-opname.palet.store') }}" id="form-opname"
              data-confirm="Simpan stock opname batch {{ $batch->input_code }}? Stok batch yang selisih akan disesuaikan dengan hasil hitung fisik."
              data-confirm-judul="Simpan Stock Opname" data-confirm-tombol="Ya, Simpan">
            @csrf
            <input type="hidden" name="input_code" value="{{ $batch->input_code }}">

            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span>Stok Tercatat Batch {{ $batch->input_code }}</span>
                    <span class="small">
                        Status {{ ucfirst($batch->status) }} · Tanggal {{ \Carbon\Carbon::parse($batch->tanggal_input)->format('d-m-Y') }}
                    </span>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-2">
                        Hitung pallet pada batch ini lalu isi jumlah PCS per ukuran. Semua ukuran ditampilkan;
                        ukuran yang <em>belum ada di batch</em> sudah terisi 0, ubah jika ternyata ada (belum terinput).
                        Isi 0 jika ukuran tersebut sudah tidak ada.
                    </p>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle mb-0" id="tabel-opname">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Kode</th>
                                    <th>Ukuran (L x T x P)</th>
                                    <th>Status</th>
                                    <th class="text-end">Stok Tercatat (pcs)</th>
                                    <th style="width: 140px;">Jumlah Fisik (pcs)</th>
                                    <th class="text-end">Selisih (pcs)</th>
                                    <th class="text-end">Selisih Volume (m³)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($baris as $b)
                                    <tr class="baris-opname {{ $b->row ? '' : 'text-muted' }}" data-cari="{{ strtolower("{$b->kode} {$b->ukuran}") }}"
                                        data-sistem="{{ $b->sistem }}" data-volume="{{ $b->volume_per_pcs }}">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $b->kode }}</td>
                                        <td>
                                            {{ $b->ukuran }}
                                            @unless ($b->row)
                                                <span class="badge bg-light text-secondary border ms-1">belum ada di batch</span>
                                            @endunless
                                        </td>
                                        <td>{{ ucfirst($b->status) }}</td>
                                        <td class="text-end">{{ number_format($b->sistem, 0, ',', '.') }}</td>
                                        <td>
                                            <input type="hidden" name="sistem[{{ $b->kode }}]" value="{{ $b->sistem }}">
                                            <input type="number" name="fisik[{{ $b->kode }}]" min="0" step="1" inputmode="numeric"
                                                   class="form-control form-control-sm input-fisik @error('fisik.' . $b->kode) is-invalid @enderror"
                                                   value="{{ old('fisik.' . $b->kode, $b->row ? '' : 0) }}">
                                        </td>
                                        <td class="text-end fw-semibold sel-qty">-</td>
                                        <td class="text-end sel-vol">-</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @include('stock_opname._ringkasan_dan_simpan', ['satuan' => 'pcs'])
                </div>
            </div>
        </form>
    @endif

    <a href="{{ route('stock-opname.index') }}" class="btn btn-link px-0">&larr; Kembali ke Riwayat Stock Opname</a>
</div>
@endsection

@section('scripts')
    @include('stock_opname._script')
@endsection
