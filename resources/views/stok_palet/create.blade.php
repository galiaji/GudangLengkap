@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="bg-light rounded-3 p-4 mb-4">
        <h2 class="mb-3">Tambah Data Palet</h2>
        <form action="{{ route('pallet_stocks.store') }}" method="POST" id="form-stok-palet">
            @csrf
            <div class="mb-4 row align-items-center">
                <label for="status" class="form-label fw-semibold col-12 col-sm-3 col-md-2 mb-2 mb-sm-0">Status</label>
                <div class="col-12 col-sm-5 col-md-3">
                    <select name="status" id="status" class="form-select" required>
                        <option value="basah">Basah</option>
                        <option value="penjemuran">Penjemuran</option>
                        <option value="kering">Kering</option>
                    </select>
                </div>
            </div>
            <div class="mb-4 row align-items-center" id="tanggal-row">
                <label for="tanggal_input" class="form-label fw-semibold col-12 col-sm-3 col-md-2 mb-2 mb-sm-0" id="tanggal-label">Tanggal Input</label>
                <div class="col-12 col-sm-5 col-md-3">
                    <input type="date" name="entry_date" id="tanggal_input" class="form-control" required>
                    @error('entry_date')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
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
            <div class="d-flex flex-column flex-sm-row gap-2 mt-3">
                <button type="submit" class="btn btn-success w-100 w-sm-auto">Simpan Semua</button>
                <a href="{{ route('pallet_stocks.index') }}" class="btn btn-secondary w-100 w-sm-auto">Kembali</a>
            </div>
        </form>
    </div>
</div>
<!-- Modal Sukses -->
<div class="modal fade" id="successModal" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header bg-success text-white">
      <h5 class="modal-title">Berhasil</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body" id="successModalMsg"></div>
  </div></div>
</div>
<!-- Modal Gagal -->
<div class="modal fade" id="errorModal" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header bg-danger text-white">
      <h5 class="modal-title">Gagal</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body" id="errorModalMsg"></div>
  </div></div>
</div>
<style>
    @media (max-width: 576px) {
        .table th, .table td {
            font-size: 0.95rem;
            padding: 0.5rem;
        }
        .bg-light {
            padding: 1rem !important;
        }
        h2 {
            font-size: 1.2rem;
        }
    }
</style>
<script>
    // Set default tanggal ke hari ini
    document.addEventListener('DOMContentLoaded', function() {
        const today = new Date();
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const dd = String(today.getDate()).padStart(2, '0');
        document.getElementById('tanggal_input').value = `${yyyy}-${mm}-${dd}`;
    });

    // Ganti label dan name tanggal sesuai status
    document.getElementById('status').addEventListener('change', function() {
        const status = this.value;
        const tanggalLabel = document.getElementById('tanggal-label');
        const tanggalInput = document.getElementById('tanggal_input');
        if (status === 'penjemuran') {
            tanggalLabel.textContent = 'Tanggal Mulai Jemur';
            tanggalInput.name = 'start_drying_date';
        } else {
            tanggalLabel.textContent = 'Tanggal Input';
            tanggalInput.name = 'entry_date';
        }
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

    document.querySelector('#form-stok-palet').addEventListener('submit', function(e) {
        e.preventDefault();
        let form = this;
        let formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {'X-Requested-With': 'XMLHttpRequest'}
        })
        .then(res => res.json().then(data => ({status: res.status, body: data})))
        .then(({status, body}) => {
            if (body.success) {
                document.getElementById('successModalMsg').innerText = body.message;
                new bootstrap.Modal(document.getElementById('successModal')).show();
                setTimeout(function() {
                    window.location.href = "{{ route('pallet_stocks.index') }}";
                }, 1000);
            } else {
                document.getElementById('errorModalMsg').innerText = body.message || 'Terjadi kesalahan.';
                new bootstrap.Modal(document.getElementById('errorModal')).show();
            }
        })
        .catch(() => {
            document.getElementById('errorModalMsg').innerText = 'Terjadi kesalahan jaringan.';
            new bootstrap.Modal(document.getElementById('errorModal')).show();
        });
    });
</script>
@endsection