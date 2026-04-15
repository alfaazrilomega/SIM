<?php
$host = '127.0.0.1';
$db   = 'sim_orders';
$user = 'root';
$pass = '';

try {
     $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
     $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

     $stmt = $pdo->query("SELECT * FROM karyawan WHERE nama_karyawan LIKE '%Andika%'");
     $k = $stmt->fetch(PDO::FETCH_ASSOC);

     if (!$k) {
         echo "Not found\n";
         exit;
     }

     echo "Found Karyawan ID: " . $k['id_karyawan'] . "\n";

     try {
         $del = $pdo->prepare('DELETE FROM karyawan WHERE id_karyawan = ?');
         $del->execute([$k['id_karyawan']]);
         echo "Deleted via raw SQL\n";
     } catch (\Exception $e) {
         echo "Raw SQL Delete Error:\n";
         echo $e->getMessage() . "\n";
     }
} catch (PDOException $e) {
     echo "Connection failed: " . $e->getMessage();
}
