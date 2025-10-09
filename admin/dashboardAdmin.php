<?php
session_start();
include '../config/db.php';

// Cek login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Ambil data dari database
$total_user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users"))['total'];
$total_mobil = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM mobil"))['total'];
$total_berita = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM berita"))['total'];
$total_review = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM review"))['total'];
$total_booking_tes_drive = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM booking_test_drive"))['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }
    .chart-container {
      background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    }
  </style>
</head>
<body class="bg-gray-50 min-h-screen">
  <div class="flex">
    <!-- Sidebar -->
    <aside class="w-64 bg-gradient-to-b from-blue-800 to-blue-900 text-white min-h-screen sticky top-0">
      <div class="p-6 text-2xl font-bold border-b border-blue-700 flex items-center">
        <i class="fas fa-car mr-3 text-blue-300"></i>
        <span>AdminMobil</span>
      </div>
      <nav class="p-4 space-y-2 mt-4">
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
        <a href="dataReview.php" class="sidebar-link flex items-center px-4 py-3 rounded-lg hover:bg-blue-700">
          <i class="fas fa-comment-alt mr-3 text-blue-300"></i>
          <span>Data Review</span>
        </a>
        <a href="dataBerita.php" class="sidebar-link flex items-center px-4 py-3 rounded-lg hover:bg-blue-700">
          <i class="fas fa-newspaper mr-3 text-blue-300"></i>
          <span>Data Berita</span>
        </a>
      </nav>
      <div class="absolute bottom-0 w-full p-4 border-t border-blue-700">
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
    <main class="flex-1 p-6">
      <!-- Header -->
      <header class="bg-white p-4 shadow rounded-lg flex justify-between items-center mb-6">
        <div>
          <h1 class="text-xl font-bold text-gray-800">Dashboard Admin</h1>
          <p class="text-sm text-gray-500">Selamat datang di panel administrasi</p>
        </div>
        <div class="flex items-center space-x-4">
          <div class="relative">
            <input type="text" placeholder="Search..." class="pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
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

      <!-- Dynamic Panels -->
      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-6">
        <div class="stat-card bg-white p-5 shadow rounded-lg border-l-4 border-blue-500">
          <div class="flex justify-between items-start">
            <div>
              <h3 class="text-sm font-medium text-gray-500">Total Pengguna</h3>
              <p class="text-2xl font-bold text-gray-800 mt-2"><?= $total_user ?></p>
            </div>
            <div class="p-3 rounded-full bg-blue-100 text-blue-600">
              <i class="fas fa-users text-lg"></i>
            </div>
          </div>
          <div class="mt-4 flex items-center text-sm text-gray-500">
            <i class="fas fa-arrow-up text-green-500 mr-1"></i>
            <span>12% dari bulan lalu</span>
          </div>
        </div>
        
        <div class="stat-card bg-white p-5 shadow rounded-lg border-l-4 border-green-500">
          <div class="flex justify-between items-start">
            <div>
              <h3 class="text-sm font-medium text-gray-500">Total Mobil</h3>
              <p class="text-2xl font-bold text-gray-800 mt-2"><?= $total_mobil ?></p>
            </div>
            <div class="p-3 rounded-full bg-green-100 text-green-600">
              <i class="fas fa-car text-lg"></i>
            </div>
          </div>
          <div class="mt-4 flex items-center text-sm text-gray-500">
            <i class="fas fa-arrow-up text-green-500 mr-1"></i>
            <span>5% dari bulan lalu</span>
          </div>
        </div>
        
        <div class="stat-card bg-white p-5 shadow rounded-lg border-l-4 border-yellow-500">
          <div class="flex justify-between items-start">
            <div>
              <h3 class="text-sm font-medium text-gray-500">Total Berita</h3>
              <p class="text-2xl font-bold text-gray-800 mt-2"><?= $total_berita ?></p>
            </div>
            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
              <i class="fas fa-newspaper text-lg"></i>
            </div>
          </div>
          <div class="mt-4 flex items-center text-sm text-gray-500">
            <i class="fas fa-arrow-up text-green-500 mr-1"></i>
            <span>8% dari bulan lalu</span>
          </div>
        </div>
        
        <div class="stat-card bg-white p-5 shadow rounded-lg border-l-4 border-purple-500">
          <div class="flex justify-between items-start">
            <div>
              <h3 class="text-sm font-medium text-gray-500">Total Booking Test Drive</h3>
              <p class="text-2xl font-bold text-gray-800 mt-2"><?= $total_booking_tes_drive ?></p>
            </div>
            <div class="p-3 rounded-full bg-purple-100 text-purple-600">
              <i class="fas fa-calendar-check text-lg"></i>
            </div>
          </div>
          <div class="mt-4 flex items-center text-sm text-gray-500">
            <i class="fas fa-arrow-up text-green-500 mr-1"></i>
            <span>15% dari bulan lalu</span>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Activities -->
        <div class="bg-white p-6 shadow rounded-lg">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-bold text-gray-800">Aktivitas Terbaru</h2>
            <a href="#" class="text-sm text-blue-600 hover:text-blue-800">Lihat Semua</a>
          </div>
          <div class="space-y-4">
            <div class="flex items-start">
              <div class="p-2 rounded-full bg-blue-100 text-blue-600 mr-3">
                <i class="fas fa-user-plus"></i>
              </div>
              <div>
                <p class="text-sm font-medium">Pengguna baru terdaftar</p>
                <p class="text-xs text-gray-500">2 menit yang lalu</p>
              </div>
            </div>
            <div class="flex items-start">
              <div class="p-2 rounded-full bg-green-100 text-green-600 mr-3">
                <i class="fas fa-car"></i>
              </div>
              <div>
                <p class="text-sm font-medium">Mobil baru ditambahkan</p>
                <p class="text-xs text-gray-500">1 jam yang lalu</p>
              </div>
            </div>
            <div class="flex items-start">
              <div class="p-2 rounded-full bg-yellow-100 text-yellow-600 mr-3">
                <i class="fas fa-calendar-alt"></i>
              </div>
              <div>
                <p class="text-sm font-medium">Booking test drive baru</p>
                <p class="text-xs text-gray-500">3 jam yang lalu</p>
              </div>
            </div>
            <div class="flex items-start">
              <div class="p-2 rounded-full bg-purple-100 text-purple-600 mr-3">
                <i class="fas fa-comment"></i>
              </div>
              <div>
                <p class="text-sm font-medium">Review baru diterima</p>
                <p class="text-xs text-gray-500">5 jam yang lalu</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Stats -->
        <div class="bg-white p-6 shadow rounded-lg">
          <h2 class="text-lg font-bold text-gray-800 mb-4">Statistik Cepat</h2>
          <div class="grid grid-cols-2 gap-4">
            <div class="bg-blue-50 p-4 rounded-lg">
              <div class="flex items-center">
                <div class="p-2 rounded-full bg-blue-100 text-blue-600 mr-3">
                  <i class="fas fa-star"></i>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Rating Rata-rata</p>
                  <p class="text-lg font-bold">4.7/5</p>
                </div>
              </div>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
              <div class="flex items-center">
                <div class="p-2 rounded-full bg-green-100 text-green-600 mr-3">
                  <i class="fas fa-check-circle"></i>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Booking Selesai</p>
                  <p class="text-lg font-bold"><?= $total_booking_tes_drive - 5 ?></p>
                </div>
              </div>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg">
              <div class="flex items-center">
                <div class="p-2 rounded-full bg-yellow-100 text-yellow-600 mr-3">
                  <i class="fas fa-clock"></i>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Pending Review</p>
                  <p class="text-lg font-bold">12</p>
                </div>
              </div>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg">
              <div class="flex items-center">
                <div class="p-2 rounded-full bg-purple-100 text-purple-600 mr-3">
                  <i class="fas fa-eye"></i>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Pengunjung Hari Ini</p>
                  <p class="text-lg font-bold">245</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>

  <script>
    document.getElementById('logoutBtn').addEventListener('click', function(e) {
      e.preventDefault();
      Swal.fire({
        title: 'Konfirmasi Logout',
        text: "Anda yakin ingin keluar?",
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

    // Add active class to current page link
    document.addEventListener('DOMContentLoaded', function() {
      const currentPage = window.location.pathname.split('/').pop();
      const links = document.querySelectorAll('.sidebar-link');
      
      links.forEach(link => {
        if (link.getAttribute('href') === currentPage) {
          link.classList.add('active');
        }
      });
    });
  </script>
</body>
</html>