@extends('layouts.app')

@section('title', 'Stock Opname ' . $opname->kode)

@php
    $isAdmin = auth()->user()->role === 'admin';
    $rp  = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
    $vol = fn ($n) => number_format((float) $n, 3, ',', '.');
    $tandai = fn ($n) => $n > 0 ? '+' . number_format($n, 0, ',', '.') : number_format($n, 0, ',', '.');
@endphp

@section('content')
<div class="container py-3">

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach (collect($errors->all())->unique() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Informasi sesi --}}
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span>
                Stock Opname {{ $opname->kode }}
                <span class="badge {{ $opname->statusBadge() }} ms-1">{{ $opname->statusLabel() }}</span>
            </span>
            <div class="d-flex flex-wrap gap-2">
                @if ($opname->isAktif())
                    <a href="{{ route('stock-opname.lembar', $opname) }}" class="btn btn-sm btn-light">
                        <i class="bi bi-printer"></i> Lembar Hitung
                    </a>
                @endif
                @if (in_array($opname->status, ['menunggu', 'selesai']))
                    <a href="{{ route('stock-opname.laporan', $opname) }}" class="btn btn-sm btn-light">
                        <i class="bi bi-file-earmark-pdf"></i> Laporan PDF
                    </a>
                @endif
                @if ($isAdmin && $opname->isAktif())
                    <form action="{{ route('stock-opname.batal', $opname) }}" method="POST"
                          onsubmit="return confirm('Batalkan stock opname ini? Stok tidak diubah dan kunci dilepas.');">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-light">
                            <i class="bi bi-x-circle"></i> Batalkan
                        </button>
                    </form>
                @endif
            </div>
        </div>
        <div class="card-body">
            <div class="row g-2 small">
                <div class="col-sm-6 col-lg-3"><span class="text-muted">Cakupan</span><br><strong>{{ $opname->cakupan }}</strong></div>
                <div class="col-sm-6 col-lg-3"><span class="text-muted">Tanggal</span><br><strong>{{ $opname->tanggal->format('d-m-Y') }}</strong></div>
                <div class="col-sm-6 col-lg-3"><span class="text-muted">Dibuat oleh</span><br><strong>{{ $opname->pembuat->name ?? '-' }}</strong></div>
                <div class="col-sm-6 col-lg-3">
                    <span class="text-muted">Disetujui oleh</span><br>
                    <strong>{{ $opname->penyetuju->name ?? '-' }}</strong>
                    @if ($opname->disetujui_at)
                        <span class="text-muted">({{ $opname->disetujui_at->format('d-m-Y H:i') }})</span>
                    @endif
                </div>
                @if ($opname->catatan)
                    <div class="col-12"><span class="text-muted">Catatan</span><br>{{ $opname->catatan }}</div>
                @endif
            </div>
        </div>
    </div>

    @if ($opname->status === 'menghitung')
        {{-- ================= TAHAP MENGHITUNG ================= --}}
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span>Input Hasil Hitung Fisik</span>
                <span class="small">{{ $ringkasan['terisi'] }} / {{ $ringkasan['jumlah'] }} item terisi</span>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-2">
                    Hitung barang di gudang lalu isi jumlahnya (pcs). Jumlah stok sistem sengaja tidak ditampilkan
                    agar penghitungan tidak terpengaruh. Isi <strong>0</strong> jika barangnya tidak ada.
                </p>

                <input type="search" id="cari-item" class="form-control form-control-sm mb-2" placeholder="Cari item…">

                <form action="{{ route('stock-opname.hitung', $opname) }}" method="POST" id="form-hitung">
                    @csrf
                    <div class="table-responsive" style="max-height: 60vh;">
                        <table class="table table-sm table-bordered align-middle mb-0">
                            <thead class="table-light" style="position: sticky; top: 0; z-index: 1;">
                                <tr>
                                    <th style="width: 60px;">No</th>
                                    <th>Item</th>
                                    <th style="width: 180px;">Jumlah Fisik (pcs)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($opname->items as $item)
                                    <tr class="baris-item" data-label="{{ strtolower($item->label) }}">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $item->label }}</td>
                                        <td>
                                            <input type="number" name="fisik[{{ $item->id }}]" min="0" step="1"
                                                   class="form-control form-control-sm @error('fisik.' . $item->id) is-invalid @enderror"
                                                   value="{{ old('fisik.' . $item->id, $item->qty_fisik) }}">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <button type="submit" name="aksi" value="simpan" class="btn btn-secondary">
                            <i class="bi bi-save"></i> Simpan Sementara
                        </button>
                        <button type="submit" name="aksi" value="ajukan" class="btn btn-success"
                                onclick="return confirm('Ajukan hasil hitung ke admin? Setelah diajukan, hasil hitung tidak bisa diubah kecuali dikembalikan admin.');">
                            <i class="bi bi-send"></i> Ajukan untuk Diperiksa
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @else
        {{-- ================= TAHAP PEMERIKSAAN / HASIL ================= --}}
        @if ($opname->status !== 'batal')
            <div class="row g-2 mb-3">
                <div class="col-6 col-lg-3">
                    <div class="border rounded p-2 h-100 bg-white">
                        <div class="small text-muted">Item selisih</div>
                        <div class="fs-5 fw-bold">{{ $ringkasan['selisih'] }} <span class="fs-6 fw-normal text-muted">/ {{ $ringkasan['jumlah'] }}</span></div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="border rounded p-2 h-100 bg-white">
                        <div class="small text-muted">Total kurang</div>
                        <div class="fs-5 fw-bold text-danger">{{ $tandai($ringkasan['kurang']) }} pcs</div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="border rounded p-2 h-100 bg-white">
                        <div class="small text-muted">Total lebih</div>
                        <div class="fs-5 fw-bold text-success">{{ $tandai($ringkasan['lebih']) }} pcs</div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="border rounded p-2 h-100 bg-white">
                        <div class="small text-muted">Nilai selisih</div>
                        <div class="fs-5 fw-bold {{ $ringkasan['nilai'] < 0 ? 'text-danger' : '' }}">{{ $rp($ringkasan['nilai']) }}</div>
                        <div class="small text-muted">{{ $vol($ringkasan['volume']) }} m³</div>
                    </div>
                </div>
            </div>
        @endif

        @php $bisaSetujui = $opname->status === 'menunggu' && $isAdmin; @endphp

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span>{{ $bisaSetujui ? 'Periksa Selisih & Setujui' : 'Hasil Stock Opname' }}</span>
                <div class="form-check form-switch mb-0 small">
                    <input class="form-check-input" type="checkbox" id="hanya-selisih" @checked($opname->status === 'menunggu')>
                    <label class="form-check-label" for="hanya-selisih">Hanya tampilkan yang selisih</label>
                </div>
            </div>
            <div class="card-body">
                @if ($opname->status === 'menunggu' && !$isAdmin)
                    <div class="alert alert-info small">Hasil hitung sudah diajukan dan sedang menunggu pemeriksaan admin.</div>
                @elseif ($bisaSetujui)
                    <p class="small text-muted mb-2">
                        Periksa item yang selisih. Jika perlu, kembalikan untuk dihitung ulang. Untuk menyetujui,
                        pilih alasan setiap selisih. Setelah disetujui, stok sistem diubah menjadi jumlah fisik dan kunci dilepas.
                    </p>
                @elseif ($opname->status === 'batal')
                    <div class="alert alert-secondary small">Opname dibatalkan. Stok tidak diubah.</div>
                @endif

                @if ($bisaSetujui)
                    <form action="{{ route('stock-opname.setujui', $opname) }}" method="POST" id="form-setujui"
                          onsubmit="return confirm('Setujui stock opname ini? Stok sistem akan disesuaikan dengan jumlah fisik.');">
                        @csrf
                @endif

                <div class="table-responsive" style="max-height: 65vh;">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead class="table-light" style="position: sticky; top: 0; z-index: 1;">
                            <tr>
                                <th>No</th>
                                <th>Item</th>
                                <th class="text-end">Sistem</th>
                                <th class="text-end">Fisik</th>
                                <th class="text-end">Selisih</th>
                                <th class="text-end">Volume (m³)</th>
                                <th class="text-end">Nilai</th>
                                <th style="min-width: 160px;">Alasan</th>
                                <th style="min-width: 160px;">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($opname->items as $item)
                                @php $adaSelisih = $item->selisih !== null && $item->selisih !== 0; @endphp
                                <tr class="baris-item {{ $adaSelisih ? ($item->selisih < 0 ? 'table-danger' : 'table-success') : 'tanpa-selisih' }}">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->label }}</td>
                                    <td class="text-end">{{ number_format($item->qty_sistem, 0, ',', '.') }}</td>
                                    <td class="text-end">{{ $item->qty_fisik === null ? '-' : number_format($item->qty_fisik, 0, ',', '.') }}</td>
                                    <td class="text-end fw-semibold">{{ $item->selisih === null ? '-' : $tandai($item->selisih) }}</td>
                                    <td class="text-end">{{ $item->volume_selisih === null ? '-' : $vol($item->volume_selisih) }}</td>
                                    <td class="text-end">{{ $item->nilai_selisih === null ? '-' : $rp($item->nilai_selisih) }}</td>
                                    <td>
                                        @if ($bisaSetujui && $adaSelisih)
                                            <select name="alasan[{{ $item->id }}]"
                                                    class="form-select form-select-sm @error('alasan.' . $item->id) is-invalid @enderror">
                                                <option value="">-- Pilih --</option>
                                                @foreach ($alasanList as $alasan)
                                                    <option value="{{ $alasan }}" @selected(old('alasan.' . $item->id) === $alasan)>{{ $alasan }}</option>
                                                @endforeach
                                            </select>
                                        @else
                                            {{ $item->alasan ?? '' }}
                                        @endif
                                    </td>
                                    <td>
                                        @if ($bisaSetujui && $adaSelisih)
                                            <input type="text" name="keterangan[{{ $item->id }}]" maxlength="255"
                                                   class="form-control form-control-sm" value="{{ old('keterangan.' . $item->id) }}">
                                        @else
                                            {{ $item->keterangan ?? '' }}
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            <tr id="baris-kosong" class="d-none">
                                <td colspan="9" class="text-center text-muted">Tidak ada item yang selisih.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                @if ($bisaSetujui)
                    </form>
                    <form action="{{ route('stock-opname.kembalikan', $opname) }}" method="POST" id="form-kembalikan"
                          onsubmit="return confirm('Kembalikan untuk dihitung ulang? Stok tetap terkunci.');">
                        @csrf
                    </form>
                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <button type="submit" form="form-kembalikan" class="btn btn-warning">
                            <i class="bi bi-arrow-counterclockwise"></i> Kembalikan untuk Hitung Ulang
                        </button>
                        <button type="submit" form="form-setujui" class="btn btn-success">
                            <i class="bi bi-check2-circle"></i> Setujui &amp; Sesuaikan Stok
                        </button>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <a href="{{ route('stock-opname.index') }}" class="btn btn-link px-0 mt-2">&larr; Kembali ke daftar stock opname</a>
</div>
@endsection

@section('scripts')
<script>
    (function () {
        // Pencarian item di form hitung
        const cari = document.getElementById('cari-item');
        if (cari) {
            cari.addEventListener('input', function () {
                const q = this.value.trim().toLowerCase();
                document.querySelectorAll('.baris-item').forEach(tr => {
                    tr.classList.toggle('d-none', q !== '' && !tr.dataset.label.includes(q));
                });
            });
        }

        // Tampilkan hanya item yang selisih
        const switchSelisih = document.getElementById('hanya-selisih');
        if (switchSelisih) {
            const terapkan = () => {
                const hanya = switchSelisih.checked;
                let tampil = 0;
                document.querySelectorAll('.baris-item').forEach(tr => {
                    const sembunyi = hanya && tr.classList.contains('tanpa-selisih');
                    tr.classList.toggle('d-none', sembunyi);
                    if (!sembunyi) tampil++;
                });
                document.getElementById('baris-kosong').classList.toggle('d-none', tampil > 0);
            };
            switchSelisih.addEventListener('change', terapkan);
            terapkan();
        }
    })();
</script>
@endsection
