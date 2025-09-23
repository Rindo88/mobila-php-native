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
  <link href="assets/css/berita-otomotif.css" rel="stylesheet" />

  <!-- Font Awesome -->
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
        integrity="sha512-pIVp6fOS8l9kBdlx2Y7NggAWn6jISjzA4k9sbw4dNf5Wh0n2FElz2ZyPhY1D9shCqOQ73N0lZfNEJZvhTgA5iw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

  <style>
    /* ====== Warna dasar yang lebih kalem ====== */
    :root {
      --primary: #2563eb;    /* biru utama */
      --primary-dark: #1e40af;
      --background: #f5f7fa; /* abu terang */
      --text-main: #1f2937;  /* abu gelap */
      --text-secondary: #4b5563;
      --card-bg: #ffffff;
    }

    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: var(--background);
      color: var(--text-main);
      margin: 0;
      padding: 0;
      line-height: 1.6;
    }

    header {
      text-align: center;
      padding: 30px 20px 10px;
      background: linear-gradient(135deg, var(--primary-dark), var(--primary));
      color: #fff;
    }

    header h1 {
      margin: 0;
      font-size: 28px;
    }

    .btn-back {
      display: inline-block;
      margin: 20px;
      padding: 10px 20px;
      background-color: var(--primary);
      color: #fff;
      text-decoration: none;
      border-radius: 6px;
      transition: background 0.2s ease;
    }

    .btn-back:hover {
      background-color: var(--primary-dark);
    }

    .news-container {
      max-width: 1000px;
      margin: 0 auto;
      padding: 20px;
    }

    .news-container h2 {
      font-size: 24px;
      margin-bottom: 20px;
      color: var(--primary);
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .news-item {
      display: flex;
      gap: 20px;
      background: var(--card-bg);
      border-radius: 16px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.08);
      padding: 16px;
      margin-bottom: 24px;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .news-item:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.12);
    }

    .news-item img {
      width: 220px;
      height: 150px;
      object-fit: cover;
      border-radius: 12px;
      flex-shrink: 0;
    }

    .news-content {
      flex: 1;
      display: flex;
      flex-direction: column;
    }

    .news-content h3 {
      font-size: 20px;
      font-weight: 700;
      color: var(--text-main);
      margin: 0 0 10px;
    }

    .meta {
      font-size: 13px;
      color: var(--text-secondary);
      margin-bottom: 10px;
    }

    .excerpt {
      font-size: 15px;
      color: var(--text-main);
      margin-bottom: 12px;
    }

    .btn-readmore {
      align-self: flex-start;
      background-color: var(--primary);
      color: #fff;
      padding: 10px 20px;
      border-radius: 6px;
      text-decoration: none;
      font-weight: 600;
      transition: background 0.2s ease;
    }

    .btn-readmore:hover {
      background-color: var(--primary-dark);
    }
  </style>
</head>
<body>

<header>
  <h1><i class="fa-solid fa-car-side"></i> Berita Otomotif Terkini</h1>
</header>

<div>
  <a class="btn-back" href="<?= $is_logged_in ? 'pengguna.php' : 'index.php'; ?>">← Kembali</a>
</div>

<div class="news-container">
  <h2><i class="fa-solid fa-fire"></i> Trending</h2>

  <?php while ($row = mysqli_fetch_assoc($berita)): ?>
    <div class="news-item">
      <a href="berita-detail.php?id=<?= $row['id']; ?>">
        <img src="uploads/<?= htmlspecialchars($row['gambar']); ?>"
             alt="<?= htmlspecialchars($row['judul']); ?>" />
      </a>

      <div class="news-content">
        <h3><?= htmlspecialchars($row['judul']); ?></h3>
        <div class="meta">
          Dipublikasikan: <?= date('d M Y', strtotime($row['tanggal_publikasi'])); ?>
          — Oleh: <?= htmlspecialchars($row['penulis']); ?>
        </div>
        <p class="excerpt">
          <?= mb_strimwidth(strip_tags($row['isi']), 0, 150, '...'); ?>
        </p>
        <a class="btn-readmore" href="berita-detail.php?id=<?= $row['id']; ?>">Read More ›</a>
      </div>
    </div>
  <?php endwhile; ?>
</div>

</body>
</html>
