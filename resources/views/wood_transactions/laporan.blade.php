@extends('layouts.laporan')

@section('title', $judul ?? 'Laporan')

@section('content')
<h2>{{ $judul ?? 'Laporan' }}</h2>
@if(isset($periode))
    <p><strong>Periode:</strong> {{ $periode }}</p>
@endif

<p><strong>Dicetak pada:</strong> {{ now()->translatedFormat('H:i - l - d/m/Y') }}</p>

<table>
    <thead>
        <tr>
            <th>No.</th>
            <th>Jenis Transaksi</th>
            <th>Atas Nama</th>
            <th>volume Stock (m³)</th>
            <th>Total Keuntungan</th>
            <th>Tanggal</th>
        </tr>
    </thead>
    <tbody>
        @forelse($transaksi as $index => $item)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ ucfirst($item->jenis_transaksi ?? '') }}</td>
            <td>{{ $item->atas_nama ?? '' }}</td>
            <td>{{ number_format((float)($item->total_volume ?? 0), 3, ',', '.') }}</td>
            <td>Rp{{ number_format((float)($item->total_pembayaran ?? 0), 0, ',', '.') }}</td>
            <td>{{ \Carbon\Carbon::parse($item->tanggal_transaksi ?? $item->created_at)->format('d-m-Y H:i') }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="6" class="text-center">Tidak ada transaksi pada periode ini.</td>
        </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <th colspan="3" style="text-align:right;">Total</th>
            <th>{{ number_format($total_volume, 3, ',', '.') }}</th>
            <th>Rp{{ number_format($total_pembayaran, 0, ',', '.') }}</th>
            <th></th>
        </tr>
    </tfoot>
</table>
@endsection