<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$query = "
  SELECT r.*, m.nama_mobil 
  FROM review r
  JOIN mobil m ON r.mobil_id = m.id_mobil
  ORDER BY r.created_at DESC
";
$result = $conn->query($query);
$total_reviews = $result->num_rows;

// Query untuk statistik
$avg_rating = $conn->query("SELECT AVG(rating) as avg_rating FROM review")->fetch_assoc()['avg_rating'];
$today_reviews = $conn->query("SELECT COUNT(*) as count FROM review WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['count'];
$five_star_reviews = $conn->query("SELECT COUNT(*) as count FROM review WHERE rating = 5")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Data Review - AdminMobil</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
    .sidebar-link {
      transition: all 0.3s ease;
      position: relative;
    }
    .sidebar-link:hover {
      transform: translateX(5px);
    }
    .sidebar-link.active {
      background-color: rgba(255, 255, 255, 0.1);
      border-left: 4px solid white;
    }
    .stat-card {
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .stat-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }
    .table-row-hover:hover {
      background-color: #f8fafc;
      transition: all 0.2s ease;
    }
    .review-comment {
      max-width: 300px;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
    .rating-stars {
      display: inline-flex;
      align-items: center;
    }
  </style>
</head>
<body class="bg-gray-50 min-h-screen">
  <div class="flex min-h-screen">
    <!-- Sidebar -->
    <aside class="w-64 bg-gradient-to-b from-blue-800 to-blue-900 text-white flex flex-col sticky top-0 h-screen">
      <div class="p-6 text-2xl font-bold border-b border-blue-700 flex items-center">
        <i class="fas fa-car mr-3 text-blue-300"></i>
        <span>AdminMobil</span>
      </div>
      <nav class="flex-1 p-4 space-y-2 mt-4">
        <a href="dashboardAdmin.php" class="sidebar-link flex items-center px-4 py-3 rounded-lg hover:bg-blue-700">
          <i class="fas fa-tachometer-alt mr-3 text-blue-300"></i>
          <span>Dashboard</span>
        </a>
        <a href="dataPengguna.php" class="sidebar-link flex items-center px-4 py-3 rounded-lg hover:bg-blue-700">
          <i class="fas fa-users mr-3 text-blue-300"></i>
          <span>Data Pengguna</span>
        </a>
        <a href="dataMobil.php" class="sidebar-link flex items-center px-4 py-3 rounded-lg hover:bg-blue-700">
          <i class="fas fa-car mr-3 text-blue-300"></i>
          <span>Data Mobil</span>
        </a>
        <a href="dataBooking.php" class="sidebar-link flex items-center px-4 py-3 rounded-lg hover:bg-blue-700">
          <i class="fas fa-calendar-alt mr-3 text-blue-300"></i>
          <span>Data Booking</span>
        </a>
        <a href="dataReview.php" class="sidebar-link flex items-center px-4 py-3 rounded-lg bg-blue-700 active">
          <i class="fas fa-comment-alt mr-3 text-blue-300"></i>
          <span>Data Review</span>
        </a>
        <a href="dataBerita.php" class="sidebar-link flex items-center px-4 py-3 rounded-lg hover:bg-blue-700">
          <i class="fas fa-newspaper mr-3 text-blue-300"></i>
          <span>Data Berita</span>
        </a>
      </nav>
      <div class="p-4 border-t border-blue-700">
        <div class="flex items-center">
          <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center">
            <i class="fas fa-user text-white"></i>
          </div>
          <div class="ml-3">
            <p class="text-sm font-medium"><?= htmlspecialchars($_SESSION['admin_username']) ?></p>
            <p class="text-xs text-blue-300">Administrator</p>
          </div>
        </div>
      </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col">
      <!-- Header -->
      <header class="bg-white p-4 shadow-sm flex justify-between items-center mb-6">
        <div>
          <h1 class="text-xl font-bold text-gray-900">Data Review</h1>
          <p class="text-sm text-gray-600">Kelola ulasan dan rating dari pelanggan</p>
        </div>
        <div class="flex items-center space-x-4">
          <div class="relative">
            <input type="text" placeholder="Cari review..." class="pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent w-64">
            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
          </div>
          <div class="relative group">
            <button class="flex items-center space-x-2 focus:outline-none">
              <div class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold">
                <?= strtoupper(substr(htmlspecialchars($_SESSION['admin_username']), 0, 1)) ?>
              </div>
            </button>
          </div>
          <button id="logoutBtn" class="px-4 py-2 rounded-lg bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-medium transition-all flex items-center">
            <i class="fas fa-sign-out-alt mr-2"></i>
            Logout
          </button>
        </div>
      </header>

      <!-- Content -->
      <main class="p-6 overflow-auto">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
          <div class="stat-card bg-white p-4 rounded-lg shadow-sm border-l-4 border-blue-500">
            <div class="flex justify-between items-center">
              <div>
                <p class="text-sm text-gray-500">Total Review</p>
                <p class="text-2xl font-bold text-gray-800"><?= $total_reviews ?></p>
              </div>
              <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                <i class="fas fa-comments"></i>
              </div>
            </div>
          </div>
          <div class="stat-card bg-white p-4 rounded-lg shadow-sm border-l-4 border-yellow-500">
            <div class="flex justify-between items-center">
              <div>
                <p class="text-sm text-gray-500">Rating Rata-rata</p>
                <p class="text-2xl font-bold text-gray-800"><?= number_format($avg_rating, 1) ?>/5</p>
              </div>
              <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                <i class="fas fa-star"></i>
              </div>
            </div>
          </div>
          <div class="stat-card bg-white p-4 rounded-lg shadow-sm border-l-4 border-green-500">
            <div class="flex justify-between items-center">
              <div>
                <p class="text-sm text-gray-500">5 Bintang</p>
                <p class="text-2xl font-bold text-gray-800"><?= $five_star_reviews ?></p>
              </div>
              <div class="p-3 rounded-full bg-green-100 text-green-600">
                <i class="fas fa-star"></i>
              </div>
            </div>
          </div>
          <div class="stat-card bg-white p-4 rounded-lg shadow-sm border-l-4 border-purple-500">
            <div class="flex justify-between items-center">
              <div>
                <p class="text-sm text-gray-500">Hari Ini</p>
                <p class="text-2xl font-bold text-gray-800"><?= $today_reviews ?></p>
              </div>
              <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                <i class="fas fa-calendar-day"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Main Content Card -->
        <div class="bg-white shadow-lg rounded-xl overflow-hidden">
          <div class="p-6 border-b border-gray-200">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
              <div>
                <h2 class="text-xl font-bold text-gray-800">Daftar Review</h2>
                <p class="text-gray-600 mt-1">Kelola semua ulasan dan rating dari pelanggan</p>
              </div>
              <div class="mt-4 md:mt-0 flex space-x-3">
                <!-- <button class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors flex items-center">
                  <i class="fas fa-filter mr-2"></i>
                  Filter
                </button>
                <button class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors flex items-center">
                  <i class="fas fa-download mr-2"></i>
                  Export
                </button>
                <button class="px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all flex items-center shadow-md">
                  <i class="fas fa-sync-alt mr-2"></i>
                  Refresh
                </button> -->
              </div>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
              <thead class="bg-gradient-to-r from-blue-600 to-blue-700 text-white">
                <tr>
                  <th class="p-4 font-semibold">#</th>
                  <th class="p-4 font-semibold">Pelanggan</th>
                  <th class="p-4 font-semibold">Mobil</th>
                  <th class="p-4 font-semibold">Komentar</th>
                  <th class="p-4 font-semibold">Rating</th>
                  <th class="p-4 font-semibold">Tanggal</th>
                  <!-- <th class="p-4 font-semibold text-center">Aksi</th> -->
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <?php if ($result->num_rows > 0): ?>
                  <?php 
                  $no = 1; 
                  $result->data_seek(0);
                  while ($row = $result->fetch_assoc()): 
                    $rating = (int) $row['rating'];
                    $created_date = date('d M Y', strtotime($row['created_at']));
                    $created_time = date('H:i', strtotime($row['created_at']));
                  ?>
                    <tr class="table-row-hover">
                      <td class="p-4 font-medium text-gray-700"><?= $no++ ?></td>
                      <td class="p-4">
                        <div class="flex items-center">
                          <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold mr-3">
                            <?= strtoupper(substr($row['nama'], 0, 1)) ?>
                          </div>
                          <div>
                            <div class="font-semibold text-gray-800"><?= htmlspecialchars($row['nama']) ?></div>
                            <div class="text-xs text-gray-500">Review ID: <?= $row['id'] ?></div>
                          </div>
                        </div>
                      </td>
                      <td class="p-4">
                        <div class="font-medium text-gray-800"><?= htmlspecialchars($row['nama_mobil']) ?></div>
                      </td>
                      <td class="p-4">
                        <div class="review-comment text-gray-600" title="<?= htmlspecialchars($row['komentar']) ?>">
                          <?= nl2br(htmlspecialchars($row['komentar'])) ?>
                        </div>
                      </td>
                      <td class="p-4">
                        <div class="flex items-center space-x-2">
                          <div class="rating-stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                              <?php if ($i <= $rating): ?>
                                <i class="fas fa-star text-yellow-400 text-sm"></i>
                              <?php else: ?>
                                <i class="far fa-star text-gray-300 text-sm"></i>
                              <?php endif; ?>
                            <?php endfor; ?>
                          </div>
                          <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded-full text-xs font-semibold">
                            <?= $rating ?>.0
                          </span>
                        </div>
                      </td>
                      <td class="p-4">
                        <div class="text-gray-600">
                          <div class="font-medium"><?= $created_date ?></div>
                          <div class="text-xs text-gray-500"><?= $created_time ?></div>
                        </div>
                      </td>
                      <!-- <td class="p-4">
                        <div class="flex justify-center space-x-2">
                          <button class="bg-blue-100 text-blue-700 p-2 rounded-lg hover:bg-blue-200 transition-colors action-btn" title="Lihat Detail">
                            <i class="fas fa-eye"></i>
                          </button>
                          <button class="bg-red-100 text-red-700 p-2 rounded-lg hover:bg-red-200 transition-colors action-btn" title="Hapus Review">
                            <i class="fas fa-trash-alt"></i>
                          </button>
                        </div>
                      </td> -->
                    </tr>
                  <?php endwhile; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="7" class="text-center p-8">
                      <div class="flex flex-col items-center justify-center text-gray-500">
                        <i class="fas fa-comment-slash text-4xl mb-3 text-gray-300"></i>
                        <p class="text-lg font-medium">Belum ada data review</p>
                        <p class="text-sm">Review dari pelanggan akan muncul di sini</p>
                      </div>
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <?php if ($result->num_rows > 0): ?>
          <div class="p-4 border-t border-gray-200 bg-gray-50">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
              <div class="text-sm text-gray-600 mb-4 md:mb-0">
                Menampilkan <span class="font-semibold">1-<?= $total_reviews ?></span> dari <span class="font-semibold"><?= $total_reviews ?></span> review
              </div>
              <div class="flex space-x-1">
                <button class="px-3 py-1 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 transition-colors">
                  <i class="fas fa-chevron-left"></i>
                </button>
                <button class="px-3 py-1 rounded-lg bg-blue-600 text-white">1</button>
                <button class="px-3 py-1 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 transition-colors">
                  <i class="fas fa-chevron-right"></i>
                </button>
              </div>
            </div>
          </div>
          <?php endif; ?>
        </div>
      </main>
    </div>
  </div>

  <!-- SweetAlert Logout -->
  <script>
    document.getElementById("logoutBtn").addEventListener("click", function (e) {
      e.preventDefault();
      Swal.fire({
        title: 'Konfirmasi Logout',
        text: "Apakah Anda yakin ingin keluar?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Logout',
        cancelButtonText: 'Batal',
        customClass: {
          popup: 'rounded-xl',
          confirmButton: 'rounded-lg',
          cancelButton: 'rounded-lg'
        }
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = "logout.php";
        }
      });
    });

    // Search functionality
    document.querySelector('input[type="text"]').addEventListener('input', function(e) {
      const searchTerm = e.target.value.toLowerCase();
      const rows = document.querySelectorAll('tbody tr');
      
      rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    });

    // Action buttons hover effect
    document.querySelectorAll('.action-btn').forEach(btn => {
      btn.addEventListener('mouseenter', function() {
        this.style.transform = 'scale(1.1)';
      });
      btn.addEventListener('mouseleave', function() {
        this.style.transform = 'scale(1)';
      });
    });

    // Show full comment on click
    document.querySelectorAll('.review-comment').forEach(comment => {
      comment.addEventListener('click', function() {
        const fullText = this.getAttribute('title');
        if (fullText) {
          Swal.fire({
            title: 'Komentar Lengkap',
            text: fullText,
            icon: 'info',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Tutup',
            customClass: {
              popup: 'rounded-xl'
            }
          });
        }
      });
    });
  </script>
</body>
</html>