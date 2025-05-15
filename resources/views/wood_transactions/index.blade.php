@extends('layouts.app')

@section('content')
<style>
    .table-container-transaksi {
        margin: 0 auto;
        padding: 0;
    }
    .table-container-transaksi .table {
        width: 100%;
        font-size: 0.97rem;
        background: #fff;
        border-radius: 8px;
        overflow: hidden;
    }
    .table-container-transaksi h2 {
        margin-top: 24px;
        margin-bottom: 18px;
        font-weight: 700;
        color: #33691E;
        text-align: center;
    }
    .table thead th {
        text-align: center;
        vertical-align: middle;
        background: #33691E;
        color: #fff;
        font-size: 1rem;
        border-bottom: 2px solid #689F38;
        letter-spacing: 0.5px;
    }
    .table tbody td, .table tbody th {
        text-align: center;
        vertical-align: middle;
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
    @media (max-width: 768px) {
        .table-container-transaksi .table { font-size: 0.89rem; }
        .table-container-transaksi h2 { font-size: 1.2rem; }
    }
</style>
<div class="container table-container-transaksi">
    <h2>Manajemen Transaksi</h2>
    <div class="mb-3 text-center">
        <a href="{{ route('wood_transactions.buy') }}" class="btn btn-primary me-2">+ Transaksi Pembelian</a>
        <a href="{{ route('wood_transactions.sell') }}" class="btn btn-success">+ Transaksi Penjualan</a>
    </div>

    <table class="table table-bordered table-hover shadow-sm">
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
            @foreach($transaksi as $index => $item)
            <tr class="row-color-{{ $index % 4 }}">
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->id }}</td>
                <td>{{ ucfirst($item->jenis_transaksi) }}</td>
                <td>{{ $item->atas_nama }}</td>
                <td>{{ number_format($item->total_volume, 2) }}</td>
                <td>Rp{{ number_format($item->total_pembayaran, 0, ',', '.') }}</td>
                <td>{{ $item->tanggal_transaksi }}</td>
                <td>
                    <a href="{{ route('transaksi.show', $item->id) }}" class="btn btn-info">Detail</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection