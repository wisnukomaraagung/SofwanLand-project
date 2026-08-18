# Implementation Plan

## Overview

Implementasi fitur pemisahan keuangan masuk/keluar di menu Keuangan, mengikuti pola yang sudah ada di menu Barang. Perubahan mencakup tiga lapisan: Model (query baru), Controller (method aksi baru + update index), dan View (tiga tab baru).

## Tasks

- [x] 1. Update KeuanganModel — tambah method query masuk/keluar/laporan
  - Tambah method `getMasuk(?int $idProyek): array` yang query laporan_keuangan WHERE tipe='pemasukan'
  - Tambah method `getKeluar(?int $idProyek): array` yang query laporan_keuangan WHERE tipe='pengeluaran'
  - Tambah method `getBarangMasukUntukLaporan(?int $idProyek): array` yang JOIN barang_masuk + barang, filter via barang.id_proyek
  - Tambah method `getBarangKeluarUntukLaporan(?int $idProyek): array` yang JOIN barang_keluar + barang, filter via barang_keluar.id_proyek
  - Tambah method `getSummaryGabungan(?int $idProyek): array` yang hitung total pemasukan, total pengeluaran gabungan (pengeluaran langsung + nilai barang masuk), dan saldo
  - _Requirements: Req 3.1, 5.1, 6.1-6.5, 7.1, 8.1, 9.1_

- [x] 2. Update KeuanganController — tambah method aksi dan update index()
  - Depends on: 1
  - Tambah method `storeMasuk()` — requireManagerPermission, validasi jumlah > 0, simpan tipe='pemasukan', redirect ke tab=masuk
  - Tambah method `storeKeluar()` — requireManagerPermission, validasi jumlah > 0, simpan tipe='pengeluaran', redirect ke tab=keluar
  - Tambah method `deleteMasuk(int $id)` — requireManagerPermission, hapus transaksi, redirect ke tab=masuk
  - Tambah method `deleteKeluar(int $id)` — requireManagerPermission, hapus transaksi, redirect ke tab=keluar
  - Tambah method `exportMasukExcel()` — export hanya pemasukan Proyek_Aktif ke .xls
  - Tambah method `exportKeluarExcel()` — export hanya pengeluaran Proyek_Aktif ke .xls
  - Tambah method `exportLaporanExcel()` — export gabungan tiga bagian: summary + barang masuk + transaksi langsung
  - Update method `index()` — sertakan BarangModel, pass: masukList, keluarList, barangMasukList, barangKeluarList, summary
  - _Requirements: Req 2.1-2.5, 4.1-4.5, 11.1-11.3, 12.1-12.3_

- [x] 3. Update view views/keuangan/index.php — implementasi tiga tab
  - Depends on: 2
  - Tambah pill-nav dengan tiga tab: Keuangan Masuk (tab=masuk), Keuangan Keluar (tab=keluar), Laporan Keuangan (tab=laporan); default tab=masuk
  - Implementasi Tab Masuk: summary card pemasukan + split-layout (form input kiri, tabel riwayat masuk kanan) + tombol export Excel masuk
  - Implementasi Tab Keluar: summary card pengeluaran + split-layout (form input kiri, tabel riwayat keluar kanan) + tombol export Excel keluar
  - Implementasi Tab Laporan: 3 summary cards (pemasukan/pengeluaran/saldo+badge) + tabel barang masuk + subtotal + tabel barang keluar + tabel transaksi langsung + tombol export Excel laporan
  - Pastikan roleCanManage('keuangan') mengontrol visibilitas form input dan tombol hapus di semua tab
  - _Requirements: Req 1.1-1.4, 2.1-2.5, 3.1-3.5, 4.1-4.5, 5.1-5.5, 6.1-6.5, 7.1-7.3, 8.1-8.2, 9.1-9.2, 10.1-10.4, 11.3_

## Task Dependency Graph

```json
{
  "waves": [
    { "wave": 1, "tasks": ["1"] },
    { "wave": 2, "tasks": ["2"] },
    { "wave": 3, "tasks": ["3"] }
  ]
}
```

## Notes

- Tidak ada perubahan skema database. Tabel `laporan_keuangan` tetap digunakan apa adanya.
- Kolom `tipe` yang sudah ada ('pemasukan'/'pengeluaran') menjadi pembeda antara tab masuk dan tab keluar.
- Method `store()`, `delete()`, `exportExcel()` lama di KeuanganController tetap dipertahankan untuk backward compatibility.
- Referensi pola: BarangController.php (storeMasuk/storeKeluar/exportMasukExcel/exportKeluarExcel) dan views/barang/index.php (pill-nav, split-layout).
