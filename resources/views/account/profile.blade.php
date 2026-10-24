@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
<style>
    .profile-card {
        max-width: 440px;
        margin: 0 auto;
        border-radius: 12px;
        box-shadow: 0 2px 8px #e0f2f1;
        overflow: hidden;
        background: #fff;
    }
    .profile-card .card-body {
        padding: 28px 24px 18px 24px;
    }
    .profile-card img {
        width: 80px;
        height: 80px;
        border-radius: 14px;
        object-fit: cover;
        margin-bottom: 18px;
        border: 2px solid #A5D6A7;
    }
    .profile-card h4 {
        font-size: 1.25rem;
        font-weight: 700;
        color: #33691E;
    }
    .profile-card .badge {
        font-size: 1rem;
        padding: 8px 18px;
        border-radius: 8px;
    }
    .profile-card table {
        font-size: 1.04rem;
    }
    .profile-card th {
        width: 44px;
        padding-left: 0 !important;
        text-align: center;
        vertical-align: middle;
    }
    .profile-card td {
        vertical-align: middle;
    }
    @media (max-width: 575.98px) {
        .profile-card {
            max-width: 100% !important;
            margin: 0 2px;
        }
        .profile-card .card-body {
            padding: 14px 4px 10px 4px;
        }
        .profile-card img {
            width: 60px;
            height: 60px;
            margin-bottom: 12px;
        }
        .profile-card h4 {
            font-size: 1.05rem;
        }
        .profile-card .badge {
            font-size: 0.93rem;
            padding: 6px 12px;
        }
        .profile-card table {
            font-size: 0.93rem;
        }
        .profile-card th {
            width: 32px;
            font-size: 1.1em;
        }
        .btn {
            font-size: 0.93rem;
            padding: 5px 12px;
        }
    }
</style>
<div class="container py-4">
    <div class="card shadow-sm profile-card">
        <div class="card-body text-center">
            <img src="{{ asset('images/logo-emblem.png') }}" alt="Logo UD. TPKS">
            <h4 class="mb-1">{{ auth()->user()->name }}</h4>
            <div class="mb-2 text-muted">{{ auth()->user()->email }}</div>
            <div class="mb-3">
                <span class="badge bg-success px-3 py-2" style="font-size:1rem;">{{ ucfirst(auth()->user()->role) }}</span>
            </div>
            <hr>
            <table class="table table-borderless text-start mb-3">
                <tr>
                    <th class="ps-0" style="width:44px;"><i class="bi bi-person-fill text-success"></i></th>
                    <td>Nama Lengkap</td>
                    <td>: {{ auth()->user()->name }}</td>
                </tr>
                <tr>
                    <th class="ps-0"><i class="bi bi-envelope-fill text-success"></i></th>
                    <td>Email</td>
                    <td>: {{ auth()->user()->email }}</td>
                </tr>
                @if(auth()->user()->username)
                <tr>
                    <th class="ps-0"><i class="bi bi-person-badge-fill text-success"></i></th>
                    <td>Username</td>
                    <td>: {{ auth()->user()->username }}</td>
                </tr>
                @endif
                <tr>
                    <th class="ps-0"><i class="bi bi-shield-lock-fill text-success"></i></th>
                    <td>Role</td>
                    <td>: {{ ucfirst(auth()->user()->role) }}</td>
                </tr>
                <tr>
                    <th class="ps-0"><i class="bi bi-calendar-plus text-success"></i></th>
                    <td>Dibuat pada</td>
                    <td>: {{ auth()->user()->created_at->format('d M Y H:i') }}</td>
                </tr>
                <tr>
                    <th class="ps-0"><i class="bi bi-calendar-check text-success"></i></th>
                    <td>Terakhir diubah</td>
                    <td>: {{ auth()->user()->updated_at->format('d M Y H:i') }}</td>
                </tr>
            </table>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-success mt-2">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
</div>
@endsection