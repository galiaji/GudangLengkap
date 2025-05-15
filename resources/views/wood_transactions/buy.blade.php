@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="mb-3 text-center">Beli Stok</h1>
    <form action="{{ route('wood_transactions.storeBuy') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="seller_name" class="form-label fw-bold">Nama Penjual</label>
            <input type="text" name="seller_name" id="seller_name" class="form-control form-control-sm" placeholder="Masukkan nama penjual" required>
        </div>

        @if($woodTypes->isNotEmpty() && $grades->isNotEmpty())
            <div class="row">
                @foreach($woodTypes as $type)
                <div class="col-md-6 mb-3">
                    <div class="card">
                        <div class="card-header bg-primary text-white text-center p-2">
                            <h2 class="h6 mb-0">{{ $type->name }}</h2>
                        </div>
                        <div class="card-body p-2">
                            <table class="table table-bordered table-sm">
                                <thead class="table-dark text-center">
                                    <tr>
                                        <th rowspan="2" class="align-middle">Diameter</th>
                                        <th colspan="4">Panjang (cm)</th>
                                    </tr>
                                    <tr>
                                        <th>100</th>
                                        <th>130</th>
                                        <th>200</th>
                                        <th>260</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($grades as $grade)
                                    <tr class="table-secondary">
                                        <td colspan="5" class="text-center fw-bold">Grade: {{ $grade->name }}</td>
                                    </tr>
                                    @for($diameter = $grade->min_diameter; $diameter <= $grade->max_diameter; $diameter++)
                                    <tr>
                                        <td class="text-center">{{ $diameter }}</td>
                                        @foreach([100, 130, 200, 260] as $panjang)
                                        <td>
                                            @php
                                                $volume = $diameters->where('diameter', $diameter)->where('panjang', $panjang)->first()->volume ?? 0;
                                                $price = $woodPrices->where('wood_type_id', $type->id)
                                                    ->where('grade_id', $grade->id)
                                                    ->where('panjang', $panjang)
                                                    ->first()->price_per_m3 ?? 0;
                                            @endphp
                                            <input type="hidden" name="details[{{ $loop->parent->index }}][jenis_kayu_id]" value="{{ $type->id }}">
                                            <input type="hidden" name="details[{{ $loop->parent->index }}][grade_id]" value="{{ $grade->id }}">
                                            <input type="hidden" name="details[{{ $loop->parent->index }}][diameter]" value="{{ $diameter }}">
                                            <input type="hidden" name="details[{{ $loop->parent->index }}][panjang]" value="{{ $panjang }}">
                                            <input type="hidden" name="details[{{ $loop->parent->index }}][volume]" value="{{ $volume }}">
                                            <input type="hidden" name="details[{{ $loop->parent->index }}][price]" value="{{ $price }}">
                                            <input type="number" name="details[{{ $loop->parent->index }}][quantity]" 
                                                   class="form-control form-control-sm quantity text-center" 
                                                   data-wood-type="{{ $type->id }}" 
                                                   data-grade="{{ $grade->id }}" 
                                                   data-diameter="{{ $diameter }}" 
                                                   data-panjang="{{ $panjang }}" 
                                                   data-price="{{ $price }}" 
                                                   placeholder="pcs" 
                                                   value="0" 
                                                   required>
                                        </td>
                                        @endforeach
                                    </tr>
                                    @endfor
                                    <tr class="table-secondary">
                                        <td class="text-center fw-bold">Subtotal</td>
                                        @foreach([100, 130, 200, 260] as $panjang)
                                        <td>
                                            <input type="text" id="subtotal_volume_{{ $type->id }}_{{ $grade->id }}_{{ $panjang }}" class="form-control form-control-sm subtotal-volume text-center" readonly>
                                            <input type="text" id="subtotal_price_{{ $type->id }}_{{ $grade->id }}_{{ $panjang }}" class="form-control form-control-sm subtotal-price text-center mt-1" readonly>
                                        </td>
                                        @endforeach
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <div class="mt-2">
                                <label for="grand_total_volume_{{ $type->id }}" class="form-label fw-bold">Total Volume (m³)</label>
                                <input type="text" id="grand_total_volume_{{ $type->id }}" class="form-control form-control-sm grand-total-volume text-center" readonly>

                                <label for="grand_total_price_{{ $type->id }}" class="form-label fw-bold">Total Harga (Rp)</label>
                                <input type="text" id="grand_total_price_{{ $type->id }}" class="form-control form-control-sm grand-total-price text-center" readonly>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <p class="text-center text-danger">Data jenis kayu atau grade tidak tersedia.</p>
        @endif

        <div class="row mt-3">
            <div class="col-md-6">
                <label for="transaction_total_volume" class="form-label fw-bold">Grand Total Volume (m³)</label>
                <input type="text" id="transaction_total_volume" class="form-control form-control-sm transaction-total-volume text-center" readonly>

                <label for="transaction_total_price" class="form-label fw-bold">Grand Total Harga (Rp)</label>
                <input type="text" id="transaction_total_price" class="form-control form-control-sm transaction-total-price text-center" readonly>
            </div>
        </div>
        <div class="text-center mt-3">
            <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('input', function (event) {
        if (event.target.classList.contains('quantity')) {
            // Pastikan input quantity tidak kosong
            if (event.target.value === '') {
                event.target.value = 0;
            }

            const woodTypeId = event.target.dataset.woodType;
            const gradeId = event.target.dataset.grade;
            const panjang = event.target.dataset.panjang;

            // Hitung subtotal volume dan harga untuk panjang tertentu dalam grade
            let subtotalVolume = 0;
            let subtotalPrice = 0;

            document.querySelectorAll(`.quantity[data-wood-type="${woodTypeId}"][data-grade="${gradeId}"][data-panjang="${panjang}"]`).forEach(input => {
                const quantity = parseFloat(input.value) || 0;
                const volume = parseFloat(input.closest('td').querySelector('input[name*="[volume]"]').value) || 0;
                const price = parseFloat(input.dataset.price) || 0;

                subtotalVolume += quantity * volume;
                subtotalPrice += quantity * volume * price;
            });

            const subtotalVolumeField = document.getElementById(`subtotal_volume_${woodTypeId}_${gradeId}_${panjang}`);
            const subtotalPriceField = document.getElementById(`subtotal_price_${woodTypeId}_${gradeId}_${panjang}`);
            if (subtotalVolumeField && subtotalPriceField) {
                subtotalVolumeField.value = subtotalVolume.toFixed(3);
                subtotalPriceField.value = formatRupiah(subtotalPrice.toFixed(2));
            }

            // Hitung grand total volume dan harga untuk jenis kayu tertentu
            let grandTotalVolume = 0;
            let grandTotalPrice = 0;

            document.querySelectorAll(`.quantity[data-wood-type="${woodTypeId}"]`).forEach(input => {
                const quantity = parseFloat(input.value) || 0;
                const volume = parseFloat(input.closest('td').querySelector('input[name*="[volume]"]').value) || 0;
                const price = parseFloat(input.dataset.price) || 0;

                grandTotalVolume += quantity * volume;
                grandTotalPrice += quantity * volume * price;
            });

            const grandTotalVolumeField = document.getElementById(`grand_total_volume_${woodTypeId}`);
            const grandTotalPriceField = document.getElementById(`grand_total_price_${woodTypeId}`);
            if (grandTotalVolumeField && grandTotalPriceField) {
                grandTotalVolumeField.value = grandTotalVolume.toFixed(3);
                grandTotalPriceField.value = formatRupiah(grandTotalPrice.toFixed(2));
            }

            // Hitung grand total keseluruhan (semua jenis kayu)
            let overallTotalVolume = 0;
            let overallTotalPrice = 0;

            document.querySelectorAll('.quantity').forEach(input => {
                const quantity = parseFloat(input.value) || 0;
                const volume = parseFloat(input.closest('td').querySelector('input[name*="[volume]"]').value) || 0;
                const price = parseFloat(input.dataset.price) || 0;

                overallTotalVolume += quantity * volume;
                overallTotalPrice += quantity * volume * price;
            });

            const overallTotalVolumeField = document.getElementById('transaction_total_volume');
            const overallTotalPriceField = document.getElementById('transaction_total_price');
            if (overallTotalVolumeField && overallTotalPriceField) {
                overallTotalVolumeField.value = overallTotalVolume.toFixed(3);
                overallTotalPriceField.value = formatRupiah(overallTotalPrice.toFixed(2));
            }
        }
    });

    // Fungsi untuk memformat angka ke format Rupiah
    function formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
        }).format(angka);
    }
</script>
@endsection