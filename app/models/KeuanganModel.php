<?php
// app/models/KeuanganModel.php

class KeuanganModel {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function getAll(): array {
        return $this->db->query("
            SELECT lk.*, p.nama_proyek
            FROM laporan_keuangan lk
            JOIN proyek p ON p.id = lk.id_proyek
            ORDER BY lk.tanggal DESC, lk.id DESC
            LIMIT 100
        ")->fetchAll();
    }

    /** Riwayat transaksi dikelompokkan per proyek */
    public function getRiwayatPerProyek(?int $idProyek = null): array {
        $sql = "
            SELECT p.id, p.nama_proyek,
                COALESCE(SUM(CASE WHEN lk.tipe='pemasukan' THEN lk.jumlah ELSE 0 END), 0) AS pemasukan,
                COALESCE(SUM(CASE WHEN lk.tipe='pengeluaran' THEN lk.jumlah ELSE 0 END), 0) AS pengeluaran,
                COUNT(lk.id) AS jumlah_transaksi
            FROM proyek p
            LEFT JOIN laporan_keuangan lk ON lk.id_proyek = p.id
        ";
        $params = [];
        if ($idProyek !== null && $idProyek > 0) {
            $sql .= " WHERE p.id = ?";
            $params[] = $idProyek;
        }
        $sql .= " GROUP BY p.id, p.nama_proyek ORDER BY p.nama_proyek ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $proyeks = $stmt->fetchAll();

        $result = [];
        $txStmt = $this->db->prepare("
            SELECT lk.*, p.nama_proyek
            FROM laporan_keuangan lk
            JOIN proyek p ON p.id = lk.id_proyek
            WHERE lk.id_proyek = ?
            ORDER BY lk.tanggal DESC, lk.id DESC
        ");

        foreach ($proyeks as $proyek) {
            $txStmt->execute([(int) $proyek['id']]);
            $result[] = [
                'proyek'     => $proyek,
                'transaksi'  => $txStmt->fetchAll(),
            ];
        }

        return $result;
    }

    public function getSummary(?int $idProyek = null): array {
        if ($idProyek !== null && $idProyek > 0) {
            $stmt = $this->db->prepare("
                SELECT
                    SUM(CASE WHEN tipe='pemasukan' THEN jumlah ELSE 0 END) AS total_pemasukan,
                    SUM(CASE WHEN tipe='pengeluaran' THEN jumlah ELSE 0 END) AS total_pengeluaran,
                    COUNT(*) AS total_transaksi
                FROM laporan_keuangan
                WHERE id_proyek = ?
            ");
            $stmt->execute([$idProyek]);
            return $stmt->fetch() ?: [
                'total_pemasukan' => 0,
                'total_pengeluaran' => 0,
                'total_transaksi' => 0
            ];
        }
        return $this->db->query("
            SELECT
                SUM(CASE WHEN tipe='pemasukan' THEN jumlah ELSE 0 END) AS total_pemasukan,
                SUM(CASE WHEN tipe='pengeluaran' THEN jumlah ELSE 0 END) AS total_pengeluaran,
                COUNT(*) AS total_transaksi
            FROM laporan_keuangan
        ")->fetch() ?: [
            'total_pemasukan' => 0,
            'total_pengeluaran' => 0,
            'total_transaksi' => 0
        ];
    }

    public function getSummaryPerProyek(): array {
        return $this->db->query("
            SELECT p.id, p.nama_proyek,
                SUM(CASE WHEN lk.tipe='pemasukan' THEN lk.jumlah ELSE 0 END) AS pemasukan,
                SUM(CASE WHEN lk.tipe='pengeluaran' THEN lk.jumlah ELSE 0 END) AS pengeluaran
            FROM proyek p
            LEFT JOIN laporan_keuangan lk ON lk.id_proyek = p.id
            GROUP BY p.id, p.nama_proyek
            ORDER BY pengeluaran DESC
        ")->fetchAll();
    }

    public function getProyek(): array {
        return $this->db->query("SELECT id, nama_proyek FROM proyek ORDER BY nama_proyek")->fetchAll();
    }

    public function create(array $data): bool {
        $stmt = $this->db->prepare("
            INSERT INTO laporan_keuangan (id_proyek, tipe, kategori, jumlah, sumber, keterangan, tanggal)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['id_proyek'],
            $data['tipe'],
            $data['kategori'] ?? null,
            $data['jumlah'],
            $data['sumber'],
            $data['keterangan'],
            $data['tanggal']
        ]);
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM laporan_keuangan WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare("
            UPDATE laporan_keuangan
            SET jumlah = ?, sumber = ?, keterangan = ?, tanggal = ?, kategori = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['jumlah'],
            $data['sumber'] ?? null,
            $data['keterangan'] ?? null,
            $data['tanggal'],
            $data['kategori'] ?? null,
            $id
        ]);
    }

    public function delete(int $id): bool {
        return $this->db->prepare("DELETE FROM laporan_keuangan WHERE id = ?")->execute([$id]);
    }

    public function getMasuk(?int $idProyek = null): array {
        if ($idProyek !== null && $idProyek > 0) {
            $stmt = $this->db->prepare("
                SELECT * FROM laporan_keuangan
                WHERE tipe = 'pemasukan' AND id_proyek = ?
                ORDER BY tanggal DESC, id DESC
            ");
            $stmt->execute([$idProyek]);
            return $stmt->fetchAll();
        }
        return $this->db->query("
            SELECT * FROM laporan_keuangan
            WHERE tipe = 'pemasukan'
            ORDER BY tanggal DESC, id DESC
        ")->fetchAll();
    }

    public function getKeluar(?int $idProyek = null): array {
        if ($idProyek !== null && $idProyek > 0) {
            $stmt = $this->db->prepare("
                SELECT * FROM laporan_keuangan
                WHERE tipe = 'pengeluaran' AND id_proyek = ?
                ORDER BY tanggal DESC, id DESC
            ");
            $stmt->execute([$idProyek]);
            return $stmt->fetchAll();
        }
        return $this->db->query("
            SELECT * FROM laporan_keuangan
            WHERE tipe = 'pengeluaran'
            ORDER BY tanggal DESC, id DESC
        ")->fetchAll();
    }

    public function getBarangMasukUntukLaporan(?int $idProyek = null): array {
        if ($idProyek !== null && $idProyek > 0) {
            $stmt = $this->db->prepare("
                SELECT bm.*, b.nama_barang, b.satuan,
                       (bm.jumlah * bm.harga_satuan) AS total_nilai
                FROM barang_masuk bm
                JOIN barang b ON b.id = bm.id_barang
                WHERE b.id_proyek = ?
                ORDER BY bm.tanggal DESC
            ");
            $stmt->execute([$idProyek]);
            return $stmt->fetchAll();
        }
        return $this->db->query("
            SELECT bm.*, b.nama_barang, b.satuan,
                   (bm.jumlah * bm.harga_satuan) AS total_nilai
            FROM barang_masuk bm
            JOIN barang b ON b.id = bm.id_barang
            ORDER BY bm.tanggal DESC
        ")->fetchAll();
    }

    public function getBarangKeluarUntukLaporan(?int $idProyek = null): array {
        if ($idProyek !== null && $idProyek > 0) {
            $stmt = $this->db->prepare("
                SELECT bk.*, b.nama_barang, b.satuan, b.harga_satuan,
                       (bk.jumlah * b.harga_satuan) AS total_nilai
                FROM barang_keluar bk
                JOIN barang b ON b.id = bk.id_barang
                WHERE bk.id_proyek = ?
                ORDER BY bk.tanggal DESC
            ");
            $stmt->execute([$idProyek]);
            return $stmt->fetchAll();
        }
        return $this->db->query("
            SELECT bk.*, b.nama_barang, b.satuan, b.harga_satuan,
                   (bk.jumlah * b.harga_satuan) AS total_nilai
            FROM barang_keluar bk
            JOIN barang b ON b.id = bk.id_barang
            ORDER BY bk.tanggal DESC
        ")->fetchAll();
    }

    public function getSummaryGabungan(?int $idProyek = null): array {
        $idP = ($idProyek !== null && $idProyek > 0) ? $idProyek : null;

        // Total pemasukan dari laporan_keuangan
        if ($idP) {
            $stmt = $this->db->prepare("SELECT COALESCE(SUM(jumlah),0) FROM laporan_keuangan WHERE tipe='pemasukan' AND id_proyek=?");
            $stmt->execute([$idP]);
            $totalPemasukan = (float) $stmt->fetchColumn();
        } else {
            $totalPemasukan = (float) $this->db->query("SELECT COALESCE(SUM(jumlah),0) FROM laporan_keuangan WHERE tipe='pemasukan'")->fetchColumn();
        }

        // Pengeluaran langsung dari laporan_keuangan
        if ($idP) {
            $stmt = $this->db->prepare("SELECT COALESCE(SUM(jumlah),0) FROM laporan_keuangan WHERE tipe='pengeluaran' AND id_proyek=?");
            $stmt->execute([$idP]);
            $pengeluaranLangsung = (float) $stmt->fetchColumn();
        } else {
            $pengeluaranLangsung = (float) $this->db->query("SELECT COALESCE(SUM(jumlah),0) FROM laporan_keuangan WHERE tipe='pengeluaran'")->fetchColumn();
        }

        // Nilai barang masuk (pengeluaran material)
        if ($idP) {
            $stmt = $this->db->prepare("SELECT COALESCE(SUM(bm.jumlah * bm.harga_satuan),0) FROM barang_masuk bm JOIN barang b ON b.id=bm.id_barang WHERE b.id_proyek=?");
            $stmt->execute([$idP]);
            $nilaiBarangMasuk = (float) $stmt->fetchColumn();
        } else {
            $nilaiBarangMasuk = (float) $this->db->query("SELECT COALESCE(SUM(jumlah * harga_satuan),0) FROM barang_masuk")->fetchColumn();
        }

        $totalPengeluaran = $pengeluaranLangsung + $nilaiBarangMasuk;
        $saldo = $totalPemasukan - $totalPengeluaran;

        return [
            'total_pemasukan'      => $totalPemasukan,
            'total_pengeluaran'    => $totalPengeluaran,
            'pengeluaran_langsung' => $pengeluaranLangsung,
            'nilai_barang_masuk'   => $nilaiBarangMasuk,
            'saldo'                => $saldo,
            'label_saldo'          => $saldo >= 0 ? 'Surplus' : 'Defisit',
        ];
    }
}
