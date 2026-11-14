<script>
    // Hitung selisih jumlah & volume langsung saat jumlah fisik diketik
    (function () {
        const tabel = document.getElementById('tabel-opname');
        if (!tabel) return;

        const baris = Array.from(tabel.querySelectorAll('.baris-opname'));
        const angka = (n, d) => n.toLocaleString('id-ID', { minimumFractionDigits: d, maximumFractionDigits: d });
        const tanda = (n, d) => (n > 0 ? '+' : '') + angka(n, d);

        function hitung() {
            let terisi = 0, jumlahSelisih = 0, totalQty = 0, totalVol = 0;
            baris.forEach(tr => {
                const input = tr.querySelector('.input-fisik');
                const qtyCell = tr.querySelector('.sel-qty');
                const volCell = tr.querySelector('.sel-vol');
                tr.classList.remove('table-danger', 'table-success');

                if (input.value === '' || isNaN(Number(input.value))) {
                    qtyCell.textContent = volCell.textContent = '-';
                    return;
                }
                terisi++;
                const selisih = parseInt(input.value, 10) - Number(tr.dataset.sistem);
                const vol = selisih * Number(tr.dataset.volume);
                qtyCell.textContent = tanda(selisih, 0);
                volCell.textContent = tanda(vol, 3);
                if (selisih !== 0) {
                    jumlahSelisih++;
                    tr.classList.add(selisih < 0 ? 'table-danger' : 'table-success');
                }
                totalQty += selisih;
                totalVol += vol;
            });
            document.getElementById('ring-terisi').textContent = terisi;
            document.getElementById('ring-jumlah').textContent = baris.length;
            document.getElementById('ring-selisih').textContent = jumlahSelisih;
            document.getElementById('ring-qty').textContent = tanda(totalQty, 0);
            document.getElementById('ring-vol').textContent = tanda(totalVol, 3);
        }

        // Filter tampilan (hanya menyembunyikan baris, isian tetap ikut terkirim)
        const cari = document.getElementById('cari-item');
        const hanyaAda = document.getElementById('hanya-ada-stok');
        function filter() {
            const q = cari ? cari.value.trim().toLowerCase() : '';
            const ada = hanyaAda && hanyaAda.checked;
            baris.forEach(tr => {
                const sembunyi = (q && !tr.dataset.cari.includes(q)) || (ada && Number(tr.dataset.sistem) === 0);
                tr.classList.toggle('d-none', !!sembunyi);
            });
        }

        tabel.addEventListener('input', e => { if (e.target.classList.contains('input-fisik')) hitung(); });
        cari && cari.addEventListener('input', filter);
        hanyaAda && hanyaAda.addEventListener('change', filter);
        hitung();
    })();
</script>
