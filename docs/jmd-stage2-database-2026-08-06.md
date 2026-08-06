# Tahap 2 - Fondasi Database JMD

Tanggal: 6 Agustus 2026

## Tujuan

Tahap 2 menambahkan struktur database Job Mix Design secara aditif tanpa mengganti `projects`, menghapus tabel lama, atau mengubah alur aplikasi yang sudah berjalan. Tabel lama tetap menjadi compatibility layer sampai model dan service baru diperkenalkan pada Tahap 3.

## Strategi kompatibilitas

- `projects` tetap menjadi induk seluruh data. Kolom JMD ditambahkan pada tabel ini agar route, model, dan laporan lama tidak kehilangan relasi.
- `number` lama tidak diubah. Kolom baru `jmd_number` diisi dari `JMD-{number}` untuk empat proyek yang sudah ada dan diberi unique index.
- `aggregate_test_runs`, `aggregate_test_observations`, `laboratory_workflows`, `mix_designs`, `reference_headers`, dan seluruh tabel lama tetap utuh.
- Hasil baru mempunyai raw/rounded result, calculation log, validation messages, standard snapshot, dan revision number agar perubahan master di masa depan tidak mengubah hasil historis.
- Data penting baru memakai soft delete. Detail observasi/material memakai cascade hanya ketika induknya benar-benar dihapus permanen.
- Foreign key pengguna menggunakan `nullOnDelete` agar riwayat teknis tidak hilang ketika akun dinonaktifkan/dihapus.

## File migration

### `2026_08_06_000001_create_jmd_foundation_tables.php`

Menambahkan:

- Master standar berversi: `standard_references`, `standard_table_headers`, dan `standard_table_values`.
- Data umum JMD, status, progres, snapshot lembaga, logo/kop/tanda tangan pada `projects`.
- Material per proyek pada `jmd_project_materials`.
- Kriteria desain dan konversi mutu pada `jmd_design_criteria`.
- Watermark, kop, stempel, format nomor, separator desimal, dan pengulangan header pada `report_settings`.

### `2026_08_06_000002_create_jmd_material_test_tables.php`

Menambahkan header dan observasi terpisah untuk:

- `moisture_tests` dan `moisture_test_items`.
- `silt_tests` dan `silt_test_items`.
- `fine_aggregate_sg_tests` dan `fine_aggregate_sg_items`.
- `coarse_aggregate_sg_tests` dan `coarse_aggregate_sg_items`.
- `bulk_density_tests` dan `bulk_density_items` untuk agregat/semen serta kondisi lepas/padat.
- `cement_sg_tests` dan `cement_sg_items`.
- `sieve_tests` dan `sieve_test_items` dengan ukuran saringan dinamis.
- `abrasion_tests` dan `abrasion_test_items`.

Setiap header terhubung ke proyek/material, memiliki status, revision number, standard/result snapshot, audit pengguna, approval, timestamp, dan soft delete. Setiap observasi mempunyai primary key dan nomor observasi unik dalam pengujiannya.

### `2026_08_06_000003_create_jmd_calculation_tables.php`

Menambahkan:

- `mix_design_calculations` dan `mix_design_material_results`.
- `moisture_corrections`.
- `trial_mixes` dan `trial_mix_materials`.
- `slump_tests` beserta catatan semua penambahan bahan.
- `compressive_strength_tests` dan `compressive_strength_specimens`.
- `field_batch_conversions` untuk per m3, per zak, molen, batching, wadah, dan volume proyek.

Perhitungan resmi dapat menyimpan input snapshot, standard snapshot, hasil mentah, hasil pembulatan, log formula, pesan validasi, koreksi volume eksplisit, dan status lock/approval.

### `2026_08_06_000004_create_jmd_governance_tables.php`

Menambahkan:

- `jmd_manual_overrides` dengan nilai awal/pengganti, alasan, pelaku, waktu, dan approval.
- `jmd_revisions` dengan parent revision, snapshot perhitungan/standar/laporan, hash, approval, dan lock.
- `jmd_eligibility_checks` untuk status kelayakan per kriteria.
- `jmd_conclusions` untuk kesimpulan otomatis dan hasil edit pemeriksa.
- `jmd_photos` dengan original/processed path, rotasi, crop, kualitas kompresi, dan urutan.
- `jmd_audit_notes` untuk data laporan, hasil aplikasi, selisih, dugaan sebab, rekomendasi, dan keputusan.
- Link revisi serta snapshot kewenangan/hash konten pada `report_approvals`.

## Status proyek JMD

Kolom `jmd_status` sengaja berupa string terindeks, bukan database enum, agar workflow dapat berkembang tanpa migration perubahan enum. Nilai yang akan diberlakukan pada model/validation Tahap 3:

1. `draft`
2. `material_incomplete`
3. `material_testing`
4. `ready_to_calculate`
5. `calculation_completed`
6. `trial_mix`
7. `awaiting_strength_test`
8. `completed`
9. `approved`
10. `archived`

Status lama `projects.status` tetap dipertahankan untuk kompatibilitas aplikasi saat ini.

## Backup

Backup dibuat sebelum migration MySQL:

`storage/app/backups/mix_desain_beton_pre_jmd_stage2_20260806.sql`

- Ukuran: 397.918 byte
- SHA-256: `664FBFE2065A58A743DAEA6FA3B58C0F27DAAC4484DB426D8C378EB2EB001B8A`
- Metode: `mysqldump --single-transaction --routines --triggers`

## Hasil migration dan verifikasi

- Empat migration Tahap 2 berstatus `Ran` pada MySQL.
- 36 tabel/modul JMD baru tersedia.
- 114 foreign key baru terdeteksi pada kelompok tabel JMD.
- Empat proyek lama memperoleh `jmd_number`; tidak ada nilai kosong atau duplikat.
- Jumlah record domain sebelum/sesudah tetap: 4 proyek, 11 sumber material, 39 paket agregat, 39 observasi, dan 8 approval.
- `audit_logs` terus bertambah karena aplikasi lokal masih aktif dan pengguna menyimpan pemeriksaan selama migration. Entri baru merupakan aksi aplikasi (`Pemeriksaan Semen`, `Pemeriksaan Air`, dan `Sumber Material`), bukan backfill migration.
- Migration yang gagal sementara akibat batas 64 karakter nama foreign key MySQL dihentikan sebelum data masuk. Tabel parsial baru diverifikasi kosong, dihapus, foreign key diberi nama pendek, lalu migration berhasil dijalankan ulang. Tidak ada tabel/data lama yang dihapus.

## Pengujian

- Schema test baru: `JmdSchemaMigrationTest`.
- Hasil akhir: 37 test lulus, 252 assertion lulus.
- Fresh migration SQLite lulus.
- Rollback empat migration Tahap 2 pada SQLite in-memory lulus.
- Setelah rollback terisolasi, tabel lama `projects` tetap ada dan tabel baru `jmd_revisions` sudah tidak ada.
- Laravel Pint lulus untuk seluruh file Tahap 2.

## Batas Tahap 2

Migration ini belum mengalihkan controller lama ke tabel baru dan belum memindahkan formula. Itu merupakan ruang lingkup Tahap 3 dan seterusnya. Menulis ke tabel JMD baru sebelum model, DTO, service, Form Request, Policy, dan transaction boundary selesai tidak disarankan.

## Rollback

Rollback tersedia dalam urutan dependency yang aman:

```powershell
php artisan migrate:rollback --step=4
```

Rollback tersebut hanya menghapus struktur Tahap 2 dan kolom tambahan. Karena `jmd_number` merupakan kolom baru hasil backfill, nilai tersebut ikut hilang pada rollback; `projects.number` dan seluruh data lama tetap utuh. Pada lingkungan berisi data JMD baru, backup harus dibuat sebelum rollback.
