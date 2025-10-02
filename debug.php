<?php
require './config/db.php';
session_start();

// Cek session
if (!isset($_SESSION['user_id'])) {
    die("Anda harus login terlebih dahulu.");
}

echo "<h2>Debug Information</h2>";

// Cek session email
echo "<h3>Session Data:</h3>";
echo "User ID: " . ($_SESSION['user_id'] ?? 'Not set') . "<br>";
echo "Email: " . ($_SESSION['email'] ?? 'Not set') . "<br>";
echo "Username: " . ($_SESSION['username'] ?? 'Not set') . "<br>";

// Cek struktur tabel
echo "<h3>Table Structure:</h3>";
$result = $conn->query("DESCRIBE booking_test_drive");
if ($result) {
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Error: " . $conn->error;
}

// Cek data terakhir di tabel
echo "<h3>Last 5 Records:</h3>";
$result = $conn->query("SELECT * FROM booking_test_drive ORDER BY created_at DESC LIMIT 5");
if ($result && $result->num_rows > 0) {
    echo "<table border='1'>";
    // Header
    echo "<tr>";
    while ($field = $result->fetch_field()) {
        echo "<th>" . $field->name . "</th>";
    }
    echo "</tr>";
    // Data
    $result->data_seek(0);
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>" . htmlspecialchars($value) . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No records found or error: " . $conn->error;
}

$conn->close();
?>