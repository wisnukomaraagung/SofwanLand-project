<?php
require 'config/database.php';
$db = getDB();
$cols = $db->query("SHOW COLUMNS FROM barang")->fetchAll();
print_r($cols);
