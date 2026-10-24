@extends('layouts.app')

@section('content')
<style>
    body {
        background: #f4faef;
    }
    .bg-light {
        background: #f7fbf7 !important;
    }
    .rounded-3 {
        border-radius: 18px !important;
    }
    h4, h5, .fw-bold {
        color: #388E3C;
    }
    .form-label {
        color: #388E3C;
        font-weight: 600;
    }
    .form-control:focus {
        border-color: #388E3C;
        box-shadow: 0 0 0 0.1rem #388e3c33;
    }
    .btn-success {
        background: #388E3C;
        border-color: #388E3C;
        border-radius: 20px;
        font-weight: 600;
    }
    .btn-success:hover, .btn-success:focus {
        background: #256d27;
        border-color: #256d27;
    }
    .table-success th {
        background: #e6f4e6 !important;
        color: #388E3C;
    }
    .table-info th {
        background: #e6f0fa !important;
        color: #256d27;
    }
    .table-bordered th, .table-bordered td {
        border-color: #b7e0b7 !important;
    }
    .table th, .table td {
        vertical-align: middle !important;
    }
    .table-sm th, .table-sm td {
        padding: 0.4rem 0.5rem;
    }
    /* Responsif khusus */
    @media (max-width: 991.98px) {
        .col-md-6 {
            margin-bottom: 2rem;
        }
    }
    @media (max-width: 767.98px) {
        .bg-light {
            padding: 1rem !important;
        }
        h4 {
            font-size: 1.2rem;
        }
        h5 {
            font-size: 1rem;
        }
        .table th, .table td {
            font-size: 0.95rem;
            padding: 0.3rem;
        }
        .row.g-3.align-items-end > div {
            margin-bottom: 1rem;
        }
    }
    @media (max-width: 576px) {
        .table-responsive {
            font-size: 0.92rem;
            margin-bottom: 1rem;
        }
        .table th, .table td {
            font-size: 0.92rem;
            padding: 0.18rem 0.18rem;
            white-space: nowrap;
        }
        /* Pastikan input PCS cukup lebar */
        .table input[type="number"] {
            min-width: 60px;
            padding-left: 4px;
            padding-right: 4px;
            font-size: 0.95rem;
        }
        .form-label {
            font-size: 0.98rem;
        }
        .btn {
            font-size: 0.98rem;
        }
    }
</style>
<div class="container py-4">
    <div class="bg-light rounded-3 p-4 mb-4">
        <h4 class="mb-4 fw-bold">Transaksi Penjualan Palet</h4>
        <form action="{{ route('pallet-sales.store') }}" method="POST">
            @csrf
            <div class="row g-3 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label fw-semibold">Tanggal</label>
                    <input type="date" name="tanggal" class="form-control"
                        max="{{ date('Y-m-d') }}"
                        value="{{ old('tanggal', date('Y-m-d')) }}" required>
                </div>
                <div class="col-12 col-md-5">
                    <label class="form-label fw-semibold">
                        Customer <span class="text-danger" style="font-size:0.95em;">*</span>
                    </label>
                    <input type="text" name="customer" class="form-control" value="{{ old('customer') }}">
                </div>
            </div>
            <div class="mb-3 mt-3">
                <label class="form-label fw-semibold">Keterangan</label>
                <textarea name="keterangan" class="form-control">{{ old('keterangan') }}</textarea>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-6 mb-4 mb-md-0">
                    <h5 class="text-center mb-3">Palet Basah</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-success">
                                <tr>
                                    <th>Ukuran</th>
                                    <th>PCS</th>
                                    <th>Volume</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ukuranList as $i => $ukuran)
                                <tr>
                                    <td>
                                        <input type="hidden" name="basah[{{ $i }}][ukuran]" value="{{ $ukuran->ukuran }}">
                                        {{-- Tabel Palet Basah --}}
                                        <input type="hidden" class="harga-basah" name="basah[{{ $i }}][harga_satuan]" value="{{ $ukuran->harga_basah }}">
                                        <input type="hidden" class="volume-basah" name="basah[{{ $i }}][volume]" value="{{ $ukuran->volume }}">
                                        {{ $ukuran->ukuran }}
                                    </td>
                                    <td>
                                        <input type="number" name="basah[{{ $i }}][pcs]" class="form-control pcs-basah" min="0"
                                            value="{{ old('basah.'.$i.'.pcs', 0) }}" data-idx="{{ $i }}" data-ukuran="{{ $ukuran->ukuran }}">
                                    </td>
                                    <td>
                                        <input type="number" step="0.000001" name="basah[{{ $i }}][volume]" class="form-control volume-basah"
                                            value="{{ old('basah.'.$i.'.volume', 0) }}" readonly id="volume-basah-{{ $i }}">
                                    </td>
                                    <td>
                                        <input type="text" name="basah[{{ $i }}][subtotal]" class="form-control subtotal-basah"
                                            value="{{ old('basah.'.$i.'.subtotal', 0) }}" readonly>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-md-6">
                    <h5 class="text-center mb-3">Palet Kering</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-info">
                                <tr>
                                    <th>Ukuran</th>
                                    <th>PCS</th>
                                    <th>Volume</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ukuranList as $i => $ukuran)
                                <tr>
                                    <td>
                                        <input type="hidden" name="kering[{{ $i }}][ukuran]" value="{{ $ukuran->ukuran }}">
                                        {{-- Tabel Palet Kering --}}
                                        <input type="hidden" class="harga-kering" name="kering[{{ $i }}][harga_satuan]" value="{{ $ukuran->harga_kering }}">
                                        <input type="hidden" class="volume-kering" name="kering[{{ $i }}][volume]" value="{{ $ukuran->volume }}">
                                        {{ $ukuran->ukuran }}
                                    </td>
                                    <td>
                                        <input type="number" name="kering[{{ $i }}][pcs]" class="form-control pcs-kering" min="0"
                                            value="{{ old('kering.'.$i.'.pcs', 0) }}" data-idx="{{ $i }}" data-ukuran="{{ $ukuran->ukuran }}">
                                    </td>
                                    <td>
                                        <input type="number" step="0.000001" name="kering[{{ $i }}][volume]" class="form-control volume-kering"
                                            value="{{ old('kering.'.$i.'.volume', 0) }}" readonly id="volume-kering-{{ $i }}">
                                    </td>
                                    <td>
                                        <input type="text" name="kering[{{ $i }}][subtotal]" class="form-control subtotal-kering"
                                            value="{{ old('kering.'.$i.'.subtotal', 0) }}" readonly>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-success px-4">Simpan Transaksi</button>
            </div>
        </form>
    </div>
</div>




<!-- Modal Error -->
<div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="errorModalLabel">Error</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        @if ($errors->any() || session('error'))
          @php
            $allErrors = $errors->all();
            if (session('error')) {
                $allErrors[] = session('error');
            }
          @endphp
          <ul class="mb-0">
            @foreach ($allErrors as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        @endif
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

@if ($errors->any() || session('error'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
        errorModal.show();
    });
</script>
@endif

<script>
    // Mapping ukuran ke volume per batang (m3)
    // Diambil dari tabel harga palet (dibagi 1000 seperti sebelumnya), jadi ukuran baru otomatis ikut
    const volumePerBatang = Object.fromEntries(
        Object.entries(@json($ukuranList->mapWithKeys(fn ($p) => [$p->ukuran => $p->volume_per_pcs])))
            .map(([ukuran, vol]) => [ukuran, vol / 1000])
    );

    // Hitung subtotal otomatis untuk palet basah
    document.querySelectorAll('.pcs-basah').forEach(function(input) {
        input.addEventListener('input', function() {
            let idx = this.getAttribute('data-idx');
            let pcs = parseFloat(this.value) || 0;
            let ukuran = this.getAttribute('data-ukuran');
            let volPerBatang = volumePerBatang[ukuran] || 0;
            let totalVolume = pcs * volPerBatang; // volumePerBatang sudah m³
            document.getElementById('volume-basah-' + idx).value = totalVolume.toFixed(3);

            let harga = parseFloat(document.querySelectorAll('.harga-basah')[idx].value) || 0;
            let subtotal = harga * totalVolume;
            document.querySelectorAll('.subtotal-basah')[idx].value = formatRupiah(subtotal);
        });
    });

    // Hitung subtotal otomatis untuk palet kering
    document.querySelectorAll('.pcs-kering').forEach(function(input) {
        input.addEventListener('input', function() {
            let idx = this.getAttribute('data-idx');
            let pcs = parseFloat(this.value) || 0;
            let ukuran = this.getAttribute('data-ukuran');
            let volPerBatang = volumePerBatang[ukuran] || 0;
            let totalVolume = pcs * volPerBatang; // volumePerBatang sudah m³
            document.getElementById('volume-kering-' + idx).value = totalVolume.toFixed(3);

            let harga = parseFloat(document.querySelectorAll('.harga-kering')[idx].value) || 0;
            let subtotal = harga * totalVolume;
            document.querySelectorAll('.subtotal-kering')[idx].value = formatRupiah(subtotal);
        });
    });

    // Fungsi format rupiah
function formatRupiah(angka) {
    return angka.toLocaleString('id-ID', { minimumFractionDigits: 0 });
}

// Format subtotal basah saat input
document.querySelectorAll('.pcs-basah').forEach(function(input, idx) {
    input.addEventListener('input', function() {
        let subtotalInput = document.querySelectorAll('.subtotal-basah')[idx];
        let subtotal = parseFloat(subtotalInput.value.replace(/\./g, '')) || 0;
        subtotalInput.value = formatRupiah(subtotal);
    });
    // Hapus angka 0 saat klik
    input.addEventListener('focus', function() {
        if (this.value == '0') this.value = '';
    });
});

// Format subtotal kering saat input
document.querySelectorAll('.pcs-kering').forEach(function(input, idx) {
    input.addEventListener('input', function() {
        let subtotalInput = document.querySelectorAll('.subtotal-kering')[idx];
        let subtotal = parseFloat(subtotalInput.value.replace(/\./g, '')) || 0;
        subtotalInput.value = formatRupiah(subtotal);
    });
    // Hapus angka 0 saat klik
    input.addEventListener('focus', function() {
        if (this.value == '0') this.value = '';
    });
});

// Format subtotal saat halaman dimuat
document.querySelectorAll('.subtotal-basah, .subtotal-kering').forEach(function(input) {
    let val = parseFloat(input.value.replace(/\./g, '')) || 0;
    input.value = formatRupiah(val);
});
</script>
@endsection