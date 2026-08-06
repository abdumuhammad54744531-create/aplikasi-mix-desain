# Audit penyimpanan proyek, observasi, dan pemeriksaan

Tanggal audit: 1 Agustus 2026

## Ringkasan penyebab

Aplikasi menggunakan Laravel, Blade, JavaScript, Eloquent ORM, dan MySQL. Hasil audit menemukan bahwa observasi agregat sebelumnya disimpan bersama-sama di kolom JSON `aggregate_test_runs.observations`. Observasi tidak mempunyai primary key sendiri sehingga browser tidak dapat membedakan observasi lama dan baru saat edit atau hapus. Proses simpan juga selalu membuat paket pengujian baru dan beberapa form pemeriksaan tidak mengirim ID record lama. Akibatnya, memilih proyek kembali dapat menampilkan satu observasi saja, membuat record ganda, atau membuka form kosong.

Masalah tambahan ditemukan pada validasi observasi dinamis: nilai selain `id` dapat dibuang dari hasil validasi sebelum transaksi. Form pemeriksaan material, alur beton, dan desain campuran juga belum memuat record tersimpan ke input untuk diedit berdasarkan ID.

## Struktur setelah perbaikan

- `projects` tetap menjadi induk data.
- `aggregate_test_runs` tetap menyimpan identitas paket pemeriksaan dan salinan JSON lama untuk kompatibilitas laporan.
- `aggregate_test_observations` menjadi sumber record per observasi, dengan primary key `id`, foreign key `aggregate_test_run_id`, foreign key `project_id`, nomor urut, dan data JSON khusus satu observasi.
- Kombinasi `aggregate_test_run_id` dan `observation_number` unik. Penghapusan proyek atau paket menghapus detail observasinya melalui foreign key cascade.
- Pemeriksaan semen, air, pasir, kerikil, alur beton, kuat tekan, dan desain campuran menggunakan ID record tersembunyi ketika memperbarui data lama.
- Nilai `0`, nilai desimal, `null`, dan string kosong dipetakan tanpa pemeriksaan JavaScript berbasis nilai truthy/falsy.

Migration `2026_08_01_000001_create_aggregate_test_observations_table.php` membuat tabel baru dan menyalin semua array observasi JSON lama menjadi baris terpisah. Kolom JSON lama tidak dihapus agar laporan lama tetap kompatibel dan rollback struktur aman.

## Alur simpan observasi

1. Frontend mengirim `observations[index][id]` dan setiap input memakai indeks observasi yang berbeda.
2. Backend memvalidasi proyek, array observasi, ID opsional, serta seluruh nilai dinamis.
3. Seluruh proses berjalan dalam transaksi database.
4. Observasi dengan ID dicari berdasarkan ID, paket pengujian, dan proyek; hanya record tersebut yang diperbarui.
5. Observasi tanpa ID dibuat sebagai record baru.
6. Hasil perhitungan dan salinan JSON kompatibilitas disusun kembali dari seluruh record normalisasi.
7. Saat halaman dibuka kembali, jumlah kolom dibangun sesuai jumlah record dan setiap ID/nilai dipasang ke kolomnya sendiri.

Endpoint hapus adalah `DELETE /projects/{project}/aggregate-tests/{run}/observations/{observation}`. Endpoint memeriksa hubungan ketiga record, menolak penghapusan observasi terakhir, menghapus satu observasi, menomori ulang tampilan, dan menghitung ulang hasil. Observasi baru yang belum mempunyai ID hanya dihapus dari DOM.

## Cakupan modul

| Modul | Tabel/model | Hasil audit |
| --- | --- | --- |
| Proyek | `projects` / `Project` | Induk relasi tetap, tidak dihapus saat observasi dihapus |
| Observasi pasir dan kerikil | `aggregate_test_observations` | Dinormalisasi, ID unik, tambah/edit/hapus/muat ulang |
| Kadar air, lumpur, berat jenis, berat isi, saringan, Los Angeles | `aggregate_test_runs` + detail observasi | Semua memakai alur transaksi dan pemuatan ulang yang sama |
| Air | `water_tests` | Muat ulang dan update berdasarkan `test_id` |
| Semen | `cement_tests` | Muat ulang dan update berdasarkan `test_id` |
| Pemeriksaan material pasir | `fine_aggregate_tests` | Muat ulang dan update berdasarkan `test_id` |
| Pemeriksaan material kerikil | `coarse_aggregate_tests` | Muat ulang dan update berdasarkan `test_id` |
| Alur pemeriksaan beton | `laboratory_workflows` | Muat input lama, update berdasarkan `workflow_id`, transaksi |
| Kuat tekan | `laboratory_workflows` | Muat kembali seluruh baris benda uji dan update record terpilih |
| Desain campuran SNI 7656:2012 | `laboratory_workflows` | Muat input, pilihan radio/dropdown, tanggal, catatan, dan ID lama |
| Tanah dan aspal | Tidak tersedia pada source aplikasi ini | Tidak ada route, controller, model, migration, atau form yang dapat diperbaiki |
| Dokumentasi/lampiran | `test_documentations` | Tidak dihapus atau diubah oleh operasi observasi |

## Backup, migrasi, dan pemulihan

Backup aktual sebelum migration tersimpan di:

`storage/app/backups/mix_desain_beton_pre_observation_normalization_20260801.sql`

Contoh backup ulang sebelum deployment lain:

```powershell
mysqldump --host=127.0.0.1 --port=3306 --user=root --single-transaction --routines --triggers --result-file=storage/app/backups/mix_desain_beton_before_migration.sql mix_desain_beton
```

Jalankan migration:

```powershell
php artisan migrate --force
```

Rollback hanya migration terakhir (kolom JSON lama tetap ada):

```powershell
php artisan migrate:rollback --step=1
```

Untuk pemulihan penuh, hentikan penulisan aplikasi terlebih dahulu lalu impor file backup ke database kosong/terverifikasi. Jangan mengimpor backup di atas database aktif tanpa pemeriksaan administrator.

## Verifikasi

Pengujian otomatis mencakup tiga observasi berbeda, buka ulang, edit observasi kedua, tambah keempat, hapus kedua, larangan lintas proyek, rollback transaksi, pemeriksaan air, seluruh pemeriksaan material, nilai nol/kosong/desimal, serta alur beton. Jalankan:

```powershell
php artisan test
npm run build
```

Setelah migration pada database lokal, verifikasi menghasilkan 28 paket, 28 record observasi normalisasi, nol paket tanpa detail, dan nol relasi proyek yatim.

## Catatan risiko

- Tombol hapus sengaja menolak penghapusan observasi terakhir; pengguna dapat memperbarui observasi tersebut atau mengarsipkan paket melalui alur arsip yang sudah tersedia.
- Salinan JSON pada `aggregate_test_runs` dipertahankan untuk kompatibilitas. Semua perubahan melalui controller harus selalu menyinkronkan salinan tersebut dari tabel detail.
- Modul tanah dan aspal belum ada dalam codebase. Jika modul tersebut ditambahkan, gunakan pola ID record + `project_id`, transaksi, dan pemuatan ulang yang sama.
- Deployment harus menggunakan build Vite terbaru dan membersihkan cache view setelah memperbarui source.
