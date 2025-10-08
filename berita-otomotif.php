<?php
session_start();
require './config/db.php';

$is_logged_in = isset($_SESSION['user_id']);
$berita = mysqli_query($conn, "SELECT * FROM berita WHERE status = 'publikasi' ORDER BY tanggal_publikasi DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <title>Berita Otomotif</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  
  <!-- Font Awesome -->
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
        integrity="sha512-pIVp6fOS8l9kBdlx2Y7NggAWn6jISjzA4k9sbw4dNf5Wh0n2FElz2ZyPhY1D9shCqOQ73N0lZfNEJZvhTgA5iw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
  
  <!-- Custom CSS -->
  <style>
    /* Animasi fade-in */
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .fade-in {
      animation: fadeIn 0.6s ease-out forwards;
    }
    
    /* Efek hover untuk card */
    .news-card {
      transition: all 0.3s ease;
    }
    
    /* Efek gradient untuk header */
    .gradient-header {
      background: linear-gradient(135deg, #1e40af, #2563eb);
    }
    
    /* Custom scrollbar */
    ::-webkit-scrollbar {
      width: 8px;
    }
    
    ::-webkit-scrollbar-track {
      background: #f1f1f1;
    }
    
    ::-webkit-scrollbar-thumb {
      background: #888;
      border-radius: 4px;
    }
    
    ::-webkit-scrollbar-thumb:hover {
      background: #555;
    }
  </style>
</head>
<body class="bg-gray-50 min-h-screen">

<!-- Header -->
<header class="gradient-header text-white py-8 px-4 shadow-lg">
  <div class="max-w-6xl mx-auto">
    <h1 class="text-3xl md:text-4xl font-bold flex items-center justify-center gap-3">
      <i class="fa-solid fa-car-side"></i> Berita Otomotif Terkini
    </h1>
  </div>
</header>

<!-- Navigation -->
<div class="bg-white shadow-sm sticky top-0 z-10">
  <div class="max-w-6xl mx-auto px-4 py-3 flex justify-between items-center">
    <a href="<?= $is_logged_in ? 'pengguna.php' : 'index.php'; ?>" 
       class="flex items-center gap-2 text-blue-600 hover:text-blue-800 transition font-medium">
      <i class="fas fa-arrow-left"></i> Kembali
    </a>
    
    <div class="flex items-center gap-4">
      <button id="searchToggle" class="text-gray-600 hover:text-blue-600 transition">
        <i class="fas fa-search text-lg"></i>
      </button>
      <button id="filterToggle" class="text-gray-600 hover:text-blue-600 transition">
        <i class="fas fa-filter text-lg"></i>
      </button>
    </div>
  </div>
  
  <!-- Search Bar (Hidden by default) -->
  <div id="searchBar" class="hidden bg-gray-100 py-3 px-4 border-t border-gray-200">
    <div class="max-w-6xl mx-auto">
      <div class="relative">
        <input type="text" placeholder="Cari berita otomotif..." 
               class="w-full py-2 px-4 pr-10 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        <button class="absolute right-3 top-2.5 text-gray-500 hover:text-blue-600">
          <i class="fas fa-search"></i>
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Main Content -->
<main class="max-w-6xl mx-auto px-4 py-8">
  <!-- Section Header -->
  <div class="mb-8 flex items-center justify-between">
    <h2 class="text-2xl md:text-3xl font-bold text-blue-700 flex items-center gap-3">
      <i class="fa-solid fa-fire text-orange-500"></i> Trending
    </h2>
    
  </div>

  <!-- News Container -->
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="newsContainer">
    <?php 
    $counter = 0;
    while ($row = mysqli_fetch_assoc($berita)): 
      $counter++;
      $delay = $counter * 0.1; // Staggered animation
    ?>
      <article class="news-card bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl fade-in" style="animation-delay: <?= $delay ?>s">
        <div class="relative">
          <a href="berita-detail.php?id=<?= $row['id']; ?>">
            <img src="uploads/<?= htmlspecialchars($row['gambar']); ?>"
                 alt="<?= htmlspecialchars($row['judul']); ?>" 
                 class="w-full h-48 object-cover transition-transform duration-500 hover:scale-105" />
          </a>
          
          <!-- Category Badge -->
          <div class="absolute top-3 right-3 bg-blue-600 text-white text-xs font-bold px-2 py-1 rounded">
            Otomotif
          </div>
        </div>
        
        <div class="p-5">
          <h3 class="text-xl font-bold text-gray-800 mb-2 line-clamp-2">
            <a href="berita-detail.php?id=<?= $row['id']; ?>" class="hover:text-blue-600 transition">
              <?= htmlspecialchars($row['judul']); ?>
            </a>
          </h3>
          
          <div class="flex items-center text-sm text-gray-500 mb-3">
            <span class="flex items-center mr-4">
              <i class="far fa-calendar mr-1"></i>
              <?= date('d M Y', strtotime($row['tanggal_publikasi'])); ?>
            </span>
            <span class="flex items-center">
              <i class="far fa-user mr-1"></i>
              <?= htmlspecialchars($row['penulis']); ?>
            </span>
          </div>
          
          <p class="text-gray-600 mb-4 line-clamp-3">
            <?= mb_strimwidth(strip_tags($row['isi']), 0, 150, '...'); ?>
          </p>
          
          <div class="flex justify-between items-center">
            <a href="berita-detail.php?id=<?= $row['id']; ?>" 
               class="inline-flex items-center text-blue-600 font-medium hover:text-blue-800 transition">
              Baca Selengkapnya <i class="fas fa-arrow-right ml-2 text-sm"></i>
            </a>
            
            <div class="flex gap-2">
              <button class="text-gray-400 hover:text-red-500 transition">
                <i class="far fa-heart"></i>
              </button>
              <button class="text-gray-400 hover:text-blue-500 transition">
                <i class="far fa-bookmark"></i>
              </button>
            </div>
          </div>
        </div>
      </article>
    <?php endwhile; ?>
  </div>

  <!-- Load More Button -->
  <div class="mt-10 text-center">
    <button id="loadMoreBtn" class="px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition shadow-md">
      <i class="fas fa-sync-alt mr-2"></i> Muat Lebih Banyak
    </button>
  </div>
</main>

<!-- Footer -->
<footer class="bg-gray-800 text-white py-8 mt-12">
  <div class="max-w-6xl mx-auto px-4">
    <div class="flex flex-col md:flex-row justify-between items-center">
      <div class="mb-4 md:mb-0">
        <p>&copy; <?= date('Y'); ?> Portal Berita Otomotif. Semua hak dilindungi.</p>
      </div>
      
      <div class="flex space-x-4">
        <a href="#" class="text-gray-400 hover:text-white transition">
          <i class="fab fa-facebook-f"></i>
        </a>
        <a href="#" class="text-gray-400 hover:text-white transition">
          <i class="fab fa-twitter"></i>
        </a>
        <a href="#" class="text-gray-400 hover:text-white transition">
          <i class="fab fa-instagram"></i>
        </a>
        <a href="#" class="text-gray-400 hover:text-white transition">
          <i class="fab fa-youtube"></i>
        </a>
      </div>
    </div>
  </div>
</footer>

<!-- JavaScript -->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Toggle search bar
    const searchToggle = document.getElementById('searchToggle');
    const searchBar = document.getElementById('searchBar');
    
    searchToggle.addEventListener('click', function() {
      searchBar.classList.toggle('hidden');
      if (!searchBar.classList.contains('hidden')) {
        searchBar.querySelector('input').focus();
      }
    });
    
    // Toggle filter (placeholder functionality)
    const filterToggle = document.getElementById('filterToggle');
    filterToggle.addEventListener('click', function() {
      alert('Fitur filter akan segera hadir!');
    });
    
    // Load more button (placeholder functionality)
    const loadMoreBtn = document.getElementById('loadMoreBtn');
    loadMoreBtn.addEventListener('click', function() {
      // Simulate loading
      this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Memuat...';
      
      setTimeout(() => {
        this.innerHTML = '<i class="fas fa-sync-alt mr-2"></i> Muat Lebih Banyak';
        alert('Tidak ada berita lagi untuk ditampilkan');
      }, 1500);
    });
    
    // Add to bookmark/favorite functionality
    document.querySelectorAll('.fa-heart, .fa-bookmark').forEach(icon => {
      icon.addEventListener('click', function() {
        this.classList.toggle('far');
        this.classList.toggle('fas');
        
        if (this.classList.contains('fas')) {
          this.classList.add('text-red-500');
          this.classList.remove('text-gray-400');
        } else {
          this.classList.remove('text-red-500');
          this.classList.add('text-gray-400');
        }
      });
    });
    
    // Add smooth scroll behavior
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function (e) {
        e.preventDefault();
        document.querySelector(this.getAttribute('href')).scrollIntoView({
          behavior: 'smooth'
        });
      });
    });
  });
</script>
</body>
</html>