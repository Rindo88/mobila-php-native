<?php
require './config/db.php';
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}

// Update status dibaca_user ketika halaman riwayat diakses
if (isset($_SESSION['user_id'])) {
    $update_sql = "UPDATE booking_test_drive SET dibaca_user = 1 WHERE email = ? AND status IN ('Disetujui', 'Ditolak')";
    $stmt_update = $conn->prepare($update_sql);
    $stmt_update->bind_param("s", $email_user);
    $stmt_update->execute();
    $stmt_update->close();
}

$email_user = $_SESSION['email'];

// DEBUG: Tampilkan email user
// echo "<!-- Debug: Email user: " . htmlspecialchars($email_user) . " -->";

// Hitung notifikasi baru
$sql_notif = "SELECT COUNT(*) as jumlah_baru 
              FROM booking_test_drive 
              WHERE email = ? 
              AND status IN ('Disetujui', 'Ditolak') 
              AND (dibaca_user = 0 OR dibaca_user IS NULL)";
$notif_stmt = $conn->prepare($sql_notif);
$notif_stmt->bind_param("s", $email_user);
$notif_stmt->execute();
$notif_result = $notif_stmt->get_result();
$notif_data = $notif_result->fetch_assoc();
$jumlah_notif_baru = $notif_data['jumlah_baru'] ?? 0;
$notif_stmt->close();

// Update notifikasi setelah dihitung
if ($jumlah_notif_baru > 0) {
    $update = "UPDATE booking_test_drive 
               SET dibaca_user = 1 
               WHERE email = ? 
               AND status IN ('Disetujui', 'Ditolak') 
               AND (dibaca_user = 0 OR dibaca_user IS NULL)";
    $update_stmt = $conn->prepare($update);
    $update_stmt->bind_param("s", $email_user);
    $update_stmt->execute();
    $update_stmt->close();
}

// Query sederhana dulu - tanpa join
$sql = "SELECT b.*, m.nama_mobil, m.bahan_bakar, m.transmisi
        FROM booking_test_drive b 
        LEFT JOIN mobil m ON b.id_mobil = m.id_mobil 
        WHERE b.email = ? 
        ORDER BY b.created_at DESC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}

$stmt->bind_param("s", $email_user);

if (!$stmt->execute()) {
    die("Error executing statement: " . $stmt->error);
}

$result = $stmt->get_result();

// DEBUG: Hitung total rows
$total_rows = $result->num_rows;
// echo "<!-- Debug: Total rows: " . $total_rows . " -->";

// Fungsi untuk mendapatkan gambar utama mobil
function getMobilGambar($conn, $id_mobil) {
    if (empty($id_mobil)) return 'assets/img/default-car.jpg';
    
    $sql_gambar = "SELECT gambar FROM gambar_mobil WHERE id_mobil = ? LIMIT 1";
    $stmt_gambar = $conn->prepare($sql_gambar);
    if (!$stmt_gambar) {
        return 'assets/img/default-car.jpg';
    }
    
    $stmt_gambar->bind_param("i", $id_mobil);
    $stmt_gambar->execute();
    $result_gambar = $stmt_gambar->get_result();
    
    if ($result_gambar->num_rows > 0) {
        $row_gambar = $result_gambar->fetch_assoc();
        return 'uploads/' . $row_gambar['gambar'];
    }
    return 'assets/img/default-car.jpg';
}

// Fungsi untuk format status
function getStatusBadge($status) {
    $status = strtolower($status);
    switch($status) {
        case 'disetujui':
            return '<span class="status-badge bg-green-100 text-green-800 border border-green-200"><i class="fas fa-check-circle mr-1.5"></i> Disetujui</span>';
        case 'ditolak':
            return '<span class="status-badge bg-red-100 text-red-800 border border-red-200"><i class="fas fa-times-circle mr-1.5"></i> Ditolak</span>';
        case 'pending':
        case 'menunggu':
            return '<span class="status-badge bg-yellow-100 text-yellow-800 border border-yellow-200"><i class="fas fa-clock mr-1.5"></i> Menunggu</span>';
        default:
            return '<span class="status-badge bg-gray-100 text-gray-800 border border-gray-200"><i class="fas fa-question-circle mr-1.5"></i> ' . ucfirst($status) . '</span>';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Riwayat Booking - Showroom</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 text-gray-800">
  <!-- Header -->
  <header class="bg-white shadow-sm">
    <div class="max-w-6xl mx-auto px-4 py-4">
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Riwayat Booking</h1>
          <p class="text-gray-600 text-sm mt-1">Kelola permintaan test drive Anda</p>
        </div>
        <div class="flex items-center space-x-4">
          <a href="pengguna.php" class="text-blue-600 hover:text-blue-800 font-medium">
            <i class="fas fa-car mr-1"></i> Lihat Mobil
          </a>
          <a href="logout.php" class="text-gray-600 hover:text-gray-800 font-medium">
            <i class="fas fa-sign-out-alt mr-1"></i> Logout
          </a>
        </div>
      </div>
    </div>
  </header>

  <div class="max-w-6xl mx-auto py-8 px-4">
    <!-- Debug Info -->
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
      <div class="flex items-center">
        <i class="fas fa-info-circle text-yellow-600 mr-2"></i>
        <div>
          <strong>Debug Info:</strong> 
          Total booking: <?php echo $total_rows; ?> | 
          Notifikasi baru: <?php echo $jumlah_notif_baru; ?> |
          Email: <?php echo htmlspecialchars($email_user); ?>
        </div>
      </div>
    </div>

    <!-- Statistik Header -->
    <div class="mb-8 bg-white rounded-xl shadow-sm p-6">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
          <h2 class="text-xl font-semibold text-gray-900">Ringkasan Booking</h2>
          <p class="text-gray-600 mt-1">Total <?php echo $total_rows; ?> booking test drive</p>
        </div>
        <div class="mt-4 md:mt-0 flex space-x-4">
          <div class="text-center">
            <div class="text-2xl font-bold text-blue-600"><?php echo $total_rows; ?></div>
            <div class="text-sm text-gray-600">Total</div>
          </div>
          <?php if ($jumlah_notif_baru > 0): ?>
          <div class="text-center relative">
            <div class="text-2xl font-bold text-red-600"><?php echo $jumlah_notif_baru; ?></div>
            <div class="text-sm text-gray-600">Update Baru</div>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <?php if ($result->num_rows > 0): ?>
      <div class="grid grid-cols-1 gap-5">
        <?php 
        $counter = 0;
        while ($row = $result->fetch_assoc()): 
          $status = $row['status'];
          $waktu_booking = date('d M Y, H:i', strtotime($row['waktu_testdrive']));
          $created_at = date('d M Y H:i', strtotime($row['created_at']));
          $gambar_mobil = getMobilGambar($conn, $row['id_mobil']);
          
          $is_new = ($counter < $jumlah_notif_baru && in_array($status, ['Disetujui', 'Ditolak']));
          $counter++;
        ?>
          <div class="bg-white rounded-xl shadow-md overflow-hidden border-l-4 <?php echo $is_new ? 'border-blue-500' : 'border-transparent'; ?>">
            <?php if ($is_new): ?>
            <div class="bg-blue-50 py-2 px-4 border-b border-blue-100">
              <div class="flex items-center text-blue-700">
                <i class="fas fa-bell mr-2"></i>
                <span class="text-sm font-medium">Update baru pada booking Anda!</span>
              </div>
            </div>
            <?php endif; ?>
            
            <div class="p-5 flex flex-col md:flex-row">
              <div class="flex-shrink-0 mb-4 md:mb-0 md:mr-5">
                <div class="w-24 h-24 bg-gray-200 rounded-lg overflow-hidden flex items-center justify-center relative">
                  <?php if (!empty($gambar_mobil) && $gambar_mobil != 'assets/img/default-car.jpg'): ?>
                    <img src="<?= htmlspecialchars($gambar_mobil) ?>" alt="<?= htmlspecialchars($row['nama_mobil'] ?? 'Mobil') ?>" class="w-full h-full object-cover">
                  <?php else: ?>
                    <i class="fas fa-car text-gray-400 text-2xl"></i>
                  <?php endif; ?>
                </div>
              </div>
              
              <div class="flex-grow">
                <div class="flex flex-col md:flex-row md:justify-between md:items-start">
                  <div>
                    <h3 class="text-lg font-semibold text-gray-900">
                      <?= htmlspecialchars($row['nama_mobil'] ?? 'Mobil Tidak Ditemukan') ?>
                    </h3>
                    <div class="flex items-center mt-2 text-gray-600">
                      <i class="far fa-calendar-plus text-sm mr-2"></i>
                      <span class="text-sm">Dibuat: <?= $created_at ?></span>
                    </div>
                  </div>
                  
                  <div class="mt-3 md:mt-0">
                    <?php echo getStatusBadge($status); ?>
                  </div>
                </div>
                
                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                  <div class="flex items-center">
                    <div class="bg-blue-50 p-2 rounded-lg">
                      <i class="fas fa-calendar-day text-blue-600"></i>
                    </div>
                    <div class="ml-3">
                      <p class="text-xs text-gray-500">Tanggal Test Drive</p>
                      <p class="text-sm font-medium"><?= $waktu_booking ?></p>
                    </div>
                  </div>
                  
                  <div class="flex items-center">
                    <div class="bg-blue-50 p-2 rounded-lg">
                      <i class="fas fa-user text-blue-600"></i>
                    </div>
                    <div class="ml-3">
                      <p class="text-xs text-gray-500">Nama Pemesan</p>
                      <p class="text-sm font-medium"><?= htmlspecialchars($row['nama_lengkap']) ?></p>
                    </div>
                  </div>

                  <div class="flex items-center">
                    <div class="bg-blue-50 p-2 rounded-lg">
                      <i class="fas fa-phone text-blue-600"></i>
                    </div>
                    <div class="ml-3">
                      <p class="text-xs text-gray-500">Telepon</p>
                      <p class="text-sm font-medium"><?= htmlspecialchars($row['no_hp']) ?></p>
                    </div>
                  </div>

                  <div class="flex items-center">
                    <div class="bg-blue-50 p-2 rounded-lg">
                      <i class="fas fa-map-marker-alt text-blue-600"></i>
                    </div>
                    <div class="ml-3">
                      <p class="text-xs text-gray-500">Lokasi</p>
                      <p class="text-sm font-medium"><?= htmlspecialchars($row['kota']) ?></p>
                    </div>
                  </div>
                </div>
                
                <!-- Debug info untuk setiap row -->
                <div class="mt-3 p-2 bg-gray-100 rounded text-xs">
                  <strong>Debug:</strong> 
                  ID Booking: <?= $row['id_booking'] ?> | 
                  Mobil ID: <?= $row['id_mobil'] ?> | 
                  Status: <?= $row['status'] ?> |
                  Dibaca: <?= $row['dibaca_user'] ?>
                </div>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    <?php else: ?>
      <div class="bg-white rounded-xl shadow-sm p-8 text-center">
        <div class="max-w-md mx-auto">
          <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-100 text-blue-600 rounded-full mb-5">
            <i class="fas fa-calendar-times text-xl"></i>
          </div>
          <h3 class="text-xl font-semibold text-gray-800 mb-2">Belum ada riwayat booking</h3>
          <p class="text-gray-600 mb-6">Data tidak ditemukan untuk email: <?= htmlspecialchars($email_user) ?></p>
          <a href="pengguna.php" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
            <i class="fas fa-car mr-2"></i> Jelajahi Mobil
          </a>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <?php
  // Tutup koneksi database
  if (isset($stmt)) $stmt->close();
  $conn->close();
  ?>
</body>
</html>