<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Filter tab
$status_filter = '';
$active_tab = $_GET['tab'] ?? 'all';
if ($active_tab === 'publikasi') {
    $status_filter = "WHERE status = 'publikasi'";
} elseif ($active_tab === 'draft') {
    $status_filter = "WHERE status = 'draft'";
}

// Ambil data berita dengan error handling
$query = "SELECT * FROM berita $status_filter ORDER BY id DESC";
$result = mysqli_query($conn, $query);

// Cek jika query berhasil
if ($result) {
    $total_berita = mysqli_num_rows($result);
} else {
    $total_berita = 0;
    // Debug: Tampilkan error query
    error_log("Query error: " . mysqli_error($conn));
}

// Query untuk statistik dengan error handling
$published_count = 0;
$draft_count = 0;
$today_count = 0;

$published_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM berita WHERE status = 'publikasi'");
if ($published_query) {
    $published_count = mysqli_fetch_assoc($published_query)['count'];
}

$draft_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM berita WHERE status = 'draft'");
if ($draft_query) {
    $draft_count = mysqli_fetch_assoc($draft_query)['count'];
}

// Untuk today_count, gunakan tanggal_publikasi jika created_at tidak ada
$today_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM berita WHERE DATE(tanggal_publikasi) = CURDATE()");
if ($today_query) {
    $today_count = mysqli_fetch_assoc($today_query)['count'];
}

// Debug: Tampilkan kolom yang tersedia (opsional)
if ($result && mysqli_num_rows($result) > 0) {
    $first_row = mysqli_fetch_assoc($result);
    mysqli_data_seek($result, 0); // Reset pointer
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Data Berita - AdminMobil</title>
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
    .action-btn {
      transition: all 0.2s ease;
    }
    .action-btn:hover {
      transform: scale(1.1);
    }
    .tab-active {
      border-bottom: 3px solid #3b82f6;
      color: #3b82f6;
      font-weight: 600;
    }
    .news-excerpt {
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
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
        <a href="dataReview.php" class="sidebar-link flex items-center px-4 py-3 rounded-lg hover:bg-blue-700">
          <i class="fas fa-comment-alt mr-3 text-blue-300"></i>
          <span>Data Review</span>
        </a>
        <a href="dataBerita.php" class="sidebar-link flex items-center px-4 py-3 rounded-lg bg-blue-700 active">
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
          <h1 class="text-xl font-bold text-gray-900">Data Berita</h1>
          <p class="text-sm text-gray-600">Kelola berita dan artikel mobil</p>
        </div>
        <div class="flex items-center space-x-4">
          <div class="relative">
            <input type="text" placeholder="Cari berita..." class="pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent w-64">
            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
          </div>
          <div class="relative group">
            <button class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 hover:bg-blue-200 transition-colors">
              <i class="fas fa-bell"></i>
            </button>
            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"><?= $draft_count ?></span>
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
                <p class="text-sm text-gray-500">Total Berita</p>
                <p class="text-2xl font-bold text-gray-800"><?= $total_berita ?></p>
              </div>
              <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                <i class="fas fa-newspaper"></i>
              </div>
            </div>
          </div>
          <div class="stat-card bg-white p-4 rounded-lg shadow-sm border-l-4 border-green-500">
            <div class="flex justify-between items-center">
              <div>
                <p class="text-sm text-gray-500">Terpublikasi</p>
                <p class="text-2xl font-bold text-gray-800"><?= $published_count ?></p>
              </div>
              <div class="p-3 rounded-full bg-green-100 text-green-600">
                <i class="fas fa-check-circle"></i>
              </div>
            </div>
          </div>
          <div class="stat-card bg-white p-4 rounded-lg shadow-sm border-l-4 border-yellow-500">
            <div class="flex justify-between items-center">
              <div>
                <p class="text-sm text-gray-500">Draft</p>
                <p class="text-2xl font-bold text-gray-800"><?= $draft_count ?></p>
              </div>
              <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                <i class="fas fa-edit"></i>
              </div>
            </div>
          </div>
          <div class="stat-card bg-white p-4 rounded-lg shadow-sm border-l-4 border-purple-500">
            <div class="flex justify-between items-center">
              <div>
                <p class="text-sm text-gray-500">Hari Ini</p>
                <p class="text-2xl font-bold text-gray-800"><?= $today_count ?></p>
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
                <h2 class="text-xl font-bold text-gray-800">Daftar Berita</h2>
                <p class="text-gray-600 mt-1">Kelola semua berita dan artikel mobil</p>
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
                <a href="tambahBerita.php" class="px-4 py-2 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg hover:from-green-700 hover:to-green-800 transition-all flex items-center shadow-md">
                  <i class="fas fa-plus mr-2"></i>
                  Tambah Berita
                </a>
              </div>
            </div>
          </div>

          <!-- Tabs -->
          <div class="px-6 pt-4 border-b border-gray-200">
            <div class="flex space-x-8">
              <a href="dataBerita.php" 
                 class="pb-4 px-1 font-medium text-sm border-b-2 transition-colors <?= $active_tab === 'all' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' ?>">
                Semua Berita
                <span class="ml-2 bg-gray-100 text-gray-600 py-0.5 px-2 rounded-full text-xs"><?= $total_berita ?></span>
              </a>
              <a href="dataBerita.php?tab=publikasi" 
                 class="pb-4 px-1 font-medium text-sm border-b-2 transition-colors <?= $active_tab === 'publikasi' ? 'border-green-600 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700' ?>">
                Terpublikasi
                <span class="ml-2 bg-green-100 text-green-600 py-0.5 px-2 rounded-full text-xs"><?= $published_count ?></span>
              </a>
              <a href="dataBerita.php?tab=draft" 
                 class="pb-4 px-1 font-medium text-sm border-b-2 transition-colors <?= $active_tab === 'draft' ? 'border-yellow-600 text-yellow-600' : 'border-transparent text-gray-500 hover:text-gray-700' ?>">
                Draft
                <span class="ml-2 bg-yellow-100 text-yellow-600 py-0.5 px-2 rounded-full text-xs"><?= $draft_count ?></span>
              </a>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
              <thead class="bg-gradient-to-r from-blue-600 to-blue-700 text-white">
                <tr>
                  <th class="p-4 font-semibold">#</th>
                  <th class="p-4 font-semibold">Berita</th>
                  <th class="p-4 font-semibold">Gambar</th>
                  <th class="p-4 font-semibold">Penulis</th>
                  <th class="p-4 font-semibold">Tanggal</th>
                  <th class="p-4 font-semibold">Status</th>
                  <th class="p-4 font-semibold text-center">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <?php if ($result && mysqli_num_rows($result) > 0): ?>
                  <?php 
                  $no = 1; 
                  // Reset pointer result
                  mysqli_data_seek($result, 0);
                  while ($row = mysqli_fetch_assoc($result)): 
                    // Tangani kolom tanggal dengan aman
                    $publish_date = !empty($row['tanggal_publikasi']) ? date('d M Y', strtotime($row['tanggal_publikasi'])) : '-';
                    
                    // Gunakan created_at jika ada, jika tidak gunakan tanggal_publikasi atau tanggal saat ini
                    $created_date = '-';
                    if (!empty($row['created_at'])) {
                        $created_date = date('d M Y', strtotime($row['created_at']));
                    } elseif (!empty($row['tanggal_publikasi'])) {
                        $created_date = date('d M Y', strtotime($row['tanggal_publikasi']));
                    }
                    
                    $excerpt = strip_tags($row['isi'] ?? '');
                    $excerpt = strlen($excerpt) > 100 ? substr($excerpt, 0, 100) . '...' : $excerpt;
                  ?>
                    <tr class="table-row-hover">
                      <td class="p-4 font-medium text-gray-700"><?= $no++ ?></td>
                      <td class="p-4">
                        <div>
                          <div class="font-semibold text-gray-800 text-sm mb-1"><?= htmlspecialchars($row['judul'] ?? 'Judul tidak tersedia') ?></div>
                          <div class="text-gray-600 text-xs news-excerpt"><?= htmlspecialchars($excerpt) ?></div>
                        </div>
                      </td>
                      <td class="p-4">
                        <?php if (!empty($row['gambar'])): ?>
                          <img src="../uploads/<?= htmlspecialchars($row['gambar']) ?>" 
                               alt="Gambar Berita" 
                               class="w-16 h-12 object-cover rounded-lg shadow-sm">
                        <?php else: ?>
                          <div class="w-16 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-image text-gray-400 text-sm"></i>
                          </div>
                        <?php endif; ?>
                      </td>
                      <td class="p-4">
                        <div class="text-gray-800 font-medium"><?= htmlspecialchars($row['penulis'] ?? 'Tidak diketahui') ?></div>
                      </td>
                      <td class="p-4">
                        <div class="text-gray-600">
                          <div class="text-sm font-medium"><?= $publish_date ?></div>
                          <div class="text-xs text-gray-500">Dibuat: <?= $created_date ?></div>
                        </div>
                      </td>
                      <td class="p-4">
                        <?php if (($row['status'] ?? '') === 'publikasi'): ?>
                          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <i class="fas fa-check-circle mr-1 text-xs"></i>
                            Terpublikasi
                          </span>
                        <?php elseif (($row['status'] ?? '') === 'draft'): ?>
                          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            <i class="fas fa-edit mr-1 text-xs"></i>
                            Draft
                          </span>
                        <?php else: ?>
                          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            <i class="fas fa-question-circle mr-1 text-xs"></i>
                            Tidak Diketahui
                          </span>
                        <?php endif; ?>
                      </td>
                      <td class="p-4">
                        <div class="flex justify-center space-x-2">
                          <a href="editBerita.php?id=<?= $row['id'] ?>" 
                             class="action-btn bg-blue-100 text-blue-700 p-2 rounded-lg hover:bg-blue-200 transition-colors" 
                             title="Edit Berita">
                            <i class="fas fa-edit"></i>
                          </a>
                          <button onclick="confirmHapus(<?= $row['id'] ?>, '<?= htmlspecialchars(addslashes($row['judul'] ?? 'Berita')) ?>')" 
                                  class="action-btn bg-red-100 text-red-700 p-2 rounded-lg hover:bg-red-200 transition-colors" 
                                  title="Hapus Berita">
                            <i class="fas fa-trash-alt"></i>
                          </button>
                          <button class="action-btn bg-green-100 text-green-700 p-2 rounded-lg hover:bg-green-200 transition-colors" 
                                  title="Lihat Detail">
                            <i class="fas fa-eye"></i>
                          </button>
                        </div>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="7" class="text-center p-8">
                      <div class="flex flex-col items-center justify-center text-gray-500">
                        <i class="fas fa-newspaper text-4xl mb-3 text-gray-300"></i>
                        <p class="text-lg font-medium">Belum ada data berita</p>
                        <p class="text-sm mb-4">Berita yang ditambahkan akan muncul di sini</p>
                        <a href="tambahBerita.php" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center">
                          <i class="fas fa-plus mr-2"></i>
                          Tambah Berita Pertama
                        </a>
                      </div>
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <?php if ($result && mysqli_num_rows($result) > 0): ?>
          <div class="p-4 border-t border-gray-200 bg-gray-50">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
              <div class="text-sm text-gray-600 mb-4 md:mb-0">
                Menampilkan <span class="font-semibold">1-<?= $total_berita ?></span> dari <span class="font-semibold"><?= $total_berita ?></span> berita
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

  <script>
    // SweetAlert konfirmasi hapus
    function confirmHapus(id, judul) {
      Swal.fire({
        title: 'Hapus Berita?',
        html: `Anda yakin ingin menghapus berita <strong>"${judul}"</strong>?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        customClass: {
          popup: 'rounded-xl',
          confirmButton: 'rounded-lg px-6',
          cancelButton: 'rounded-lg px-6'
        }
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = "hapusBerita.php?id=" + id;
        }
      });
    }

    // Logout SweetAlert
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
  </script>

  <!-- Status Feedback -->
  <?php if (isset($_SESSION['berita_status'])): ?>
    <script>
      document.addEventListener("DOMContentLoaded", function () {
        <?php
          $status = $_SESSION['berita_status'];
          unset($_SESSION['berita_status']);

          if ($status === 'deleted') {
              echo "Swal.fire('Berhasil', 'Berita berhasil dihapus.', 'success');";
          } elseif ($status === 'delete_failed') {
              echo "Swal.fire('Gagal', 'Gagal menghapus berita.', 'error');";
          } elseif ($status === 'invalid_id') {
              echo "Swal.fire('Error', 'ID berita tidak valid.', 'warning');";
          } elseif ($status === 'updated') {
              echo "Swal.fire('Berhasil', 'Berita berhasil diperbarui.', 'success');";
          } elseif ($status === 'added') {
              echo "Swal.fire('Berhasil', 'Berita berhasil ditambahkan.', 'success');";
          }
        ?>
      });
    </script>
  <?php endif; ?>
</body>
</html>