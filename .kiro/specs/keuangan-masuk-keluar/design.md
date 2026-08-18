# Desain Teknis — Keuangan Masuk/Keluar/Laporan

## 1. Arsitektur

Perubahan ini bersifat **murni pada layer Controller dan View**. Tidak ada perubahan skema database sama sekali — tabel `laporan_keuangan` tetap digunakan apa adanya dengan kolom `tipe` ('pemasukan'/'pengeluaran') sebagai pembeda tab.

Pola implementasi mengikuti `BarangController` yang sudah berjalan:
- Controller menangani routing tab melalui parameter `?tab=masuk|keluar|laporan`
- View menggunakan pill navigation yang sama dengan barang
- Model menambah method query spesifik per kebutuhan, bukan mengubah method yang ada

**Komponen yang diubah:**
- `app/controllers/KeuanganController.php` — tambah method baru, update `index()`
- `app/models/KeuanganModel.php` — tambah method query baru
- `app/views/keuangan/index.php` — implementasi ulang dengan tiga tab

**Komponen yang TIDAK diubah:**
- Skema database (tidak ada ALTER TABLE, CREATE TABLE, dll.)
- `app/models/BarangModel.php` — hanya dipakai (di-instantiate di controller)
- Routing `index.php` — tidak perlu penambahan route baru jika aksi ditangani via `?action=`

---

## 2. Perubahan KeuanganController

### 2.1 Update `index()`

Method `index()` diperbarui untuk meng-instantiate `BarangModel` dan melempar seluruh data yang dibutuhkan ketiga tab ke view.

```php
public function index() {
    $idProyek = $_SESSION['selected_project_id'] ?? null;
    $barangModel = new BarangModel();

    $masukList       = $this->model->getMasuk($idProyek);
    $keluarList      = $this->model->getKeluar($idProyek);
    $barangMasukList = $this->model->getBarangMasukUntukLaporan($idProyek);
    $barangKeluarList= $this->model->getBarangKeluarUntukLaporan($idProyek);
    $summary         = $this->model->getSummaryGabungan($idProyek);

    $pageTitle    = 'Keuangan';
    $pageSubtitle = 'Manajemen pemasukan, pengeluaran, dan laporan keuangan proyek';
    require BASE_PATH . '/app/views/keuangan/index.php';
}
```

### 2.2 Method `storeMasuk()`

Menyimpan transaksi ke `laporan_keuangan` dengan `tipe='pemasukan'` secara otomatis — tidak ada dropdown tipe di form.

```php
public function storeMasuk() {
    requireManagerPermission('keuangan');
    $idProyek = $_SESSION['selected_project_id'] ?? null;

    if (!$idProyek) {
        $_SESSION['flash'] = ['type'=>'error','message'=>'Harap pilih proyek terlebih dahulu.'];
        header('Location: ' . BASE_URL . '/public/index.php?page=keuangan&tab=masuk'); exit;
    }

    $jumlah = floatval(str_replace(['.', ','], ['', '.'], $_POST['jumlah'] ?? 0));
    if ($jumlah < 1) {
        $_SESSION['flash'] = ['type'=>'error','message'=>'Jumlah pemasukan wajib diisi dan harus lebih dari 0'];
        header('Location: ' . BASE_URL . '/public/index.php?page=keuangan&tab=masuk'); exit;
    }

    $data = [
        'id_proyek'  => $idProyek,
        'tipe'       => 'pemasukan',
        'jumlah'     => $jumlah,
        'sumber'     => trim($_POST['sumber'] ?? ''),
        'keterangan' => trim($_POST['keterangan'] ?? ''),
        'tanggal'    => $_POST['tanggal'] ?? date('Y-m-d'),
    ];
    $this->model->create($data);
    $_SESSION['flash'] = ['type'=>'success','message'=>'Pemasukan berhasil dicatat.'];
    header('Location: ' . BASE_URL . '/public/index.php?page=keuangan&tab=masuk'); exit;
}
```

### 2.3 Method `storeKeluar()`

Identik dengan `storeMasuk()` namun `tipe='pengeluaran'` dan redirect ke `tab=keluar`.

```php
public function storeKeluar() {
    requireManagerPermission('keuangan');
    $idProyek = $_SESSION['selected_project_id'] ?? null;

    if (!$idProyek) {
        $_SESSION['flash'] = ['type'=>'error','message'=>'Harap pilih proyek terlebih dahulu.'];
        header('Location: ' . BASE_URL . '/public/index.php?page=keuangan&tab=keluar'); exit;
    }

    $jumlah = floatval(str_replace(['.', ','], ['', '.'], $_POST['jumlah'] ?? 0));
    if ($jumlah < 1) {
        $_SESSION['flash'] = ['type'=>'error','message'=>'Jumlah pengeluaran wajib diisi dan harus lebih dari 0'];
        header('Location: ' . BASE_URL . '/public/index.php?page=keuangan&tab=keluar'); exit;
    }

    $data = [
        'id_proyek'  => $idProyek,
        'tipe'       => 'pengeluaran',
        'jumlah'     => $jumlah,
        'sumber'     => trim($_POST['sumber'] ?? ''),
        'keterangan' => trim($_POST['keterangan'] ?? ''),
        'tanggal'    => $_POST['tanggal'] ?? date('Y-m-d'),
    ];
    $this->model->create($data);
    $_SESSION['flash'] = ['type'=>'success','message'=>'Pengeluaran berhasil dicatat.'];
    header('Location: ' . BASE_URL . '/public/index.php?page=keuangan&tab=keluar'); exit;
}
```

### 2.4 Method `deleteMasuk(int $id)`

```php
public function deleteMasuk(int $id) {
    requireManagerPermission('keuangan');
    $this->model->delete($id);
    $_SESSION['flash'] = ['type'=>'success','message'=>'Transaksi pemasukan berhasil dihapus.'];
    header('Location: ' . BASE_URL . '/public/index.php?page=keuangan&tab=masuk'); exit;
}
```

### 2.5 Method `deleteKeluar(int $id)`

```php
public function deleteKeluar(int $id) {
    requireManagerPermission('keuangan');
    $this->model->delete($id);
    $_SESSION['flash'] = ['type'=>'success','message'=>'Transaksi pengeluaran berhasil dihapus.'];
    header('Location: ' . BASE_URL . '/public/index.php?page=keuangan&tab=keluar'); exit;
}
```

### 2.6 Method `exportMasukExcel()`

Export file `.xls` hanya untuk transaksi `tipe='pemasukan'` dari proyek aktif.

- Header: No, Tanggal, Jumlah, Sumber, Keterangan
- Nama file: `Keuangan_Masuk_{YYYYMMDD}.xls`

### 2.7 Method `exportKeluarExcel()`

Export file `.xls` hanya untuk transaksi `tipe='pengeluaran'` dari proyek aktif.

- Header: No, Tanggal, Jumlah, Sumber, Keterangan
- Nama file: `Keuangan_Keluar_{YYYYMMDD}.xls`

### 2.8 Method `exportLaporanExcel()`

Export file `.xls` gabungan dalam satu sheet dengan tiga bagian terpisah:

1. **Bagian Summary** — baris label: Total Pemasukan, Total Pengeluaran, Saldo
2. **Bagian Barang Masuk** — header + baris data + baris subtotal
3. **Bagian Transaksi Langsung** — header + baris data seluruh `laporan_keuangan`

- Nama file: `Laporan_Keuangan_{nama_proyek}_{YYYYMMDD}.xls`
- Jika tidak ada data, tetap output file dengan header kolom + baris "Tidak ada data"

---

## 3. Perubahan KeuanganModel

### 3.1 Method `getMasuk(?int $idProyek): array`

```sql
SELECT *
FROM laporan_keuangan
WHERE tipe = 'pemasukan'
  AND id_proyek = ?    -- jika $idProyek tidak null
ORDER BY tanggal DESC, id DESC
```

### 3.2 Method `getKeluar(?int $idProyek): array`

```sql
SELECT *
FROM laporan_keuangan
WHERE tipe = 'pengeluaran'
  AND id_proyek = ?    -- jika $idProyek tidak null
ORDER BY tanggal DESC, id DESC
```

### 3.3 Method `getBarangMasukUntukLaporan(?int $idProyek): array`

JOIN `barang_masuk` dengan `barang` untuk mendapatkan nama barang, satuan, dan harga_satuan. Filter melalui `barang.id_proyek`.

```sql
SELECT bm.*, b.nama_barang, b.satuan, b.harga_satuan AS harga_satuan_barang,
       (bm.jumlah * bm.harga_satuan) AS total_nilai
FROM barang_masuk bm
JOIN barang b ON b.id = bm.id_barang
WHERE b.id_proyek = ?
ORDER BY bm.tanggal DESC
```

### 3.4 Method `getBarangKeluarUntukLaporan(?int $idProyek): array`

JOIN `barang_keluar` dengan `barang`. Filter melalui `barang_keluar.id_proyek`.

```sql
SELECT bk.*, b.nama_barang, b.satuan, b.harga_satuan,
       (bk.jumlah * b.harga_satuan) AS total_nilai
FROM barang_keluar bk
JOIN barang b ON b.id = bk.id_barang
WHERE bk.id_proyek = ?
ORDER BY bk.tanggal DESC
```

### 3.5 Method `getSummaryGabungan(?int $idProyek): array`

Menghitung tiga nilai untuk summary cards Tab Laporan:

- **total_pemasukan** — SUM jumlah dari `laporan_keuangan` WHERE tipe='pemasukan'
- **total_pengeluaran** — SUM jumlah dari `laporan_keuangan` WHERE tipe='pengeluaran' + SUM(jumlah × harga_satuan) dari `barang_masuk` JOIN `barang`
- **saldo** — total_pemasukan - total_pengeluaran

```php
return [
    'total_pemasukan'   => float,
    'total_pengeluaran' => float,  // pengeluaran langsung + nilai barang masuk
    'saldo'             => float,
    'label_saldo'       => 'Surplus' | 'Defisit',
];
```

---

## 4. Perubahan View — `views/keuangan/index.php`

### 4.1 Struktur Umum

```
[Guard: cek $_SESSION['selected_project_id'] — tampilkan pesan jika tidak ada]

[Pill Nav]
  ↑ Keuangan Masuk  (tab=masuk)
  ↓ Keuangan Keluar (tab=keluar)
  ≡ Laporan Keuangan (tab=laporan)

[Tab Content sesuai ?tab=]
```

Tab aktif default: `masuk` (jika parameter `tab` tidak ada).

### 4.2 Tab Masuk

```
[Summary Card] Total Pemasukan — nilai dari $summary['total_pemasukan']

[Split Layout 2 kolom]
  Kolom Kiri: Form Input Pemasukan
    - Field: jumlah (number, required)
    - Field: tanggal (date, default today)
    - Field: sumber / dari (text)
    - Field: keterangan (textarea)
    - Tombol Submit → action=storeMasuk
    - [Sembunyikan jika !roleCanManage('keuangan')]

  Kolom Kanan: Tabel Riwayat Masuk
    - Kolom: No | Tanggal | Jumlah | Sumber | Keterangan | Aksi
    - Baris data dari $masukList
    - Tombol Hapus per baris [hanya jika roleCanManage('keuangan')]
    - Tombol "↓ Excel" → action=exportMasukExcel
```

### 4.3 Tab Keluar

```
[Summary Card] Total Pengeluaran — nilai dari $summary['total_pengeluaran']

[Split Layout 2 kolom]
  Kolom Kiri: Form Input Pengeluaran
    - Field: jumlah (number, required)
    - Field: tanggal (date, default today)
    - Field: sumber / kepada (text)
    - Field: keterangan (textarea)
    - Tombol Submit → action=storeKeluar
    - [Sembunyikan jika !roleCanManage('keuangan')]

  Kolom Kanan: Tabel Riwayat Keluar
    - Kolom: No | Tanggal | Jumlah | Sumber | Keterangan | Aksi
    - Baris data dari $keluarList
    - Tombol Hapus per baris [hanya jika roleCanManage('keuangan')]
    - Tombol "↓ Excel" → action=exportKeluarExcel
```

### 4.4 Tab Laporan

```
[3 Summary Cards]
  Card 1: Total Pemasukan   — $summary['total_pemasukan']
  Card 2: Total Pengeluaran — $summary['total_pengeluaran']
  Card 3: Saldo             — $summary['saldo']
           + Badge: "Surplus" (hijau) atau "Defisit" (merah) dari $summary['label_saldo']

[Tabel: Pembelian Material (Barang Masuk)]
  Kolom: No | Tanggal | Nama Barang | Jumlah | Satuan | Harga Satuan | Total | Supplier
  Data dari $barangMasukList
  Baris terakhir: Subtotal nilai barang masuk

[Tabel: Distribusi Material (Barang Keluar)]
  Kolom: No | Tanggal | Nama Barang | Jumlah | Satuan | Keterangan
  Data dari $barangKeluarList

[Tabel: Transaksi Keuangan Langsung]
  Kolom: No | Tanggal | Tipe | Jumlah | Sumber | Keterangan
  Kolom Tipe: badge warna hijau untuk "pemasukan", merah untuk "pengeluaran"
  Data: gabungan $masukList + $keluarList (atau query getAll per proyek)

[Tombol] ↓ Export Excel Laporan → action=exportLaporanExcel
```

### 4.5 Kontrol Akses

Semua form input dan tombol hapus di ketiga tab dibungkus kondisi `roleCanManage('keuangan')`. Aksi mutasi di controller memanggil `requireManagerPermission('keuangan')` sebagai guard server-side.
