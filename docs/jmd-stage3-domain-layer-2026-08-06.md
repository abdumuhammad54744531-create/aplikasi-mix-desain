# Tahap 3 - Model, DTO, Service, Validation, dan Policy JMD

Tanggal: 6 Agustus 2026

## Tujuan

Tahap 3 membangun lapisan domain di atas skema Tahap 2 tanpa memindahkan route/controller lama secara langsung. Pemisahan ini menjaga aplikasi aktif tetap stabil sambil menyediakan satu sumber resmi untuk formula, snapshot hasil, validasi request, relasi database, dan aturan penguncian proyek.

## Struktur baru

| Lapisan | Lokasi | Jumlah |
| --- | --- | ---: |
| Enum | `app/Enums` | 3 |
| DTO JMD | `app/Data/Jmd` | 11 |
| Model JMD | `app/Models/Jmd` | 35 |
| Model standar | `app/Models/Standard*.php` | 3 |
| Service JMD | `app/Services/Jmd` | 17 |
| Form Request | `app/Http/Requests/Jmd` | 8 |
| Policy | `app/Policies/ProjectPolicy.php` | 1 |

## Enum

- `JmdStatus`: draft sampai archived dengan label Indonesia.
- `AggregateType`: agregat halus dan kasar.
- `SpecimenType`: silinder, kubus, balok, dan bentuk khusus.

`Project.jmd_status` sekarang dicast langsung ke `JmdStatus`, sedangkan status lama tetap dipertahankan untuk kompatibilitas.

## Kontrak hasil perhitungan

Semua service utama mengembalikan `CalculationResult` dengan struktur konsisten:

- `raw`: hasil tanpa pembulatan antara.
- `rounded`: salinan khusus tampilan/laporan.
- `units`: satuan setiap kelompok hasil.
- `formulae`: formula yang diterapkan.
- `sources`: sumber standar atau tabel master.
- `messages`: peringatan dan validasi teknis.
- `log`: substitusi atau jejak perhitungan.
- `valid`: status validitas hasil.

Nilai master tidak ditanam ke service. Batas FAS, kadar lumpur, abrasi, faktor umur, faktor konversi, toleransi saringan, dan sumber standar harus diberikan oleh caller dari snapshot tabel standar.

## DTO

DTO typed/read-only tersedia untuk:

- Observasi kadar air dan kadar lumpur.
- Berat jenis agregat halus dan kasar.
- Berat volume.
- Abrasi.
- Input Mix Design.
- Koreksi kadar air/penyerapan berbasis SSD.
- Trial mix.
- Benda uji kuat tekan.

DTO mencegah controller meneruskan array ambigu ke formula dan menjaga notasi input konsisten.

## Model dan relasi

Model mencakup seluruh kelompok berikut:

- Master standar, header tabel, dan nilai tabel.
- Material dan kriteria desain per proyek.
- Header/item kadar air, kadar lumpur, berat jenis, berat volume, semen, saringan, dan abrasi.
- Perhitungan Mix Design, hasil material, koreksi kelembapan, trial, slump, kuat tekan, dan batching lapangan.
- Override, revisi, pemeriksaan kelayakan, kesimpulan, foto, dan catatan audit.

Model header penting memakai soft delete, cast JSON snapshot, relasi actor/approval, serta relasi ke `Project`. Model `Project` dan `ReportApproval` telah ditambah relasi JMD tanpa menghapus relasi lama.

## Service formula

### Material

- `MoistureContentService`: W4, W5, kadar air, rata-rata, minimum, maksimum, rentang, standar deviasi sampel, dan peringatan nilai negatif.
- `SiltContentService`: massa bersih dari tiga input cawan, kadar lumpur, statistik, batas master, dan status.
- `FineAggregateSpecificGravityService`: bulk dry, bulk SSD, apparent, dan absorption.
- `CoarseAggregateSpecificGravityService`: bulk dry, bulk SSD, apparent, absorption, dan validasi penyebut.
- `BulkDensityService`: kondisi lepas/padat dan konversi kg/m3, g/cm3, serta ton/m3.
- `CementSpecificGravityService`: selisih massa yang benar, volume, densitas, dan berat jenis.
- `SieveAnalysisService`: neraca massa berdasarkan massa awal, persen tertahan/kumulatif/lolos, FM, titik keluar batas, maximum size, dan nominal maximum size.
- `AbrasionService`: abrasi per observasi, statistik, batas master, dan status.

### Desain dan beton

- `ConcreteStrengthService`: margin, kuat tekan rata-rata target, dan konversi K/kubus/silinder dengan faktor supplied.
- `MixDesignService`: FAS paling ketat, override beralasan, semen minimum/maksimum, volume absolut, massa agregat, rasio berat, total massa, dan pemeriksaan volume.
- `MoistureCorrectionService`: basis SSD, massa kering/lapangan, air bebas tiap agregat, air mixer, dan FAS efektif. Air mixer negatif tidak dijepit diam-diam menjadi nol.
- `TrialMixService`: volume silinder/kubus/balok/khusus, waste factor, volume slump/manual, kebutuhan teoritis, hasil pembulatan timbang, dan selisih pembulatan.
- `FieldBatchService`: konversi volume dan per zak berdasarkan berat/berat volume disertai peringatan ketelitian takaran volume.
- `CompressiveStrengthService`: N/kN/kgf/ton ke Newton, silinder/kubus, MPa, kg/cm2, estimasi 28 hari, statistik, persentase memenuhi, dan kuat tekan karakteristik hanya bila jumlah sampel memenuhi batas supplied.
- `JmdValidationService`: status akhir hijau/kuning/merah dari seluruh kriteria, bukan hanya kuat tekan.
- `JmdReportService`: canonical snapshot dan SHA-256 yang sensitif terhadap seluruh konten namun independen dari urutan associative key.

## Form Request

Request tersedia untuk data umum proyek, kadar air, kadar lumpur, Mix Design, trial mix, kuat tekan, dan override manual. Validasi penting:

- Minimal dua observasi untuk pengujian material.
- Pembanding massa dan penyebut yang valid.
- FAS 0-1 dan override wajib mempunyai alasan.
- Sumber standar wajib tersedia pada Mix Design.
- Geometri benda uji sesuai jenis.
- Satuan beban dibatasi ke N, kN, kgf, dan ton.
- Record material/kriteria/perhitungan/trial harus benar-benar milik `project_id` yang dipilih.

Base request menolak pemohon, akun read-only, dan semua perubahan langsung pada proyek yang sudah dikunci.

## Policy

`ProjectPolicy` didaftarkan secara eksplisit pada `AppServiceProvider` dan menyediakan:

- `view`
- `updateJmd`
- `createRevision`
- `approve`

Approval teknisi wajib cocok dengan `approval_authority`. Administrator dapat memberi approval, tetapi lock proyek tetap tidak boleh dilewati untuk update langsung; perubahan proyek terkunci harus melalui revisi.

Policy dan Form Request baru belum dipasang ke controller lama. Hal ini disengaja agar Tahap 3 tidak mengubah perilaku route produksi sebelum controller baru memakai transaction dan service baru secara utuh.

## Pengujian baru

- `JmdFormulaServicesTest`: raw precision, contoh 2,08% lumpur, abrasi 34,65%, berat jenis, berat volume, saringan, Mix Design, koreksi SSD, trial, statistik kuat tekan, validasi akhir, dan canonical report hash.
- `JmdDomainLayerTest`: cast enum, relasi proyek-material-observasi, lock, revision permission, dan approval authority.
- `JmdFormRequestValidationTest`: minimal observasi, geometri, satuan beban, alasan override, sumber standar, serta larangan relasi lintas proyek.

Hasil verifikasi akhir seluruh aplikasi: 48 test dan 303 assertion lulus. Composer optimized autoload berhasil dibuat, inspeksi Eloquent terhadap `MixDesignCalculation` berhasil membaca seluruh atribut/cast/relasi, dan build Vite berhasil tanpa error.

## Batas Tahap 3

- Controller/Blade lama masih menggunakan model dan service lama.
- Belum ada backfill record pengujian lama ke tabel normalisasi baru.
- Belum ada UI master standar atau wizard baru.
- Belum ada generator PDF baru yang memakai `JmdReportService`.

Integrasi pertama dilakukan pada Tahap 4 dengan modul pengujian material. Setiap modul akan memakai Form Request, DTO, service, transaction, snapshot hasil, dan audit log yang dibuat pada tahap ini.
