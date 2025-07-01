@extends('layouts.app')

@section('title', 'Edit Akun')

@section('content')
@php use Vinkla\Hashids\Facades\Hashids; @endphp
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
        font-size: 1.15rem;
        letter-spacing: 1px;
        border-bottom: 1px solid #A5D6A7;
        padding: 14px 0;
    }
    .account-card .card-body {
        padding: 18px 12px;
        background: #fff;
    }
    label {
        color: #33691E;
        font-weight: 600;
    }
    h2 {
        color: #33691E;
        font-weight: 700;
        letter-spacing: 1px;
    }
    @media (max-width: 575.98px) {
        .account-card {
            margin-bottom: 12px;
        }
        .account-card .card-header {
            font-size: 1rem;
            padding: 10px 0;
        }
        .account-card .card-body {
            padding: 10px 2px;
        }
        .form-control {
            font-size: 0.95rem;
            padding: 6px 8px;
        }
        label {
            font-size: 0.97rem;
        }
        button.btn, a.btn {
            font-size: 0.95rem;
            padding: 5px 12px;
        }
        div[style*="max-width: 420px"] {
            max-width: 100% !important;
            padding: 0 2px;
        }
    }
</style>
<div class="d-flex justify-content-center">
    <div style="width:100%; max-width: 420px;">
        <div class="account-card card">
            <div class="card-header text-center">
                Edit Akun
            </div>
            <div class="card-body">
                <form action="{{ route('account.update', ['hashid' => Hashids::encode($account->id)]) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label>Nama</label>
                        <input type="text" name="name" class="form-control" value="{{ $account->name }}" required>
                    </div>
                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" value="{{ $account->email }}" required>
                    </div>
                    <div class="mb-3">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" value="{{ $account->username }}" required>
                    </div>
                    <div class="mb-3">
                        <label>Role</label>
                        <select name="role" class="form-control" required>
                            <option value="admin" {{ $account->role == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="staff" {{ $account->role == 'staff' ? 'selected' : '' }}>Staff</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Password (isi jika ingin ganti)</label>
                        <input type="password" name="password" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" class="form-control">
                    </div>
                    <button class="btn btn-primary" style="background:#33691E; border-color:#33691E;">Update</button>
                    <a href="{{ route('account.index') }}" class="btn btn-secondary">Kembali</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection