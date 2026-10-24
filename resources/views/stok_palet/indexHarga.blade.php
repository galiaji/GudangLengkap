@extends('layouts.app')

@section('title', 'Harga Palet')

@section('content')
<style>
    body {
        background: #f4faef;
    }
    .card {
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        border: none;
    }
    .card-header {
        background: #4e944f !important;
        color: #fff !important;
        border-radius: 16px 16px 0 0;
    }
    .table thead {
        background: #e6f4e6;
        color: #2d4d2d;
    }
    .table-bordered {
        border-color: #b6e2b6 !important;
    }
    .btn-warning {
        background: #4e944f;
        border: none;
        color: #fff;
    }
    .btn-warning:hover, .btn-warning:focus {
        background: #3b6e3b;
        color: #fff;
    }
    @media (max-width: 576px) {
        .card-header h4 {
            font-size: 1.1rem;
        }
        .table th, .table td {
            font-size: 0.95rem;
            padding: 0.5rem;
        }
        .modal-dialog {
            max-width: 95vw;
            margin: 1.75rem auto;
        }
    }
</style>
<div class="container-fluid px-1 px-md-4">
    <div class="card shadow-sm">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h4 class="mb-0">Daftar Harga Palet</h4>
            <a href="{{ route('pallet-prices.create') }}" class="btn btn-light btn-sm fw-semibold">
                + Tambah Ukuran Palet
            </a>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                </div>
            @endif
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="display:none">Kode Palet</th>
                            <th>Ukuran</th>
                            <th>Harga Basah</th>
                            <th>Harga Kering</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($palletPrices as $palletPrice)
                        <tr @if(session('new_id') == $palletPrice->id) class="table-warning" id="row-baru" @endif>
                            <td style="display:none">{{ $palletPrice->pallet_code }}</td>
                            <td>
                                {{ $palletPrice->ukuran }}
                                @if(session('new_id') == $palletPrice->id)
                                    <span class="badge bg-success ms-1">Baru</span>
                                @endif
                            </td>
                            <td>Rp {{ number_format($palletPrice->harga_basah, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($palletPrice->harga_kering, 0, ',', '.') }}</td>
                            <td>
                                <!-- Tombol Edit -->
                                <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal{{ $palletPrice->id }}">
                                    Edit
                                </button>
                            </td>
                        </tr>

                        <!-- Modal Edit -->
                        <div class="modal fade" id="editModal{{ $palletPrice->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $palletPrice->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('pallet-prices.update', $palletPrice->id) }}" method="POST" class="edit-harga-form">
                                        @csrf
                                        @method('PUT')
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Harga Palet</h5>
                                              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Kode Palet</label>
                                                    <input type="text" class="form-control" value="{{ $palletPrice->pallet_code }}" readonly>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Ukuran</label>
                                                    <input type="text" class="form-control" value="{{ $palletPrice->ukuran }}" readonly>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="harga_basah_{{ $palletPrice->id }}" class="form-label">Harga Basah</label>
                                                    <input type="text"
                                                        name="harga_basah"
                                                        id="harga_basah_{{ $palletPrice->id }}"
                                                        value="Rp{{ number_format(old('harga_basah', $palletPrice->harga_basah), 0, ',', '.') }}"
                                                        class="form-control" required oninput="formatRupiah(this)">
                                                </div>
                                                <div class="mb-3">
                                                    <label for="harga_kering_{{ $palletPrice->id }}" class="form-label">Harga Kering</label>
                                                    <input type="text" name="harga_kering" id="harga_kering_{{ $palletPrice->id }}"
                                                        value="Rp{{ number_format(old('harga_kering', $palletPrice->harga_kering), 0, ',', '.') }}"
                                                        class="form-control" required oninput="formatRupiah(this)">
                                                </div>
                                            </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-success">Simpan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Notifikasi -->
<div class="modal fade" id="notifModal" tabindex="-1" aria-labelledby="notifModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header" id="notifModalHeader">
        <h5 class="modal-title" id="notifModalLabel">Notifikasi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="notifModalMsg"></div>
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

    document.addEventListener('DOMContentLoaded', function () {
        // Format input saat halaman dimuat (jika ada value)
        document.querySelectorAll('input[name="harga_basah"], input[name="harga_kering"]').forEach(function(input) {
            formatRupiah(input);
        });

        // Event submit pada form edit harga
        document.querySelectorAll('.edit-harga-form').forEach(function(form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                let basah = form.querySelector('[name="harga_basah"]');
                let kering = form.querySelector('[name="harga_kering"]');
                if(basah) basah.value = basah.value.replace(/\D/g, '');
                if(kering) kering.value = kering.value.replace(/\D/g, '');

                let formData = new FormData(form);

                fetch(form.action, {
                    method: 'POST',
                    headers: {'X-Requested-With': 'XMLHttpRequest'},
                    body: formData
                })
                .then(res => res.json().then(data => ({status: res.status, body: data})))
                .then(({status, body}) => {
                    // Tutup modal edit sebelum tampilkan notif
                    let editModal = form.closest('.modal');
                    let bsEditModal = bootstrap.Modal.getInstance(editModal);
                    if (bsEditModal) bsEditModal.hide();

                    setTimeout(function() { // beri jeda agar animasi modal edit selesai
                        let modalHeader = document.getElementById('notifModalHeader');
                        let modalMsg = document.getElementById('notifModalMsg');
                        if (body.success) {
                            modalHeader.className = "modal-header bg-success text-white";
                            modalMsg.innerText = body.message || 'Harga berhasil diubah.';
                            new bootstrap.Modal(document.getElementById('notifModal')).show();
                            setTimeout(() => window.location.href = body.redirect_url, 1500);
                        } else {
                            modalHeader.className = "modal-header bg-danger text-white";
                            modalMsg.innerText = body.message || 'Gagal mengubah harga.';
                            new bootstrap.Modal(document.getElementById('notifModal')).show();
                        }
                    }, 400); // 400ms cukup untuk animasi modal edit
                })
                .catch(() => {
                    let editModal = form.closest('.modal');
                    let bsEditModal = bootstrap.Modal.getInstance(editModal);
                    if (bsEditModal) bsEditModal.hide();

                    setTimeout(function() {
                        let modalHeader = document.getElementById('notifModalHeader');
                        let modalMsg = document.getElementById('notifModalMsg');
                        modalHeader.className = "modal-header bg-danger text-white";
                        modalMsg.innerText = 'Terjadi kesalahan jaringan.';
                        new bootstrap.Modal(document.getElementById('notifModal')).show();
                    }, 400);
                });
            });
        });

        // Gulir ke baris ukuran yang baru ditambahkan
        var rowBaru = document.getElementById('row-baru');
        if (rowBaru) rowBaru.scrollIntoView({ behavior: 'smooth', block: 'center' });

        // Tampilkan toast jika ada
        var toastEl = document.getElementById('toastSuccess');
        if (toastEl) {
            var toast = new bootstrap.Toast(toastEl);
            toast.show();
        }
    });
</script>
@endsection
