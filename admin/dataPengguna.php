<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$result = $conn->query("SELECT * FROM users");
$total_users = $result->num_rows;

// Query untuk statistik tambahan - disesuaikan dengan kolom yang ada
$today_users = $conn->query("SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['count'];
$new_this_month = $conn->query("SELECT COUNT(*) as count FROM users WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())")->fetch_assoc()['count'];

// Untuk active users, kita asumsikan berdasarkan created_at (karena tidak ada last_login)
$active_users = $conn->query("SELECT COUNT(*) as count FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Data Pengguna - AdminMobil</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
    .action-btn {
      transition: all 0.2s ease;
    }
    .action-btn:hover {
      transform: scale(1.1);
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
        <a href="dataPengguna.php" class="sidebar-link flex items-center px-4 py-3 rounded-lg bg-blue-700 active">
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
        <a href="dataReview.php" class="sidebar-link flex items-center px-4 py-3 rounded-lg hover:bg-blue-700">
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
          <h1 class="text-xl font-bold text-gray-900">Data Pengguna</h1>
          <p class="text-sm text-gray-600">Kelola data pengguna yang terdaftar</p>
        </div>
        <div class="flex items-center space-x-4">
          <div class="relative">
            <input type="text" placeholder="Cari pengguna..." class="pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent w-64">
            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
          </div>
          <div class="relative group">
            <button class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 hover:bg-blue-200 transition-colors">
              <i class="fas fa-bell"></i>
            </button>
            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">3</span>
          </div>
          <div class="relative group">
            <button class="flex items-center space-x-2 focus:outline-none">
              <div class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold">
                <?= strtoupper(substr(htmlspecialchars($_SESSION['admin_username']), 0, 1)) ?>
              </div>
            </button>
          </div>
          <button id="btn-logout" class="px-4 py-2 rounded-lg bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-medium transition-all flex items-center">
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
                <p class="text-sm text-gray-500">Total Pengguna</p>
                <p class="text-2xl font-bold text-gray-800"><?= $total_users ?></p>
              </div>
              <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                <i class="fas fa-users"></i>
              </div>
            </div>
          </div>
          <div class="stat-card bg-white p-4 rounded-lg shadow-sm border-l-4 border-green-500">
            <div class="flex justify-between items-center">
              <div>
                <p class="text-sm text-gray-500">Hari Ini</p>
                <p class="text-2xl font-bold text-gray-800"><?= $today_users ?></p>
              </div>
              <div class="p-3 rounded-full bg-green-100 text-green-600">
                <i class="fas fa-user-plus"></i>
              </div>
            </div>
          </div>
          <div class="stat-card bg-white p-4 rounded-lg shadow-sm border-l-4 border-yellow-500">
            <div class="flex justify-between items-center">
              <div>
                <p class="text-sm text-gray-500">Baru (30 hari)</p>
                <p class="text-2xl font-bold text-gray-800"><?= $active_users ?></p>
              </div>
              <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                <i class="fas fa-user-clock"></i>
              </div>
            </div>
          </div>
          <div class="stat-card bg-white p-4 rounded-lg shadow-sm border-l-4 border-purple-500">
            <div class="flex justify-between items-center">
              <div>
                <p class="text-sm text-gray-500">Bulan Ini</p>
                <p class="text-2xl font-bold text-gray-800"><?= $new_this_month ?></p>
              </div>
              <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                <i class="fas fa-chart-line"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Main Content Card -->
        <div class="bg-white shadow-lg rounded-xl overflow-hidden">
          <div class="p-6 border-b border-gray-200">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
              <div>
                <h2 class="text-xl font-bold text-gray-800">Daftar Pengguna</h2>
                <p class="text-gray-600 mt-1">Kelola semua data pengguna yang terdaftar dalam sistem</p>
              </div>
              <div class="mt-4 md:mt-0 flex space-x-3">
                <button class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors flex items-center">
                  <i class="fas fa-filter mr-2"></i>
                  Filter
                </button>
                <button class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors flex items-center">
                  <i class="fas fa-download mr-2"></i>
                  Export
                </button>
                <button class="px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all flex items-center shadow-md">
                  <i class="fas fa-plus mr-2"></i>
                  Tambah Pengguna
                </button>
              </div>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
              <thead class="bg-gradient-to-r from-blue-600 to-blue-700 text-white">
                <tr>
                  <th class="p-4 font-semibold">#</th>
                  <th class="p-4 font-semibold">Pengguna</th>
                  <th class="p-4 font-semibold">Email</th>
                  <th class="p-4 font-semibold">Tanggal Bergabung</th>
                  <th class="p-4 font-semibold">Dibuat Pada</th>
                  <th class="p-4 font-semibold text-center">Status</th>
                  <!-- <th class="p-4 font-semibold text-center">Aksi</th> -->
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <?php 
                $no = 1; 
                $result->data_seek(0); // Reset pointer
                while ($row = $result->fetch_assoc()): 
                  $join_date = !empty($row['join_date']) ? date('d M Y', strtotime($row['join_date'])) : '-';
                  $created_at = !empty($row['created_at']) ? date('d M Y H:i', strtotime($row['created_at'])) : '-';
                  
                  // Tentukan status berdasarkan created_at (asumsi: user baru dalam 30 hari dianggap aktif)
                  $is_new = strtotime($row['created_at']) >= strtotime('-30 days');
                ?>
                  <tr class="table-row-hover">
                    <td class="p-4 font-medium text-gray-700"><?= $no++ ?></td>
                    <td class="p-4">
                      <div class="flex items-center">
                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold mr-3">
                          <?= strtoupper(substr($row['username'], 0, 1)) ?>
                        </div>
                        <div>
                          <div class="font-semibold text-gray-800"><?= htmlspecialchars($row['username']) ?></div>
                          <div class="text-xs text-gray-500">ID: <?= $row['id'] ?></div>
                        </div>
                      </div>
                    </td>
                    <td class="p-4">
                      <div class="text-gray-800"><?= htmlspecialchars($row['email']) ?></div>
                    </td>
                    <td class="p-4 text-gray-600"><?= $join_date ?></td>
                    <td class="p-4 text-gray-600"><?= $created_at ?></td>
                    <td class="p-4">
                      <div class="flex justify-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $is_new ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                          <i class="fas fa-circle mr-1" style="font-size: 6px; color: <?= $is_new ? '#10B981' : '#9CA3AF' ?>"></i>
                          <?= $is_new ? 'Baru' : 'Lama' ?>
                        </span>
                      </div>
                    </td>
                    <!-- <td class="p-4">
                      <div class="flex justify-center space-x-2">
                        <button class="action-btn bg-blue-100 text-blue-700 p-2 rounded-lg hover:bg-blue-200 transition-colors" title="Edit">
                          <i class="fas fa-edit"></i>
                        </button>
                        <button class="action-btn bg-red-100 text-red-700 p-2 rounded-lg hover:bg-red-200 transition-colors" title="Hapus">
                          <i class="fas fa-trash-alt"></i>
                        </button>
                        <button class="action-btn bg-green-100 text-green-700 p-2 rounded-lg hover:bg-green-200 transition-colors" title="Detail">
                          <i class="fas fa-eye"></i>
                        </button>
                      </div>
                    </td> -->
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div class="p-4 border-t border-gray-200 bg-gray-50">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
              <div class="text-sm text-gray-600 mb-4 md:mb-0">
                Menampilkan <span class="font-semibold">1-<?= $total_users ?></span> dari <span class="font-semibold"><?= $total_users ?></span> pengguna
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
        </div>
      </main>
    </div>
  </div>

  <!-- SweetAlert Logout Script -->
  <script>
    document.getElementById('btn-logout').addEventListener('click', function (e) {
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
          window.location.href = 'logout.php';
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
  </script>
</body>
</html>