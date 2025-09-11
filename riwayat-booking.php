<?php
require './config/db.php';
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['email'])) {
    echo "<p>Anda belum login. Silakan <a href='login.php'>login di sini</a>.</p>";
    exit;
}

// Hindari cache agar status terbaru selalu ditampilkan
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 1 Jan 2000 00:00:00 GMT");

// Ambil data email user dari session
$email_user = $_SESSION['email'];

// Update notifikasi yang belum dibaca menjadi sudah dibaca
$update = "UPDATE booking_test_drive 
           SET dibaca_user = 1 
           WHERE email = ? AND status IN ('Disetujui', 'Ditolak') AND dibaca_user = 0";
$update_stmt = $conn->prepare($update);
$update_stmt->bind_param("s", $email_user);
$update_stmt->execute();

// Ambil data booking dan gabungkan dengan data mobil
// Diperbaiki: menghapus referensi ke m.gambar_mobil yang tidak ada
$sql = "SELECT b.*, m.nama_mobil 
        FROM booking_test_drive b 
        JOIN mobil m ON b.id_mobil = m.id_mobil 
        WHERE b.email = ? 
        ORDER BY b.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email_user);
$stmt->execute();
$result = $stmt->get_result();

// Fungsi untuk mendapatkan gambar utama mobil
function getMobilGambar($conn, $id_mobil) {
    $sql_gambar = "SELECT gambar FROM gambar_mobil WHERE id_mobil = ? LIMIT 1";
    $stmt_gambar = $conn->prepare($sql_gambar);
    $stmt_gambar->bind_param("i", $id_mobil);
    $stmt_gambar->execute();
    $result_gambar = $stmt_gambar->get_result();
    
    if ($result_gambar->num_rows > 0) {
        $row_gambar = $result_gambar->fetch_assoc();
        return $row_gambar['gambar'];
    }
    return null;
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
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
    
    body {
      font-family: 'Inter', sans-serif;
      background-color: #f8fafc;
    }
    
    .status-badge {
      display: inline-flex;
      align-items: center;
      padding: 0.35rem 0.75rem;
      border-radius: 9999px;
      font-size: 0.75rem;
      font-weight: 500;
    }
    
    .card-hover {
      transition: all 0.3s ease;
    }
    
    .card-hover:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
    
    .empty-state {
      background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    }
  </style>
</head>
<body class="bg-gray-50 text-gray-800">

  <div class="max-w-6xl mx-auto py-8 px-4">
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Riwayat Booking Test Drive</h1>
      <p class="text-gray-600 mt-2">Lihat status dan riwayat permintaan test drive Anda</p>
    </div>

    <?php if ($result->num_rows > 0): ?>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-1 gap-5">
        <?php $no = 1; while ($row = $result->fetch_assoc()): 
          $status = strtolower($row['status']);
          $waktu_booking = date('d M Y, H:i', strtotime($row['waktu_testdrive']));
          $created_at = date('d M Y', strtotime($row['created_at']));
          $gambar_mobil = getMobilGambar($conn, $row['id_mobil']);
        ?>
          <div class="bg-white rounded-xl shadow-md overflow-hidden card-hover">
            <div class="p-5 flex flex-col md:flex-row">
              <div class="flex-shrink-0 mb-4 md:mb-0 md:mr-5">
                <div class="w-24 h-24 bg-gray-200 rounded-lg overflow-hidden flex items-center justify-center">
                  <?php if (!empty($gambar_mobil)): ?>
                    <img src="<?= htmlspecialchars($gambar_mobil) ?>" alt="<?= htmlspecialchars($row['nama_mobil']) ?>" class="w-full h-full object-cover">
                  <?php else: ?>
                    <i class="fas fa-car text-gray-400 text-2xl"></i>
                  <?php endif; ?>
                </div>
              </div>
              
              <div class="flex-grow">
                <div class="flex flex-col md:flex-row md:justify-between md:items-start">
                  <div>
                    <h3 class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($row['nama_mobil']) ?></h3>
                    <div class="flex items-center mt-1 text-gray-600">
                      <i class="far fa-calendar-alt text-sm mr-2"></i>
                      <span class="text-sm">Dibuat pada: <?= $created_at ?></span>
                    </div>
                  </div>
                  
                  <div class="mt-3 md:mt-0">
                    <?php
                      if ($status == 'disetujui') {
                        echo '<span class="status-badge bg-green-100 text-green-800"><i class="fas fa-check-circle mr-1.5"></i> Disetujui</span>';
                      } elseif ($status == 'ditolak') {
                        echo '<span class="status-badge bg-red-100 text-red-800"><i class="fas fa-times-circle mr-1.5"></i> Ditolak</span>';
                      } else {
                        echo '<span class="status-badge bg-yellow-100 text-yellow-800"><i class="fas fa-clock mr-1.5"></i> Menunggu</span>';
                      }
                    ?>
                  </div>
                </div>
                
                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
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
                </div>
                
                <?php if (!empty($row['catatan']) && $status == 'ditolak'): ?>
                  <div class="mt-4 p-3 bg-red-50 rounded-lg">
                    <p class="text-xs font-medium text-red-700">Catatan Penolakan:</p>
                    <p class="text-sm text-red-600 mt-1"><?= htmlspecialchars($row['catatan']) ?></p>
                  </div>
                <?php elseif (!empty($row['catatan']) && $status == 'disetujui'): ?>
                  <div class="mt-4 p-3 bg-green-50 rounded-lg">
                    <p class="text-xs font-medium text-green-700">Catatan Persetujuan:</p>
                    <p class="text-sm text-green-600 mt-1"><?= htmlspecialchars($row['catatan']) ?></p>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php $no++; endwhile; ?>
      </div>
    <?php else: ?>
      <div class="empty-state rounded-xl shadow-sm p-8 text-center">
        <div class="max-w-md mx-auto">
          <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-100 text-blue-600 rounded-full mb-5">
            <i class="fas fa-calendar-times text-xl"></i>
          </div>
          <h3 class="text-xl font-semibold text-gray-800 mb-2">Belum ada riwayat booking</h3>
          <p class="text-gray-600 mb-6">Anda belum melakukan booking test drive. Silakan kunjungi halaman mobil untuk melakukan booking.</p>
          <a href="pengguna.php" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
            <i class="fas fa-car mr-2"></i> Jelajahi Mobil
          </a>
        </div>
      </div>
    <?php endif; ?>
  </div>

</body>
</html>