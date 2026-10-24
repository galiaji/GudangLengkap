@extends('layouts.app')

@section('title', 'Stock Opname')

@section('content')
<div class="container py-3">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span>Stock Opname</span>
            <a href="{{ route('stock-opname.create') }}" class="btn btn-success btn-sm">
                <i class="bi bi-plus-lg"></i> Mulai Stock Opname
            </a>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <p class="text-muted small mb-3">
                Stock opname mencocokkan jumlah stok di sistem dengan jumlah fisik di gudang.
                Selama opname berjalan, transaksi untuk stok yang sedang dihitung dikunci.
            </p>

            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Tanggal</th>
                            <th>Cakupan</th>
                            <th>Status</th>
                            <th class="text-end">Item</th>
                            <th class="text-end">Item Selisih</th>
                            <th>Dibuat Oleh</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($opnames as $opname)
                            <tr>
                                <td class="fw-semibold">{{ $opname->kode }}</td>
                                <td>{{ $opname->tanggal->format('d-m-Y') }}</td>
                                <td>{{ $opname->cakupan }}</td>
                                <td><span class="badge {{ $opname->statusBadge() }}">{{ $opname->statusLabel() }}</span></td>
                                <td class="text-end">{{ $opname->items_count }}</td>
                                <td class="text-end">
                                    {{ in_array($opname->status, ['menunggu', 'selesai']) ? $opname->items_selisih_count : '-' }}
                                </td>
                                <td>{{ $opname->pembuat->name ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('stock-opname.show', $opname) }}" class="btn btn-sm btn-primary">
                                        {{ $opname->status === 'menghitung' ? 'Hitung' : 'Lihat' }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">Belum ada stock opname.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
