# Tahap 4 — Workflow Pengujian Material JMD

Tanggal penyelesaian: 7 Agustus 2026

## Hasil

Tahap ini menghubungkan skema database dan service perhitungan JMD ke workflow web yang dapat digunakan teknisi. Modul lama tetap tersedia tanpa perubahan route atau tabel.

Modul pengujian yang tersedia per proyek:

1. Kadar air agregat.
2. Kadar lumpur agregat.
3. Berat jenis agregat halus.
4. Berat jenis agregat kasar.
5. Berat volume material.
6. Berat jenis semen.
7. Analisis saringan.
8. Abrasi agregat kasar.

## Alur Pengguna

- Menu `Pengujian Material JMD` membuka pemilih proyek.
- Dashboard proyek menampilkan delapan modul, jumlah record, status, dan hasil terakhir.
- Setiap lembar kerja mendukung minimal dua observasi, tambah baris, hapus baris terpilih, simpan baru, edit, dan hitung ulang.
- Riwayat 15 pengujian terakhir tersedia pada setiap lembar kerja.
- Nomor pengujian dibuat di server dengan ULID.
- Proyek yang sudah dikunci dapat dilihat, tetapi tidak dapat diubah.

## Integritas dan Keamanan Data

- `project_id` selalu diambil ulang dari route model binding, sehingga tidak dapat dipalsukan lewat hidden input.
- Material dan record uji wajib berasal dari proyek yang sama.
- Update menggunakan `test_id` yang dicari dalam scope proyek.
- Header, observasi, snapshot hasil, progres proyek, dan audit disimpan dalam satu transaksi database.
- Observasi yang tidak lagi dikirim oleh formulir dihapus; observasi yang dipertahankan diperbarui berdasarkan ID.
- Perhitungan selalu dilakukan ulang melalui service domain Tahap 3.
- Kesalahan domain dikonversi menjadi validation error dan tidak menghasilkan penyimpanan parsial.
- Sumber standar dan limit disimpan dalam `standard_snapshot`; hasil mentah, hasil pembulatan, satuan, rumus, sumber, pesan, dan log disimpan dalam `result_snapshot`.
- Pengguna baca-saja tidak dapat menyimpan. Pemohon tidak dapat membuka modul staf.

## Route Baru

- `GET /jmd/material-tests`
- `GET /jmd/projects/{project}/material-tests`
- `GET /jmd/projects/{project}/material-tests/{module}`
- Delapan endpoint `POST` spesifik modul di bawah `/jmd/projects/{project}/material-tests/*`.

## Verifikasi

- PHP lint: lulus.
- Route discovery: 11 route JMD material testing terdaftar.
- Blade compilation: lulus.
- PHPUnit: 53 test, 346 assertion, seluruhnya lulus.
- Browser QA lokal: portal proyek, dashboard delapan modul, formulir dua baris awal, tambah/hapus observasi, status proyek terkunci, dan console browser diverifikasi tanpa error.

## Batas Tahap 4

Nilai standar belum otomatis dipilih dari master standard. Teknisi wajib menyebut sumber acuan dan limit yang relevan. Otomatisasi master standard, rekomendasi nilai, dan penguncian parameter desain dilanjutkan pada tahap berikutnya.
