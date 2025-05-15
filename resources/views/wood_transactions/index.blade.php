@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Manajemen Transaksi</h2>
    <div class="mb-3">
        <a href="{{ route('wood_transactions.buy') }}" class="btn btn-primary">+ Transaksi Pembelian</a>
        <a href="{{ route('wood_transactions.sell') }}" class="btn btn-success">+ Transaksi Penjualan</a>
    </div>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No.</th>
                <th>Id Transaksi</th>
                <th>Jenis Transaksi</th>
                <th>Atas Nama</th>
                <th>Volume (m3)</th>
                <th>Total Pembayaran</th>
                <th>Tanggal Transaksi</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transaksi as $index => $item)
            <tr>
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