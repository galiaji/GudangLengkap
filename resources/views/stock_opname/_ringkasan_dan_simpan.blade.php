{{-- Ringkasan selisih (dihitung langsung saat mengetik), keterangan, dan tombol simpan --}}
<div class="row g-2 my-3">
    <div class="col-6 col-lg-3">
        <div class="border rounded p-2 h-100 bg-white">
            <div class="small text-muted">Baris terisi</div>
            <div class="fs-5 fw-bold"><span id="ring-terisi">0</span> <span class="fs-6 fw-normal text-muted">/ <span id="ring-jumlah">0</span></span></div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="border rounded p-2 h-100 bg-white">
            <div class="small text-muted">Baris selisih</div>
            <div class="fs-5 fw-bold" id="ring-selisih">0</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="border rounded p-2 h-100 bg-white">
            <div class="small text-muted">Total selisih</div>
            <div class="fs-5 fw-bold"><span id="ring-qty">0</span> {{ $satuan }}</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="border rounded p-2 h-100 bg-white">
            <div class="small text-muted">Total selisih volume</div>
            <div class="fs-5 fw-bold"><span id="ring-vol">0,000</span> m³</div>
        </div>
    </div>
</div>

<div class="mb-3">
    <label for="keterangan" class="form-label fw-semibold">Keterangan</label>
    <textarea name="keterangan" id="keterangan" rows="2" maxlength="1000"
              class="form-control @error('keterangan') is-invalid @enderror"
              placeholder="Contoh: hitung ulang gudang A, 2 batang busuk dibuang">{{ old('keterangan') }}</textarea>
</div>

<button type="submit" class="btn btn-success">
    <i class="bi bi-save"></i> Simpan Hasil Stock Opname
</button>
