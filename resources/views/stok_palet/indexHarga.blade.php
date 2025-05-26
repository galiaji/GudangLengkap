@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Daftar Harga Palet</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Kode Palet</th>
                            <th>Ukuran</th>
                            <th>Harga Basah</th>
                            <th>Harga Kering</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($palletPrices as $palletPrice)
                        <tr>
                            <td>{{ $palletPrice->pallet_code }}</td>
                            <td>{{ $palletPrice->ukuran }}</td>
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
                                    </div>
                                </form>
                            </div>
                        </div>
                        @endforeach
                    </tbody>
                </table>
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

    document.addEventListener('DOMContentLoaded', function () {
        // Format input saat halaman dimuat (jika ada value)
        document.querySelectorAll('input[name="harga_basah"], input[name="harga_kering"]').forEach(function(input) {
            formatRupiah(input);
        });

        // Event submit pada form edit harga
        document.querySelectorAll('.edit-harga-form').forEach(function(form) {
            form.addEventListener('submit', function (e) {
                let basah = form.querySelector('[name="harga_basah"]');
                let kering = form.querySelector('[name="harga_kering"]');
                if(basah) basah.value = basah.value.replace(/\D/g, '');
                if(kering) kering.value = kering.value.replace(/\D/g, '');
            });
        });

        // Tampilkan toast jika ada
        var toastEl = document.getElementById('toastSuccess');
        if (toastEl) {
            var toast = new bootstrap.Toast(toastEl);
            toast.show();
        }
    });
</script>
@endsection
