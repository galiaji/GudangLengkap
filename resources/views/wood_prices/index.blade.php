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
        font-size: 0.95rem;
        margin-bottom: 0;
        background: #fff;
        border-radius: 8px;
        overflow: hidden;
    }
    .table-container h3 {
        margin-top: 24px;
        margin-bottom: 12px;
        font-weight: 600;
        color: #33691E;
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
    .edit-btn {
        padding: 2px 8px;
        font-size: 0.85rem;
        margin-left: 4px;
    }
    .table-bordered > :not(caption) > * > * {
        border-color: #c5e1a5;
    }
    @media (max-width: 768px) {
        .table-container .table { font-size: 0.85rem; }
        .table-container h3 { font-size: 1.1rem; }
    }
</style>
<div class="container-fluid">
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

    @php
        $groupedWoodPrices = $woodPrices->groupBy('wood_type');
        $allLengths = $woodPrices->pluck('panjang')->unique()->sort()->values();
        $allGrades = $woodPrices->pluck('grade')->unique()->values();
    @endphp

    <div class="row table-container">
        @foreach ($groupedWoodPrices as $woodType => $prices)
            <div class="col-md-6 mb-4">
                <h3 class="text-center">{{ $woodType }}</h3>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover shadow-sm">
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
                                <tr class="row-color-{{ $gIndex % 4 }}">
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
                                                    <div class="modal-dialog modal-sm"> <!-- Tambahkan modal-sm -->
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
        @endforeach
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
// Pastikan format rupiah tetap aman untuk banyak form/modal
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