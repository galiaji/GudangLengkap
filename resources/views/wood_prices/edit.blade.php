@extends('layouts.app')

@section('title', 'Edit Harga Kayu')

@section('content')
<div class="container">
    <h1 class="mb-4">Edit Harga Kayu</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('wood_prices.update', $woodPrice->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="wood_type" class="form-label">Jenis Kayu</label>
            <input type="text" id="wood_type" class="form-control" value="{{ $woodPrice->wood_type_name }}" disabled>
        </div>

        <div class="mb-3">
            <label for="grade" class="form-label">Grade</label>
            <input type="text" id="grade" class="form-control" value="{{ $woodPrice->grade_name }}" disabled>
        </div>

        <div class="mb-3">
            <label for="panjang" class="form-label">Panjang (cm)</label>
            <input type="number" id="panjang" class="form-control" value="{{ $woodPrice->panjang }}" disabled>
        </div>

        <div class="mb-3">
            <label for="price_per_m3" class="form-label">Harga per m³ (Rp)</label>
            <input type="text" name="price_per_m3" id="price_per_m3" class="form-control" 
                   value="{{ number_format($woodPrice->price_per_m3, 0, ',', '.') }}" 
                   oninput="formatRupiah(this)">
        </div>

        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="{{ route('wood_prices.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>

<script>
    function formatRupiah(input) {
        let value = input.value.replace(/\D/g, ''); // Hapus semua karakter non-digit
        input.value = new Intl.NumberFormat('id-ID', {
            style: 'decimal',
            maximumFractionDigits: 0
        }).format(value); // Format ke rupiah
    }

    // Hapus format rupiah sebelum form dikirim
    document.querySelector('form').addEventListener('submit', function (e) {
        const priceInput = document.getElementById('price_per_m3');
        priceInput.value = priceInput.value.replace(/\./g, ''); // Hapus semua tanda titik
    });
</script>
@endsection