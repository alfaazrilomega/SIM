<?php
$host = '127.0.0.1';
$db   = 'sim_orders';
$user = 'root';
$pass = '';

try {
     $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
     $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

     // Find duplicates based on id_karyawan and periode_bulan
     $stmt = $pdo->query("
         SELECT id_karyawan, periode_bulan, COUNT(*) as c 
         FROM penggajian 
         GROUP BY id_karyawan, periode_bulan 
         HAVING c > 1
     ");
     
     $duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);

     if (!$duplicates) {
         echo "No duplicates found in penggajian table.\n";
     } else {
         foreach ($duplicates as $dup) {
             echo "Fixing duplicate for Karyawan ID: {$dup['id_karyawan']} Periode: {$dup['periode_bulan']}\n";
             
             // Get all records for this duplicate group
             $recordsStmt = $pdo->prepare("SELECT * FROM penggajian WHERE id_karyawan = ? AND periode_bulan = ? ORDER BY id_penggajian ASC");
             $recordsStmt->execute([$dup['id_karyawan'], $dup['periode_bulan']]);
             $records = $recordsStmt->fetchAll(PDO::FETCH_ASSOC);
             
             $hasPaid = false;
             $paidId = null;
             
             // First check if any of them is Paid
             foreach($records as $rec) {
                 if ($rec['status_pembayaran'] === 'Sudah Dibayar') {
                     $hasPaid = true;
                     $paidId = $rec['id_penggajian'];
                     break;
                 }
             }
             
             // Keep ID
             $keepId = $hasPaid ? $paidId : $records[0]['id_penggajian'];
             
             // Delete the rest
             $delStmt = $pdo->prepare("DELETE FROM penggajian WHERE id_karyawan = ? AND periode_bulan = ? AND id_penggajian != ?");
             $delStmt->execute([$dup['id_karyawan'], $dup['periode_bulan'], $keepId]);
             
             echo "Deleted duplicates, kept ID: $keepId\n";
         }
     }
} catch (PDOException $e) {
     echo "Connection failed: " . $e->getMessage();
}
