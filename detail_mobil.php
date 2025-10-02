<?php
session_start();
require './config/db.php';

// Validasi input
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit;
}

 $id = intval($_GET['id']);

// Menggunakan prepared statement untuk keamanan
 $stmt = $conn->prepare("
    SELECT m.*, mr.nama_merek, k.nama_kategori
    FROM mobil m
    JOIN merek mr ON m.id_merek = mr.id_merek
    JOIN kategori k ON m.id_kategori = k.id_kategori
    WHERE m.id_mobil = ?
");
 $stmt->bind_param("i", $id);
 $stmt->execute();
 $result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<div class='alert alert-danger'>Mobil tidak ditemukan.</div>";
    exit;
}

 $data = $result->fetch_assoc();

// Ambil gambar mobil dengan prepared statement
 $gambarList = [];
 $stmt_gambar = $conn->prepare("SELECT gambar FROM gambar_mobil WHERE id_mobil = ?");
 $stmt_gambar->bind_param("i", $id);
 $stmt_gambar->execute();
 $gambar_result = $stmt_gambar->get_result();

while ($row = $gambar_result->fetch_assoc()) {
    $gambarList[] = 'uploads/' . htmlspecialchars($row['gambar']);
}

// Jika tidak ada gambar, tambahkan gambar default
if (empty($gambarList)) {
    $gambarList[] = 'assets/images/default-car.jpg';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($data['nama_mobil']) ?> - Detail Mobil</title>
  
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <!-- Custom CSS -->
  <style>
    .image-slider { position: relative; width: 100%; max-width: 600px; margin: 20px auto; overflow: hidden; border-radius: 8px; background-color: #f9f9f9; }
    .slides { display: flex; transition: transform 0.4s ease-in-out; width: calc(100% * <?= max(1, count($gambarList)) ?>); }
    .slide { min-width: 100%; }
    .slide img { display: block; width: 100%; object-fit: cover; }
    .thumbnail-container img:hover { opacity: 0.8; border: 2px solid #0288D1; }
    .btn:hover { opacity: 0.9; }
    .tab-item.active {
      border-bottom: 2px solid #0288D1;
      color: #0288D1;
    }
    .main-image-container {
      position: relative;
      overflow: hidden;
      border-radius: 0.5rem;
    }
    .nav-button {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      background-color: rgba(0, 0, 0, 0.5);
      color: white;
      border: none;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      z-index: 10;
      transition: background-color 0.3s;
    }
    .nav-button:hover {
      background-color: rgba(0, 0, 0, 0.7);
    }
    .prev-button {
      left: 10px;
    }
    .next-button {
      right: 10px;
    }
    .image-counter {
      position: absolute;
      bottom: 10px;
      right: 10px;
      background-color: rgba(0, 0, 0, 0.6);
      color: white;
      padding: 5px 10px;
      border-radius: 4px;
      font-size: 14px;
    }
    .thumbnail-container {
      display: flex;
      gap: 8px;
      margin-top: 10px;
      overflow-x: auto;
      padding-bottom: 5px;
    }
    .thumbnail {
      min-width: 80px;
      height: 60px;
      object-fit: cover;
      border-radius: 4px;
      cursor: pointer;
      border: 2px solid transparent;
      transition: border-color 0.3s;
    }
    .thumbnail.active {
      border-color: #0288D1;
    }
    .spec-row {
      display: flex;
      justify-content: space-between;
      padding: 12px 0;
      border-bottom: 1px solid #eee;
    }
    .spec-label {
      color: #666;
      font-weight: 500;
    }
    .spec-value {
      font-weight: 600;
    }
    .review-card {
      background: white;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      padding: 20px;
      margin-bottom: 20px;
    }
    .star-rating {
      color: #f59e0b;
    }
    .discussion-card {
      background: white;
      border-radius: 8px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.1);
      padding: 16px;
      margin-bottom: 16px;
    }
    .reply-card {
      margin-left: 24px;
      border-left: 3px solid #e5e7eb;
      padding-left: 16px;
      background: #f9fafb;
    }
  </style>
</head>
<body class="bg-gray-50">
  <!-- Header -->
<header class="bg-white shadow-sm">
  <div class="container mx-auto px-4 py-4">
    <div class="flex items-center justify-between">
      <a href="pengguna.php" class="text-xl font-bold text-blue-600">Mobila</a> <!-- Ubah dari index.php ke pengguna.php -->
      <nav>
        <ul class="flex space-x-6">
          <li><a href="pengguna.php" class="text-gray-700 hover:text-blue-600">Beranda</a></li>
          <li><a href="pengguna.php#shop" class="text-gray-700 hover:text-blue-600">Mobil</a></li> <!-- Tambah anchor -->
          <?php if (isset($_SESSION['email'])): ?>
            <li><a href="logout.php" class="text-gray-700 hover:text-blue-600">Logout</a></li>
          <?php else: ?>
            <li><a href="login.php" class="text-gray-700 hover:text-blue-600">Login</a></li>
          <?php endif; ?>
        </ul>
      </nav>
    </div>
  </div>
</header>

  <!-- Main Content -->
  <main class="container mx-auto px-4 py-8">
    <div class="flex flex-col lg:flex-row gap-8">
      <!-- Gambar dan Slider -->
      <div class="lg:w-1/2">
        <div class="main-image-container">
          <img id="mainDisplay" src="<?= htmlspecialchars($gambarList[0]) ?>" class="w-full h-auto object-cover" alt="<?= htmlspecialchars($data['nama_mobil']) ?>" />
          <button onclick="prevImage()" class="nav-button prev-button">
            <i class="fas fa-chevron-left"></i>
          </button>
          <button onclick="nextImage()" class="nav-button next-button">
            <i class="fas fa-chevron-right"></i>
          </button>
          <div id="imageCounter" class="image-counter">
            1 / <?= count($gambarList) ?>
          </div>
        </div>
        <div class="thumbnail-container">
          <?php foreach ($gambarList as $index => $img): ?>
            <img src="<?= htmlspecialchars($img) ?>" onclick="goToImage(<?= $index ?>)" class="thumbnail <?= $index === 0 ? 'active' : '' ?>" alt="Gambar <?= $index + 1 ?>" />
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Informasi Mobil & Tombol Booking -->
      <div class="lg:w-1/2">
        <div class="bg-white rounded-lg shadow-sm p-6">
          <div class="flex items-center gap-2 mb-2">
            <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded"><?= htmlspecialchars($data['nama_merek']) ?></span>
            <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded"><?= htmlspecialchars($data['nama_kategori']) ?></span>
          </div>
          
          <h1 class="text-2xl font-bold text-gray-900 mb-4"><?= htmlspecialchars($data['nama_mobil']) ?></h1>
          
          <div class="mb-6">
            <div class="flex items-baseline">
              <span class="text-3xl font-bold text-gray-900">Rp <?= number_format($data['harga'], 0, ',', '.') ?></span>
              <span class="text-gray-500 ml-2">Harga mulai</span>
            </div>
          </div>
          
          <div class="mb-6">
            <p class="text-gray-700"><?= nl2br(htmlspecialchars($data['deskripsi'])) ?></p>
          </div>
          
          <div class="flex flex-wrap gap-3 mb-6">
            <div class="flex items-center text-gray-600">
              <i class="fas fa-cog mr-2"></i>
              <span><?= htmlspecialchars($data['transmisi']) ?></span>
            </div>
            <div class="flex items-center text-gray-600">
              <i class="fas fa-gas-pump mr-2"></i>
              <span><?= htmlspecialchars($data['bahan_bakar']) ?></span>
            </div>
            <div class="flex items-center text-gray-600">
              <i class="fas fa-users mr-2"></i>
              <span><?= htmlspecialchars($data['kapasitas_tempat_duduk']) ?></span>
            </div>
          </div>
          
          <div class="flex flex-col sm:flex-row gap-3">
            <?php if (isset($_SESSION['email'])): ?>
              <a href="booking.php?id=<?= htmlspecialchars($data['id_mobil']) ?>" class="px-6 py-3 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition text-center">
                <i class="fas fa-calendar-check mr-2"></i> Booking Test Drive
              </a>
            <?php else: ?>
              <a href="login.php?redirect=<?= urlencode('detail-mobil.php?id=' . $data['id_mobil']) ?>" class="px-6 py-3 bg-gray-800 text-white font-medium rounded-lg hover:bg-gray-900 transition text-center">
                <i class="fas fa-sign-in-alt mr-2"></i> Login untuk Booking
              </a>
            <?php endif; ?>
            <a href="#" class="px-6 py-3 bg-white text-gray-800 font-medium rounded-lg border border-gray-300 hover:bg-gray-50 transition text-center">
              <i class="fas fa-heart mr-2"></i> Simpan
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Tab Navigasi & Konten -->
    <div class="mt-12 bg-white rounded-lg shadow-sm overflow-hidden">
      <div class="border-b border-gray-200">
        <nav class="flex -mb-px">
          <button class="tab-item py-4 px-6 text-center border-b-2 border-blue-500 font-medium text-blue-600" data-tab="spec">
            Spesifikasi
          </button>
          <button class="tab-item py-4 px-6 text-center border-b-2 border-transparent font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="video">
            Video
          </button>
          <button class="tab-item py-4 px-6 text-center border-b-2 border-transparent font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="review">
            Review
          </button>
          <button class="tab-item py-4 px-6 text-center border-b-2 border-transparent font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="diskusi">
            Diskusi
          </button>
        </nav>
      </div>  

      <div class="p-6">
        <!-- Tab Spesifikasi -->
        <div class="tab-content" id="tab-spec">
          <h2 class="text-xl font-bold mb-4">Spesifikasi <?= htmlspecialchars($data['nama_mobil']) ?></h2>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="spec-row">
              <span class="spec-label">Jenis Transmisi</span>
              <span class="spec-value"><?= htmlspecialchars($data['transmisi']) ?></span>
            </div>
            <div class="spec-row">
              <span class="spec-label">Jenis Bahan Bakar</span>
              <span class="spec-value"><?= htmlspecialchars($data['bahan_bakar']) ?></span>
            </div>
            <div class="spec-row">
              <span class="spec-label">Kapasitas Mesin</span>
              <span class="spec-value"><?= htmlspecialchars($data['kapasitas_mesin']) ?></span>
            </div>
            <div class="spec-row">
              <span class="spec-label">Tenaga</span>
              <span class="spec-value"><?= htmlspecialchars($data['tenaga']) ?></span>
            </div>
            <div class="spec-row">
              <span class="spec-label">Kapasitas Tempat Duduk</span>
              <span class="spec-value"><?= htmlspecialchars($data['kapasitas_tempat_duduk']) ?></span>
            </div>
          </div>
        </div>

        <!-- Tab Video -->
        <div class="tab-content hidden" id="tab-video">
          <h2 class="text-xl font-bold mb-4">Video <?= htmlspecialchars($data['nama_mobil']) ?></h2>
          
          <?php if (!empty($data['video_url'])): ?>
            <div class="aspect-w-16 aspect-h-9">
              <iframe src="<?= htmlspecialchars($data['video_url']) ?>" frameborder="0" allowfullscreen class="w-full h-96 rounded-lg"></iframe>
            </div>
          <?php else: ?>
            <div class="bg-gray-100 rounded-lg p-8 text-center">
              <i class="fas fa-video text-gray-400 text-4xl mb-3"></i>
              <p class="text-gray-600">Video belum tersedia untuk mobil ini.</p>
            </div>
          <?php endif; ?>
        </div>

        <!-- Tab Review -->
        <div class="tab-content hidden" id="tab-review">
          <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold">Review <?= htmlspecialchars($data['nama_mobil']) ?></h2>
            <a href="tambahReview.php?mobil_id=<?= $id ?>" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
              <i class="fas fa-pen mr-2"></i> Tulis Review
            </a>
          </div>
          
          <?php
          $stmt_review = $conn->prepare("SELECT * FROM review WHERE mobil_id = ? ORDER BY created_at DESC");
          $stmt_review->bind_param("i", $id);
          $stmt_review->execute();
          $reviews = $stmt_review->get_result();

          if ($reviews->num_rows > 0):
            while ($rev = $reviews->fetch_assoc()): 
              $rating = floatval($rev['rating']);
          ?>
            <div class="review-card">
              <?php if (!empty($rev['judul'])): ?>
                <h3 class="text-lg font-bold mb-2"><?= htmlspecialchars($rev['judul']) ?></h3>
              <?php endif; ?>

              <div class="flex items-center mb-3">
                <div class="star-rating">
                  <?php
                  for ($i = 1; $i <= 5; $i++) {
                    if ($rating >= $i) {
                      echo '<i class="fas fa-star"></i>';
                    } elseif ($rating >= ($i - 0.5)) {
                      echo '<i class="fas fa-star-half-alt"></i>';
                    } else {
                      echo '<i class="far fa-star"></i>';
                    }
                  }
                  ?>
                </div>
                <span class="ml-2 font-medium"><?= number_format($rating, 1) ?>/5</span>
              </div>

              <p class="text-gray-700 mb-4"><?= nl2br(htmlspecialchars($rev['komentar'])) ?></p>

              <div class="flex items-center">
                <div class="w-10 h-10 rounded-full bg-red-500 flex items-center justify-center text-white font-bold mr-3">
                  <?= strtoupper(substr(htmlspecialchars($rev['nama']), 0, 1)) ?>
                </div>
                <div>
                  <p class="font-medium"><?= htmlspecialchars($rev['nama']) ?></p>
                  <p class="text-sm text-gray-500"><?= date('d M Y', strtotime($rev['created_at'])) ?></p>
                </div>
              </div>
            </div>
          <?php endwhile;
          else: ?>
            <div class="bg-gray-50 rounded-lg p-8 text-center">
              <i class="fas fa-comment-alt text-gray-400 text-4xl mb-3"></i>
              <p class="text-gray-600">Belum ada ulasan untuk mobil ini.</p>
              <a href="tambahReview.php?mobil_id=<?= $id ?>" class="mt-3 inline-block text-blue-600 hover:underline">
                Jadilah yang pertama mengulas
              </a>
            </div>
          <?php endif; ?>
        </div>

        <!-- Tab Diskusi -->
        <div class="tab-content hidden" id="tab-diskusi">
          <h2 class="text-xl font-bold mb-4">Diskusi <?= htmlspecialchars($data['nama_mobil']) ?></h2>

          <!-- Form Komentar Utama -->
          <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <h3 class="font-medium mb-3">Tambah Komentar</h3>
            <form method="POST" action="tambahDiskusi.php" class="space-y-3">
              <?php if (!isset($_SESSION['username'])): ?>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                  <input type="text" name="nama" placeholder="Nama Anda" class="w-full p-2 border border-gray-300 rounded-md" required>
                </div>
              <?php else: ?>
                <input type="hidden" name="nama" value="<?= htmlspecialchars($_SESSION['username']) ?>">
                <div class="mb-2 text-sm text-gray-600">
                  Berkomentar sebagai <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>
                </div>
              <?php endif; ?>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Komentar</label>
                <textarea name="komentar" placeholder="Tulis komentar atau pertanyaan..." class="w-full p-2 border border-gray-300 rounded-md" rows="3" required></textarea>
              </div>
              
              <input type="hidden" name="id_mobil" value="<?= htmlspecialchars($id) ?>">
              
              <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition">
                <i class="fas fa-paper-plane mr-2"></i> Kirim Komentar
              </button>
            </form>
          </div>

          <!-- List Komentar -->
          <h3 class="font-medium mb-3">Komentar Terbaru</h3>
          
          <?php
          $stmt_diskusi = $conn->prepare("SELECT * FROM diskusi WHERE id_mobil = ? AND parent_id IS NULL ORDER BY tanggal DESC");
          $stmt_diskusi->bind_param("i", $id);
          $stmt_diskusi->execute();
          $komentarUtama = $stmt_diskusi->get_result();

          if ($komentarUtama->num_rows > 0):
            while ($komentar = $komentarUtama->fetch_assoc()):
          ?>
            <div class="discussion-card">
              <div class="flex items-start">
                <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold mr-3">
                  <?= strtoupper(substr(htmlspecialchars($komentar['nama']), 0, 1)) ?>
                </div>
                <div class="flex-1">
                  <div class="flex justify-between">
                    <h4 class="font-medium"><?= htmlspecialchars($komentar['nama']) ?></h4>
                    <span class="text-sm text-gray-500"><?= date('d M Y, H:i', strtotime($komentar['tanggal'])) ?></span>
                  </div>
                  <p class="text-gray-700 mt-1"><?= nl2br(htmlspecialchars($komentar['komentar'])) ?></p>
                  
                  <button class="mt-2 text-sm text-blue-600 hover:underline" onclick="toggleReplyForm(<?= $komentar['id'] ?>)">
                    Balas
                  </button>
                  
                  <!-- Form Balas ( disembunyikan secara default ) -->
                  <div id="reply-form-<?= $komentar['id'] ?>" class="hidden mt-3 p-3 bg-gray-50 rounded-md">
                    <form method="POST" action="tambahDiskusi.php" class="space-y-2">
                      <?php if (!isset($_SESSION['username'])): ?>
                        <input type="text" name="nama" placeholder="Nama Anda" class="w-full p-2 border border-gray-300 rounded-md" required>
                      <?php else: ?>
                        <input type="hidden" name="nama" value="<?= htmlspecialchars($_SESSION['username']) ?>">
                      <?php endif; ?>
                      
                      <textarea name="komentar" placeholder="Balas komentar ini..." class="w-full p-2 border border-gray-300 rounded-md" rows="2" required></textarea>
                      
                      <input type="hidden" name="parent_id" value="<?= $komentar['id'] ?>">
                      <input type="hidden" name="id_mobil" value="<?= htmlspecialchars($id) ?>">
                      
                      <div class="flex justify-end">
                        <button type="button" onclick="toggleReplyForm(<?= $komentar['id'] ?>)" class="px-3 py-1 mr-2 text-gray-600 hover:text-gray-800">
                          Batal
                        </button>
                        <button type="submit" class="px-3 py-1 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                          Balas
                        </button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>

              <!-- Balasan -->
              <?php
              $idKomentar = $komentar['id'];
              $stmt_balasan = $conn->prepare("SELECT * FROM diskusi WHERE parent_id = ? ORDER BY tanggal ASC");
              $stmt_balasan->bind_param("i", $idKomentar);
              $stmt_balasan->execute();
              $balasan = $stmt_balasan->get_result();

              if ($balasan->num_rows > 0):
                while ($reply = $balasan->fetch_assoc()):
              ?>
                <div class="reply-card mt-4">
                  <div class="flex items-start">
                    <div class="w-8 h-8 rounded-full bg-green-500 flex items-center justify-center text-white text-sm font-bold mr-2">
                      <?= strtoupper(substr(htmlspecialchars($reply['nama']), 0, 1)) ?>
                    </div>
                    <div class="flex-1">
                      <div class="flex justify-between">
                        <h5 class="font-medium text-sm"><?= htmlspecialchars($reply['nama']) ?></h5>
                        <span class="text-xs text-gray-500"><?= date('d M Y, H:i', strtotime($reply['tanggal'])) ?></span>
                      </div>
                      <p class="text-gray-700 text-sm mt-1"><?= nl2br(htmlspecialchars($reply['komentar'])) ?></p>
                    </div>
                  </div>
                </div>
              <?php 
                endwhile;
              endif;
              ?>
            </div>
          <?php 
            endwhile;
          else:
          ?>
            <div class="bg-gray-50 rounded-lg p-8 text-center">
              <i class="fas fa-comments text-gray-400 text-4xl mb-3"></i>
              <p class="text-gray-600">Belum ada diskusi untuk mobil ini.</p>
              <p class="text-gray-500 text-sm mt-2">Mulailah diskusi dengan mengajukan pertanyaan atau memberikan komentar.</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </main>

  <!-- Footer -->
  <footer class="bg-gray-800 text-white mt-12 py-8">
    <div class="container mx-auto px-4">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
        <div>
          <h3 class="text-lg font-bold mb-4">MobilKu</h3>
          <p class="text-gray-400">Platform terpercaya untuk informasi dan pembelian mobil terbaru.</p>
        </div>
        <div>
          <h4 class="font-medium mb-3">Menu</h4>
          <ul class="space-y-2 text-gray-400">
            <li><a href="#" class="hover:text-white">Beranda</a></li>
            <li><a href="#" class="hover:text-white">Mobil</a></li>
            <li><a href="#" class="hover:text-white">Promo</a></li>
            <li><a href="#" class="hover:text-white">Tentang</a></li>
          </ul>
        </div>
        <div>
          <h4 class="font-medium mb-3">Layanan</h4>
          <ul class="space-y-2 text-gray-400">
            <li><a href="#" class="hover:text-white">Test Drive</a></li>
            <li><a href="#" class="hover:text-white">Kredit</a></li>
            <li><a href="#" class="hover:text-white">Asuransi</a></li>
            <li><a href="#" class="hover:text-white">Servis</a></li>
          </ul>
        </div>
        <div>
          <h4 class="font-medium mb-3">Kontak</h4>
          <ul class="space-y-2 text-gray-400">
            <li><i class="fas fa-phone mr-2"></i> (021) 1234-5678</li>
            <li><i class="fas fa-envelope mr-2"></i> info@mobila.co.id</li>
            <li><i class="fas fa-map-marker-alt mr-2"></i> Jakarta, Indonesia</li>
          </ul>
        </div>
      </div>
      <div class="border-t border-gray-700 mt-8 pt-6 text-center text-gray-400">
        <p>&copy; 2023 MobilKu. Hak Cipta Dilindungi.</p>
      </div>
    </div>
  </footer>

  <!-- Script -->
  <script>
    const imageList = <?= json_encode($gambarList) ?>;
    let currentIndex = 0;

    function updateImage() {
      const mainDisplay = document.getElementById('mainDisplay');
      const imageCounter = document.getElementById('imageCounter');
      
      mainDisplay.src = imageList[currentIndex];
      imageCounter.textContent = (currentIndex + 1) + ' / ' + imageList.length;
      
      // Update thumbnail active state
      document.querySelectorAll('.thumbnail').forEach((thumb, index) => {
        if (index === currentIndex) {
          thumb.classList.add('active');
        } else {
          thumb.classList.remove('active');
        }
      });
    }

    function nextImage() {
      currentIndex = (currentIndex + 1) % imageList.length;
      updateImage();
    }

    function prevImage() {
      currentIndex = (currentIndex - 1 + imageList.length) % imageList.length;
      updateImage();
    }

    function goToImage(index) {
      currentIndex = index;
      updateImage();
    }

    // Tab navigation
    document.querySelectorAll('.tab-item').forEach(tab => {
      tab.addEventListener('click', () => {
        // Remove active class from all tabs and contents
        document.querySelectorAll('.tab-item').forEach(t => {
          t.classList.remove('border-blue-500', 'text-blue-600');
          t.classList.add('border-transparent', 'text-gray-500');
        });
        document.querySelectorAll('.tab-content').forEach(c => c.classList.add('hidden'));
        
        // Add active class to selected tab and content
        tab.classList.remove('border-transparent', 'text-gray-500');
        tab.classList.add('border-blue-500', 'text-blue-600');
        
        const selectedTab = tab.getAttribute('data-tab');
        document.getElementById('tab-' + selectedTab).classList.remove('hidden');
      });
    });

    // Toggle reply form
    function toggleReplyForm(commentId) {
      const form = document.getElementById('reply-form-' + commentId);
      form.classList.toggle('hidden');
    }

    // Check for tab parameter in URL
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam = urlParams.get("tab");
    if (tabParam) {
      const targetTab = document.querySelector(`.tab-item[data-tab="${tabParam}"]`);
      if (targetTab) {
        targetTab.click();
      }
    }

    // Auto-slide for images (optional)
    let slideInterval = setInterval(nextImage, 5000);
    
    // Pause auto-slide when user interacts with slider
    const mainImageContainer = document.querySelector('.main-image-container');
    mainImageContainer.addEventListener('mouseenter', () => {
      clearInterval(slideInterval);
    });
    
    mainImageContainer.addEventListener('mouseleave', () => {
      slideInterval = setInterval(nextImage, 5000);
    });
  </script>
</body>
</html>