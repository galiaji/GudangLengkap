@extends('layouts.app')

@section('title', 'Jenis Kayu')

@section('content')
<div class="container">

    <div class="card">

        <div class="card-header">
            Jenis Kayu
        </div>

        <div class="card-body">

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('wood-types.store') }}"
                  method="POST">
                @csrf

                <div class="row">

                    <div class="col-md-10">
                        <input type="text"
                               name="name"
                               value="{{ old('name') }}"
                               class="form-control"
                               placeholder="Masukkan jenis kayu baru"
                               required>
                        <small class="text-muted">
                            Daftar harga dan kombinasi stok akan dibuat otomatis (harga awal 0).
                        </small>
                    </div>

                    <div class="col-md-2">
                        <button class="btn btn-success w-100">
                            Tambah
                        </button>
                    </div>

                </div>
            </form>

            <hr>

            <div class="table-responsive">
            <table class="table table-bordered align-middle">

                <thead>
                    <tr>
                        <th>No</th>
                        <th>Jenis Kayu</th>
                        <th>Kombinasi Stok</th>
                        <th>Total Stok (pcs)</th>
                        <th>Riwayat Transaksi</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($woodTypes as $woodType)
                    <tr @if($woodType->is_hidden) class="table-secondary text-muted" @endif>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $woodType->name }}</td>
                        <td>{{ $woodType->stocks_count }}</td>
                        <td>{{ (int) $woodType->total_quantity }}</td>
                        <td>{{ $woodType->transaksi_count }}</td>
                        <td>
                            @if ($woodType->is_hidden)
                                <span class="badge bg-secondary">Disembunyikan</span>
                            @else
                                <span class="badge bg-success">Tampil</span>
                            @endif
                        </td>
                        <td>
                          <div class="d-flex flex-wrap gap-1">
                            {{-- Sembunyikan / Tampilkan --}}
                            @if ($woodType->is_hidden)
                                <form action="{{ route('wood-types.unhide', $woodType->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-primary">Tampilkan</button>
                                </form>
                            @elseif ((int) $woodType->total_quantity === 0)
                                <form action="{{ route('wood-types.hide', $woodType->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-outline-secondary"
                                            title="Sembunyikan dari form & dashboard, riwayat tetap tersimpan">
                                        Sembunyikan
                                    </button>
                                </form>
                            @endif

                            {{-- Hapus --}}
                            @if ($woodType->deleteBlocker)
                                <button type="button" class="btn btn-sm btn-outline-danger" disabled>Hapus</button>
                          </div>
                                <div class="small text-muted mt-1">Tidak bisa dihapus: {{ $woodType->deleteBlocker }}</div>
                            @else
                                <button type="button" class="btn btn-sm btn-danger"
                                        data-bs-toggle="modal" data-bs-target="#hapusModal{{ $woodType->id }}">
                                    Hapus
                                </button>
                          </div>

                                <div class="modal fade" id="hapusModal{{ $woodType->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <form action="{{ route('wood-types.destroy', $woodType->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <div class="modal-header bg-danger text-white">
                                                    <h5 class="modal-title">Hapus Jenis Kayu</h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                                </div>
                                                <div class="modal-body">
                                                    Yakin menghapus jenis kayu <strong>{{ $woodType->name }}</strong>?
                                                    {{ $woodType->stocks_count }} kombinasi stok (semua kosong) dan daftar harganya
                                                    juga akan dihapus. Tindakan ini tidak bisa dibatalkan.
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>

            </table>
            </div>

        </div>
    </div>

</div>
@endsection