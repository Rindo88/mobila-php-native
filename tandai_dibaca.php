<?php
session_start();
require './config/db.php';

$email = $_SESSION['email'];

$query = "UPDATE booking_test_drive 
          SET dibaca_user = 1 
          WHERE email = '$email' 
          AND status IN ('Disetujui', 'Ditolak') 
          AND dibaca_user = 0";

mysqli_query($conn, $query);
?>
