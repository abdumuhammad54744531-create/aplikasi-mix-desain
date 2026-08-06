# Audit Tahap 1 Aplikasi Job Mix Design Beton

Tanggal audit: 6 Agustus 2026

Lokasi proyek: `C:\laragon\www\mix-desain-beton`

Branch saat audit: `main`
Kondisi awal worktree: bersih

## 1. Ringkasan eksekutif

Aplikasi bukan proyek kosong. Fondasi yang sudah berfungsi meliputi autentikasi, permohonan laboratorium, proyek, sumber material, pemeriksaan material, observasi agregat dinamis, desain campuran SNI 7656:2012, gradasi gabungan, workflow beton, dokumentasi, arsip, persetujuan, verifikasi QR, laporan HTML, dan unduhan PDF melalui Dompdf.

Baseline teknis sehat: seluruh 18 migration telah terpasang, 36 test dengan 211 assertion lulus, dan build Vite berhasil. Database produksi lokal berisi data lama sehingga perubahan berikutnya wajib bersifat aditif dan menggunakan migration aman.

Namun aplikasi belum memenuhi spesifikasi JMD lengkap. Kesenjangan utama adalah struktur data JMD yang masih terlalu generik/JSON, banyak tabel standar masih di-hardcode, beberapa rumus masih berada di controller dan JavaScript, modul statistik kuat tekan belum lengkap, status/progres proyek belum sesuai workflow JMD, snapshot revisi belum lengkap, dan laporan belum memiliki seluruh opsi ekspor serta kontrol audit yang diminta.

## 2. Inventaris teknologi dan struktur

- Laravel 13.22.0, PHP 8.5.9, MySQL 8.4.3.
- Blade + Bootstrap 5.3.7, Tailwind/Vite tersedia untuk aset utama.
- Dompdf 3.1 untuk unduhan PDF dan Endroid QR Code untuk verifikasi.
- 64 route aplikasi.
- 17 controller aplikasi, 19 model, 7 service perhitungan/audit, 18 migration, 14 file test, dan 32 view Blade.
- Penyimpanan file publik sudah terhubung melalui `public/storage`.
- Tidak ada `AGENTS.md` di repository.

### Controller yang tersedia

`AccountController`, `AggregateTestController`, `ArchiveController`, `AuthController`, `DashboardController`, `LaboratoryWorkRequestController`, `MaterialResultController`, `MaterialSourceController`, `MaterialTestController`, `MixDesignController`, `MixDesign2012Controller`, `ProjectController`, `ReferenceController`, `ReportSettingController`, `TestDocumentationController`, dan `WorkflowController`.

### Service yang tersedia

- `AggregateTestCalculator`
- `AuditService`
- `AbsoluteVolumeCalculator`
- `CompressiveStrengthCalculator`
- `InterpolationService`
- `MixDesign2012Calculator`
- `MoistureCorrectionCalculator`

Service spesifik yang belum tersedia mencakup service kadar lumpur, berat jenis agregat halus/kasar, berat volume, saringan, abrasi, trial mix, field batch, validasi JMD, statistik kuat tekan lengkap, dan generator laporan JMD sebagai unit terpisah.

## 3. Inventaris database aktual

Tabel domain aplikasi saat ini:

| Kelompok | Tabel |
| --- | --- |
| Pengguna/infrastruktur | `users`, `password_reset_tokens`, `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs` |
| Proyek/lembaga | `projects`, `laboratory_profiles`, `laboratory_work_requests` |
| Material | `material_sources`, `cement_tests`, `water_tests`, `fine_aggregate_tests`, `coarse_aggregate_tests` |
| Observasi agregat | `aggregate_test_runs`, `aggregate_test_observations` |
| Desain/workflow | `mix_designs`, `laboratory_workflows` |
| Standar | `reference_headers`, `reference_details` |
| Dokumentasi/pelaporan | `test_documentations`, `report_settings`, `report_approvals`, `audit_logs` |

Relasi `project_id` sudah digunakan pada sebagian besar data domain. Observasi agregat sudah mempunyai ID sendiri dan unik per paket/nomor observasi. Soft delete telah digunakan pada proyek dan beberapa data penting. Foreign key serta transaction sudah digunakan pada sejumlah alur simpan kompleks.

Kesenjangan skema terhadap kebutuhan JMD:

- Belum ada entitas induk `jmd_projects` atau padanan satu-ke-satu yang menyimpan nomor laporan/contoh/surat, tanggal-tanggal laboratorium, data perusahaan lengkap, desain kriteria, dan status workflow JMD.
- Belum ada tabel terpisah untuk design criteria, koreksi kadar air, trial mix/material, slump, benda uji kuat tekan, kesimpulan, foto dengan transformasi, approval JMD, catatan audit referensi, serta revision snapshot.
- `laboratory_workflows.input_data` dan `result_data` menampung banyak modul dalam JSON. Ini cepat untuk prototipe tetapi lemah untuk foreign key detail, query statistik, validasi per baris, revision snapshot, dan audit perubahan granular.
- `aggregate_test_runs` masih mempertahankan observasi dan hasil JSON untuk kompatibilitas, sementara detail observasi juga sudah dinormalisasi. Sinkronisasi kedua sumber harus dijaga sampai migrasi transisi selesai.
- `reference_details` hanya menyediakan pasangan angka generik; belum ada header/kolom/nilai tabel multidimensi, satuan per dimensi, versi, alasan override, dan riwayat revisi standar.
- Beberapa model memakai `$guarded = []`. Validasi controller mengurangi risiko, tetapi DTO/Form Request dan daftar field eksplisit lebih aman untuk modul teknis yang akan bertambah.

## 4. Fitur yang sudah ada dan tingkat kesiapan

| Area | Kondisi saat ini | Penilaian |
| --- | --- | --- |
| Proyek | Nomor otomatis, nama, pemilik, lokasi, kontrak, jenis konstruksi, dua opsi laporan | Sebagian |
| Status proyek | Hanya `aktif` dan `selesai`, ditambah status dokumen terpisah | Belum sesuai |
| Progres modul | Belum ada indikator kelengkapan per modul | Belum ada |
| Material | Sumber semen/air/pasir/kerikil dan pemeriksaan ringkas | Sebagian |
| Observasi dinamis | Tambah, muat ulang, edit, hapus per ID, isolasi proyek, transaction | Baik sebagai fondasi |
| Kadar air | Rumus otomatis dan rata-rata | Sebagian; minimum masih 1 observasi |
| Kadar lumpur | Rumus otomatis dari massa bersih sebelum/sesudah | Sebagian; input cawan dan master batas belum ada |
| BJ/penyerapan agregat | Rumus halus/kasar tersedia | Sebagian; detail input, validasi, tooltip, dan test lengkap belum ada |
| Berat isi agregat | Berat isi dan rongga tersedia | Sebagian; kondisi lepas/padat serta pilihan nilai belum dimodelkan |
| Analisis saringan | Persen kumulatif, FM, grafik, dan gradasi gabungan | Sebagian; seri kasar tidak lengkap dan sumbu grafik bukan logaritmik |
| Los Angeles | Rumus keausan dasar | Sebagian; fraksi, bola baja, putaran, batas master, dan statistik belum ada |
| Semen | Karakteristik semen disimpan | Belum ada observasi/rincian BJ dan berat volume semen |
| Master standar | Katalog 17 standar BSN | Belum menjadi tabel nilai perencanaan |
| Mix Design 2012 | FCR, FAS, volume absolut, koreksi kelembapan, trial ringkas | Sebagian |
| Trial/slump | Workflow generik dengan input dasar | Belum memenuhi form dan perhitungan rinci |
| Kuat tekan | Baris dinamis dan kuat tekan silinder dasar | Belum ada kubus, satuan lengkap, faktor umur, dan statistik penuh |
| Kelayakan akhir | Evaluasi sederhana kuat tekan + slump | Belum komprehensif |
| Kesimpulan | Teks laporan sebagian otomatis/statis | Belum berupa data editable sebelum pengesahan |
| Dokumentasi | Multi-upload, judul, tanggal, kategori, urutan, hapus | Belum ada crop, rotasi, kompresi terkontrol, dan preview edit |
| Laporan | Sampul, pendahuluan, hasil, lampiran, QR, print HTML, unduh Dompdf | Sebagian besar struktur dasar ada |
| Revisi/pengesahan | Lock, hash, approval history, QR, revisi dokumen | Fondasi baik, snapshot belum memadai |

## 5. Temuan teknis prioritas

### Kritis

1. **Integritas dokumen yang disahkan belum mencakup seluruh isi laporan.** Hash dokumen dibentuk dari sebagian atribut proyek dan ringkasan record. Input lengkap, seluruh hasil, foto, pengaturan laporan, identitas lembaga, dan konten kesimpulan tidak semuanya masuk hash. Perubahan pada data yang tidak dicakup dapat menghasilkan dokumen visual berbeda tanpa membuat hash verifikasi gagal.

2. **Otorisasi persetujuan belum memeriksa kewenangan pejabat.** Route perubahan status laporan hanya membutuhkan staf dengan akses edit. Field `approval_authority` tersedia pada user, tetapi tidak dipakai untuk memastikan pengguna berhak bertindak sebagai pemeriksa/mengetahui/menyetujui.

3. **Route Mix Design lama tidak memakai middleware `edit.access`.** `POST /mix-design` dapat melewati pemeriksaan akun read-only dan lock proyek yang sudah disahkan. Jalur Mix Design 2012 sudah memakai middleware tersebut.

4. **Master perencanaan masih tertanam di source.** Tabel hubungan kuat tekan-FAS, rasio volume agregat kasar, ukuran agregat, serta batas gradasi gabungan berada langsung di `MixDesign2012Calculator` dan `MixDesign2012Controller`. Pembaruan standar akan mengubah perilaku tanpa revision snapshot dan tidak memenuhi kewajiban sumber nilai transparan.

### Tinggi

5. **Rumus domain masih berada di controller.** `WorkflowController` menghitung gabungan agregat, koreksi kadar air, trial mix, beton segar, benda uji, kuat tekan, dan evaluasi. Ini melanggar pemisahan service dan menyulitkan unit test/audit formula.

6. **Koreksi kelembapan di Mix Design belum sepenuhnya transparan.** Arah kontribusi air sudah benar pada service mandiri, tetapi `MixDesign2012Calculator` memakai `max(0, air rencana - air bebas)`. Clamp tersebut menyembunyikan kondisi agregat menyumbang air lebih besar dari air rencana; seharusnya menghasilkan status/error audit, bukan koreksi diam-diam.

7. **Metode Mix Design menggabungkan basis massa dan volume secara tidak seragam.** Mode biasa menghitung pasir dari sisa volume absolut, sedangkan mode gradasi gabungan membagi sisa massa beton segar berdasarkan persentase lalu menghitung volume. Kedua jalur perlu diberi nama metode, sumber tabel, pemeriksaan jumlah volume, dan faktor koreksi eksplisit.

8. **Observasi pengujian hanya mensyaratkan minimal satu.** Spesifikasi meminta minimal dua untuk pengujian dinamis dan statistik minimum/maksimum/selisih/status validasi. Calculator saat ini hanya menghasilkan rata-rata dan daftar data tidak lengkap.

9. **Analisis saringan belum memenuhi kebutuhan teknis.** Seri saringan agregat kasar hanya 75; 37,5; 19; 9,5; 4,75; pan. Neraca massa tidak mempunyai toleransi master/status, zona tidak dinilai otomatis secara lengkap, dan grafik Chart.js memakai sumbu kategori biasa alih-alih semi-logaritmik.

10. **Kuat tekan belum siap untuk evaluasi laboratorium.** Service hanya menerima N/kN dan luas yang sudah disediakan. Workflow utama hanya silinder, diameter, beban kN, dan target. Belum ada tanggal/umur otomatis, kubus, kgf/ton, faktor umur master, MPa-kg/cm2-K, statistik sampel, karakteristik, homogenitas, dan catatan kecukupan sampel.

11. **Data hasil lama belum memiliki calculation snapshot yang lengkap.** `result_data` menyimpan hasil pada workflow, tetapi tidak menyimpan versi standar, tabel sumber, formula version, raw result, rounding policy, override, dan alasan. Perubahan master di masa depan tidak dapat direkonstruksi secara forensik.

### Menengah

12. Data umum proyek belum memuat sebagian besar nomor/tanggal laporan, data perusahaan, petugas, logo, tanda tangan, dan opsi mengambil profil lembaga global.

13. Status proyek belum mengikuti sembilan status JMD dan belum dihitung dari kelengkapan modul.

14. Modul kadar lumpur meminta massa bersih sebelum/sesudah, bukan tiga input cawan yang diminta; batas 5%/1% dan status belum berasal dari master.

15. Modul BJ agregat halus tidak menampilkan berat piknometer kosong sebagai input/notasi terpisah. Rumus dasarnya dapat tetap valid, tetapi worksheet belum sama dengan format laporan referensi.

16. Berat isi agregat belum memisahkan observasi lepas dan padat dalam satu pengujian serta belum menyimpan nilai terpilih/manual beserta alasan.

17. Los Angeles hanya menyimpan massa awal dan massa tertahan; metadata gradasi, fraksi, bola baja, putaran, dan batas kelayakan belum tersedia.

18. Katalog standar bersifat read-only dan hanya berisi header. Belum ada CRUD nilai tabel, status versi, riwayat revisi, atau override manual.

19. Tidak ada autosave draft umum. JavaScript dinamis menyimpan saat submit; penghapusan observasi tersimpan memakai fetch terpisah.

20. JavaScript utama banyak ditulis inline di Blade, khususnya worksheet agregat dan form Mix Design. Ini memperbesar risiko regresi dan menyulitkan unit test frontend.

21. Beberapa view laporan memformat/memilih data langsung di Blade. Formula utama tidak dominan di Blade, tetapi report view berukuran besar dan perlu dipisah menjadi view model/section agar generator laporan dapat diuji.

22. Aplikasi memuat Bootstrap/Chart.js dari CDN pada beberapa halaman. Laporan/halaman perlu diuji untuk penggunaan offline dan kebijakan CSP.

23. Timezone aplikasi terdeteksi `UTC`, sementara UI menuliskan `WITA`. Timestamp approval dan laporan berisiko tampil delapan jam berbeda bila tidak dikonversi eksplisit.

24. Terdapat string mojibake seperti `mÂ³`, `mmÂ²`, dan simbol perkalian yang rusak di source/output terminal. Normalisasi UTF-8 dan test rendering PDF diperlukan.

## 6. Audit proses simpan dan isolasi proyek

Hal yang sudah benar:

- Proses penyimpanan worksheet agregat memakai transaction.
- Update observasi mencari ID dalam paket dan proyek yang sama.
- Penghapusan memastikan relasi project-run-observation dan menolak penghapusan observasi terakhir.
- Data material/workflow dapat dimuat kembali berdasarkan ID.
- Test meliputi edit/tambah/hapus observasi, larangan lintas proyek, dan rollback transaksi.

Risiko lanjutan:

- Banyak endpoint memakai validasi inline, belum Form Request/Policy. Konsistensi otorisasi mudah terlewat seperti pada Mix Design lama.
- Beberapa nomor record dibentuk dari timestamp/count; strategi ini dapat collision pada request paralel dan sebaiknya memakai sequence/UUID dengan unique retry.
- Relasi `material_source_id` divalidasi `exists`, tetapi semua endpoint harus konsisten memverifikasi bahwa sumber merupakan milik proyek atau sumber global.
- Cascade delete masih digunakan pada beberapa data penting. Karena proyek memakai soft delete, kondisi normal aman, tetapi permanent delete akan menghapus data teknis dan approval; perlu arsip/snapshot eksternal sebelum destructive purge.
- Record yang disahkan dilindungi middleware berdasarkan `project_id`, tetapi perlindungan harus dipindahkan ke Policy/domain guard agar juga berlaku pada command, job, dan future API.

## 7. Audit laporan dan PDF

Laporan saat ini sudah mencakup sampul, kata pengantar, daftar isi statis, BAB I-III, lembar hasil Mix Design, pemakaian bahan, volume absolut, koreksi kelembapan, perbandingan campuran, kuat tekan, lampiran material/agregat/saringan/Los Angeles, dokumentasi, dasar teori, tanda tangan, QR, header, footer, dan format A4.

Kesenjangan:

- Daftar isi dan nomor halaman masih statis; panjang laporan dinamis dapat membuat nomor tidak sesuai.
- Belum ada watermark on/off, ekspor bagian tertentu, lembar hasil saja, dan lampiran tertentu.
- Preview adalah HTML print; belum ada satu generator/report DTO yang dipakai identik oleh preview dan PDF.
- Pengulangan header tabel pada page break dan larangan row terpotong belum konsisten untuk semua tabel.
- Grafik saringan dibuat sebagai SVG/report logic di view dan bukan aset/snapshot per revisi.
- Kesimpulan belum berasal dari validator kelayakan seluruh modul.
- File asli bernama `LAPORAN JMD GABUNGAN.pdf` tidak ditemukan pada tiga lokasi yang diberikan/diperiksa. Repository memiliki gambar hasil render 29 halaman di `tmp/pdfs/jmd-reference`, sehingga perbandingan visual awal masih mungkin, tetapi sumber PDF asli tetap dibutuhkan untuk audit teks, metadata, dan angka yang dapat dipercaya.

## 8. Cakupan test saat ini

Test yang sudah ada memverifikasi:

- Rumus kadar air, BJ agregat kasar, Los Angeles, massa saringan, interpolasi, volume absolut, koreksi kelembapan, kuat tekan kN, Mix Design 2012, dan optimasi gradasi gabungan.
- Persistensi observasi, material examination, workflow laboratorium, proyek, pilihan laporan, permohonan, dan arsip.

Celah test:

- Belum ada fixture referensi yang mereproduksi 2,08%; 0,13%; 34,65%; FAS 0,55; komposisi contoh; dan kuat tekan rata-rata 18,85 MPa sebagai satu alur end-to-end.
- Belum ada unit test seluruh rumus BJ agregat halus, berat volume, saringan lengkap, statistik kuat tekan, faktor umur, konversi beban/mutu, trial berbagai bentuk, field batching, status kelayakan, dan conclusion rules.
- Belum ada feature test approval authority, immutable approved project untuk semua endpoint, hash seluruh isi laporan, duplicate/revision snapshot, partial PDF, upload/transform foto, dan status progres.
- Belum ada browser test untuk error JavaScript, grafik, autosave, reload, dan print layout.

## 9. Baseline verifikasi

| Pemeriksaan | Hasil |
| --- | --- |
| `git status --short --branch` sebelum audit | Bersih, `main...origin/main` |
| `php artisan migrate:status` | Semua 18 migration `Ran`; tidak ada migration tertunda |
| `php artisan test` | Lulus: 36 test, 211 assertion |
| `pnpm run build` dengan Node bundel workspace | Lulus, Vite 8.2.0 |
| Peringatan build | Paket opsional `fontaine` belum dipasang; build tetap sukses |
| Perubahan data produksi | Tidak ada; audit hanya membaca database |

Catatan: `php artisan db:show --counts --views` menampilkan seluruh schema yang dapat dilihat user MySQL, bukan hanya schema aplikasi. Untuk aplikasi ini, schema yang relevan adalah `mix_desain_beton` dengan 27 tabel dan data lama aktif, termasuk 4 proyek, 39 paket agregat, 39 observasi, 190 audit log, dan 7 approval pada saat audit.

## 10. Rencana implementasi aman

Urutan berikut menjaga fitur lama tetap hidup:

1. Tambahkan tabel/kolom JMD secara aditif, status baru, revision snapshot, standard table versioning, dan foreign key tanpa menghapus JSON/kolom lama.
2. Buat model, enum, DTO, Form Request, Policy, service formula, calculation result contract, dan compatibility adapter terhadap tabel lama.
3. Pindahkan formula dari controller/JavaScript ke service yang diuji; frontend hanya preview dan server tetap sumber hasil resmi.
4. Normalisasi modul pengujian satu per satu dengan backfill dari record lama dan dual-read selama transisi.
5. Implementasikan master standar berversi; setiap perhitungan menyimpan snapshot nilai dan sumber yang digunakan.
6. Implementasikan wizard Mix Design, koreksi SSD, trial, slump, field batch, dan kuat tekan statistik.
7. Tambahkan validator kelengkapan/kelayakan dan conclusion rules yang dapat diedit.
8. Refactor laporan ke report DTO + section renderer, lalu tambahkan partial export, watermark, nomor halaman dinamis, dan snapshot grafik.
9. Tambahkan fixture laporan referensi dan end-to-end test dengan toleransi tanpa hardcode hasil di service.
10. Migrasikan bertahap pada salinan database, backup sebelum deployment, lalu smoke test data lama dan proyek baru.

## 11. Keputusan Tahap 1

Tahap 1 selesai sebagai audit dan baseline. Belum ada alasan aman untuk langsung menulis ulang aplikasi. Tahap 2 sebaiknya dimulai dengan migration aditif untuk fondasi JMD, standard versioning, revision snapshot, dan policy/lock yang konsisten. Migration tersebut harus diuji pada SQLite dan salinan MySQL sebelum dijalankan pada database lokal yang berisi data lama.
