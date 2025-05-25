@extends('layouts.app')

@section('title', 'Manage Akun')

@section('content')
<style>
    .account-card {
        border-radius: 10px;
        box-shadow: 0 2px 8px #e0f2f1;
        overflow: hidden;
        margin-bottom: 32px;
        background: #fff;
    }
    .account-card .card-header {
        background: #33691E;
        color: #fff;
        font-weight: 700;
        font-size: 1.2rem;
        letter-spacing: 1px;
        border-bottom: 1px solid #A5D6A7;
        padding: 14px 0 14px 0;
    }
    .account-card .card-body {
        padding: 20px 18px 18px 18px;
        background: #fff;
    }
    .table th, .table td {
        text-align: center;
        vertical-align: middle !important;
        white-space: nowrap;
    }
    .table th {
        background: #33691E;
        color: #fff;
        font-weight: 600;
    }
    .btn-sm {
        padding: 2px 10px;
        font-size: 0.95rem;
        margin-right: 4px;
    }
    .btn-danger.btn-sm {
        margin-right: 0;
    }
    .btn-success {
        background: #388e3c;
        border-color: #388e3c;
        font-weight: 600;
    }
    .btn-success:hover {
        background: #2e7031;
        border-color: #2e7031;
    }
    .table-responsive {
        margin-bottom: 0;
    }

    /* Responsive: Tablet */
    @media (max-width: 991.98px) {
        .account-card .card-body {
            padding: 14px 4px 12px 4px;
        }
        .table th, .table td {
            font-size: 0.93rem;
            padding: 4px 3px;
        }
        .btn-sm {
            font-size: 0.92rem;
            padding: 2px 7px;
        }
        .account-card .card-header {
            font-size: 1.05rem;
            padding: 10px 0;
        }
    }

    /* Responsive: Mobile */
    @media (max-width: 575.98px) {
        .account-card {
            margin-bottom: 14px;
        }
        .account-card .card-body {
            padding: 8px 1px 8px 1px;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .table {
            min-width: 600px;
            font-size: 0.85rem;
        }
        .table th, .table td {
            font-size: 0.85rem;
            padding: 3px 2px;
        }
        .btn-sm {
            font-size: 0.85rem;
            padding: 2px 6px;
        }
        .account-card .card-header {
            font-size: 0.97rem;
            padding: 8px 0;
        }
    }
</style>
<div class="d-flex justify-content-center">
    <div class="w-100">
        <div class="account-card card mt-4 w-100">
            <div class="card-header text-center">
                <span style="font-size:1.3rem;">Manajemen Akun</span>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                <div class="d-flex justify-content-end align-items-center mb-3">
                    <a href="{{ route('account.create') }}" class="btn btn-success btn-sm">+ Tambah Akun</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm mb-0 w-100">
                        <thead>
                            <tr>
                                <th style="width:40px;">No.</th>
                                <th>Nama</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th> <!-- Tambahkan kolom Role -->
                                <th>Ditambahkan Pada</th>
                                <th>Diupdate Pada</th>
                                <th style="width:110px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $i => $user)
                            <tr>
                                <td>{{ $i+1 }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->username }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    <span class="badge bg-success" style="font-size:0.97rem;">{{ ucfirst($user->role) }}</span>
                                </td>
                                <td>
                                    {{ \Carbon\Carbon::parse($user->created_at)->format('d-m-Y H:i') }}
                                </td>
                                <td>
                                    {{ \Carbon\Carbon::parse($user->updated_at)->format('d-m-Y H:i') }}
                                </td>
                                <td>
                                    <a href="{{ route('account.edit', $user->hashid) }}" class="btn btn-primary btn-sm">Edit</a>
                                    <form action="{{ route('account.destroy', $user) }}" method="POST" class="d-inline delete-form" style="display:none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                    <button type="button" class="btn btn-danger btn-sm btn-delete" data-action="{{ route('account.destroy', $user->hashid) }}">Hapus</button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">Belum ada akun.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmDeleteLabel">Konfirmasi Hapus</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        Yakin hapus akun ini?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Hapus</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let actionUrl = '';
    document.querySelectorAll('.btn-delete').forEach(function(btn) {
        btn.addEventListener('click', function() {
            actionUrl = this.getAttribute('data-action');
            var modal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
            modal.show();
        });
    });

    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        // Buat form hapus dinamis dan submit
        let form = document.createElement('form');
        form.method = 'POST';
        form.action = actionUrl;
        form.innerHTML = `
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" name="_method" value="DELETE">
        `;
        document.body.appendChild(form);
        form.submit();
    });
});
</script>
@endsection