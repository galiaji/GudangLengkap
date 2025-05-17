@extends('layouts.app')

@section('title', 'Transaksi')

@section('content')
<style>
    .transaksi-title {
        color: #33691E;
        font-size: 2rem;
        letter-spacing: 1px;
        text-align: center;
        font-weight: 700;
        margin-bottom: 2rem;
    }
    .transaksi-card {
        border-radius: 10px;
        box-shadow: 0 2px 8px #e0f2f1;
        background: #f5fbe8; /* samakan dengan background halaman */
        width: 100%;
        margin-top: 32px;
        margin-bottom: 24px;
        padding: 32px 24px 24px 24px;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
    }
    .transaksi-card .card-header {
        background: #33691E;
        color: #fff;
        font-weight: 700;
        font-size: 1.3rem;
        letter-spacing: 1px;
        border-bottom: 1px solid #A5D6A7;
        text-align: center;
        padding: 22px 0 22px 0;
        margin-bottom: 24px;
        border-radius: 8px 8px 0 0;
    }
    .transaksi-card .card-body {
        padding: 0;
        background: inherit;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }
    .table-container-transaksi {
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }
    .table-responsive {
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }
    .table-container-transaksi .table {
        width: 100%;
        font-size: 0.97rem;
        background: #fff;
        border-radius: 8px;
        overflow: hidden;
        flex-grow: 1;
    }
    .table thead th {
        text-align: center;
        vertical-align: middle;
        background: #33691E;
        color: #fff;
        font-size: 1rem;
        border-bottom: 2px solid #689F38;
        letter-spacing: 0.5px;
        padding: 8px 6px;
    }
    .table tbody td, .table tbody th {
        text-align: center;
        vertical-align: middle;
        padding: 8px 6px;
    }
    .row-color-0 { background-color: #f1f8e9; }
    .row-color-1 { background-color: #dcedc8; }
    .row-color-2 { background-color: #f8fafc; }
    .row-color-3 { background-color: #e6ee9c; }
    .table-hover tbody tr:hover {
        background-color: #A5D6A7 !important;
        transition: background 0.2s;
    }
    .btn-info {
        background-color: #689F38;
        border-color: #689F38;
        color: #fff;
        font-weight: 600;
        padding: 2px 14px;
        border-radius: 6px;
    }
    .btn-info:hover, .btn-info:focus {
        background-color: #33691E;
        border-color: #33691E;
        color: #fff;
    }
    .btn-primary, .btn-success {
        font-weight: 600;
        border-radius: 6px;
    }
    @media (max-width: 1200px) {
        .transaksi-card { max-width: 100%; }
    }
    @media (max-width: 768px) {
        .transaksi-card { padding: 12px 2px 2px 2px; }
    }
    .sidebar {
        width: 220px;
        min-width: 220px;
        max-width: 220px;
    }
</style>
<div class="main-content flex-grow-1 col p-0 ps-4">
    <div class="transaksi-card">
        <div class="card-header">
            Manajemen Transaksi
        </div>
        <div class="card-body table-container-transaksi">
            <div class="mb-3 text-start">
                <a href="{{ route('wood_transactions.buy') }}" class="btn btn-primary me-2">+ Transaksi Pembelian</a>
                <a href="{{ route('wood_transactions.sell') }}" class="btn btn-success">+ Transaksi Penjualan</a>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-hover shadow-sm mb-0 w-100">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Id Transaksi</th>
                            <th>Jenis Transaksi</th>
                            <th>Atas Nama</th>
                            <th>Volume (m³)</th>
                            <th>Total Pembayaran</th>
                            <th>Tanggal Transaksi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transaksi as $index => $item)
                        <tr class="row-color-{{ $index % 4 }}">
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->id ?? '' }}</td>
                            <td>{{ ucfirst($item->jenis_transaksi ?? '') }}</td>
                            <td>{{ $item->atas_nama ?? '' }}</td>
                            <td>{{ number_format((float)($item->total_volume ?? 0), 3, ',', '.') }}</td>
                            <td>Rp{{ number_format((float)($item->total_pembayaran ?? 0), 0, ',', '.') }}</td>
                            <td>
                                {{ \Carbon\Carbon::parse($item->tanggal_transaksi ?? $item->created_at)->format('d-m-Y H:i') }}
                            </td>
                            <td>
                                <a href="{{ route('transaksi.show', $item->id) }}" class="btn btn-info">Detail</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">Belum ada data transaksi.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection