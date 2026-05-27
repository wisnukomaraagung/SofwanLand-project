<?php
// app/models/Karyawan.php

class Karyawan {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM karyawan ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function create($nama, $jabatan, $gaji_per_hari, $foto) {
        $stmt = $this->db->prepare("INSERT INTO karyawan (nama, jabatan, gaji_per_hari, foto) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nama, $jabatan, $gaji_per_hari, $foto]);
        return $this->db->lastInsertId();
    }
    
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM karyawan WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function countAll() {
        $stmt = $this->db->query("SELECT COUNT(*) FROM karyawan");
        return $stmt->fetchColumn();
    }
    
    public function findByFaceDescriptor($descriptor) {
        $stmt = $this->db->prepare("SELECT * FROM karyawan WHERE face_descriptor IS NOT NULL");
        $stmt->execute();
        $karyawan = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($karyawan as $k) {
            $savedDescriptor = json_decode($k['face_descriptor'], true);
            if ($savedDescriptor && $this->compareDescriptor($descriptor, $savedDescriptor)) {
                return $k;
            }
        }
        return null;
    }
    
    private function compareDescriptor($desc1, $desc2, $threshold = 0.6) {
        if (count($desc1) != count($desc2)) return false;
        $distance = 0;
        for ($i = 0; $i < count($desc1); $i++) {
            $distance += pow($desc1[$i] - $desc2[$i], 2);
        }
        $distance = sqrt($distance);
        return $distance < $threshold;
    }
    
    public function updateFaceDescriptor($id, $descriptor) {
        $stmt = $this->db->prepare("UPDATE karyawan SET face_descriptor = ? WHERE id = ?");
        return $stmt->execute([$descriptor, $id]);
    }
}
?>