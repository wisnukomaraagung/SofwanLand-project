<?php
// app/models/BarangModel.php

class BarangModel {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function getAll(?int $idProyek = null): array {
        if ($idProyek !== null) {
            $stmt = $this->db->prepare("
                SELECT b.*,
                    COALESCE(SUM(bm.jumlah), 0) AS total_masuk,
                    COALESCE(SUM(bk.jumlah), 0) AS total_keluar
                FROM barang b
                LEFT JOIN barang_masuk bm ON bm.id_barang = b.id
                LEFT JOIN barang_keluar bk ON bk.id_barang = b.id
                WHERE b.id_proyek = ?
                GROUP BY b.id ORDER BY b.nama_barang ASC
            ");
            $stmt->execute([$idProyek]);
            return $stmt->fetchAll();
        }
        return $this->db->query("
            SELECT b.*,
                COALESCE(SUM(bm.jumlah), 0) AS total_masuk,
                COALESCE(SUM(bk.jumlah), 0) AS total_keluar
            FROM barang b
            LEFT JOIN barang_masuk bm ON bm.id_barang = b.id
            LEFT JOIN barang_keluar bk ON bk.id_barang = b.id
            GROUP BY b.id ORDER BY b.nama_barang ASC
        ")->fetchAll();
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM barang WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function getAllForSelect(?int $idProyek = null): array {
        if ($idProyek !== null) {
            $stmt = $this->db->prepare("SELECT id, nama_barang, satuan, stok FROM barang WHERE id_proyek = ? ORDER BY nama_barang");
            $stmt->execute([$idProyek]);
            return $stmt->fetchAll();
        }
        return $this->db->query("SELECT id, nama_barang, satuan, stok FROM barang ORDER BY nama_barang")->fetchAll();
    }

    public function create(array $data, ?int $idProyek = null): bool {
        $stmt = $this->db->prepare("INSERT INTO barang (nama_barang, satuan, stok, harga_satuan, id_proyek) VALUES (?,?,?,?,?)");
        return $stmt->execute([$data['nama_barang'], $data['satuan'], $data['stok'], $data['harga_satuan'], $idProyek]);
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare("UPDATE barang SET nama_barang=?, satuan=?, harga_satuan=? WHERE id=?");
        return $stmt->execute([$data['nama_barang'], $data['satuan'], $data['harga_satuan'], $id]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM barang WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getMasuk(?int $idProyek = null): array {
        if ($idProyek !== null) {
            $stmt = $this->db->prepare("
                SELECT bm.*, b.nama_barang, b.satuan
                FROM barang_masuk bm JOIN barang b ON b.id = bm.id_barang
                WHERE b.id_proyek = ?
                ORDER BY bm.tanggal DESC LIMIT 50
            ");
            $stmt->execute([$idProyek]);
            return $stmt->fetchAll();
        }
        return $this->db->query("
            SELECT bm.*, b.nama_barang, b.satuan
            FROM barang_masuk bm JOIN barang b ON b.id = bm.id_barang
            ORDER BY bm.tanggal DESC LIMIT 50
        ")->fetchAll();
    }

    public function getDashboardSummary(?int $idProyek = null): array {
        $bulanIni = date('Y-m');
        
        if ($idProyek !== null) {
            $pengeluaran = $this->db->prepare("
                SELECT COALESCE(SUM(bm.harga_satuan * bm.jumlah), 0) 
                FROM barang_masuk bm
                JOIN barang b ON b.id = bm.id_barang
                WHERE DATE_FORMAT(bm.tanggal, '%Y-%m') = ? AND b.id_proyek = ?
            ");
            $pengeluaran->execute([$bulanIni, $idProyek]);
            $pengeluaranVal = $pengeluaran->fetchColumn();

            $jenisBarang = $this->db->prepare("SELECT COUNT(*) FROM barang WHERE id_proyek = ?");
            $jenisBarang->execute([$idProyek]);
            $jenisBarangVal = $jenisBarang->fetchColumn();

            $transaksiMasuk = $this->db->prepare("
                SELECT COUNT(*) FROM barang_masuk bm
                JOIN barang b ON b.id = bm.id_barang
                WHERE b.id_proyek = ?
            ");
            $transaksiMasuk->execute([$idProyek]);
            $transaksiMasukVal = $transaksiMasuk->fetchColumn();

            $transaksiKeluar = $this->db->prepare("SELECT COUNT(*) FROM barang_keluar WHERE id_proyek = ?");
            $transaksiKeluar->execute([$idProyek]);
            $transaksiKeluarVal = $transaksiKeluar->fetchColumn();

            $totalPengeluaran = $this->db->prepare("
                SELECT COALESCE(SUM(bm.harga_satuan * bm.jumlah), 0) 
                FROM barang_masuk bm
                JOIN barang b ON b.id = bm.id_barang
                WHERE b.id_proyek = ?
            ");
            $totalPengeluaran->execute([$idProyek]);
            $totalPengeluaranVal = $totalPengeluaran->fetchColumn();

            return [
                'pengeluaran_bulan_ini' => $pengeluaranVal,
                'jenis_barang' => $jenisBarangVal,
                'transaksi_masuk' => $transaksiMasukVal,
                'transaksi_keluar' => $transaksiKeluarVal,
                'total_pengeluaran' => $totalPengeluaranVal
            ];
        }
        
        $pengeluaran = $this->db->query("SELECT COALESCE(SUM(harga_satuan * jumlah), 0) FROM barang_masuk WHERE DATE_FORMAT(tanggal, '%Y-%m') = '$bulanIni'")->fetchColumn();
        $jenisBarang = $this->db->query("SELECT COUNT(*) FROM barang")->fetchColumn();
        $transaksiMasuk = $this->db->query("SELECT COUNT(*) FROM barang_masuk")->fetchColumn();
        $transaksiKeluar = $this->db->query("SELECT COUNT(*) FROM barang_keluar")->fetchColumn();
        $totalPengeluaran = $this->db->query("SELECT COALESCE(SUM(harga_satuan * jumlah), 0) FROM barang_masuk")->fetchColumn();
        
        return [
            'pengeluaran_bulan_ini' => $pengeluaran,
            'jenis_barang' => $jenisBarang,
            'transaksi_masuk' => $transaksiMasuk,
            'transaksi_keluar' => $transaksiKeluar,
            'total_pengeluaran' => $totalPengeluaran
        ];
    }

    public function getKeluar(?int $idProyek = null): array {
        if ($idProyek !== null) {
            $stmt = $this->db->prepare("
                SELECT bk.*, b.nama_barang, b.satuan, p.nama_proyek
                FROM barang_keluar bk
                JOIN barang b ON b.id = bk.id_barang
                JOIN proyek p ON p.id = bk.id_proyek
                WHERE bk.id_proyek = ?
                ORDER BY bk.tanggal DESC LIMIT 50
            ");
            $stmt->execute([$idProyek]);
            return $stmt->fetchAll();
        }
        return $this->db->query("
            SELECT bk.*, b.nama_barang, b.satuan, p.nama_proyek
            FROM barang_keluar bk
            JOIN barang b ON b.id = bk.id_barang
            JOIN proyek p ON p.id = bk.id_proyek
            ORDER BY bk.tanggal DESC LIMIT 50
        ")->fetchAll();
    }

    public function getKeluarById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT bk.*, b.nama_barang, b.satuan, p.nama_proyek FROM barang_keluar bk JOIN barang b ON b.id = bk.id_barang JOIN proyek p ON p.id = bk.id_proyek WHERE bk.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function updateKeluar(int $id, array $data): bool {
        $pdo = $this->db;
        $pdo->beginTransaction();
        try {
            $old = $pdo->query("SELECT id_barang, jumlah FROM barang_keluar WHERE id = $id")->fetch();
            if ($old) {
                if ($old['id_barang'] == $data['id_barang']) {
                    $diff = $data['jumlah'] - $old['jumlah'];
                    $pdo->prepare("UPDATE barang SET stok = stok - ? WHERE id = ?")->execute([$diff, $old['id_barang']]);
                } else {
                    $pdo->prepare("UPDATE barang SET stok = stok + ? WHERE id = ?")->execute([$old['jumlah'], $old['id_barang']]);
                    $pdo->prepare("UPDATE barang SET stok = stok - ? WHERE id = ?")->execute([$data['jumlah'], $data['id_barang']]);
                }

                $stmt = $pdo->prepare("UPDATE barang_keluar SET id_barang=?, id_proyek=?, jumlah=?, tanggal=?, keterangan=?, foto_bukti=? WHERE id=?");
                $stmt->execute([
                    $data['id_barang'], $data['id_proyek'], $data['jumlah'], $data['tanggal'], $data['keterangan'] ?? null, $data['foto_bukti'] ?? null, $id
                ]);
            }
            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            return false;
        }
    }

    public function deleteKeluar(int $id): bool {
        $pdo = $this->db;
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("SELECT id_barang, jumlah FROM barang_keluar WHERE id = ?");
            $stmt->execute([$id]);
            $keluar = $stmt->fetch();
            if ($keluar) {
                $pdo->prepare("UPDATE barang SET stok = stok + ? WHERE id = ?")->execute([$keluar['jumlah'], $keluar['id_barang']]);
                $pdo->prepare("DELETE FROM barang_keluar WHERE id = ?")->execute([$id]);
            }
            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            return false;
        }
    }

    public function storeMasuk(array $data): bool {
        $pdo = $this->db;
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("INSERT INTO barang_masuk (id_barang, jumlah, tanggal, harga_satuan, supplier, no_kuitansi, foto_kuitansi, keterangan) VALUES (?,?,?,?,?,?,?,?)");
            $stmt->execute([
                $data['id_barang'], $data['jumlah'], $data['tanggal'], 
                $data['harga_satuan'] ?? 0, $data['supplier'] ?? null, 
                $data['no_kuitansi'] ?? null, $data['foto_kuitansi'] ?? null, 
                $data['keterangan'] ?? null
            ]);
            $pdo->prepare("UPDATE barang SET stok = stok + ? WHERE id = ?")->execute([$data['jumlah'], $data['id_barang']]);
            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            return false;
        }
    }

    public function storeKeluar(array $data): bool {
        $pdo = $this->db;
        
        // Ensure foto_bukti column exists
        try {
            $pdo->query("SELECT foto_bukti FROM barang_keluar LIMIT 1");
        } catch (Exception $e) {
            $pdo->exec("ALTER TABLE barang_keluar ADD COLUMN foto_bukti VARCHAR(255) NULL");
        }

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("INSERT INTO barang_keluar (id_barang, id_proyek, jumlah, tanggal, keterangan, foto_bukti) VALUES (?,?,?,?,?,?)");
            $stmt->execute([$data['id_barang'], $data['id_proyek'], $data['jumlah'], $data['tanggal'], $data['keterangan'], $data['foto_bukti'] ?? null]);
            $pdo->prepare("UPDATE barang SET stok = stok - ? WHERE id = ?")->execute([$data['jumlah'], $data['id_barang']]);
            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            return false;
        }
    }

    public function getMasukById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM barang_masuk WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function updateMasuk(int $id, array $data): bool {
        $pdo = $this->db;
        $pdo->beginTransaction();
        try {
            $old = $pdo->query("SELECT id_barang, jumlah FROM barang_masuk WHERE id = $id")->fetch();
            if ($old) {
                $pdo->prepare("UPDATE barang SET stok = stok - ? WHERE id = ?")->execute([$old['jumlah'], $old['id_barang']]);
                $pdo->prepare("UPDATE barang SET stok = stok + ? WHERE id = ?")->execute([$data['jumlah'], $data['id_barang']]);
                
                $stmt = $pdo->prepare("UPDATE barang_masuk SET id_barang=?, jumlah=?, tanggal=?, harga_satuan=?, supplier=?, no_kuitansi=?, foto_kuitansi=?, keterangan=? WHERE id=?");
                $stmt->execute([
                    $data['id_barang'], $data['jumlah'], $data['tanggal'], 
                    $data['harga_satuan'] ?? 0, $data['supplier'] ?? null, 
                    $data['no_kuitansi'] ?? null, $data['foto_kuitansi'] ?? null, $data['keterangan'] ?? null,
                    $id
                ]);
            }
            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            return false;
        }
    }

    public function deleteMasuk(int $id): bool {
        $pdo = $this->db;
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("SELECT id_barang, jumlah FROM barang_masuk WHERE id = ?");
            $stmt->execute([$id]);
            $masuk = $stmt->fetch();
            if ($masuk) {
                $pdo->prepare("UPDATE barang SET stok = stok - ? WHERE id = ?")->execute([$masuk['jumlah'], $masuk['id_barang']]);
                $pdo->prepare("DELETE FROM barang_masuk WHERE id = ?")->execute([$id]);
            }
            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            return false;
        }
    }
}
