@extends('layouts.app')

@section('content')
<style>
    .main-container {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        padding: 32px 24px;
        margin-top: 24px;
        margin-bottom: 24px;
    }
    .table thead {
        background: #e6f4e6;
        color: #2d4d2d;
    }
    .table-striped tbody tr:nth-of-type(odd) {
        background: #f7fbf7;
    }
    .btn-success, .btn-primary, .btn-secondary {
        border-radius: 20px;
    }
    .fw-bold, .section-title {
        color: #388E3C;
    }
    .table td, .table th {
        vertical-align: middle !important;
    }
    .pagination .page-link {
        border-radius: 12px !important;
        color: #388E3C;
    }
    .pagination .active .page-link {
        background: #388E3C;
        color: #fff;
        border-color: #388E3C;
    }
    @media (max-width: 767.98px) {
        .main-container {
            padding: 12px 4px;
        }
        .table-responsive {
            font-size: 0.95rem;
        }
    }
</style>
<div class="container main-container">
    <h4 class="mb-4 fw-bold">Riwayat Penjualan Palet</h4>
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <a href="{{ route('pallet-sales.create') }}" class="btn btn-success mb-2">
            <i class="bi bi-plus-circle me-1"></i> Transaksi Baru
        </a>
        <a href="{{ route('pallet-sales.laporan-mingguan') }}" class="btn btn-warning mb-2" target="_blank">
            <i class="bi bi-calendar-week"></i> Laporan Penjualan Mingguan
        </a>
        <form method="GET" class="row g-2 mb-2">
            <div class="col-auto">
                <input type="text" name="customer" class="form-control" placeholder="Cari Customer" value="{{ request('customer') }}">
            </div>
            <div class="col-auto">
                <input type="date" name="tanggal" class="form-control" value="{{ request('tanggal') }}">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Cari</button>
                <a href="{{ route('pallet-sales.index') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead>
                <tr>
                    <th>Kode Transaksi</th>
                    <th>
                        @php
                            $nextDirection = (request('direction', 'desc') === 'asc') ? 'desc' : 'asc';
                        @endphp
                        <a href="{{ route('pallet-sales.index', array_merge(request()->all(), ['sort' => 'tanggal', 'direction' => $nextDirection])) }}" class="text-success text-decoration-none">
                            Tanggal
                            @if(request('sort') === 'tanggal')
                                @if(request('direction', 'desc') === 'asc')
                                    &#9650;
                                @else
                                    &#9660;
                                @endif
                            @endif
                        </a>
                    </th>
                    <th>Customer</th>
                    <th>Keterangan</th>
                    <th>Detail Item</th>
                    <th>Grand Total</th>
                    <th>Aksi</th> <!-- Tambahkan ini -->
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $sale)
                <tr>
                    <td>{{ substr($sale->kode_transaksi, -5) }}</td>
                    <td>{{ date('d/m/Y', strtotime($sale->tanggal)) }}</td>
                    <td>{{ $sale->customer }}</td>
                    <td>{{ $sale->keterangan }}</td>
                    <td>
                        <ul class="mb-0 ps-2" id="item-list-{{ $sale->id }}">
                            @foreach($sale->items as $index => $item)
                                <li class="{{ $index >= 2 ? 'more-item-'.$sale->id.' d-none' : '' }}" style="margin-bottom:2px;">
                                    <span class="badge bg-light text-dark border">{{ $item->ukuran }}</span>
                                    <span class="text-success fw-bold">{{ $item->pcs }}x</span>
                                    <span class="text-secondary small">Vol: {{ number_format($item->pcs * $item->volume, 3, ',', '.') }}</span>
                                </li>
                            @endforeach
                            @if($sale->items->count() > 2)
                                <li>
                                    <span id="dots-{{ $sale->id }}"> ...</span>
                                    <button class="btn btn-link btn-sm p-0 ms-1" type="button" onclick="toggleDetail('{{ $sale->id }}')" id="btn-{{ $sale->id }}">Lihat Semua</button>
                                </li>
                            @endif
                        </ul>
                    </td>
                    <td>
                        @php
                            $grand_total = $sale->items->sum('subtotal');
                            $total_volume = $sale->items->sum(function($item) {
                                return $item->pcs * $item->volume;
                            });
                        @endphp
                        <div><span class="fw-bold">Total Volume:</span> {{ number_format($total_volume, 3, ',', '.') }}</div>
                        <div><span class="fw-bold">Nilai Barang:</span> Rp{{ number_format($grand_total,0,',','.') }}</div>
                    </td>
                    <td>
                        <a href="{{ route('pallet-sales.nota', $sale->id) }}" class="btn btn-outline-primary btn-sm" target="_blank">
                            <i class="bi bi-download"></i> Nota
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted">Belum ada transaksi penjualan palet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="d-flex justify-content-center">
            {{ $sales->links() }}
        </div>
    </div>
</div>

<script>
function toggleDetail(saleId) {
    const moreItems = document.querySelectorAll('.more-item-' + saleId);
    const dots = document.getElementById("dots-" + saleId);
    const btnText = document.getElementById("btn-" + saleId);
    const show = moreItems.length && moreItems[0].classList.contains('d-none');
    moreItems.forEach(item => item.classList.toggle('d-none'));
    dots.style.display = show ? "none" : "inline";
    btnText.innerHTML = show ? "Lihat Sedikit" : "Lihat Semua";
}
</script>@endsection