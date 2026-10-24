@extends('layouts.app')

@section('title', 'Tambah Kombinasi Stok')

@section('content')
<style>
    .range-card {
        background: #fff;
        border: 1px solid #dcedc8;
        border-radius: 12px;
        overflow: hidden;
        max-width: 760px;
        margin: 0 auto;
    }
    .range-card-header {
        background: #33691E;
        color: #fff;
        padding: 14px 18px;
    }
    .range-card-header h4 { margin: 0; font-weight: 700; letter-spacing: .5px; }
    .range-card-header p { margin: 4px 0 0; font-size: .9rem; opacity: .9; }
    .range-card-body { padding: 20px 18px; }

    .range-card label {
        color: #33691E;
        font-weight: 600;
    }
    .range-card .form-control:focus,
    .range-card .form-select:focus {
        border-color: #689F38;
        box-shadow: 0 0 0 .15rem #c5e1a5;
    }

    .range-info {
        background: #f1f8e9;
        border: 1px solid #dcedc8;
        border-left: 4px solid #689F38;
        border-radius: 8px;
        color: #2d4d2d;
        padding: 12px 16px;
        font-size: .93rem;
    }
    .range-info ul { margin: 6px 0 0; padding-left: 1.2rem; }

    .btn-hijau {
        background: #558B2F;
        border: 1px solid #33691E;
        color: #fff;
        font-weight: 600;
        border-radius: 6px;
    }
    .btn-hijau:hover, .btn-hijau:focus { background: #33691E; color: #fff; }
    .btn-kembali {
        background: #fff;
        border: 1px solid #a5d6a7;
        color: #33691E;
        font-weight: 600;
        border-radius: 6px;
    }
    .btn-kembali:hover { background: #f1f8e9; color: #33691E; }

    @media (max-width: 575.98px) {
        .range-card-body { padding: 14px 12px; }
        .range-actions .btn { width: 100%; }
    }
</style>

<div class="container-fluid px-2 px-md-3 py-2">
    <div class="range-card shadow-sm">
        <div class="range-card-header">
            <h4>Tambah Kombinasi Stok</h4>
            <p>Daftarkan kombinasi jenis kayu, grade, diameter, dan panjang baru.</p>
        </div>

        <div class="range-card-body">

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('stok.range.store') }}" method="POST">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="wood_type_id" class="form-label">Jenis Kayu</label>
                        <select name="wood_type_id" id="wood_type_id" class="form-select @error('wood_type_id') is-invalid @enderror" required>
                            <option value="">Pilih jenis kayu</option>
                            @foreach ($woodTypes as $woodType)
                                <option value="{{ $woodType->id }}" @selected(old('wood_type_id') == $woodType->id)>
                                    {{ $woodType->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="grade" class="form-label">Grade</label>
                        <select name="grade" id="grade" class="form-select @error('grade') is-invalid @enderror" required>
                            <option value="">Pilih grade</option>
                            @foreach ($grades as $grade)
                                <option value="{{ $grade }}" @selected(old('grade') == $grade)>{{ $grade }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="diameter" class="form-label">Diameter (cm)</label>
                        <input type="number" name="diameter" id="diameter" value="{{ old('diameter') }}"
                               class="form-control @error('diameter') is-invalid @enderror"
                               min="1" placeholder="contoh: 41" required>
                    </div>

                    <div class="col-md-6">
                        <label for="panjang" class="form-label">Panjang (cm)</label>
                        <select name="panjang" id="panjang" class="form-select @error('panjang') is-invalid @enderror" required>
                            <option value="">Pilih panjang</option>
                            @foreach ([100, 130, 200, 260] as $p)
                                <option value="{{ $p }}" @selected(old('panjang') == $p)>{{ $p }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="range-info mt-4">
                    Sistem akan membuat kombinasi stok baru dengan:
                    <ul>
                        <li>Jumlah (quantity) = 0</li>
                        <li>Total volume = 0</li>
                        <li>Volume per batang dihitung otomatis</li>
                        <li>Harga mengikuti grade &amp; panjang (dibuat 0 jika belum ada)</li>
                    </ul>
                </div>

                <div class="range-actions d-flex flex-wrap gap-2 mt-4">
                    <button type="submit" class="btn btn-hijau">Simpan Kombinasi</button>
                    <a href="{{ url()->previous() }}" class="btn btn-kembali">Kembali</a>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection
