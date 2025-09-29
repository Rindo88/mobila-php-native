<?php
session_start();
require './config/db.php';

// Validasi session email
if (!isset($_SESSION['email'])) {
    echo "<p class='text-danger text-center'>Silakan login terlebih dahulu.</p>";
    exit;
}

$email = $_SESSION['email'];

// Gunakan prepared statement untuk keamanan
$query = "SELECT merk_mobil, status, created_at, id 
          FROM booking_test_drive 
          WHERE email = ? 
          AND status IN ('Disetujui', 'Ditolak') 
          AND dibaca_user = 0 
          ORDER BY created_at DESC";

$stmt = mysqli_prepare($conn, $query);

if (!$stmt) {
    echo "<p class='text-danger text-center'>Terjadi kesalahan sistem.</p>";
    exit;
}

// Bind parameter dan eksekusi
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Cek apakah ada notifikasi
if (mysqli_num_rows($result) == 0) {
    echo "<div class='text-center py-4'>
            <i class='bi bi-bell-slash fs-1 text-muted'></i>
            <p class='text-muted mt-2'>Tidak ada notifikasi baru.</p>
          </div>";
} else {
    // Tampilkan setiap notifikasi
    while ($row = mysqli_fetch_assoc($result)) {
        // Tentukan warna dan ikon berdasarkan status
        if ($row['status'] == 'Disetujui') {
            $badgeClass = 'bg-success';
            $iconClass = 'bi-check-circle-fill text-success';
            $borderClass = 'border-success';
        } else {
            $badgeClass = 'bg-danger';
            $iconClass = 'bi-x-circle-fill text-danger';
            $borderClass = 'border-danger';
        }
        
        // Format tanggal agar lebih readable
        $tanggal = date('d M Y, H:i', strtotime($row['created_at']));
        
        echo "<div class='alert alert-light border {$borderClass} mb-3' role='alert'>
                <div class='d-flex align-items-start'>
                  <i class='bi {$iconClass} fs-4 me-3'></i>
                  <div class='flex-grow-1'>
                    <h6 class='alert-heading mb-1'>
                      <span class='badge {$badgeClass}'>" . htmlspecialchars($row['status']) . "</span>
                    </h6>
                    <p class='mb-1'>
                      Booking test drive untuk <strong>" . htmlspecialchars($row['merk_mobil']) . "</strong> telah " . strtolower($row['status']) . ".
                    </p>
                    <small class='text-muted'>
                      <i class='bi bi-clock'></i> " . htmlspecialchars($tanggal) . "
                    </small>
                  </div>
                </div>
              </div>";
    }
    
    echo "<div class='text-center mt-3'>
            <a href='riwayat-booking.php' class='btn btn-sm btn-outline-primary'>
              <i class='bi bi-list-ul'></i> Lihat Semua Riwayat
            </a>
          </div>";
}

// Tutup statement dan koneksi
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>