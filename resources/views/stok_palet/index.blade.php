@extends('layouts.app')

@section('content')
<style>
    body {
        background: #f4faef;
    }
    .main-container {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        padding: 32px 24px;
        margin-top: 24px;
        margin-bottom: 24px;
        max-width: 1600px; /* Tambahkan ini */
        width: 98%;        /* Tambahkan ini */
        margin-left: auto; /* Tambahkan ini */
        margin-right: auto;/* Tambahkan ini */
    }
    h2 {
        color: #3b6e3b;
        font-weight: 700;
        font-size: 1.5rem;
        margin-bottom: 18px;
    }
    .table thead {
        background: #e6f4e6;
        color: #2d4d2d;
    }
    .table-striped tbody tr:nth-of-type(odd) {
        background: #f7fbf7;
    }
    .btn-primary, .btn-info, .btn-warning {
        border-radius: 20px;
    }
    .section-title {
        font-size: 1.2rem;
        color: #4e944f;
        font-weight: 600;
        margin-bottom: 10px;
    }
    .form-label {
        color: #3b6e3b;
        font-weight: 500;
    }
    .table td, .table th {
        vertical-align: middle !important;
    }
    .mb-section {
        margin-bottom: 32px;
    }
    @media (max-width: 991.98px) {
        .mb-lg-0 {
            margin-bottom: 24px !important;
        }
    }
</style>
<div class="container-fluid main-container">
    {{-- TABEL RANGKUMAN --}}
    <div class="row mb-section">
        <div class="col-12 col-lg-4 mb-4 mb-lg-0">
            <div class="section-title">Rangkuman Stok Palet Basah</div>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
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
                        @forelse($wetSummary as $row)
                        <tr>
                            <td>{{ $no++ }}</td>
                            <td>{{ $row->pallet_code }}</td>
                            <td>{{ $row->ukuran }}</td>
                            <td>{{ $row->pcs }}</td>
                            <td>{{ number_format($row->volume, 3, ',', '.') }}</td>
                            <td>
                                {{ isset($row->harga_basah) ? number_format($row->volume * $row->harga_basah, 0, ',', '.') : '-' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">Belum ada data palet basah.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-12 col-lg-4 mb-4 mb-lg-0">
            <div class="section-title">Rangkuman Stok Palet Dalam Penjemuran</div>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
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
                            <td>{{ number_format(($row->volume ) * $row->harga_satuan, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">Belum ada data palet dalam pengeringan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="section-title">Rangkuman Stok Palet Kering</div>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
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
                        @forelse($drySummary as $row)
                        <tr>
                            <td>{{ $no++ }}</td>
                            <td>{{ $row->pallet_code }}</td>
                            <td>{{ $row->ukuran }}</td>
                            <td>{{ $row->pcs }}</td>
                            <td>{{ number_format($row->volume, 3, ',', '.') }}</td>
                            <td>{{ number_format(($row->volume ) * $row->harga_satuan, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">Belum ada data palet kering.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- TABEL LOG & MODAL DETAIL --}}
    <div class="row mb-section">
        {{-- BASAH --}}
        <div class="col-12 col-lg-4 mb-4 mb-lg-0">
            <div class="section-title">Log Palet Basah</div>
            <form method="GET" class="mb-3 row g-2 align-items-end">
                <div class="col-12 col-md-auto">
                    <label for="tanggal_masuk" class="form-label mb-0 me-2">Cari Tanggal:</label>
                    <input type="date" name="tanggal_masuk" id="tanggal_masuk" class="form-control" value="{{ request('tanggal_masuk') }}">
                </div>
                <div class="col-12 col-md-auto">
                    <label for="sort_wet" class="form-label mb-0 me-2">Urutkan:</label>
                    <select name="sort_wet" id="sort_wet" class="form-select">
                        <option value="desc" {{ request('sort_wet', 'desc') == 'desc' ? 'selected' : '' }}>Terbaru</option>
                        <option value="asc" {{ request('sort_wet') == 'asc' ? 'selected' : '' }}>Terlama</option>
                    </select>
                </div>
                <div class="col-12 col-md-auto">
                    <button type="submit" class="btn btn-primary w-100">Terapkan</button>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Tanggal Masuk</th>
                            <th>Total Jumlah (pcs)</th>
                            <th>Total Volume</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($groupedWet as $i => $group)
                        <tr>
                            <td>{{ $i+1 }}</td>
                            <td>{{ $group->tanggal_input }}</td>
                            <td>{{ $group->total_pcs }}</td>
                            <td>{{ number_format($group->total_volume, 3, ',', '.') }}</td>
                            <td>
                                <button type="button"
                                    class="btn btn-info btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalDetailBasah{{ $group->input_code }}">
                                    Detail
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">Data palet basah belum terisi.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{-- Modal Detail Stok Basah --}}
            @foreach($groupedWet as $group)
            <div class="modal fade" id="modalDetailBasah{{ $group->input_code }}" tabindex="-1" aria-labelledby="modalLabelBasah{{ $group->input_code }}" aria-hidden="true">
              <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="modalLabelBasah{{ $group->input_code }}">Detail Palet Basah: {{ $group->input_code }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                  </div>
                  <div class="px-4 pt-2">
                    <strong>Tanggal Masuk:</strong> {{ $group->tanggal_input }}
                  </div>
                  <div class="modal-body">
                    <div class="table-responsive">
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
                                @foreach($allWetDetails[$group->input_code] ?? [] as $detail)
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
                    <form action="{{ route('stok_palet.moveToNextStatus', $group->input_code) }}" method="POST" class="mt-3">
                        @csrf
                       <button type="button" class="btn btn-warning"
                            onclick="showConfirmMoveModal('{{ route('stok_palet.moveToNextStatus', $group->input_code) }}', 'Pindahkan semua palet dengan kode input <strong>{{ $group->input_code }}</strong> ke penjemuran?')">
                            Pindahkan ke Penjemuran
                        </button>
                    </form>
                  </div>
                </div>
              </div>
            </div>
            @endforeach
        </div>

        {{-- PENJEMURAN --}}
        <div class="col-12 col-lg-4 mb-4 mb-lg-0">
            <div class="section-title">Log Penjemuran Pallet</div>
            <form method="GET" class="mb-3 row g-2 align-items-end">
                <div class="col-12 col-md-auto">
                    <label for="tanggal_cari" class="form-label mb-0 me-2">Cari Tanggal:</label>
                    <input type="date" name="tanggal_cari" id="tanggal_cari" class="form-control" value="{{ request('tanggal_cari') }}">
                </div>
                <div class="col-12 col-md-auto">
                    <label for="sort" class="form-label mb-0 me-2">Urutkan:</label>
                    <select name="sort" id="sort" class="form-select">
                        <option value="desc" {{ request('sort', 'desc') == 'desc' ? 'selected' : '' }}>Terbaru</option>
                        <option value="asc" {{ request('sort') == 'asc' ? 'selected' : '' }}>Terlama</option>
                    </select>
                </div>
                <div class="col-12 col-md-auto">
                    <button type="submit" class="btn btn-primary w-100">Terapkan</button>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Tanggal Penjemuran</th>
                            <th>Total Jumlah (pcs)</th>
                            <th>Total Volume</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($groupedDrying as $i => $group)
                        <tr>
                            <td>{{ $i+1 }}</td>
                            <td>{{ $group->tanggal_input }}</td>
                            <td>{{ $group->total_pcs }}</td>
                            <td>{{ number_format($group->total_volume, 3, ',', '.') }}</td>
                            <td>
                                <button type="button"
                                    class="btn btn-info btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalDetailJemur{{ $group->input_code }}">
                                    Detail
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">Belum ada data penjemuran.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{-- Modal Detail Penjemuran --}}
            @foreach($groupedDrying as $group)
            <div class="modal fade" id="modalDetailJemur{{ $group->input_code }}" tabindex="-1" aria-labelledby="modalLabelJemur{{ $group->input_code }}" aria-hidden="true">
              <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="modalLabelJemur{{ $group->input_code }}">Detail Penjemuran Palet: {{ $group->input_code }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                  </div>
                  <div class="px-4 pt-2">
                    <strong>Tanggal Penjemuran:</strong> {{ $group->tanggal_input }}
                  </div>
                  <div class="modal-body">
                    <div class="table-responsive">
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
                                @foreach($allDryingDetails[$group->input_code] ?? [] as $detail)
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
                    <form action="{{ route('stok_palet.moveToNextStatus', $group->input_code) }}" method="POST" class="mt-3">
                        @csrf
                        <button type="button" class="btn btn-warning"
                            onclick="showConfirmMoveModal('{{ route('stok_palet.moveToNextStatus', $group->input_code) }}', 'Pindahkan semua palet dengan kode input <strong>{{ $group->input_code }}</strong> ke kering?')">
                            Pindahkan ke Kering
                        </button>
                    </form>
                  </div>
                </div>
              </div>
            </div>
            @endforeach
        </div>

        {{-- KERING --}}
        <div class="col-12 col-lg-4">
            <div class="section-title">Log Pallet Kering</div>
            <form method="GET" class="mb-3 row g-2 align-items-end">
                <div class="col-12 col-md-auto">
                    <label for="tanggal_kering" class="form-label mb-0 me-2">Cari Tanggal:</label>
                    <input type="date" name="tanggal_kering" id="tanggal_kering" class="form-control" value="{{ request('tanggal_kering') }}">
                </div>
                <div class="col-12 col-md-auto">
                    <label for="sort_dry" class="form-label mb-0 me-2">Urutkan:</label>
                    <select name="sort_dry" id="sort_dry" class="form-select">
                        <option value="desc" {{ request('sort_dry', 'desc') == 'desc' ? 'selected' : '' }}>Terbaru</option>
                        <option value="asc" {{ request('sort_dry') == 'asc' ? 'selected' : '' }}>Terlama</option>
                    </select>
                </div>
                <div class="col-12 col-md-auto">
                    <button type="submit" class="btn btn-primary w-100">Terapkan</button>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Tanggal Kering</th>
                            <th>Total Jumlah (pcs)</th>
                            <th>Total Volume</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($groupedDry as $i => $group)
                        <tr>
                            <td>{{ $i+1 }}</td>
                            <td>{{ $group->tanggal_input }}</td>
                            <td>{{ $group->total_pcs }}</td>
                            <td>{{ number_format($group->total_volume, 3, ',', '.') }}</td>
                            <td>
                                <button type="button"
                                    class="btn btn-info btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalDetailKering{{ $group->input_code }}">
                                    Detail
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">Belum ada data palet kering.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{-- Modal Detail Pallet Kering --}}
            @foreach($groupedDry as $group)
            <div class="modal fade" id="modalDetailKering{{ $group->input_code }}" tabindex="-1" aria-labelledby="modalLabelKering{{ $group->input_code }}" aria-hidden="true">
              <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="modalLabelKering{{ $group->input_code }}">Detail Palet Kering: {{ $group->input_code }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                  </div>
                  <div class="px-4 pt-2">
                    <strong>Tanggal Kering:</strong> {{ $group->tanggal_input }}
                  </div>
                  <div class="modal-body">
                    <div class="table-responsive">
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
                                @foreach($allDryDetails[$group->input_code] ?? [] as $detail)
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
                    {{-- Tidak ada tombol pindah status untuk kering --}}
                  </div>
                </div>
              </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Modal Notifikasi -->
<div class="modal fade" id="notifModal" tabindex="-1" aria-labelledby="notifModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-success text-white" id="notifModalHeader">
        <h5 class="modal-title" id="notifModalLabel">Notifikasi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="notifModalMsg"></div>
    </div>
  </div>
</div>

<!-- Modal Konfirmasi Universal -->
<div class="modal fade" id="confirmMoveUniversalModal" tabindex="-1" aria-labelledby="confirmMoveUniversalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title" id="confirmMoveUniversalLabel">Konfirmasi</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body" id="confirmMoveUniversalBody">
        <!-- Isi dinamis -->
      </div>
      <div class="modal-footer">
        <form id="confirmMoveUniversalForm" method="POST" class="m-0">
            @csrf
            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-warning btn-sm" id="confirmMoveUniversalSubmit">Ya, Pindahkan</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@if(session('success'))
<script>
document.addEventListener('DOMContentLoaded', function() {
    let modalHeader = document.getElementById('notifModalHeader');
    let modalMsg = document.getElementById('notifModalMsg');
    modalHeader.className = "modal-header bg-success text-white";
    modalMsg.innerText = "{{ session('success') }}";
    new bootstrap.Modal(document.getElementById('notifModal')).show();
});
</script>
@endif
<script>
function showConfirmMoveModal(actionUrl, bodyText) {
    document.getElementById('confirmMoveUniversalForm').action = actionUrl;
    document.getElementById('confirmMoveUniversalBody').innerHTML = bodyText;
    var modal = new bootstrap.Modal(document.getElementById('confirmMoveUniversalModal'));
    modal.show();
}
</script>
@endsection

