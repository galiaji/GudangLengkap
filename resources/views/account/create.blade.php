@extends('layouts.app')

@section('title', 'Tambah Akun')

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
    .password-wrapper {
        position: relative;
    }
    .password-wrapper button {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        background: transparent;
        border: none;
        color: #33691E;
    }
</style>

<div class="d-flex justify-content-center">
    <div style="width:100%; max-width: 420px;">
        <div class="account-card card">
            <div class="card-header text-center">
                Tambah Akun
            </div>
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('account.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label>Nama <span class="text-red-500">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="Nama" value="{{ old('name') }}" required>
                    </div>
                    <div class="mb-3">
                        <label>Username <span class="text-red-500">*</span></label>
                        <input type="text" name="username" class="form-control" placeholder="Username" value="{{ old('username') }}" required>
                    </div>
                    <div class="mb-3">
                        <label>Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" class="form-control" placeholder="Email" value="{{ old('email') }}" required>
                    </div>
                    <div class="mb-3">
                        <label>Role <span class="text-red-500">*</span></label>
                        <select name="role" class="form-control" required>
                            <option value="" disabled selected>Pilih Role</option>
                            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="staff" {{ old('role') == 'staff' ? 'selected' : '' }}>Staff</option>
                        </select>
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <label>Password <span class="text-red-500">*</span></label>
                        <div class="password-wrapper">
                            <input type="password" name="password" id="password" class="form-control" placeholder="Password" required>
                            <button type="button" onclick="togglePassword('password','toggleIcon1')">
                                <i id="toggleIcon1" class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Konfirmasi Password -->
                    <div class="mb-3">
                        <label>Konfirmasi Password <span class="text-red-500">*</span></label>
                        <div class="password-wrapper">
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Konfirmasi Password" required>
                            <button type="button" onclick="togglePassword('password_confirmation','toggleIcon2')">
                                <i id="toggleIcon2" class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button class="btn btn-primary" style="background:#33691E; border-color:#33691E;">Simpan</button>
                    <a href="{{ route('account.index') }}" class="btn btn-secondary">Kembali</a>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    if (input.type === "password") {
        input.type = "text";
        icon.classList.replace("bi-eye", "bi-eye-slash");
    } else {
        input.type = "password";
        icon.classList.replace("bi-eye-slash", "bi-eye");
    }
}
</script>
@endsection
