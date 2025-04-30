@extends('layouts.app')

@section('title', 'Daftar Harga Kayu')

@section('content')
<style>
    .table-container {
        margin: 0 auto;
        padding: 0;
    }

    .table-container .table {
        width: 100%;
    }

    .table-container h3 {
        margin-top: 20px;
    }

    /* Warna untuk setiap 4 baris */
    .row-color-0 {
        background-color: #f8f9fa; /* Warna abu-abu terang */
    }

    .row-color-1 {
        background-color: #e9ecef; /* Warna abu-abu sedikit lebih gelap */
    }

    .row-color-2 {
        background-color: #dee2e6; /* Warna abu-abu lebih gelap */
    }

    .row-color-3 {
        background-color: #ced4da; /* Warna abu-abu paling gelap */
    }
</style>
<div class="container-fluid">
    <h1 class="mb-4 text-center">Daftar Harga Kayu</h1>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @php
        // Kelompokkan data berdasarkan jenis kayu
        $groupedWoodPrices = $woodPrices->groupBy('wood_type_name');
    @endphp

    <div class="row table-container">
        @foreach ($groupedWoodPrices as $woodType => $prices)
            <div class="col-md-4 mb-4">
                <h3 class="text-center">{{ $woodType }}</h3>
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Grade</th>
                            <th>Panjang (cm)</th>
                            <th>Harga per m³ (Rp)</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($prices as $index => $price)
                            <tr class="row-color-{{ $index % 4 }}"> <!-- Tentukan warna berdasarkan $index % 4 -->
                                <td>{{ $index + 1 }}</td> <!-- Penomoran dimulai dari 1 -->
                                <td>{{ $price->grade_name }}</td>
                                <td>{{ $price->panjang }}</td>
                                <td>{{ number_format($price->price_per_m3, 0, ',', '.') }}</td>
                                <td>
                                    <a href="{{ route('wood_prices.edit', $price->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    </div>
</div>
@endsection