@extends('layouts.app')

@section('title', 'Stock Opname')

@section('content')
<div class="container-fluid py-3 px-2 px-md-4">
    @include('stock_opname._pesan')

    <div class="row g-3">
        @foreach ([
            ['data' => $kayu,  'judul' => 'Riwayat Stock Opname Log Kayu', 'tombol' => 'Opname Log Kayu', 'route' => 'stock-opname.kayu'],
            ['data' => $palet, 'judul' => 'Riwayat Stock Opname Pallet',   'tombol' => 'Opname Pallet',   'route' => 'stock-opname.palet'],
        ] as $t)
            <div class="col-xl-6">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <span>{{ $t['judul'] }}</span>
                        <a href="{{ route($t['route']) }}" class="btn btn-sm btn-light">
                            <i class="bi bi-plus-circle"></i> {{ $t['tombol'] }}
                        </a>
                    </div>
                    <div class="card-body">
                        @if ($t['data']->isEmpty())
                            <p class="text-muted mb-0">Belum ada stock opname. Mulai dengan tombol <strong>{{ $t['tombol'] }}</strong>.</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Kode</th>
                                            <th>Tanggal</th>
                                            <th>Cakupan</th>
                                            <th class="text-end">Selisih</th>
                                            <th>Oleh</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($t['data'] as $o)
                                            <tr>
                                                <td class="text-nowrap">{{ $o->kode }}</td>
                                                <td class="text-nowrap">{{ $o->tanggal->format('d-m-Y') }}</td>
                                                <td>
                                                    {{ $o->cakupan }}
                                                    @if ($o->catatan)
                                                        <div class="small text-muted">{{ \Illuminate\Support\Str::limit($o->catatan, 50) }}</div>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    <span class="badge {{ $o->items_selisih_count > 0 ? 'bg-warning text-dark' : 'bg-success' }}"
                                                          title="{{ $o->items_selisih_count }} dari {{ $o->items_count }} item selisih">
                                                        {{ $o->items_selisih_count }} / {{ $o->items_count }}
                                                    </span>
                                                </td>
                                                <td>{{ $o->pembuat->name ?? '-' }}</td>
                                                <td><a href="{{ route('stock-opname.show', $o) }}" class="btn btn-sm btn-outline-secondary">Detail</a></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">{{ $t['data']->links('pagination::bootstrap-5') }}</div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
