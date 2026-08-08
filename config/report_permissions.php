<?php

return [
    'modules' => [
        'dashboard' => ['label' => 'Dashboard', 'actions' => ['view']],
        'projects' => ['label' => 'Data Proyek', 'actions' => ['view', 'create', 'edit', 'delete', 'print']],
        'materials' => ['label' => 'Sumber Material', 'actions' => ['view', 'create', 'edit', 'delete']],
        'pasir' => ['label' => 'Pemeriksaan Pasir', 'actions' => ['view', 'create', 'edit', 'delete', 'print']],
        'kerikil' => ['label' => 'Pemeriksaan Kerikil', 'actions' => ['view', 'create', 'edit', 'delete', 'print']],
        'mix-design' => ['label' => 'Desain Campuran', 'actions' => ['view', 'create', 'edit', 'print']],
        'dokumentasi' => ['label' => 'Dokumentasi', 'actions' => ['view', 'create', 'delete']],
        'kuat-tekan' => ['label' => 'Kuat Tekan Beton', 'actions' => ['view', 'create', 'edit', 'print']],
        'laporan' => ['label' => 'Laporan', 'actions' => ['view', 'edit', 'print']],
        'report-settings' => ['label' => 'Pengaturan Laporan', 'actions' => ['view', 'edit']],
        'users' => ['label' => 'Manajemen Pengguna', 'actions' => ['view', 'create', 'edit', 'delete']],
        'standards' => ['label' => 'Master Standar', 'actions' => ['view', 'create', 'edit', 'delete']],
        'archive' => ['label' => 'Arsip', 'actions' => ['view', 'edit', 'delete']],
        'audit' => ['label' => 'Riwayat Audit', 'actions' => ['view']],
        'requests' => ['label' => 'Permohonan Laboratorium', 'actions' => ['view', 'create', 'edit']],
    ],
    'action_labels' => [
        'view' => 'Lihat', 'create' => 'Tambah', 'edit' => 'Edit',
        'delete' => 'Hapus', 'print' => 'Cetak/Export',
    ],
];
