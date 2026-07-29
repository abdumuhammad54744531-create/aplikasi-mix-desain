# MIX DESIGN BETON SNI 7656:2012

Aplikasi laboratorium berbasis Laravel untuk perencanaan, pengujian, evaluasi, dan pelaporan campuran beton.

## Status implementasi

Fondasi Tahap 1 telah tersedia: autentikasi, peran pengguna, dashboard, data proyek, sumber material, rancangan database inti, audit log, tabel referensi, draft wizard mix design, dan calculation services awal. Menu tahap berikutnya sudah disiapkan tetapi belum boleh dianggap selesai.

## Instalasi Laragon

1. Pilih PHP 8.3 atau lebih baru di Laragon.
2. Salin `.env.example` menjadi `.env`, lalu atur database MySQL:
   `DB_CONNECTION=mysql`, `DB_HOST=127.0.0.1`, `DB_PORT=3306`, `DB_DATABASE=mix_desain_beton`, `DB_USERNAME=root`.
3. Buat database `mix_desain_beton`.
4. Jalankan `composer install`, `php artisan key:generate`, lalu `php artisan migrate:fresh --seed`.
5. Buka `http://mix-desain-beton.test` melalui Laragon.

Akun awal: username `admin`, password sementara `Admin@12345`. Password ini wajib diganti sebelum penggunaan produksi.

## Prinsip perhitungan

Semua rumus berada di `app/Services/MixDesign`, menerima input terstruktur, memvalidasi nilai, dan mengembalikan hasil, rumus, substitusi, satuan, serta pesan. Nilai penting disimpan sebagai `DECIMAL`, bukan `FLOAT`. Field kosong tidak dianggap nol.

Rumus dasar:

- Volume absolut: `V = W / (SG × ρ air)`.
- Interpolasi: `y = y1 + ((x − x1) / (x2 − x1)) × (y2 − y1)`.
- Kuat tekan: `f'c = P / A`, dengan N/mm² menghasilkan MPa.
- Koreksi kelembapan membedakan kadar air aktual dengan penyerapan SSD.

## Tabel referensi

Angka tabel SNI tidak disertakan. Administrator harus mengisi atau mengimpor nilai dari dokumen resmi yang dimiliki laboratorium, lengkap dengan nomor standar, tahun, tanggal berlaku, dan sumber.

## Peringatan

Hasil aplikasi wajib diverifikasi oleh tenaga teknis yang kompeten. Draft bukan laporan resmi dan tidak boleh digunakan sebagai dasar penerimaan pekerjaan.
