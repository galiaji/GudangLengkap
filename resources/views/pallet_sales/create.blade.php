@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Transaksi Penjualan Palet</h4>
    <form action="{{ route('pallet-sales.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label>Tanggal</label>
            <input type="date" name="tanggal" class="form-control" required
    max="{{ date('Y-m-d') }}"
    value="{{ old('tanggal', date('Y-m-d')) }}">
        </div>
        <div class="mb-3">
            <label>Customer</label>
            <input type="text" name="customer" class="form-control" value="{{ old('customer') }}">
        </div>
        <div class="mb-3">
            <label>Keterangan</label>
            <textarea name="keterangan" class="form-control">{{ old('keterangan') }}</textarea>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-6">
                <h5 class="text-center">Palet Basah</h5>
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
                                <input type="hidden" class="harga-basah" name="basah[{{ $i }}][harga_satuan]" value="{{ $ukuran->harga_basah }}">
                                <input type="hidden" class="volume-basah" name="basah[{{ $i }}][volume]" value="{{ $ukuran->volume }}">
                                {{ $ukuran->ukuran }}
                            </td>
                            <td>
                                <input type="number" name="basah[{{ $i }}][pcs]" class="form-control pcs-basah" min="0"
    value="{{ old('basah.'.$i.'.pcs', 0) }}" data-idx="{{ $i }}" data-ukuran="{{ $ukuran->ukuran }}">
                            </td>
                            <td>
                                <input type="number" step="0.000001" name="basah[{{ $i }}][volume]" class="form-control volume-basah" value="0" readonly id="volume-basah-{{ $i }}">
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
            <div class="col-md-6">
                <h5 class="text-center">Palet Kering</h5>
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
        <div class="mt-3">
            <button type="submit" class="btn btn-success">Simpan Transaksi</button>
        </div>
    </form>
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
    const volumePerBatang = {
        "8x1.2x100": 0.96 / 1000,
        "8x1.2x130": 1.248/ 1000,
        "10x1.2x100": 1.2/ 1000,
        "10x1.2x130": 1.56/ 1000,
        "12x1.2x100": 1.44/ 1000,
        "12x1.2x130": 1.872/ 1000,
        "15x1.3x100": 1.8/ 1000,
        "15x1.3x130": 2.34/ 1000
        // Tambahkan sesuai kebutuhan
    };

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