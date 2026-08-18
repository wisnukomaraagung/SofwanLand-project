# Dokumen Requirements

## Pendahuluan

Fitur ini memisahkan tampilan menu Keuangan menjadi tiga tab terpisah: **Keuangan Masuk**, **Keuangan Keluar**, dan **Laporan Keuangan** — mengikuti pola yang sudah berjalan di menu Barang. Saat ini menu Keuangan menampilkan satu form dengan dropdown tipe (pemasukan/pengeluaran). Perubahan bersifat murni UI/controller; struktur tabel `laporan_keuangan` tidak berubah. Tab Laporan menggabungkan transaksi keuangan langsung dengan nilai rupiah dari transaksi barang masuk/keluar.

## Glosarium

- **KeuanganController**: Controller PHP yang menangani semua aksi menu Keuangan.
- **KeuanganModel**: Model PHP yang mengakses tabel `laporan_keuangan`.
- **BarangModel**: Model PHP yang mengakses tabel `barang_masuk` dan `barang_keluar`.
- **Tab_Masuk**: Tab "Keuangan Masuk" — menampilkan form input dan riwayat pemasukan.
- **Tab_Keluar**: Tab "Keuangan Keluar" — menampilkan form input dan riwayat pengeluaran.
- **Tab_Laporan**: Tab "Laporan Keuangan" — menampilkan ringkasan gabungan.
- **Transaksi_Langsung**: Transaksi yang dicatat langsung di tabel `laporan_keuangan`.
- **Nilai_Barang_Masuk**: Nilai rupiah dari tabel `barang_masuk` (jumlah × harga_satuan).
- **Nilai_Barang_Keluar**: Nilai rupiah dari tabel `barang_keluar` (jumlah × harga_satuan barang).
- **Saldo**: Selisih antara total pemasukan dan total pengeluaran.
- **Proyek_Aktif**: Proyek yang sedang dipilih melalui `$_SESSION['selected_project_id']`.
- **roleCanManage**: Fungsi kontrol akses yang memeriksa apakah pengguna berwenang mengelola suatu modul.

---

## Requirements

### Requirement 1: Navigasi Tab Tiga Panel

**User Story:** Sebagai pengguna, saya ingin melihat menu Keuangan terbagi dalam tiga tab, sehingga saya bisa berpindah antara pemasukan, pengeluaran, dan laporan dengan cepat.

#### Kriteria Penerimaan

1. THE **KeuanganController** SHALL merender halaman keuangan dengan tiga tab navigasi: Tab_Masuk, Tab_Keluar, dan Tab_Laporan.
2. WHEN pengguna mengakses `?page=keuangan` tanpa parameter `tab`, THE **KeuanganController** SHALL menampilkan Tab_Masuk sebagai tab aktif secara default.
3. WHEN pengguna mengklik tab tertentu, THE **halaman** SHALL memuat ulang dengan parameter `tab` yang sesuai (`masuk`, `keluar`, atau `laporan`) dan menandai tab tersebut sebagai aktif.
4. IF `$_SESSION['selected_project_id']` tidak tersedia, THEN THE **KeuanganController** SHALL menampilkan pesan "Belum Ada Proyek yang Dipilih" dan menyembunyikan semua tab.

---

### Requirement 2: Tab Keuangan Masuk — Form Input

**User Story:** Sebagai admin/manager, saya ingin mencatat pemasukan keuangan melalui form khusus di Tab_Masuk, sehingga pencatatan lebih fokus dan tidak ada risiko memilih tipe yang salah.

#### Kriteria Penerimaan

1. WHEN Tab_Masuk aktif DAN `roleCanManage('keuangan')` bernilai true, THE **KeuanganController** SHALL menampilkan form input pemasukan yang berisi field: jumlah (angka, wajib), tanggal (date, default hari ini), sumber/dari (teks), dan keterangan (textarea, opsional).
2. WHEN pengguna mengirim form pada Tab_Masuk, THE **KeuanganController** SHALL menyimpan transaksi ke tabel `laporan_keuangan` dengan kolom `tipe` diset otomatis ke nilai `'pemasukan'` tanpa memerlukan pilihan dropdown dari pengguna.
3. IF field `jumlah` bernilai kosong atau kurang dari 1, THEN THE **KeuanganController** SHALL menolak penyimpanan dan menampilkan pesan kesalahan "Jumlah pemasukan wajib diisi dan harus lebih dari 0".
4. WHEN penyimpanan berhasil, THE **KeuanganController** SHALL mengarahkan pengguna kembali ke `?page=keuangan&tab=masuk` dan menampilkan pesan sukses.
5. WHILE `roleCanManage('keuangan')` bernilai false, THE **halaman** SHALL menyembunyikan form input dan menampilkan informasi bahwa hanya admin/manager yang dapat mencatat pemasukan.

---

### Requirement 3: Tab Keuangan Masuk — Riwayat Pemasukan

**User Story:** Sebagai pengguna, saya ingin melihat riwayat transaksi pemasukan di Tab_Masuk, sehingga saya bisa memantau arus kas masuk tanpa tercampur data pengeluaran.

#### Kriteria Penerimaan

1. WHEN Tab_Masuk aktif, THE **KeuanganModel** SHALL mengambil hanya transaksi dengan `tipe = 'pemasukan'` dari tabel `laporan_keuangan` milik Proyek_Aktif, diurutkan berdasarkan tanggal terbaru.
2. THE **halaman** SHALL menampilkan riwayat pemasukan dalam tabel dengan kolom: No, Tanggal, Jumlah, Sumber, Keterangan, dan Aksi.
3. WHEN `roleCanManage('keuangan')` bernilai true, THE **halaman** SHALL menampilkan tombol "Hapus" pada setiap baris riwayat pemasukan.
4. WHEN pengguna mengklik tombol hapus pada Tab_Masuk, THE **KeuanganController** SHALL menghapus transaksi yang dipilih dari tabel `laporan_keuangan` dan mengarahkan kembali ke `?page=keuangan&tab=masuk`.
5. THE **halaman** SHALL menampilkan tombol "↓ Excel" yang mengekspor hanya data pemasukan Proyek_Aktif ke file `.xls`.

---

### Requirement 4: Tab Keuangan Keluar — Form Input

**User Story:** Sebagai admin/manager, saya ingin mencatat pengeluaran keuangan melalui form khusus di Tab_Keluar, sehingga pencatatan lebih terstruktur dan terpisah dari pemasukan.

#### Kriteria Penerimaan

1. WHEN Tab_Keluar aktif DAN `roleCanManage('keuangan')` bernilai true, THE **KeuanganController** SHALL menampilkan form input pengeluaran yang berisi field: jumlah (angka, wajib), tanggal (date, default hari ini), sumber/kepada (teks), dan keterangan (textarea, opsional).
2. WHEN pengguna mengirim form pada Tab_Keluar, THE **KeuanganController** SHALL menyimpan transaksi ke tabel `laporan_keuangan` dengan kolom `tipe` diset otomatis ke nilai `'pengeluaran'` tanpa memerlukan pilihan dropdown dari pengguna.
3. IF field `jumlah` bernilai kosong atau kurang dari 1, THEN THE **KeuanganController** SHALL menolak penyimpanan dan menampilkan pesan kesalahan "Jumlah pengeluaran wajib diisi dan harus lebih dari 0".
4. WHEN penyimpanan berhasil, THE **KeuanganController** SHALL mengarahkan pengguna kembali ke `?page=keuangan&tab=keluar` dan menampilkan pesan sukses.
5. WHILE `roleCanManage('keuangan')` bernilai false, THE **halaman** SHALL menyembunyikan form input dan menampilkan informasi bahwa hanya admin/manager yang dapat mencatat pengeluaran.

---

### Requirement 5: Tab Keuangan Keluar — Riwayat Pengeluaran

**User Story:** Sebagai pengguna, saya ingin melihat riwayat transaksi pengeluaran di Tab_Keluar, sehingga saya bisa memantau arus kas keluar secara terpisah.

#### Kriteria Penerimaan

1. WHEN Tab_Keluar aktif, THE **KeuanganModel** SHALL mengambil hanya transaksi dengan `tipe = 'pengeluaran'` dari tabel `laporan_keuangan` milik Proyek_Aktif, diurutkan berdasarkan tanggal terbaru.
2. THE **halaman** SHALL menampilkan riwayat pengeluaran dalam tabel dengan kolom: No, Tanggal, Jumlah, Sumber, Keterangan, dan Aksi.
3. WHEN `roleCanManage('keuangan')` bernilai true, THE **halaman** SHALL menampilkan tombol "Hapus" pada setiap baris riwayat pengeluaran.
4. WHEN pengguna mengklik tombol hapus pada Tab_Keluar, THE **KeuanganController** SHALL menghapus transaksi yang dipilih dari tabel `laporan_keuangan` dan mengarahkan kembali ke `?page=keuangan&tab=keluar`.
5. THE **halaman** SHALL menampilkan tombol "↓ Excel" yang mengekspor hanya data pengeluaran Proyek_Aktif ke file `.xls`.

---

### Requirement 6: Tab Laporan — Summary Cards

**User Story:** Sebagai manajer proyek, saya ingin melihat ringkasan keuangan gabungan di Tab_Laporan, sehingga saya bisa mengetahui posisi keuangan proyek secara keseluruhan dalam satu layar.

#### Kriteria Penerimaan

1. WHEN Tab_Laporan aktif, THE **KeuanganController** SHALL menghitung dan menampilkan tiga summary card: Total Pemasukan, Total Pengeluaran, dan Saldo.
2. THE **KeuanganController** SHALL menghitung Total Pemasukan sebagai jumlah seluruh transaksi `tipe = 'pemasukan'` dari tabel `laporan_keuangan` milik Proyek_Aktif.
3. THE **KeuanganController** SHALL menghitung Total Pengeluaran sebagai penjumlahan dari: (a) seluruh transaksi `tipe = 'pengeluaran'` dari tabel `laporan_keuangan` milik Proyek_Aktif, ditambah (b) total Nilai_Barang_Masuk dari tabel `barang_masuk` yang terkait Proyek_Aktif.
4. THE **KeuanganController** SHALL menghitung Saldo sebagai selisih Total Pemasukan dikurangi Total Pengeluaran.
5. IF Saldo bernilai negatif, THEN THE **halaman** SHALL menampilkan label "Defisit" pada card Saldo. IF Saldo bernilai nol atau positif, THEN THE **halaman** SHALL menampilkan label "Surplus".

---

### Requirement 7: Tab Laporan — Tabel Data Barang Masuk (Nilai Rupiah)

**User Story:** Sebagai manajer proyek, saya ingin melihat daftar pembelian material (barang masuk) beserta nilai rupiahnya di Tab_Laporan, sehingga saya bisa mengaudit pengeluaran material proyek.

#### Kriteria Penerimaan

1. WHEN Tab_Laporan aktif, THE **KeuanganController** SHALL mengambil data dari tabel `barang_masuk` yang ter-join dengan tabel `barang` untuk mendapatkan nama barang, satuan, dan harga_satuan, difilter berdasarkan `id_proyek` Proyek_Aktif melalui relasi `barang.id_proyek`.
2. THE **halaman** SHALL menampilkan data barang masuk dalam tabel dengan kolom: No, Tanggal, Nama Barang, Jumlah, Satuan, Harga Satuan, Total (jumlah × harga_satuan), dan Supplier.
3. THE **halaman** SHALL menampilkan subtotal Nilai_Barang_Masuk di baris terakhir tabel.

---

### Requirement 8: Tab Laporan — Tabel Data Barang Keluar

**User Story:** Sebagai manajer proyek, saya ingin melihat distribusi material (barang keluar) di Tab_Laporan, sehingga saya dapat melacak pemakaian material per proyek.

#### Kriteria Penerimaan

1. WHEN Tab_Laporan aktif, THE **KeuanganController** SHALL mengambil data dari tabel `barang_keluar` yang ter-join dengan tabel `barang`, difilter berdasarkan `barang_keluar.id_proyek` milik Proyek_Aktif.
2. THE **halaman** SHALL menampilkan data barang keluar dalam tabel dengan kolom: No, Tanggal, Nama Barang, Jumlah, Satuan, dan Keterangan.

---

### Requirement 9: Tab Laporan — Tabel Transaksi Keuangan Langsung

**User Story:** Sebagai manajer proyek, saya ingin melihat semua Transaksi_Langsung (pemasukan dan pengeluaran kas) di Tab_Laporan, sehingga semua arus keuangan tersaji dalam satu halaman laporan.

#### Kriteria Penerimaan

1. WHEN Tab_Laporan aktif, THE **KeuanganModel** SHALL mengambil seluruh transaksi dari tabel `laporan_keuangan` milik Proyek_Aktif, diurutkan berdasarkan tanggal terbaru.
2. THE **halaman** SHALL menampilkan Transaksi_Langsung dalam tabel dengan kolom: No, Tanggal, Tipe (badge warna berbeda untuk pemasukan/pengeluaran), Jumlah, Sumber, dan Keterangan.

---

### Requirement 10: Tab Laporan — Export Excel Gabungan

**User Story:** Sebagai manajer proyek, saya ingin mengekspor laporan keuangan gabungan ke Excel, sehingga saya dapat berbagi laporan lengkap kepada pemilik proyek.

#### Kriteria Penerimaan

1. WHEN Tab_Laporan aktif, THE **halaman** SHALL menampilkan tombol "↓ Export Excel Laporan".
2. WHEN pengguna mengklik tombol export, THE **KeuanganController** SHALL menghasilkan file `.xls` dengan nama `Laporan_Keuangan_{nama_proyek}_{YYYYMMDD}.xls`.
3. THE **KeuanganController** SHALL menyusun file Excel dengan tiga bagian terpisah dalam satu sheet: bagian pertama berisi summary (Total Pemasukan, Total Pengeluaran, Saldo), bagian kedua berisi data barang masuk, dan bagian ketiga berisi Transaksi_Langsung.
4. IF tidak ada data sama sekali untuk Proyek_Aktif, THEN THE **KeuanganController** SHALL tetap menghasilkan file Excel dengan header kolom yang lengkap dan baris keterangan "Tidak ada data".

---

### Requirement 11: Kontrol Akses Berbasis Peran

**User Story:** Sebagai sistem, saya ingin memastikan hanya pengguna dengan peran yang tepat yang dapat mencatat atau menghapus transaksi, sehingga integritas data keuangan terjaga.

#### Kriteria Penerimaan

1. WHEN aksi `storeMasuk`, `storeKeluar`, atau `delete` dipanggil, THE **KeuanganController** SHALL memanggil `requireManagerPermission('keuangan')` sebelum memproses data.
2. IF pengguna tidak memiliki izin, THEN THE **KeuanganController** SHALL menghentikan eksekusi dan mengarahkan pengguna ke halaman login atau menampilkan pesan "Akses ditolak".
3. THE **KeuanganController** SHALL menggunakan fungsi `roleCanManage('keuangan')` untuk menentukan visibilitas form input dan tombol hapus di semua tab.

---

### Requirement 12: Konsistensi Data — Tidak Mengubah Struktur Database

**User Story:** Sebagai pengembang, saya ingin memastikan perubahan ini tidak mengubah skema database, sehingga tidak ada risiko kehilangan data historis.

#### Kriteria Penerimaan

1. THE **KeuanganController** SHALL menyimpan semua transaksi keuangan langsung ke tabel `laporan_keuangan` yang sudah ada, tanpa menambah, mengubah, atau menghapus kolom pada tabel tersebut.
2. THE **KeuanganController** SHALL menggunakan kolom `tipe` yang sudah ada (`'pemasukan'` atau `'pengeluaran'`) sebagai pembeda antara Tab_Masuk dan Tab_Keluar, bukan dengan membuat tabel baru.
3. WHEN metode `storeMasuk` dipanggil, THE **KeuanganController** SHALL mengisi kolom `tipe` dengan nilai `'pemasukan'`. WHEN metode `storeKeluar` dipanggil, THE **KeuanganController** SHALL mengisi kolom `tipe` dengan nilai `'pengeluaran'`.
