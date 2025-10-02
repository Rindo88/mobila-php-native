<?php
session_start();
require './config/db.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    echo "0";
    exit();
}

$email_user = $_SESSION['email'] ?? '';

// Gunakan prepared statement untuk notifikasi
$stmt_notif = mysqli_prepare($conn, "SELECT COUNT(*) AS jumlah 
                                    FROM booking_test_drive 
                                    WHERE email = ? 
                                    AND status IN ('Disetujui', 'Ditolak') 
                                    AND (dibaca_user = 0 OR dibaca_user IS NULL)");
mysqli_stmt_bind_param($stmt_notif, "s", $email_user);
mysqli_stmt_execute($stmt_notif);
$result_notif = mysqli_stmt_get_result($stmt_notif);
$data_notif = mysqli_fetch_assoc($result_notif);
$jumlah_notif = $data_notif['jumlah'] ?? 0;
mysqli_stmt_close($stmt_notif);

echo $jumlah_notif;
?>