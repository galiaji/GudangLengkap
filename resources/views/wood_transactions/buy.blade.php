@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="mb-4">Beli Stok</h1>
    <form action="{{ route('wood_transactions.storeBuy') }}" method="POST">
        @csrf

        <div class="row">
            @foreach($woodTypes as $type)
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h2 class="h5 mb-0">{{ $type->name }}</h2>
                    </div>
                    <div class="card-body">
                        @foreach($grades as $grade)
                        <div class="card mb-3">
                            <div class="card-header">
                                <h4 class="h6 mb-0">Grade: {{ $grade->name }}</h4>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Diameter</th>
                                            <th>100</th>
                                            <th>130</th>
                                            <th>200</th>
                                            <th>260</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @for($diameter = $grade->min_diameter; $diameter <= $grade->max_diameter; $diameter++)
                                            @if(($grade->name === 'Super' || str_contains($grade->name, 'Rijek')) && $diameter > 30)
                                                @break
                                            @endif
                                            <tr>
                                                <td>
                                                    <input type="hidden" name="jenis_kayu_id[]" value="{{ $type->id }}">
                                                    <input type="hidden" name="grade_id[]" value="{{ $grade->id }}">
                                                    <input type="hidden" name="diameter[]" value="{{ $diameter }}">
                                                    {{ $diameter }}
                                                </td>
                                                @foreach([100, 130, 200, 260] as $panjang)
                                                <td>
                                                    @php
                                                        $volume = $diameters->where('diameter', $diameter)->where('panjang', $panjang)->first()->volume ?? 0;
                                                        $price = $woodPrices->where('wood_type_id', $type->id)
                                                            ->where('grade_id', $grade->id)
                                                            ->where('panjang', $panjang)
                                                            ->first()->price_per_m3 ?? 0;
                                                    @endphp
                                                    <input type="hidden" name="panjang[]" value="{{ $panjang }}">
                                                    <input type="hidden" name="volume[]" value="{{ $volume }}">
                                                    <input type="hidden" name="price[]" value="{{ $price }}">
                                                    <input type="number" 
                                                           name="quantity[]" 
                                                           class="form-control quantity" 
                                                           data-wood-type="{{ $type->id }}" 
                                                           data-diameter="{{ $diameter }}" 
                                                           data-panjang="{{ $panjang }}" 
                                                           data-grade="{{ $grade->id }}" 
                                                           data-price="{{ $price }}" 
                                                           placeholder="pcs" 
                                                           required>
                                                </td>
                                                @endforeach
                                            </tr>
                                        @endfor
                                        <tr class="table-secondary">
                                            <td><strong>Subtotal</strong></td>
                                            @foreach([100, 130, 200, 260] as $panjang)
                                            <td>
                                                <input type="text" id="subtotal_volume_{{ $type->id }}_{{ $grade->id }}_{{ $panjang }}" class="form-control subtotal-volume" readonly placeholder="Volume (m³)">
                                                <input type="text" id="subtotal_price_{{ $type->id }}_{{ $grade->id }}_{{ $panjang }}" class="form-control subtotal-price mt-2" readonly placeholder="Harga (Rp)">
                                            </td>
                                            @endforeach
                                        </tr>
                                    </tbody>
                                </table>

                                <div class="row">
                                    <div class="col-md-6">
                                        <label for="total_volume_{{ $type->id }}_{{ $grade->id }}" class="form-label">Total Volume untuk Grade (m³)</label>
                                        <input type="text" id="total_volume_{{ $type->id }}_{{ $grade->id }}" class="form-control total-volume mb-3" readonly>

                                        <label for="total_price_{{ $type->id }}_{{ $grade->id }}" class="form-label">Total Harga untuk Grade (Rp)</label>
                                        <input type="text" id="total_price_{{ $type->id }}_{{ $grade->id }}" class="form-control total-price" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <button type="submit" class="btn btn-primary">Tambah Stok</button>
    </form>
</div>

<script>
    document.addEventListener('input', function (event) {
        if (event.target.classList.contains('quantity')) {
            const woodTypeId = event.target.dataset.woodType;
            const gradeId = event.target.dataset.grade;
            const panjang = event.target.dataset.panjang;
            const price = parseFloat(event.target.dataset.price);

            // Hitung subtotal volume dan harga untuk panjang tertentu
            let subtotalVolume = 0;
            let subtotalPrice = 0;

            // Filter input berdasarkan woodType, grade, dan panjang
            document.querySelectorAll(`.quantity[data-wood-type="${woodTypeId}"][data-grade="${gradeId}"][data-panjang="${panjang}"]`).forEach(input => {
                const quantity = parseFloat(input.value) || 0;
                const volume = parseFloat(input.closest('td').querySelector('input[name="volume[]"]').value) || 0;
                subtotalVolume += quantity * volume;
                subtotalPrice += quantity * volume * price;
            });

            // Update subtotal fields
            const subtotalVolumeField = document.getElementById(`subtotal_volume_${woodTypeId}_${gradeId}_${panjang}`);
            const subtotalPriceField = document.getElementById(`subtotal_price_${woodTypeId}_${gradeId}_${panjang}`);
            if (subtotalVolumeField && subtotalPriceField) {
                subtotalVolumeField.value = subtotalVolume.toFixed(2);
                subtotalPriceField.value = subtotalPrice.toFixed(2);
            }

            // Hitung total volume dan harga untuk seluruh grade dalam jenis kayu tertentu
            let totalVolume = 0;
            let totalPrice = 0;

            // Filter input berdasarkan woodType dan grade
            document.querySelectorAll(`.quantity[data-wood-type="${woodTypeId}"][data-grade="${gradeId}"]`).forEach(input => {
                const quantity = parseFloat(input.value) || 0;
                const volume = parseFloat(input.closest('td').querySelector('input[name="volume[]"]').value) || 0;
                const price = parseFloat(input.dataset.price) || 0;
                totalVolume += quantity * volume;
                totalPrice += quantity * volume * price;
            });

            // Update total fields
            const totalVolumeField = document.getElementById(`total_volume_${woodTypeId}_${gradeId}`);
            const totalPriceField = document.getElementById(`total_price_${woodTypeId}_${gradeId}`);
            if (totalVolumeField && totalPriceField) {
                totalVolumeField.value = totalVolume.toFixed(2);
                totalPriceField.value = totalPrice.toFixed(2);
            }
        }
    });
</script>
@endsection