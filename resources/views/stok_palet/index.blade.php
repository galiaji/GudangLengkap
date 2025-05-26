@extends('layouts.app')

@section('content')
<div class="container">

    <h2>Rangkuman Stok Palet Dalam Penjemuran</h2>
    <table class="table table-striped table-bordered mb-5">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Palet</th>
                <th>Ukuran</th>
                <th>Total Jumlah (pcs)</th>
                <th>Total Volume</th>
                <th>Nilai/Harga Total</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @forelse($dryingSummary as $row)
            <tr>
                <td>{{ $no++ }}</td>
                <td>{{ $row->pallet_code }}</td>
                <td>{{ $row->ukuran }}</td>
                <td>{{ $row->pcs }}</td>
                <td>{{ number_format($row->volume, 3, ',', '.') }}</td>
                <td>
                    {{ number_format(($row->volume ) * $row->harga_satuan, 0, ',', '.') }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center text-muted">Belum ada data palet dalam pengeringan.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <h2>Stok Palet Basah</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Palet</th>
                <th>Ukuran</th>
                <th>Jumlah (pcs)</th>
                <th>Volume</th>
                <th>Tanggal Masuk</th>
            </tr>
        </thead>
        <tbody>
            @forelse($wetPallets as $index => $stok)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $stok->pallet_code }}</td>
                <td>{{ $stok->ukuran }}</td>
                <td>{{ $stok->pcs }}</td>
                <td>{{ number_format($stok->volume / 1000, 3, ',', '.') }}</td>
                <td>{{ $stok->entry_date }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center text-muted">Data palet basah belum terisi.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <h2 class="mt-4">Log Penjemuran Pallet</h2>
    <form method="GET" class="mb-3 d-flex flex-wrap gap-2 align-items-end">
        <div>
            <label for="tanggal_cari" class="form-label mb-0 me-2">Cari Tanggal:</label>
            <input type="date" name="tanggal_cari" id="tanggal_cari" class="form-control" value="{{ request('tanggal_cari') }}">
        </div>
        <div>
            <label for="sort" class="form-label mb-0 me-2">Urutkan:</label>
            <select name="sort" id="sort" class="form-select">
                <option value="desc" {{ request('sort', 'desc') == 'desc' ? 'selected' : '' }}>Terbaru</option>
                <option value="asc" {{ request('sort') == 'asc' ? 'selected' : '' }}>Terlama</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Terapkan</button>
    </form>
    <div class="table-responsive">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th style="display:none">Kode Jemur</th>
                <th>Tanggal Mulai Jemur</th>
                <th>Total Jumlah (pcs)</th>
                <th>Total Volume</th>
            </tr>
        </thead>
        <tbody>
            @forelse($groupedDrying as $i => $group)
            <tr style="cursor:pointer"
                data-bs-toggle="modal"
                data-bs-target="#modalDetailJemur{{ $group->drying_code }}">
                <td>{{ $i+1 }}</td>
                <td style="display:none">{{ $group->drying_code }}</td>
                <td>{{ $group->start_drying_date }}</td>
                <td>{{ $group->total_pcs }}</td>
                <td>{{ number_format($group->total_volume, 3, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center text-muted">Belum ada data penjemuran.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>

@foreach($groupedDrying as $group)
<div class="modal fade" id="modalDetailJemur{{ $group->drying_code }}" tabindex="-1" aria-labelledby="modalLabel{{ $group->drying_code }}" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalLabel{{ $group->drying_code }}">Detail Penjemuran: {{ $group->drying_code }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="px-4 pt-2">
        <strong>Tanggal Penjemuran:</strong> {{ $group->start_drying_date }}
      </div>
      <div class="modal-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Kode Palet</th>
                    <th>Ukuran</th>
                    <th>Jumlah (pcs)</th>
                    <th>Volume</th>
                </tr>
            </thead>
            <tbody>
                @foreach($allDetails[$group->drying_code] ?? [] as $detail)
                <tr>
                    <td>{{ $detail->pallet_code }}</td>
                    <td>{{ $detail->ukuran }}</td>
                    <td>{{ $detail->pcs }}</td>
                    <td>{{ number_format($detail->volume, 3, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endforeach
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endsection

