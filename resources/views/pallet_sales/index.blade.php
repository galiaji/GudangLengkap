@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Riwayat Penjualan Palet</h4>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <a href="{{ route('pallet-sales.create') }}" class="btn btn-success mb-3">Transaksi Baru</a>

    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-3">
            <input type="text" name="customer" class="form-control" placeholder="Cari Customer" value="{{ request('customer') }}">
        </div>
        <div class="col-md-3">
            <input type="date" name="tanggal" class="form-control" value="{{ request('tanggal') }}">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary">Cari</button>
            <a href="{{ route('pallet-sales.index') }}" class="btn btn-secondary">Reset</a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead>
                <tr>
                    <th>Kode Transaksi</th>
                    <th>
                        @php
                            $nextDirection = (request('direction', 'desc') === 'asc') ? 'desc' : 'asc';
                        @endphp
                        <a href="{{ route('pallet-sales.index', array_merge(request()->all(), ['sort' => 'tanggal', 'direction' => $nextDirection])) }}">
                            Tanggal
                            @if(request('sort') === 'tanggal')
                                @if(request('direction', 'desc') === 'asc')
                                    &#9650; {{-- Up arrow --}}
                                @else
                                    &#9660; {{-- Down arrow --}}
                                @endif
                            @endif
                        </a>
                    </th>
                    <th>Customer</th>
                    <th>Keterangan</th>
                    <th>Detail Item</th>
                    <th>Grand Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $sale)
                <tr>
                    <td>{{ $sale->kode_transaksi }}</td>
                    <td>{{ $sale->tanggal }}</td>
                    <td>{{ $sale->customer }}</td>
                    <td>{{ $sale->keterangan }}</td>
                    <td>
                        <ul class="mb-0" id="item-list-{{ $sale->id }}">
                            @foreach($sale->items as $index => $item)
                                @if($index < 4)
                                    <li>
                                        Ukuran: <b>{{ $item->ukuran }}</b> | 
                                        PCS: <b>{{ $item->pcs }}</b> | 
                                        Total Volume: <b>{{ number_format($item->pcs * $item->volume, 3, ',', '.') }}</b>
                                    </li>
                                @endif
                            @endforeach

                            @if($sale->items->count() > 4)
                                <span id="dots-{{ $sale->id }}"> ...</span>
                                <span id="more-{{ $sale->id }}" style="display:none;">
                                    @foreach($sale->items->slice(4) as $itemMore)
                                        <li>
                                            Ukuran: <b>{{ $itemMore->ukuran }}</b> | 
                                            PCS: <b>{{ $itemMore->pcs }}</b> | 
                                            Total Volume: <b>{{ number_format($itemMore->pcs * $itemMore->volume, 3, ',', '.') }}</b>
                                        </li>
                                    @endforeach
                                </span>
                                <br>
                                <button class="btn btn-link p-0" onclick="toggleDetail({{ $sale->id }})" id="btn-{{ $sale->id }}">Lihat Semua</button>
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
                        <div><b>Total Volume:</b> {{ number_format($total_volume, 3, ',', '.') }}</div>
                        <div><b>Nilai Barang:</b> Rp{{ number_format($grand_total,0,',','.') }}</div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center">Belum ada transaksi penjualan palet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div>
            {{ $sales->links() }}
        </div>
    </div>
</div>
@endsection

<script>
    function toggleDetail(saleId) {
        var dots = document.getElementById("dots-" + saleId);
        var moreText = document.getElementById("more-" + saleId);
        var btnText = document.getElementById("btn-" + saleId);

        if (moreText.style.display === "none") {
            moreText.style.display = "inline";
            dots.style.display = "none";
            btnText.innerHTML = "Lihat Sedikit";
        } else {
            moreText.style.display = "none";
            dots.style.display = "inline";
            btnText.innerHTML = "Lihat Semua";
        }
    }
</script>