<?php
require 'config/database.php';
require 'app/models/BarangModel.php';

$model = new BarangModel();
$data = [
    'id_barang' => 68, // Palu
    'jumlah' => 10,
    'tanggal' => date('Y-m-d'),
    'harga_satuan' => 50000,
    'supplier' => 'Toko Besi',
    'no_kuitansi' => 'TEST01',
    'foto_kuitansi' => null,
    'keterangan' => 'Test'
];

try {
    $res = $model->storeMasuk($data);
    echo "Result: " . ($res ? 'Success' : 'Failed');
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage();
}
