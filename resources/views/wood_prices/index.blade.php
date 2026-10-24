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
        padding: 2px 6px;
        font-size: 0.82rem;
        margin-left: 4px;
        margin-top: 2px;
        margin-bottom: 2px;
        min-width: 48px;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        justify-content: center;
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
    
    @media (max-width: 991.98px) {
        .grade-table th, .grade-table td { font-size: 0.85rem; padding: 3px 2px; }
            .col-md-6 { flex: 0 0 100%;2, h3, h1 { font-size: 1.1rem; }
        .col-md-6 { flex: 0 0 100%; max-width: 100%; }
        .grade-table { font-size: 0.92rem; }
        .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    }
    @media (max-width: 575.98px) {
        .edit-btn {
            padding: 2px 4px;
            font-size: 0.78rem;
            min-width: 38px;
            margin-left: 2px;
        }
        .grade-table th, .grade-table td {
            padding: 2px 2px;
            font-size: 0.80rem;
        }
        h2, h3, h1 { font-size: 1rem; }
        .grade-table { font-size: 0.88rem; }
        .table-responsive { overflow-x: auto; }
    }

    /* Modal responsif */
    .modal-dialog {
        max-width: 420px;
        margin: 1.75rem auto;
    }
    @media (max-width: 575.98px) {
        .modal-dialog {
            max-width: 98vw;
            margin: 0.5rem auto;
        }
        .modal-content {
            border-radius: 8px;
            padding: 2px 2px 8px 2px;
        }
        .modal-header, .modal-footer {
            padding: 8px 10px;
        }
        .modal-title {
            font-size: 1.05rem;
        }
        .modal-body {
            padding: 10px 10px 4px 10px;
            font-size: 0.97rem;
        }
        .form-label, .modal-body strong {
            font-size: 0.95rem;
        }
        .btn {
            font-size: 0.95rem;
            padding: 4px 10px;
        }
    }
    /* Toast responsif */
    @media (max-width: 575.98px) {
        .toast {
            min-width: 180px;
            font-size: 0.95rem;
            padding: 6px 8px;
        }
        .toast-body {
            padding: 6px 8px;
        }
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
                        @php $isNew = session('new_wood_type') === $woodType; @endphp
                        <div class="col-md-6 mb-4" @if($isNew) id="harga-baru" @endif>
                            <div class="grade-table mb-3" @if($isNew) style="outline: 3px solid #f0ad4e; outline-offset: 2px;" @endif>
                                <h3 class="text-center py-2" style="background: {{ $isNew ? '#fff3cd' : '#f1f8e9' }}; margin:0;">
                                    {{ $woodType }}
                                    @if($isNew)
                                        <span class="badge bg-success align-middle" style="font-size:0.6em;">Baru, isi harganya</span>
                                    @endif
                                </h3>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th style="width:120px;">Grade</th>
                                                <th style="width:100px;">Type Harga</th>
                                                @foreach ($allLengths as $length)
                                                    <th>{{ $length }} cm</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($allGrades as $gIndex => $grade)
                                                {{-- Baris BELI --}}
                                                <tr style="background:{{ $gIndex % 2 == 0 ? '#f8fafc' : '#f1f8e9' }};">
                                                    <td style="font-weight:500;" rowspan="2" class="align-middle">
                                                        {{ $gradeRanges[$grade] ?? $grade }}
                                                    </td>
                                                    <td class="align-middle" style="font-weight:600;">Beli</td>
                                                    @foreach ($allLengths as $length)
                                                        @php
                                                            $price = $prices->where('grade', $grade)->where('panjang', $length)->first();
                                                        @endphp
                                                        <td>
                                                            @if ($price)
                                                                <div style="display:flex; align-items:center; justify-content:space-between;">
                                                                    <span style="font-weight:500;">Rp{{ number_format($price->harga_beli_per_m3, 0, ',', '.') }}</span>
                                                                    <button type="button"
                                                                            class="btn btn-warning btn-sm edit-btn"
                                                                            style="margin-left:6px;padding:2px 10px;font-size:0.9rem;"
                                                                            data-bs-toggle="modal"
                                                                            data-bs-target="#editBeliModal-{{ $price->id }}">
                                                                        <i class="bi bi-pencil-square"></i> Edit
                                                                    </button>
                                                                </div>
                                                                <!-- Modal Edit Harga Beli -->
                                                                <div class="modal fade" id="editBeliModal-{{ $price->id }}" tabindex="-1">
                                                                    <div class="modal-dialog">
                                                                        <div class="modal-content">
                                                                            <form action="{{ route('wood_prices.updateBeli', \Vinkla\Hashids\Facades\Hashids::encode($price->id)) }}" method="POST" class="edit-harga-form">
                                                                                @csrf
                                                                                <div class="modal-header">
                                                                                    <h5 class="modal-title">Edit Harga Beli</h5>
                                                                                    <span class="badge bg-success ms-2">Anda sedang mengedit HARGA BELI</span>
                                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                                </div>
                                                                                <div class="modal-body">
                                                                                    <div class="mb-2">
                                                                                        <strong>Tipe Kayu:</strong> {{ $price->wood_type }}
                                                                                    </div>
                                                                                    <div class="mb-2">
                                                                                        <strong>Grade:</strong> {{ $price->grade }}
                                                                                    </div>
                                                                                    <div class="mb-2">
                                                                                        <strong>Panjang:</strong> {{ $price->panjang }} cm
                                                                                    </div>
                                                                                    <div class="mb-2">
                                                                                        <strong>Tipe Harga:</strong> Beli
                                                                                    </div>
                                                                                    <div class="mb-3">
                                                                                        <label for="harga_beli_per_m3_{{ $price->id }}" class="form-label">Harga Beli per m³</label>
                                                                                        <input type="text" name="harga_beli_per_m3" id="harga_beli_per_m3_{{ $price->id }}"
                                                                                            value="Rp{{ number_format(old('harga_beli_per_m3', $price->harga_beli_per_m3), 0, ',', '.') }}"
                                                                                            class="form-control" required oninput="formatRupiah(this)">
                                                                                    </div>
                                                                                </div>
                                                                                <div class="modal-footer">
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
                                                {{-- Baris JUAL --}}
                                                <tr style="background:{{ $gIndex % 2 == 0 ? '#f8fafc' : '#f1f8e9' }};">
                                                    <td class="align-middle" style="font-weight:600;">Jual</td>
                                                    @foreach ($allLengths as $length)
                                                        @php
                                                            $price = $prices->where('grade', $grade)->where('panjang', $length)->first();
                                                        @endphp
                                                        <td>
                                                            @if ($price)
                                                                <div style="display:flex; align-items:center; justify-content:space-between;">
                                                                    <span style="font-weight:500;">Rp{{ number_format($price->harga_jual_per_m3, 0, ',', '.') }}</span>
                                                                    <button type="button"
                                                                            class="btn btn-warning btn-sm edit-btn"
                                                                            style="margin-left:6px;padding:2px 10px;font-size:0.9rem;"
                                                                            data-bs-toggle="modal"
                                                                            data-bs-target="#editJualModal-{{ $price->id }}">
                                                                        <i class="bi bi-pencil-square"></i> Edit
                                                                    </button>
                                                                </div>
                                                                <!-- Modal Edit Harga Jual -->
                                                                <div class="modal fade" id="editJualModal-{{ $price->id }}" tabindex="-1">
                                                                    <div class="modal-dialog">
                                                                        <div class="modal-content">
                                                                            <form action="{{ route('wood_prices.updateJual', \Vinkla\Hashids\Facades\Hashids::encode($price->id)) }}" method="POST" class="edit-harga-form">
                                                                                @csrf
                                                                                <div class="modal-header">
                                                                                    <h5 class="modal-title">Edit Harga Jual</h5>
                                                                                    <span class="badge bg-info ms-2">Anda sedang mengedit HARGA JUAL</span>
                                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                                </div>
                                                                                <div class="modal-body">
                                                                                    <div class="mb-2">
                                                                                        <strong>Tipe Kayu:</strong> {{ $price->wood_type }}
                                                                                    </div>
                                                                                    <div class="mb-2">
                                                                                        <strong>Grade:</strong> {{ $price->grade }}
                                                                                    </div>
                                                                                    <div class="mb-2">
                                                                                        <strong>Panjang:</strong> {{ $price->panjang }} cm
                                                                                    </div>
                                                                                    <div class="mb-2">
                                                                                        <strong>Tipe Harga:</strong> Jual
                                                                                    </div>
                                                                                    <div class="mb-3">
                                                                                        <label for="harga_jual_per_m3_{{ $price->id }}" class="form-label">Harga Jual per m³</label>
                                                                                        <input type="text" 
                                                                                            name="harga_jual_per_m3" 
                                                                                            id="harga_jual_per_m3_{{ $price->id }}"
                                                                                            value="Rp{{ number_format(old('harga_jual_per_m3', $price->harga_jual_per_m3), 0, ',', '.') }}"
                                                                                            class="form-control" required oninput="formatRupiah(this)">
                                                                                    </div>
                                                                                </div>
                                                                                <div class="modal-footer">
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
    let value = input.value.replace(/[^0-9]/g, ''); // hanya angka
    if (!value) {
        input.value = '';
        return;
    }
    input.value = 'Rp' + value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}
document.querySelectorAll('.edit-harga-form').forEach(function(form) {
    form.addEventListener('submit', function (e) {
        let beli = form.querySelector('[name="harga_beli_per_m3"]');
        let jual = form.querySelector('[name="harga_jual_per_m3"]');
        if(beli) beli.value = beli.value.replace(/\D/g, '');
        if(jual) jual.value = jual.value.replace(/\D/g, '');
    });
});
document.addEventListener('DOMContentLoaded', function () {
    // Gulir ke tabel harga jenis kayu yang baru ditambahkan
    var hargaBaru = document.getElementById('harga-baru');
    if (hargaBaru) hargaBaru.scrollIntoView({ behavior: 'smooth', block: 'start' });

    var toastEl = document.getElementById('toastSuccess');
    if (toastEl) {
        var toast = new bootstrap.Toast(toastEl);
        toast.show();
    }
});
</script>
@endsection