@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="bg-light rounded-3 p-4 mb-4">
        <h2 class="mb-3">Tambah Data Palet Penjemuran</h2>
        <form action="{{ route('pallet_stocks.drying.store') }}" method="POST">
            @csrf
            <div class="mb-4 row align-items-center">
                <label for="start_drying_date" class="form-label fw-semibold col-sm-3 col-md-2 mb-2 mb-sm-0">Tanggal Mulai Jemur</label>
                <div class="col-sm-5 col-md-3">
                    <input type="date" name="start_drying_date" id="start_drying_date" class="form-control" required>
                    @error('start_drying_date')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered align-middle bg-white">
                    <thead class="table-success">
                        <tr>
                            <th>Kode Palet</th>
                            <th>Ukuran</th>
                            <th>Jumlah (pcs)</th>
                            <th>Volume</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($palletPrices as $index => $pallet)
                        <tr>
                            <td>
                                <input type="hidden" name="pallets[{{ $index }}][pallet_code]" value="{{ $pallet->pallet_code }}">
                                {{ $pallet->pallet_code }}
                            </td>
                            <td>
                                <input type="hidden" name="pallets[{{ $index }}][ukuran]" value="{{ $pallet->ukuran }}">
                                {{ $pallet->ukuran }}
                            </td>
                            <td>
                                <input type="number" name="pallets[{{ $index }}][pcs]" class="form-control pcs-input" min="0" placeholder="Jumlah" data-index="{{ $index }}" data-ukuran="{{ $pallet->ukuran }}">
                                @error("pallets.$index.pcs")
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </td>
                            <td>
                                <input type="number" step="0.000001" name="pallets[{{ $index }}][volume]" class="form-control volume-input" min="0" placeholder="Volume" readonly id="volume-{{ $index }}">
                                @error("pallets.$index.volume")
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">Belum ada data palet tersedia.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex gap-2 mt-3">
                <button type="submit" class="btn btn-success">Simpan Semua</button>
                <a href="{{ route('pallet_stocks.index') }}" class="btn btn-secondary">Kembali</a>
            </div>
        </form>
    </div>
</div>
<script>
    // Set default tanggal ke hari ini
    document.addEventListener('DOMContentLoaded', function() {
        const today = new Date();
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const dd = String(today.getDate()).padStart(2, '0');
        document.getElementById('start_drying_date').value = `${yyyy}-${mm}-${dd}`;
    });

    // Mapping ukuran ke volume per batang (m3)
    const volumePerBatang = {
        "8x1.2x100": 0.96,
        "8x1.2x130": 1.248,
        "10x1.2x100": 1.2,
        "10x1.2x130": 1.56,
        "12x1.2x100": 1.44,
        "12x1.2x130": 1.872,
        "15x1.3x100": 1.8,
        "15x1.3x130": 2.34
        // Tambahkan sesuai kebutuhan
    };

    document.querySelectorAll('.pcs-input').forEach(function(input) {
        input.addEventListener('input', function() {
            let index = this.getAttribute('data-index');
            let ukuran = this.getAttribute('data-ukuran');
            let pcs = parseFloat(this.value) || 0;
            let volPerBatang = volumePerBatang[ukuran] || 0;
            let totalVolume = (pcs * volPerBatang) / 1000;
            let volumeInput = document.getElementById('volume-' + index);
            volumeInput.value = totalVolume.toFixed(3);
        });
    });
</script>
@endsection