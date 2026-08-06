# Contoh Laporan JMD Lengkap

## Identitas

- Nomor proyek: `DEMO-JMD-LENGKAP-001`
- Nomor JMD: `JMD-DEMO-001`
- Nomor laporan: `LHU/JMD/DEMO/001/2026`
- Proyek: Pembangunan Gedung Laboratorium Beton — Contoh Lengkap
- Mutu: f’c 25 MPa
- Status: disetujui, dikunci, dan dapat diverifikasi publik

## Isi Data

- Empat sumber material: semen, air, pasir, dan kerikil.
- Pemeriksaan semen dan air lengkap.
- Lima paket uji pasir dengan dua observasi: kadar air, kadar lumpur, berat jenis/penyerapan, berat isi, dan saringan.
- Enam paket uji kerikil dengan dua observasi: lima pengujian yang sama ditambah Los Angeles.
- Mix design SNI 7656:2012.
- Gabungan agregat, koreksi kadar air, trial mix, beton segar, benda uji, kuat tekan tiga benda uji, dan evaluasi akhir.
- Dokumentasi ilustratif pengujian agregat.
- Persetujuan elektronik demo, QR verifikasi, dan hash dokumen.

## Hasil Utama Contoh

- Semen: 357,033 kg/m³.
- Pasir lapangan: 784,089 kg/m³.
- Kerikil lapangan: 1.040,290 kg/m³.
- Air ditambahkan: 161,722 kg/m³.
- Rasio air-semen: 0,518.
- Kuat tekan karakteristik: 27,928 MPa.
- Status evaluasi: memenuhi.

## Artifact

- PDF final: `output/pdf/Contoh-Laporan-JMD-Lengkap.pdf`
- Asset dokumentasi: `public/demo-assets/pengujian-agregat-demo.png`
- Seeder reproduksi: `database/seeders/CompleteJmdReportDemoSeeder.php`
- Foto dibuat dengan image generation bawaan sebagai foto dokumenter laboratorium pengujian saringan, tanpa logo, teks, atau watermark.

## QA

- PDF A4: 35 halaman, 3,8 MB.
- Enam halaman representatif diperiksa visual: sampul, komposisi mix design, kesimpulan/pengesahan, lampiran dua observasi, grafik gradasi, dan dokumentasi.
- Ekstraksi seluruh 35 halaman: tidak ditemukan mojibake atau replacement character.
- Teks `Observasi 2` ditemukan pada 16 bagian PDF.
- Endpoint verifikasi, laporan publik, dan unduhan merespons HTTP 200.
- Hash verifikasi publik valid.
