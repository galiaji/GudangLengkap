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
                                <th>Ditambahkan Pada</th>
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
                                    {{ \Carbon\Carbon::parse($user->created_at)->format('d-m-Y H:i') }}
                                </td>
                                <td>
                                    <a href="{{ route('account.edit', $user) }}" class="btn btn-primary btn-sm">Edit</a>
                                    <form action="{{ route('account.destroy', $user) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Yakin hapus akun ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">Belum ada akun.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection