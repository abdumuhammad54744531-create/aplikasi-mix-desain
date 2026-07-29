<?php

namespace Database\Seeders;

use App\Models\ReferenceHeader;
use Illuminate\Database\Seeder;

class StandardReferenceSeeder extends Seeder
{
    public function run(): void
    {
        $standards = [
            ['Mix Design', 'SNI 7656:2012', 'Tata cara pemilihan campuran untuk beton normal, beton berat dan beton massa', '2012', 'https://pesta.bsn.go.id/produk/detail/9094-sni76562012'],
            ['Air', 'SNI 7974:2018', 'Spesifikasi air pencampur untuk produksi beton semen hidraulis (ASTM C1602/C1602M-12, IDT)', '2018', 'https://pesta.bsn.go.id/produk/index/354'],
            ['Agregat', 'SNI 1969:2016', 'Metode uji berat jenis dan penyerapan air agregat kasar', '2016', 'https://pesta.bsn.go.id/produk/detail/10958-sni19692016'],
            ['Agregat', 'SNI 1970:2016', 'Metode uji berat jenis dan penyerapan air agregat halus', '2016', 'https://pesta.bsn.go.id/produk/detail/10957-sni19702016'],
            ['Agregat', 'SNI ASTM C136:2012', 'Metode uji untuk analisis saringan agregat halus dan agregat kasar', '2012', 'https://pesta.bsn.go.id/produk/detail/9112-sniastmc1362012'],
            ['Agregat', 'SNI 2816:2014', 'Metode uji bahan organik dalam agregat halus untuk beton (ASTM C40/C40M-11, IDT)', '2014', 'https://pesta.bsn.go.id/produk/by_ics/644'],
            ['Beton Segar', 'SNI 1972:2022', 'Metode uji slump beton semen hidraulis (ASTM C143/C143M-20, MOD)', '2022', 'https://pesta.bsn.go.id/produk/by_ics/642'],
            ['Beton Segar', 'SNI 1973:2016', 'Metode uji densitas, volume produksi campuran dan kadar udara (gravimetrik) beton', '2016', 'https://pesta.bsn.go.id/produk/by_ics/642'],
            ['Beton Segar', 'SNI 2458:2018', 'Tata cara pengambilan sampel campuran beton segar (ASTM C172/C172M-17, IDT)', '2018', 'https://pesta.bsn.go.id/produk/by_ics/642'],
            ['Benda Uji', 'SNI 2493:2011', 'Tata cara pembuatan dan perawatan benda uji beton di laboratorium', '2011', 'https://pesta.bsn.go.id/produk/by_ics/637'],
            ['Benda Uji', 'SNI 4810:2018', 'Tata cara pembuatan dan perawatan spesimen uji beton di lapangan (ASTM C31/C31M-17)', '2018', 'https://pesta.bsn.go.id/produk/by_ics/637'],
            ['Kuat Tekan', 'SNI 1974:2023', 'Metode uji untuk kekuatan tekan spesimen beton silinder (ASTM C39-20, IDT)', '2023', 'https://pesta.bsn.go.id/produk/index?key=1974'],
            ['Evaluasi', 'SNI 03-6815-2002', 'Tata cara mengevaluasi hasil uji kekuatan beton', '2002', 'https://pesta.bsn.go.id/produk/by_ics/642'],
            ['Semen', 'SNI 2049-2:2021', 'Semen portland – Bagian 2: Metode pengambilan contoh dan jumlah pengujian semen hidraulis', '2021', 'https://pesta.bsn.go.id/produk/index/10?key=21'],
            ['Semen', 'SNI 2049-5:2021', 'Semen portland – Bagian 5: Metode uji kehalusan semen hidraulis dengan alat permeabilitas udara', '2021', 'https://pesta.bsn.go.id/produk/index/10?key=21'],
            ['Semen', 'SNI 2049-7:2022', 'Semen portland – Bagian 7: Metode uji kuat tekan mortar semen hidraulis', '2022', 'https://pesta.bsn.go.id/produk/index/10?key=21'],
            ['Semen', 'SNI 2049-8:2021', 'Semen portland – Bagian 8: Metode uji waktu pengikatan semen hidraulis dengan jarum Vicat', '2021', 'https://pesta.bsn.go.id/produk/index/10?key=21'],
        ];

        foreach ($standards as [$category, $number, $name, $year, $url]) {
            ReferenceHeader::updateOrCreate(
                ['standard_number' => $number],
                ['category' => $category, 'name' => $name, 'standard_year' => $year,
                    'status' => 'Berlaku', 'is_active' => true, 'source_document' => 'Katalog resmi BSN',
                    'catalog_url' => $url]
            );
        }
    }
}
