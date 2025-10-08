<?php
require './config/db.php';

// Ambil ID dari URL, pastikan valid (angka)
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Query berita berdasarkan ID
$result = mysqli_query($conn, "SELECT * FROM berita WHERE id = $id");

// Jika tidak ada hasil, tampilkan pesan error
if (!$result || mysqli_num_rows($result) == 0) {
    die("Berita tidak ditemukan.");
}

$data = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>   
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($data['judul']); ?></title>
  
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  
  <!-- Font Awesome untuk ikon -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <!-- Custom CSS -->
  <style>
    /* Animasi fade-in */
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .fade-in {
      animation: fadeIn 0.8s ease-out forwards;
    }
    
    /* Efek parallax untuk gambar */
    .parallax-img {
      transition: transform 0.5s ease-out;
    }
    
    /* Hover effect untuk tombol */
    .btn-hover {
      transition: all 0.3s ease;
    }
    
    .btn-hover:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }
    
    /* Efek highlight untuk teks */
    .highlight-text {
      background: linear-gradient(120deg, #fbbf24 0%, #f59e0b 100%);
      background-repeat: no-repeat;
      background-size: 100% 0.2em;
      background-position: 0 88%;
      transition: background-size 0.25s ease-in;
    }
    
    .highlight-text:hover {
      background-size: 100% 88%;
    }
  </style>
</head>
<body class="bg-gray-50 min-h-screen">
  <!-- Header dengan efek parallax -->
  <header class="relative h-64 md:h-96 overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-b from-black/70 to-black/30 z-10"></div>
    <img 
      src="uploads/<?= htmlspecialchars($data['gambar']); ?>" 
      alt="<?= htmlspecialchars($data['judul']); ?>" 
      class="parallax-img w-full h-full object-cover"
    >
    <div class="absolute bottom-0 left-0 right-0 z-20 p-6 md:p-10">
      <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl md:text-5xl font-bold text-white mb-4 fade-in">
          <?= htmlspecialchars($data['judul']); ?>
        </h1>
        <div class="flex flex-wrap items-center text-white/90 text-sm md:text-base gap-4 fade-in" style="animation-delay: 0.2s">
          <span class="flex items-center">
            <i class="far fa-calendar mr-2"></i>
            <?= date('d M Y', strtotime($data['tanggal_publikasi'])); ?>
          </span>
          <span class="flex items-center">
            <i class="far fa-user mr-2"></i>
            <?= htmlspecialchars($data['penulis']); ?>
          </span>
        </div>
      </div>
    </div>
  </header>

  <!-- Konten Artikel -->
  <main class="max-w-4xl mx-auto px-4 py-8 md:py-12">
    <article class="bg-white rounded-xl shadow-lg overflow-hidden fade-in" style="animation-delay: 0.4s">
      <!-- Share buttons -->
      <div class="p-6 border-b border-gray-200 flex justify-between items-center">
        <div class="flex space-x-3">
          <button class="btn-hover bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center">
            <i class="fab fa-facebook-f mr-2"></i> Bagikan
          </button>
          <button class="btn-hover bg-blue-400 text-white px-4 py-2 rounded-lg flex items-center">
            <i class="fab fa-twitter mr-2"></i> Tweet
          </button>
          <button class="btn-hover bg-green-600 text-white px-4 py-2 rounded-lg flex items-center">
            <i class="fab fa-whatsapp mr-2"></i> WhatsApp
          </button>
        </div>
      </div>
      
      <!-- Isi artikel -->
      <div class="p-6 md:p-8">
        <div class="prose max-w-none text-gray-700 leading-relaxed">
          <?= nl2br(htmlspecialchars($data['isi'])); ?>
        </div>
        
        <!-- Tags -->
        <div class="mt-8 pt-6 border-t border-gray-200">
          <h3 class="text-lg font-semibold mb-3">Tag Terkait:</h3>
          <div class="flex flex-wrap gap-2">
            <span class="highlight-text px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm">#otomotif</span>
            <span class="highlight-text px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm">#teknologi</span>
            <span class="highlight-text px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm">#mobil</span>
          </div>
        </div>
      </div>
    </article>
    
    <!-- Tombol kembali dengan efek hover -->
    <div class="mt-8 text-center">
      <a href="berita-otomotif.php" class="btn-hover inline-flex items-center px-6 py-3 bg-indigo-600 text-white rounded-lg font-medium">
        <i class="fas fa-arrow-left mr-2"></i> Kembali ke Berita
      </a>
    </div>
  </main>

  <!-- Footer -->
  <footer class="bg-gray-800 text-white py-8 mt-12">
    <div class="max-w-4xl mx-auto px-4 text-center">
      <p>© <?= date('Y'); ?> Portal Berita Otomotif. Semua hak dilindungi.</p>
      <div class="mt-4 flex justify-center space-x-4">
        <a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-facebook-f"></i></a>
        <a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-twitter"></i></a>
        <a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-instagram"></i></a>
        <a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-youtube"></i></a>
      </div>
    </div>
  </footer>

  <!-- JavaScript -->
  <script>
    // Efek parallax untuk gambar header
    window.addEventListener('scroll', () => {
      const scrolled = window.pageYOffset;
      const parallax = document.querySelector('.parallax-img');
      parallax.style.transform = `translateY(${scrolled * 0.5}px)`;
    });

    // Animasi view count
    document.addEventListener('DOMContentLoaded', () => {
      const viewCount = document.getElementById('view-count');
      let count = 0;
      const target = parseInt(viewCount.textContent.replace(',', ''));
      const increment = target / 50;
      
      const updateCount = () => {
        count += increment;
        if (count < target) {
          viewCount.textContent = Math.floor(count).toLocaleString();
          requestAnimationFrame(updateCount);
        } else {
          viewCount.textContent = target.toLocaleString();
        }
      };
      
      updateCount();
    });

    // Share functionality
    document.querySelectorAll('button').forEach(button => {
      if (button.textContent.includes('Bagikan')) {
        button.addEventListener('click', () => {
          if (navigator.share) {
            navigator.share({
              title: '<?= htmlspecialchars($data["judul"]); ?>',
              text: '<?= htmlspecialchars(substr($data["isi"], 0, 100)); ?>...',
              url: window.location.href
            });
          } else {
            alert('Fitur share tidak didukung di browser ini');
          }
        });
      }
    });
  </script>
</body>
</html>