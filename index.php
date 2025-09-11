<?php
include './config/db.php';

// Ambil daftar merek untuk filter mobil
$brandQuery = "SELECT id_merek, nama_merek FROM merek ORDER BY nama_merek";
$brandResult = mysqli_query($conn, $brandQuery);

// Ambil data mobil dengan join ke merek
$carQuery = "
  SELECT m.*, r.nama_merek 
  FROM mobil m 
  JOIN merek r ON m.id_merek = r.id_merek
  ORDER BY m.id_mobil DESC
  LIMIT 8
";
$carResult = mysqli_query($conn, $carQuery);

// Ambil ulasan (review) terbaru dengan rating minimal 4
$reviewQuery = "
  SELECT r.*, m.nama_mobil 
  FROM review r 
  LEFT JOIN mobil m ON r.mobil_id = m.id_mobil
  WHERE r.rating >= 4
  ORDER BY r.created_at DESC
  LIMIT 6
";
$reviewResult = mysqli_query($conn, $reviewQuery);

// Ambil berita terbaru yang statusnya 'publikasi'
$newsQuery = "
  SELECT * FROM berita 
  WHERE status = 'publikasi' 
  ORDER BY tanggal_publikasi DESC 
  LIMIT 3
";
$newsResult = mysqli_query($conn, $newsQuery);

// FAQ statis (tidak ada tabel FAQ di DB)
$faqs = [
  [
    'question' => 'Apa itu Mobila?',
    'answer' => 'Mobila adalah platform terpercaya untuk pemesanan dan penjualan mobil online. Kami menyediakan berbagai pilihan mobil baru dan bekas dengan kualitas terjamin.'
  ],
  [
    'question' => 'Bagaimana cara booking mobil?',
    'answer' => 'Pilih mobil yang diinginkan, klik tombol "Booking", login ke akun Anda, isi form pemesanan, dan lakukan pembayaran. Tim kami akan menghubungi Anda untuk konfirmasi.'
  ],
  [
    'question' => 'Apakah mobil bisa diantar ke lokasi saya?',
    'answer' => 'Ya, kami menyediakan layanan pengantaran mobil ke lokasi Anda untuk area tertentu. Biaya pengantaran akan disesuaikan dengan jarak lokasi.'
  ],
  [
    'question' => 'Bagaimana jika mobil mengalami kerusakan?',
    'answer' => 'Semua mobil kami dilengkapi dengan asuransi. Untuk kerusakan di luar tanggung jawab customer, akan dikenakan biaya perbaikan sesuai dengan tingkat kerusakan.'
  ],
];
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Mobila</title>
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-900 font-sans">

  <!-- NAVBAR -->
  <nav class="fixed top-0 w-full bg-white shadow-md z-50 transition-colors duration-300" id="navbar">
    <div class="container mx-auto px-4 flex items-center justify-between h-16">
      <a href="#" class="text-2xl font-extrabold text-red-600">Mobila</a>
      <div class="hidden md:flex space-x-6 items-center">
      <a href="#shop" class="text-sky-500 hover:text-sky-700 transition">Mobil Baru</a>
      <a href="#ulasan" class="text-sky-500 hover:text-sky-700 transition">Ulasan</a>
      <a href="#berita" class="text-sky-500 hover:text-sky-700 transition">Berita</a>
      <a href="#faqs" class="text-sky-500 hover:text-sky-700 transition">FAQs</a>
      <a href="login.php" class="px-4 py-2 border border-sky-500 text-sky-500 rounded hover:bg-sky-500 hover:text-white transition">Login</a>
      <a href="register.php" class="px-4 py-2 bg-sky-500 text-white rounded hover:bg-sky-700 transition">Register</a>
    </div>
      <!-- Mobile menu button -->
      <button id="menu-btn" class="md:hidden focus:outline-none">
        <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
      </button>
    </div>
    <!-- Mobile menu -->
    <div id="mobile-menu" class="hidden md:hidden bg-white shadow-md">
      <a href="#shop" class="block px-4 py-2 border-b border-gray-200 hover:bg-red-50">Mobil Baru</a>
      <a href="#ulasan" class="block px-4 py-2 border-b border-gray-200 hover:bg-red-50">Ulasan</a>
      <a href="#berita" class="block px-4 py-2 border-b border-gray-200 hover:bg-red-50">Berita</a>
      <a href="#faqs" class="block px-4 py-2 border-b border-gray-200 hover:bg-red-50">FAQs</a>
      <a href="login.php" class="block px-4 py-2 border-b border-gray-200 text-red-600 hover:bg-red-50">Login</a>
      <a href="register.php" class="block px-4 py-2 bg-red-600 text-white hover:bg-red-700">Register</a>
    </div>
  </nav>

  <!-- HERO SECTION -->
  <section class="relative h-screen flex items-center justify-center bg-cover bg-center" style="background-image: url('assets/img/carheader.jpg')">
    <div class="absolute inset-0 bg-black bg-opacity-50"></div>
    <div class="relative max-w-3xl text-center px-4">
      <p class="text-red-600 uppercase tracking-widest font-semibold mb-2">Mobil Dalam Langkah Mudah</p>
      <h1 class="text-white text-4xl md:text-5xl font-extrabold leading-tight mb-6 drop-shadow-lg">
        Cara Sempurna Untuk Mengeksplor<br />Dan Cek Mobil Di Platform Kami
      </h1>
      <div class="flex justify-center gap-4">
        <a href="login.php" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-8 rounded shadow-lg transition">Booking</a>
        <a href="#shop" class="border-2 border-red-600 text-red-600 hover:bg-red-600 hover:text-white font-semibold py-3 px-8 rounded transition">Lihat Katalog</a>
      </div>
    </div>
  </section>

  <!-- BRAND LOGO SECTION -->
  <section class="bg-gray-900 py-8">
    <div class="container mx-auto flex flex-wrap justify-center gap-10 px-4">
      <!-- Brand logos -->
      <a href="https://www.chevrolet.com" target="_blank" rel="noopener noreferrer" class="hover:scale-110 transition-transform">
        <img src="https://static.vecteezy.com/system/resources/previews/020/498/757/original/chevrolet-brand-logo-car-symbol-with-name-white-design-usa-automobile-illustration-with-black-background-free-vector.jpg" alt="Chevrolet" class="h-12 object-contain" loading="lazy" />
      </a>
      <a href="https://www.jeep.com" target="_blank" rel="noopener noreferrer" class="hover:scale-110 transition-transform">
        <img src="https://tse4.mm.bing.net/th?id=OIP.jz71Zina4ioOEgRTtmB_YgHaE8&pid=Api&P=0&h=180" alt="Jeep" class="h-12 object-contain" loading="lazy" />
      </a>
      <a href="https://www.mitsubishi-motors.com" target="_blank" rel="noopener noreferrer" class="hover:scale-110 transition-transform">
        <img src="https://tse3.mm.bing.net/th?id=OIP.jsJs9t1UXntOqAfG06DqsAHaGB&pid=Api&P=0&h=180" alt="Mitsubishi" class="h-12 object-contain" loading="lazy" />
      </a>
      <a href="https://www.tesla.com" target="_blank" rel="noopener noreferrer" class="hover:scale-110 transition-transform">
        <img src="https://tse3.mm.bing.net/th?id=OIP.11aeOv9S0j4jTrUQRWbj7QHaHa&pid=Api&P=0&h=180" alt="Tesla" class="h-12 object-contain" loading="lazy" />
      </a>
      <a href="https://www.mercedes-benz.com" target="_blank" rel="noopener noreferrer" class="hover:scale-110 transition-transform">
        <img src="https://static.vecteezy.com/ti/vetor-gratis/p1/20502492-mercedes-benz-marca-logotipo-simbolo-com-nome-branco-design-alemao-carro-automovel-ilustracao-com-preto-fundo-gratis-vetor.jpg" alt="Mercedes-Benz" class="h-12 object-contain" loading="lazy" />
      </a>
      <a href="https://www.rolls-roycemotorcars.com" target="_blank" rel="noopener noreferrer" class="hover:scale-110 transition-transform">
        <img src="https://tse2.mm.bing.net/th?id=OIP.3f5e_4vMl8rbWQ7DiWVkGgHaGB&pid=Api&P=0&h=180" alt="Rolls-Royce" class="h-12 object-contain" loading="lazy" />
      </a>
      <a href="https://www.hummer.com" target="_blank" rel="noopener noreferrer" class="hover:scale-110 transition-transform">
        <img src="https://tse2.mm.bing.net/th?id=OIP.otaRtMs1k5u17mBQhOnHKgHaGB&pid=Api&P=0&h=180" alt="Hummer" class="h-12 object-contain" loading="lazy" />
      </a>
    </div>
  </section>

  <!-- CARS SECTION -->
  <section id="shop" class="py-16">
    <div class="container mx-auto px-4">
      <p class="text-red-600 text-center font-semibold uppercase tracking-widest mb-2">New Popular Car</p>
      <h2 class="text-3xl font-extrabold text-center mb-8">Shop Popular New Car</h2>

      <!-- Filter Buttons -->
      <div class="flex flex-wrap justify-center gap-3 mb-10">
        <button class="filter-btn bg-white border border-red-600 text-red-600 font-semibold px-5 py-2 rounded shadow-sm hover:bg-red-600 hover:text-white transition active" data-filter="all">Show All</button>
        <?php
        if(mysqli_num_rows($brandResult) > 0) {
          while($brand = mysqli_fetch_assoc($brandResult)) {
            $filterName = strtolower(str_replace(' ', '', $brand['nama_merek']));
            echo '<button class="filter-btn bg-white border border-red-600 text-red-600 font-semibold px-5 py-2 rounded shadow-sm hover:bg-red-600 hover:text-white transition" data-filter="'.$filterName.'">'.$brand['nama_merek'].'</button>';
          }
        }
        ?>
      </div>

      <!-- Car Cards Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8" id="car-list">
        <?php
        if(mysqli_num_rows($carResult) > 0) {
          while($car = mysqli_fetch_assoc($carResult)) {
            $carClass = strtolower(str_replace(' ', '', $car['nama_merek']));
            $formattedPrice = "Rp " . number_format($car['harga'], 0, ',', '.');
            $carId = $car['id_mobil'];
            $imgQuery = "SELECT gambar FROM gambar_mobil WHERE id_mobil = $carId LIMIT 1";
            $imgResult = mysqli_query($conn, $imgQuery);
            if(mysqli_num_rows($imgResult) > 0) {
              $imgRow = mysqli_fetch_assoc($imgResult);
              $imagePath = 'assets/img/mobil/' . $imgRow['gambar'];
            } else {
              $imagePath = 'assets/img/default-car.jpg';
            }
            ?>
            <div class="car-card bg-white rounded-lg shadow-md overflow-hidden flex flex-col" data-brand="<?= $carClass ?>">
              <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($car['nama_mobil']) ?>" loading="lazy" class="h-48 w-full object-cover transition-transform duration-300 hover:scale-105" />
              <div class="p-4 flex flex-col flex-grow">
                <h3 class="text-lg font-semibold mb-1"><?= htmlspecialchars($car['nama_mobil']) ?></h3>
                <p class="text-gray-500 mb-3"><?= $car['nama_merek'] ?> • <?= htmlspecialchars($car['type'] ?? '') ?></p>
                <p class="text-red-600 font-bold text-xl mb-4 mt-auto"><?= $formattedPrice ?></p>
                <div class="flex justify-between">
                  <a href="detail_mobil.php?id=<?= $carId ?>" class="text-red-600 border border-red-600 rounded px-3 py-1 text-sm font-semibold hover:bg-red-600 hover:text-white transition">Lihat Detail</a>
                  <a href="login.php" class="bg-red-600 text-white rounded px-3 py-1 text-sm font-semibold hover:bg-red-700 transition">Booking</a>
                </div>
              </div>
            </div>
            <?php
          }
        } else {
          echo '<p class="col-span-full text-center text-gray-500">Tidak ada mobil tersedia saat ini.</p>';
        }
        ?>
      </div>

    </div>
  </section>

  <!-- REVIEW SECTION -->
  <section id="ulasan" class="py-16 bg-white">
    <div class="container mx-auto px-4">
      <p class="text-red-600 text-center font-semibold uppercase tracking-widest mb-2">Testimonials</p>
      <h2 class="text-3xl font-extrabold text-center mb-10">What Our Customers Say</h2>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php
        if(mysqli_num_rows($reviewResult) > 0) {
          while($review = mysqli_fetch_assoc($reviewResult)) {
            $rating = round($review['rating']);
            $stars = str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
            ?>
            <div class="bg-gray-50 p-6 rounded-lg shadow hover:shadow-lg transition flex flex-col">
              <div class="text-yellow-400 text-xl mb-2"><?= $stars ?></div>
              <h3 class="font-semibold text-lg mb-1"><?= htmlspecialchars($review['nama_mobil'] ?? 'Mobil Tidak Diketahui') ?></h3>
              <p class="text-gray-700 flex-grow">"<?= htmlspecialchars(substr($review['komentar'], 0, 150)) ?><?= strlen($review['komentar']) > 150 ? '...' : '' ?>"</p>
              <p class="mt-4 text-sm text-gray-500 font-semibold">- <?= htmlspecialchars($review['nama']) ?></p>
            </div>
            <?php
          }
        } else {
          echo '<p class="col-span-full text-center text-gray-500">Belum ada ulasan tersedia.</p>';
        }
        ?>
      </div>

      <div class="text-center mt-10">
        <a href="review.php" class="inline-block border border-red-600 text-red-600 font-semibold px-6 py-2 rounded hover:bg-red-600 hover:text-white transition">Lihat Semua Ulasan</a>
        <a href="review.php" class="inline-block bg-red-600 text-white font-semibold px-6 py-2 rounded ml-4 hover:bg-red-700 transition">Tambah Ulasan</a>
      </div>
    </div>
  </section>

  <!-- NEWS SECTION -->
  <section id="berita" class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
      <div class="flex justify-between items-center mb-10">
        <div>
          <p class="text-red-600 font-semibold uppercase tracking-widest mb-1">Latest News</p>
          <h2 class="text-3xl font-extrabold">Berita Terbaru Otomotif</h2>
        </div>
        <a href="berita-otomotif.php" class="text-red-600 font-semibold hover:underline">Lihat Semua Berita &rarr;</a>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <?php
        if(mysqli_num_rows($newsResult) > 0) {
          while($news = mysqli_fetch_assoc($newsResult)) {
            $imagePath = !empty($news['gambar']) ? 'assets/img/berita/' . $news['gambar'] : 'assets/img/default-news.jpg';
            $excerpt = substr(strip_tags($news['isi']), 0, 120) . (strlen(strip_tags($news['isi'])) > 120 ? '...' : '');
            $dateFormatted = date('d M Y', strtotime($news['tanggal_publikasi']));
            ?>
            <article class="bg-white rounded-lg shadow hover:shadow-lg transition overflow-hidden flex flex-col">
              <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($news['judul']) ?>" loading="lazy" class="h-48 w-full object-cover" />
                            <div class="p-6 flex flex-col flex-grow">
                <time class="text-gray-400 text-sm mb-2"><?= $dateFormatted ?></time>
                <h3 class="text-lg font-semibold mb-3"><?= htmlspecialchars($news['judul']) ?></h3>
                <p class="text-gray-700 flex-grow"><?= $excerpt ?></p>
                <a href="berita-detail.php?id=<?= $news['id'] ?>" class="mt-4 text-red-600 font-semibold hover:underline self-start">Baca Selengkapnya &rarr;</a>
              </div>
            </article>
            <?php
          }
        } else {
          echo '<p class="col-span-full text-center text-gray-500">Tidak ada berita tersedia saat ini.</p>';
        }
        ?>
      </div>
    </div>
  </section>

  <!-- FAQ SECTION -->
  <section id="faqs" class="py-16 bg-white">
    <div class="container mx-auto px-4 max-w-3xl">
      <p class="text-red-600 text-center font-semibold uppercase tracking-widest mb-2">FAQs</p>
      <h2 class="text-3xl font-extrabold text-center mb-10">Pertanyaan yang Sering Diajukan</h2>

      <div class="space-y-4">
        <?php foreach ($faqs as $index => $faq): ?>
          <div class="border border-gray-200 rounded-lg">
            <button type="button" class="w-full px-6 py-4 text-left flex justify-between items-center focus:outline-none faq-question" aria-expanded="<?= $index === 0 ? 'true' : 'false' ?>">
              <span class="font-semibold text-lg"><?= htmlspecialchars($faq['question']) ?></span>
              <svg class="w-6 h-6 transition-transform duration-300 <?= $index === 0 ? 'rotate-180' : '' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>
            <div class="px-6 pb-4 text-gray-700 faq-answer <?= $index === 0 ? 'block' : 'hidden' ?>">
              <?= nl2br(htmlspecialchars($faq['answer'])) ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- FOOTER -->
  <footer class="bg-gray-900 text-gray-300 py-10">
    <div class="container mx-auto px-4 flex flex-col md:flex-row justify-between items-center">
      <div class="mb-6 md:mb-0">
        <h2 class="text-2xl font-bold text-red-600">Mobila</h2>
        <p class="max-w-md mt-2">Platform terpercaya untuk pemesanan dan penjualan mobil online. Dapatkan pengalaman terbaik dalam memiliki mobil impian Anda.</p>
      </div>
      <div class="flex space-x-6 text-2xl">
        <a href="#" class="hover:text-red-600" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
        <a href="#" class="hover:text-red-600" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
        <a href="#" class="hover:text-red-600" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
        <a href="#" class="hover:text-red-600" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
      </div>
    </div>
    <div class="text-center text-gray-500 mt-8 text-sm">
      &copy; <?= date('Y') ?> Mobila. Dibuat dengan ❤️ oleh Kelompok 5
    </div>
  </footer>

  <!-- FontAwesome CDN for icons -->
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

  <!-- Scripts -->
  <script>
    // Mobile menu toggle
    const menuBtn = document.getElementById('menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    menuBtn.addEventListener('click', () => {
      mobileMenu.classList.toggle('hidden');
    });

    // Navbar background on scroll
    const navbar = document.getElementById('navbar');
    window.addEventListener('scroll', () => {
      if(window.scrollY > 80) {
        navbar.classList.add('bg-white', 'shadow-lg');
      } else {
        navbar.classList.remove('bg-white', 'shadow-lg');
      }
    });

    // Filter cars by brand
    const filterButtons = document.querySelectorAll('.filter-btn');
    const carCards = document.querySelectorAll('.car-card');

    filterButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        filterButtons.forEach(b => b.classList.remove('active', 'bg-red-600', 'text-white'));
        btn.classList.add('active', 'bg-red-600', 'text-white');

        const filter = btn.getAttribute('data-filter');
        carCards.forEach(card => {
          if(filter === 'all' || card.getAttribute('data-brand') === filter) {
            card.classList.remove('hidden');
          } else {
            card.classList.add('hidden');
          }
        });
      });
    });

    // FAQ toggle
    document.querySelectorAll('.faq-question').forEach(button => {
      button.addEventListener('click', () => {
        const answer = button.nextElementSibling;
        const expanded = button.getAttribute('aria-expanded') === 'true';
        button.setAttribute('aria-expanded', !expanded);
        answer.classList.toggle('hidden');
        button.querySelector('svg').classList.toggle('rotate-180');
      });
    });
  </script>
</body>
</html>
