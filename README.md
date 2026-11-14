# WMS Gudang Kayu – UD TPKS

Aplikasi web **Warehouse Management System (WMS)** untuk mencatat stok kayu log dan pallet, transaksi pembelian dan penjualan, serta stock opname di UD TPKS. Aplikasi ini dibangun sebagai tugas akhir S1 Informatika Universitas Diponegoro.

![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5-7952B3?logo=bootstrap&logoColor=white)
![Livewire](https://img.shields.io/badge/Livewire-3-FB70A9?logo=livewire&logoColor=white)
![PHPUnit](https://img.shields.io/badge/Tested_with-PHPUnit-3C9CD7)

---

## Fitur Utama

| Modul | Fungsi |
| --- | --- |
| **Dashboard** | Ringkasan stok dan transaksi dalam bentuk grafik (Chart.js) |
| **Stok Kayu** | Stok per jenis kayu, diameter, panjang, dan grade, termasuk input rentang ukuran sekaligus |
| **Transaksi Kayu** | Pembelian, penjualan, dan potong gergaji, dengan perhitungan otomatis berdasarkan volume (m³) × harga/m³ |
| **Stok Pallet** | Input pallet per batch dan alur status batch sampai siap jual |
| **Penjualan Pallet** | Transaksi penjualan, nota PDF, dan laporan mingguan |
| **Stock Opname** | Hitung fisik kayu dan pallet per batch, perhitungan selisih pcs, volume, dan nilai, lalu penyesuaian stok otomatis |
| **Laporan** | Laporan transaksi harian, bulanan, dan tahunan, serta ekspor stok ke PDF |
| **Master Data** | Jenis kayu, harga beli/jual per m³, ukuran dan harga pallet |
| **Manajemen Akun** | Kelola pengguna dengan peran **admin** dan **staff** |

## Sorotan Teknis

- **Konsistensi stok.** Setiap perubahan stok (beli, jual, potong gergaji, opname) berjalan di dalam `DB::transaction` dengan `lockForUpdate()`. Dengan begitu, dua transaksi yang mengubah stok yang sama secara bersamaan tidak saling menimpa.
- **Perhitungan berbasis volume.** Semua nilai transaksi dihitung dari volume kayu (m³) × harga per m³. Hasil potong gergaji dihitung dengan harga jual.
- **Stock opname yang dapat diaudit.** Setiap opname menyimpan stok sistem, stok fisik, selisih pcs, selisih volume, dan nilai selisih per item, sehingga riwayat penyesuaian dapat ditelusuri.
- **Hak akses berbasis peran.** Middleware `role` membatasi menu master data, akun, dan stock opname hanya untuk admin.
- **ID tersamar di URL.** ID transaksi dan akun di URL disamarkan dengan Hashids.
- **Dokumen PDF.** Nota, laporan, dan data stok dibuat dalam format PDF dengan DomPDF.
- **Pengujian.** Tersedia feature test dan black-box test (PHPUnit) yang dijalankan pada database pengujian terpisah.

## Teknologi

| Lapisan | Teknologi |
| --- | --- |
| Back-end | PHP 8.2, Laravel 12, Eloquent ORM, Livewire 3 |
| Database | MySQL |
| Front-end | Blade, Bootstrap 5, Tailwind CSS, Chart.js, Vite |
| Dokumen | barryvdh/laravel-dompdf |
| Lainnya | vinkla/hashids, PHPUnit |

## Struktur Singkat

```
app/
├── Http/Controllers/   # Logika transaksi, stok, pallet, opname, laporan
├── Http/Middleware/    # RoleMiddleware (admin / staff)
└── Models/             # WoodStock, WoodTransaction, PalletStock, StockOpname, dst.
database/
├── migrations/         # Skema database
└── seeders/            # Data awal: jenis kayu, grade, diameter, harga, akun
resources/views/        # Tampilan Blade per modul
tests/Feature/          # Feature test dan black-box test
```

## Instalasi

**Kebutuhan:** PHP 8.2+, Composer, Node.js dan NPM, MySQL.

```bash
git clone https://github.com/galiaji/GudangLengkap.git
cd GudangLengkap

composer install
npm install

cp .env.example .env
php artisan key:generate
```

Atur koneksi database di `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gudang_kayu
DB_USERNAME=root
DB_PASSWORD=
```

Buat tabel dan data awal, lalu jalankan aplikasi:

```bash
php artisan migrate --seed
npm run build
php artisan serve
```

Aplikasi berjalan di `http://localhost:8000`. Akun awal dibuat oleh `database/seeders/UserSeeder.php`.

## Menjalankan Test

```bash
php artisan test
```

Gunakan database terpisah untuk pengujian, misalnya dengan mengatur `DB_DATABASE` di `.env.testing`, supaya data utama tidak terhapus.

## Pengembang

**Galih Aji Syafaat**, S1 Informatika Universitas Diponegoro
GitHub: [@galiaji](https://github.com/galiaji)
