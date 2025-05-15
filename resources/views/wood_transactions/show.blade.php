@extends('layouts.app')

@section('content')
<h1>Detail Transaksi</h1>
<p>ID Transaksi: {{ $transaksi->id }}</p>
<p>Atas Nama: {{ $transaksi->atas_nama }}</p>
<p>Tanggal Transaksi: {{ \Carbon\Carbon::parse($transaksi->tanggal_transaksi)->format('d/m/Y') }}</p>
<table>
    <thead>
        <tr>
            <th>Jenis Kayu</th>
            <th>Grade</th>
            <th>Panjang</th>
            <th>Diameter</th>
            <th>Jumlah</th>
            <th>Volume</th>
            <th>Harga</th>
        </tr>
    </thead>
    <tbody>
        @foreach($transaksi->details as $detail)
        <tr>
            <td>{{ $detail->jenis_kayu }}</td>
            <td>{{ $detail->grade }}</td>
            <td>{{ $detail->panjang }}</td>
            <td>{{ $detail->diameter }}</td>
            <td>{{ $detail->pcs }}</td>
            <td>{{ $detail->volume }}</td>
            <td>Rp{{ number_format($detail->harga,0,',','.') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
<a href="#" onclick="window.print()">Print / PDF</a>
@endsection