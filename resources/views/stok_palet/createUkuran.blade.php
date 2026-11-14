@extends('layouts.app')

@section('title', 'Tambah Ukuran Palet')

@section('content')
<style>
    body { background: #f4faef; }
    .card { border-radius: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: none; }
    .card-header { background: #4e944f !important; color: #fff !important; border-radius: 16px 16px 0 0; }
    .preview-box { background: #e6f4e6; border-radius: 10px; padding: 12px 16px; color: #2d4d2d; }
    .badge-ukuran { background: #fff; border: 1px solid #b6e2b6; color: #2d4d2d; font-weight: 500; }
</style>

<div class="container-fluid px-1 px-md-4">
    <div class="card shadow-sm" style="max-width: 720px;">
        <div class="card-header">
            <h4 class="mb-0">Tambah Ukuran Palet</h4>
        </div>
        <div class="card-body">

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('pallet-prices.store') }}" method="POST" autocomplete="off">
                @csrf

                <p class="text-muted mb-3">
                    Masukkan dimensi palet dalam cm. Kode palet dibuat otomatis
                    (<strong>{{ $nextCode }}</strong>), dan harga basah &amp; kering diisi 0
                    yang bisa diubah setelah disimpan.
                </p>

                <div class="row g-3">
                    <div class="col-12 col-sm-4">
                        <label for="lebar" class="form-label">Lebar (cm)</label>
                        <input type="number" step="0.01" min="0.01" name="lebar" id="lebar"
                               class="form-control dim-input @error('lebar') is-invalid @enderror"
                               value="{{ old('lebar') }}" placeholder="contoh: 8" required>
                    </div>
                    <div class="col-12 col-sm-4">
                        <label for="tebal" class="form-label">Tebal (cm)</label>
                        <input type="number" step="0.01" min="0.01" name="tebal" id="tebal"
                               class="form-control dim-input @error('tebal') is-invalid @enderror"
                               value="{{ old('tebal') }}" placeholder="contoh: 1.2" required>
                    </div>
                    <div class="col-12 col-sm-4">
                        <label for="panjang" class="form-label">Panjang (cm)</label>
                        <input type="number" step="0.01" min="0.01" name="panjang" id="panjang"
                               class="form-control dim-input @error('panjang') is-invalid @enderror"
                               value="{{ old('panjang') }}" placeholder="contoh: 100" required>
                    </div>
                </div>

                <div class="preview-box mt-3">
                    Ukuran (L x T x P): <strong id="preview-ukuran">-</strong>
                    &nbsp;·&nbsp; Volume per pcs: <strong id="preview-volume">-</strong>
                    <div class="small mt-1" id="preview-lebar" style="color:#b02a37;" hidden>
                        Lebar tidak boleh lebih kecil dari tebal.
                    </div>
                    <div class="small mt-1" id="preview-warning" style="color:#b02a37;" hidden>
                        Ukuran ini sudah terdaftar.
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2 mt-4">
                    <button type="submit" class="btn btn-success">Simpan Ukuran</button>
                    <a href="{{ route('pallet-prices.index') }}" class="btn btn-secondary">Kembali</a>
                </div>
            </form>

            <hr>
            <div class="small text-muted mb-2">Ukuran yang sudah terdaftar:</div>
            <div class="d-flex flex-wrap gap-2">
                @foreach ($existing as $u)
                    <span class="badge badge-ukuran">{{ $u }}</span>
                @endforeach
            </div>
        </div>
    </div>
</div>

<script>
    // Pratinjau ukuran (lebar x tebal x panjang) & volume, rumus sama dengan server: (L x T x P) / 1000
    (function () {
        const existing = @json($existing);
        const fmt = n => String(Math.round(parseFloat(n) * 100) / 100);
        function update() {
            const t = document.getElementById('tebal').value;
            const l = document.getElementById('lebar').value;
            const p = document.getElementById('panjang').value;
            const ok = [t, l, p].every(v => parseFloat(v) > 0);
            const ukuran = ok ? `${fmt(l)}x${fmt(t)}x${fmt(p)}` : '-';
            document.getElementById('preview-lebar').hidden = !(ok && parseFloat(l) < parseFloat(t));
            document.getElementById('preview-ukuran').textContent = ukuran;
            document.getElementById('preview-volume').textContent = ok
                ? (parseFloat(t) * parseFloat(l) * parseFloat(p) / 1000).toLocaleString('id-ID', { maximumFractionDigits: 6 })
                : '-';
            // Sama seperti server: kombinasi dimensi yang sama dengan urutan berbeda dianggap ukuran yang sama
            const kunci = (a, b, c) => [a, b, c].map(n => Math.round(n * 100) / 100).sort((x, y) => x - y).join('|');
            const dicari = ok ? kunci(parseFloat(t), parseFloat(l), parseFloat(p)) : null;
            const sama = ok ? existing.find(u => {
                const d = String(u).replace(/\s/g, '').toLowerCase().split('x').map(n => parseFloat(n.replace(',', '.')));
                return d.length === 3 && kunci(d[0], d[1], d[2]) === dicari;
            }) : null;
            const warning = document.getElementById('preview-warning');
            warning.textContent = sama === ukuran ? 'Ukuran ini sudah terdaftar.'
                : `Ukuran ini sudah terdaftar sebagai ${sama} (kombinasi dimensi sama, urutan berbeda).`;
            warning.hidden = !sama;
        }
        document.querySelectorAll('.dim-input').forEach(el => el.addEventListener('input', update));
        update();
    })();
</script>
@endsection
