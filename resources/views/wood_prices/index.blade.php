@extends('layouts.app')

@section('title', 'Daftar Harga Kayu')

@section('content')
@php
    $groupedWoodPrices = $woodPrices->groupBy('wood_type');
    $allLengths = $woodPrices->pluck('panjang')->unique()->sort()->values();
    $allGrades = $woodPrices->pluck('grade')->unique()->values();
@endphp
<style>
    .grade-table {
        border-radius: 8px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 2px 8px #e0f2f1;
    }
    .grade-table th, .grade-table td {
        border: 1px solid #c5e1a5;
        text-align: center;
        padding: 4px 6px;
        font-size: 0.97rem;
    }
    .grade-table th {
        background: #33691E;
        color: #fff;
        font-weight: 600;
    }
    .grade-table .row-label {
        background: #f1f8e9;
        font-weight: bold;
        color: #33691E;
    }
    .grade-table .summary {
        background: #A5D6A7;
        font-weight: bold;
        color: #33691E;
    }
    .edit-btn {
        padding: 2px 8px;
        font-size: 0.85rem;
        margin-left: 4px;
    }
    .table-hover tbody tr:hover {
        background-color: #A5D6A7 !important;
        transition: background 0.2s;
    }
    h2, h3, h1 {
        color: #33691E;
        font-weight: 700;
        letter-spacing: 1px;
    }
    label {
        color: #33691E;
        font-weight: 600;
    }
    @media (max-width: 768px) {
        .grade-table th, .grade-table td { font-size: 0.89rem; }
        h2, h3, h1 { font-size: 1.2rem; }
    }
</style>

<div class="d-flex justify-content-center">
    <div style="width:100%; max-width: 98vw;">
        <div class="card shadow-sm" style="border-radius:10px; overflow-x:auto; padding:0;">
            <div class="card-body" style="padding:18px 12px;">
                <h1 class="mb-4 text-center" style="font-weight:700;color:#33691E;">Daftar Harga Kayu</h1>
                @if (session('success'))
                <div class="position-fixed top-0 end-0 p-3" style="z-index: 1055;">
                    <div id="toastSuccess" class="toast align-items-center text-bg-success border-0 show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="3000">
                        <div class="d-flex">
                            <div class="toast-body">
                                {{ session('success') }}
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>
                </div>
                @endif

                <div class="row">
                    @foreach ($groupedWoodPrices as $woodType => $prices)
                        <div class="col-md-6 mb-4">
                            <div class="grade-table mb-3">
                                <h3 class="text-center py-2" style="background: #f1f8e9; margin:0;">{{ $woodType }}</h3>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th style="width:120px;">Grade</th>
                                                @foreach ($allLengths as $length)
                                                    <th>{{ $length }} cm</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($allGrades as $gIndex => $grade)
                                                <tr style="background:{{ $gIndex % 2 == 0 ? '#f8fafc' : '#f1f8e9' }};">
                                                    <td style="font-weight:500;">
                                                        {{ $gradeRanges[$grade] ?? $grade }}
                                                    </td>
                                                    @foreach ($allLengths as $length)
                                                        @php
                                                            $price = $prices->where('grade', $grade)->where('panjang', $length)->first();
                                                        @endphp
                                                        <td>
                                                            @if ($price)
                                                                <span style="font-weight:500;">
                                                                    Rp{{ number_format($price->price_per_m3, 0, ',', '.') }}
                                                                </span>
                                                                <button type="button"
                                                                        class="btn btn-warning btn-sm edit-btn"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#editHargaModal-{{ $price->id }}">
                                                                    <i class="bi bi-pencil-square"></i> Edit
                                                                </button>
                                                                <!-- Modal Edit Harga -->
                                                                <div class="modal fade" id="editHargaModal-{{ $price->id }}" tabindex="-1" aria-labelledby="editHargaModalLabel-{{ $price->id }}" aria-hidden="true">
                                                                    <div class="modal-dialog modal-sm">
                                                                        <div class="modal-content">
                                                                            <div class="modal-header py-2" style="background:#33691E;">
                                                                                <h6 class="modal-title text-white" id="editHargaModalLabel-{{ $price->id }}">Edit Harga Kayu</h6>
                                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="background-color:#fff;"></button>
                                                                            </div>
                                                                            <form action="{{ route('wood_prices.update', \Vinkla\Hashids\Facades\Hashids::encode($price->id)) }}" method="POST" class="edit-harga-form">
                                                                                @csrf
                                                                                @method('PUT')
                                                                                <div class="modal-body py-2 px-3" style="font-size:0.97rem;">
                                                                                    <div class="row align-items-center mb-1">
                                                                                        <label class="col-5 col-form-label text-start ps-2" style="padding-right:0;">Jenis Kayu</label>
                                                                                        <div class="col-7">
                                                                                            <input type="text" class="form-control form-control-sm pe-2" value="{{ $price->wood_type }}" disabled>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="row align-items-center mb-1">
                                                                                        <label class="col-5 col-form-label text-start ps-2" style="padding-right:0;">Grade</label>
                                                                                        <div class="col-7">
                                                                                            <input type="text" class="form-control form-control-sm pe-2"
                                                                                                value="{{ $gradeRanges[$price->grade] ?? $price->grade }}" disabled>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="row align-items-center mb-1">
                                                                                        <label class="col-5 col-form-label text-start ps-2" style="padding-right:0;">Panjang</label>
                                                                                        <div class="col-7">
                                                                                            <input type="number" class="form-control form-control-sm pe-2" value="{{ $price->panjang }}" disabled>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="row align-items-center mb-1">
                                                                                        <label class="col-5 col-form-label text-start ps-2" style="padding-right:0;">Harga/m³</label>
                                                                                        <div class="col-7">
                                                                                            <input type="text" name="price_per_m3" class="form-control form-control-sm pe-2"
                                                                                                value="{{ number_format($price->price_per_m3, 0, ',', '.') }}"
                                                                                                oninput="formatRupiah(this)">
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="modal-footer py-2">
                                                                                    <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                                                                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                                                                </div>
                                                                            </form>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @else
                                                                <span style="color:#bbb;">-</span>
                                                            @endif
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
<script>
function formatRupiah(input) {
    let value = input.value.replace(/\D/g, '');
    input.value = new Intl.NumberFormat('id-ID', {
        style: 'decimal',
        maximumFractionDigits: 0
    }).format(value);
}
document.querySelectorAll('.edit-harga-form').forEach(function(form) {
    form.addEventListener('submit', function (e) {
        const priceInput = form.querySelector('[name="price_per_m3"]');
        if(priceInput) priceInput.value = priceInput.value.replace(/\./g, '');
    });
});
document.addEventListener('DOMContentLoaded', function () {
    var toastEl = document.getElementById('toastSuccess');
    if (toastEl) {
        var toast = new bootstrap.Toast(toastEl);
        toast.show();
    }
});
</script>
@endsection