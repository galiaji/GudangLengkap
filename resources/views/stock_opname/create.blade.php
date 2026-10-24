@extends('layouts.app')

@section('title', 'Mulai Stock Opname')

@section('content')
<div class="container py-3" style="max-width: 720px;">
    <div class="card">
        <div class="card-header">Mulai Stock Opname</div>
        <div class="card-body">
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="alert alert-warning small">
                <i class="bi bi-lock-fill"></i>
                Setelah opname dimulai, <strong>semua transaksi untuk stok yang dipilih dikunci</strong>
                (pembelian, penjualan, potong gergaji, input &amp; pindah status palet) sampai opname
                disetujui atau dibatalkan admin.
            </div>

            <form action="{{ route('stock-opname.store') }}" method="POST" id="form-opname">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-semibold">Jenis Stok</label>
                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="jenis" id="jenis-kayu" value="kayu"
                                   @checked(old('jenis', 'kayu') === 'kayu')>
                            <label class="form-check-label" for="jenis-kayu">Log Kayu</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="jenis" id="jenis-palet" value="palet"
                                   @checked(old('jenis') === 'palet')>
                            <label class="form-check-label" for="jenis-palet">Palet</label>
                        </div>
                    </div>
                </div>

                <div class="mb-3" id="pilih-kayu">
                    <label class="form-label fw-semibold" for="wood_type_id">Jenis Kayu</label>
                    <select name="wood_type_id" id="wood_type_id" class="form-select">
                        <option value="">-- Pilih jenis kayu --</option>
                        @foreach ($woodTypes as $type)
                            @php $terkunci = in_array($type->id, $kayuTerkunci); @endphp
                            <option value="{{ $type->id }}" @disabled($terkunci) @selected(old('wood_type_id') == $type->id)>
                                {{ $type->name }}{{ $terkunci ? ' (sedang di-opname)' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Semua kombinasi grade, diameter, dan panjang jenis kayu ini akan dihitung.</small>
                </div>

                <div class="mb-3 d-none" id="pilih-palet">
                    <label class="form-label fw-semibold" for="pallet_status">Status Palet</label>
                    <select name="pallet_status" id="pallet_status" class="form-select">
                        <option value="">-- Pilih status palet --</option>
                        @foreach (\App\Models\StockOpname::STATUS_PALET as $status)
                            @php $terkunci = in_array($status, $paletTerkunci); @endphp
                            <option value="{{ $status }}" @disabled($terkunci) @selected(old('pallet_status') === $status)>
                                {{ ucfirst($status) }}{{ $terkunci ? ' (sedang di-opname)' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Semua ukuran palet dengan status ini akan dihitung.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold" for="catatan">Catatan <span class="text-muted fw-normal">(opsional)</span></label>
                    <textarea name="catatan" id="catatan" rows="2" class="form-control"
                              placeholder="Contoh: opname akhir bulan Oktober">{{ old('catatan') }}</textarea>
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('stock-opname.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-lock-fill"></i> Mulai &amp; Kunci Stok
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    (function () {
        const kayu = document.getElementById('pilih-kayu');
        const palet = document.getElementById('pilih-palet');

        function toggle() {
            const jenis = document.querySelector('input[name="jenis"]:checked')?.value;
            kayu.classList.toggle('d-none', jenis !== 'kayu');
            palet.classList.toggle('d-none', jenis !== 'palet');
        }

        document.querySelectorAll('input[name="jenis"]').forEach(r => r.addEventListener('change', toggle));
        toggle();

        document.getElementById('form-opname').addEventListener('submit', function (e) {
            if (!confirm('Mulai stock opname? Transaksi untuk stok yang dipilih akan dikunci sampai opname selesai.')) {
                e.preventDefault();
            }
        });
    })();
</script>
@endsection
