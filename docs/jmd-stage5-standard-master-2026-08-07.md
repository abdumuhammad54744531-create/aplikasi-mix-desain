# Tahap 5 — Master Tabel Standar JMD

Tanggal penyelesaian: 7 Agustus 2026

## Implementasi

- Katalog 20 kelompok tabel sesuai spesifikasi JMD.
- CRUD referensi, tabel, dan nilai hanya untuk administrator.
- Riwayat versi menggunakan revision-by-copy; versi lama tidak ditimpa.
- Referensi mempunyai nomor, tahun, tanggal berlaku/kedaluwarsa, status aktif, sumber, catatan, dan relasi pengganti.
- Nilai mendukung dimensi JSON, angka, teks, minimum, maksimum, satuan, catatan, urutan, dan status aktif.
- Resolver hanya menerima referensi/tabel/nilai aktif yang sedang berlaku.
- Snapshot menyimpan ID referensi, tabel, nilai, seluruh nilai terpilih, revisi, sumber, dan waktu pengambilan.
- Form pengujian kadar lumpur, abrasi, dan toleransi saringan terhubung ke nilai master.
- Nilai tabel dibaca ulang pada server dan tidak mempercayai nilai dari browser.
- Pemilihan batas pasir/kerikil yang tidak sesuai ditolak.
- Mode manual mewajibkan sumber dan alasan; mode legacy dipertahankan untuk kompatibilitas.

## Data Awal

`JmdStandardMasterSeeder` membuat 20 header dan 23 nilai konfigurasi awal secara idempotent. Referensi diberi nama **Konfigurasi Awal Master JMD — Wajib Diverifikasi** dan tidak diposisikan sebagai pengganti dokumen standar resmi.

Nilai awal yang berasal dari spesifikasi aplikasi:

- Batas lumpur agregat halus: 5%.
- Batas lumpur agregat kasar: 1%.
- Batas abrasi: 40%.
- Toleransi neraca massa awal: 1%, dapat direvisi.
- Seri saringan awal pasir dan kerikil.

## Keamanan Versi

- Referensi atau tabel nonaktif tidak dapat direvisi.
- Versi yang telah digantikan tidak dapat diaktifkan kembali.
- Kelompok tabel aktif tidak dapat diduplikasi dalam satu referensi.
- Revisi nilai membuat revisi tabel baru dan mempertahankan nilai versi lama.
- Master bertanggal masa depan atau kedaluwarsa tidak dapat dipakai menghitung.
- Semua perubahan dicatat pada audit log.

## Verifikasi

- Database lokal: 1 referensi, 20 tabel aktif, 23 nilai aktif.
- Route master: 9 endpoint.
- Blade compilation: lulus.
- Browser QA: 20 tombol revisi tabel, 20 form tambah nilai, pilihan table/manual, pengisian otomatis nilai 5% dan sumber standar, tanpa error console.
- Feature test mencakup seeder idempotent, otorisasi, preservasi versi, snapshot server-side, alasan manual, dan mismatch agregat.
