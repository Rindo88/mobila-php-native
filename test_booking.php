<?php
session_start();
require './config/db.php';

// Cek session
if (!isset($_SESSION['email'])) {
    die("Silakan login terlebih dahulu");
}

$email_user = $_SESSION['email'];

echo "<h1>Test Data Booking untuk: " . htmlspecialchars($email_user) . "</h1>";

// Test query booking
$sql = "SELECT * FROM booking_test_drive WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email_user);
$stmt->execute();
$result = $stmt->get_result();

echo "<h2>Data dari booking_test_drive:</h2>";
if ($result->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Nama</th><th>Email</th><th>Status</th><th>Mobil ID</th><th>Dibaca</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id_booking'] . "</td>";
        echo "<td>" . $row['nama_lengkap'] . "</td>";
        echo "<td>" . $row['email'] . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "<td>" . $row['id_mobil'] . "</td>";
        echo "<td>" . $row['dibaca_user'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Tidak ada data booking ditemukan";
}

$stmt->close();
$conn->close();
?>  