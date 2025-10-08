<?php
require '../config/db.php';
$alert = "";

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    header("Location: dataMobil.php");
    exit;
}

$merekResult    = $conn->query("SELECT id_merek, nama_merek FROM merek");
$kategoriResult = $conn->query("SELECT id_kategori, nama_kategori FROM kategori");

// Ambil data mobil
$stmt = $conn->prepare("
    SELECT nama_mobil, id_merek, id_kategori, harga, video_url, video, bahan_bakar, transmisi, kapasitas_mesin, tenaga, kapasitas_tempat_duduk 
    FROM mobil 
    WHERE id_mobil = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($nama_mobil, $id_merek, $id_kategori, $harga, $video_url, $oldVideo, $bahan_bakar, $transmisi, $kapasitas_mesin, $tenaga, $kapasitas_tempat_duduk);
if (!$stmt->fetch()) {
    header("Location: dataMobil.php");
    exit;
}
$stmt->close();

$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_mobil  = trim($_POST['nama_mobil']);
    $id_merek    = (int) $_POST['id_merek'];
    $id_kategori = (int) $_POST['id_kategori'];
    $harga       = (int) $_POST['harga'];
    $video_url   = trim($_POST['video_url']);
    $bahan_bakar = trim($_POST['bahan_bakar']);
    $transmisi   = trim($_POST['transmisi']);
    $kapasitas_mesin = trim($_POST['kapasitas_mesin']);
    $tenaga = trim($_POST['tenaga']);
    $kapasitas_tempat_duduk = (int) $_POST['kapasitas_tempat_duduk'];

    // Upload video baru jika ada
    $videoPath = $oldVideo;
    if (!empty($_FILES['video']['name']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {
        $tmp  = $_FILES['video']['tmp_name'];
        $name = basename($_FILES['video']['name']);
        $newVideo = "uploads/" . uniqid() . "_" . $name;
        $fullPath = __DIR__ . '/../' . $newVideo;
        if (move_uploaded_file($tmp, $fullPath)) {
            if ($oldVideo && file_exists(__DIR__ . '/../' . $oldVideo)) {
                @unlink(__DIR__ . '/../' . $oldVideo);
            }
            $videoPath = $newVideo;
        } else {
            $alert = "Gagal mengupload video baru.";
        }
    }

    if (!$alert) {
        $sql = "
            UPDATE mobil SET
              nama_mobil = ?, id_merek = ?, id_kategori = ?, harga = ?, video_url = ?, video = ?,
              bahan_bakar = ?, transmisi = ?, kapasitas_mesin = ?, tenaga = ?, kapasitas_tempat_duduk = ?
            WHERE id_mobil = ?
        ";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "siiisssssiii",
            $nama_mobil, $id_merek, $id_kategori, $harga, $video_url, $videoPath,
            $bahan_bakar, $transmisi, $kapasitas_mesin, $tenaga, $kapasitas_tempat_duduk, $id
        );

        if ($stmt->execute()) {
            $stmt->close();

            // Upload gambar baru
            if (!empty($_FILES['gambar']['name'][0])) {
                $totalFiles = count($_FILES['gambar']['name']);
                for ($i = 0; $i < $totalFiles; $i++) {
                    $tmpName  = $_FILES['gambar']['tmp_name'][$i];
                    $fileName = basename($_FILES['gambar']['name'][$i]);
                    $ext      = pathinfo($fileName, PATHINFO_EXTENSION);
                    $newName  = 'uploads/' . uniqid() . '.' . $ext;

                    if (move_uploaded_file($tmpName, __DIR__ . '/../' . $newName)) {
                        $stmt2 = $conn->prepare("INSERT INTO gambar_mobil (id_mobil, gambar) VALUES (?, ?)");
                        $stmt2->bind_param("is", $id, $newName);
                        $stmt2->execute();
                        $stmt2->close();
                    }
                }
            }

            $success = true;
        } else {
            $alert = "Gagal menyimpan perubahan ke database.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Mobil - AdminMobil</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    .form-section {
      transition: all 0.3s ease;
    }
    .form-section:hover {
      transform: translateY(-2px);
    }
    .input-focus:focus {
      box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
      border-color: #3b82f6;
    }
    .file-upload-area {
      border: 2px dashed #d1d5db;
      transition: all 0.3s ease;
    }
    .file-upload-area:hover {
      border-color: #3b82f6;
      background-color: #f8fafc;
    }
    .file-upload-area.dragover {
      border-color: #3b82f6;
      background-color: #eff6ff;
    }
  </style>
</head>
<body class="bg-gray-50 min-h-screen">
  <!-- Header -->
  <header class="bg-white shadow-sm border-b">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center py-4">
        <div class="flex items-center">
          <a href="dataMobil.php" class="text-blue-600 hover:text-blue-800 mr-4">
            <i class="fas fa-arrow-left"></i>
          </a>
          <div>
            <h1 class="text-xl font-bold text-gray-900">Edit Mobil</h1>
            <p class="text-sm text-gray-600">Edit data mobil yang sudah ada</p>
          </div>
        </div>
        <div class="flex items-center space-x-4">
          <span class="text-sm text-gray-700">Administrator</span>
          <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold">
            <i class="fas fa-user"></i>
          </div>
        </div>
      </div>
    </div>
  </header>

  <!-- Main Form -->
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Alert Messages -->
    <?php if ($alert): ?>
      <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg flex items-center">
        <i class="fas fa-exclamation-triangle mr-3"></i>
        <span><?= htmlspecialchars($alert) ?></span>
      </div>
    <?php endif; ?>

    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
      <form method="POST" enctype="multipart/form-data" id="editMobilForm">
        <!-- Informasi Dasar -->
        <div class="form-section p-6 border-b border-gray-200">
          <div class="flex items-center mb-6">
            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center mr-4">
              <i class="fas fa-car text-blue-600"></i>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-900">Informasi Dasar</h3>
              <p class="text-sm text-gray-600">Informasi utama tentang mobil</p>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Nama Mobil -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-tag mr-2 text-blue-500"></i>
                Nama Mobil <span class="text-red-500">*</span>
              </label>
              <input type="text" name="nama_mobil" required 
                     class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus focus:outline-none focus:ring-2 focus:ring-blue-500"
                     placeholder="Contoh: Toyota Avanza Veloz"
                     value="<?= htmlspecialchars($nama_mobil) ?>">
            </div>

            <!-- Harga -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-tag mr-2 text-green-500"></i>
                Harga <span class="text-red-500">*</span>
              </label>
              <div class="relative">
                <span class="absolute left-3 top-3 text-gray-500">Rp</span>
                <input type="number" name="harga" required 
                       class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg input-focus focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="300000000"
                       value="<?= $harga ?>">
              </div>
            </div>

            <!-- Merek -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-building mr-2 text-purple-500"></i>
                Merek <span class="text-red-500">*</span>
              </label>
              <select name="id_merek" required 
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">-- Pilih Merek --</option>
                <?php 
                $merekResult->data_seek(0);
                while ($m = $merekResult->fetch_assoc()): 
                ?>
                  <option value="<?= $m['id_merek'] ?>" <?= $m['id_merek'] == $id_merek ? 'selected' : '' ?>>
                    <?= htmlspecialchars($m['nama_merek']) ?>
                  </option>
                <?php endwhile; ?>
              </select>
            </div>

            <!-- Kategori -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-layer-group mr-2 text-orange-500"></i>
                Kategori <span class="text-red-500">*</span>
              </label>
              <select name="id_kategori" required 
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">-- Pilih Kategori --</option>
                <?php 
                $kategoriResult->data_seek(0);
                while ($k = $kategoriResult->fetch_assoc()): 
                ?>
                  <option value="<?= $k['id_kategori'] ?>" <?= $k['id_kategori'] == $id_kategori ? 'selected' : '' ?>>
                    <?= htmlspecialchars($k['nama_kategori']) ?>
                  </option>
                <?php endwhile; ?>
              </select>
            </div>
          </div>
        </div>

        <!-- Spesifikasi Teknis -->
        <div class="form-section p-6 border-b border-gray-200">
          <div class="flex items-center mb-6">
            <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center mr-4">
              <i class="fas fa-cogs text-green-600"></i>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-900">Spesifikasi Teknis</h3>
              <p class="text-sm text-gray-600">Detail teknis kendaraan</p>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Bahan Bakar -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-gas-pump mr-2 text-red-500"></i>
                Bahan Bakar
              </label>
              <input type="text" name="bahan_bakar" 
                     class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus focus:outline-none focus:ring-2 focus:ring-blue-500"
                     placeholder="Contoh: Bensin"
                     value="<?= htmlspecialchars($bahan_bakar) ?>">
            </div>

            <!-- Transmisi -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-exchange-alt mr-2 text-blue-500"></i>
                Transmisi
              </label>
              <input type="text" name="transmisi" 
                     class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus focus:outline-none focus:ring-2 focus:ring-blue-500"
                     placeholder="Contoh: Manual"
                     value="<?= htmlspecialchars($transmisi) ?>">
            </div>

            <!-- Kapasitas Mesin -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-tachometer-alt mr-2 text-purple-500"></i>
                Kapasitas Mesin
              </label>
              <input type="text" name="kapasitas_mesin" 
                     class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus focus:outline-none focus:ring-2 focus:ring-blue-500"
                     placeholder="Contoh: 1500 cc"
                     value="<?= htmlspecialchars($kapasitas_mesin) ?>">
            </div>

            <!-- Tenaga -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-horse-head mr-2 text-orange-500"></i>
                Tenaga
              </label>
              <input type="text" name="tenaga" 
                     class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus focus:outline-none focus:ring-2 focus:ring-blue-500"
                     placeholder="Contoh: 105 HP"
                     value="<?= htmlspecialchars($tenaga) ?>">
            </div>

            <!-- Kapasitas Tempat Duduk -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-users mr-2 text-green-500"></i>
                Kapasitas Tempat Duduk
              </label>
              <input type="number" name="kapasitas_tempat_duduk" 
                     class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus focus:outline-none focus:ring-2 focus:ring-blue-500"
                     placeholder="Contoh: 5"
                     value="<?= $kapasitas_tempat_duduk ?>">
            </div>

            <!-- Video URL -->
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-video mr-2 text-red-500"></i>
                Video URL (YouTube)
              </label>
              <input type="text" name="video_url" 
                     class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus focus:outline-none focus:ring-2 focus:ring-blue-500"
                     placeholder="https://www.youtube.com/watch?v=..."
                     value="<?= htmlspecialchars($video_url) ?>">
            </div>
          </div>
        </div>

        <!-- Video Section -->
        <div class="form-section p-6 border-b border-gray-200">
          <div class="flex items-center mb-6">
            <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center mr-4">
              <i class="fas fa-film text-purple-600"></i>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-900">Video Mobil</h3>
              <p class="text-sm text-gray-600">Kelola video untuk mobil ini</p>
            </div>
          </div>

          <!-- Current Video -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Video Saat Ini</label>
            <?php if ($oldVideo): ?>
              <div class="bg-gray-100 p-4 rounded-lg">
                <video controls class="w-full max-w-2xl mx-auto rounded-lg">
                  <source src="<?= htmlspecialchars($oldVideo) ?>" type="video/mp4">
                  Browser Anda tidak mendukung tag video.
                </video>
                <p class="text-sm text-gray-600 mt-2 text-center"><?= basename($oldVideo) ?></p>
              </div>
            <?php else: ?>
              <div class="bg-gray-100 p-8 text-center rounded-lg">
                <i class="fas fa-video-slash text-gray-400 text-4xl mb-3"></i>
                <p class="text-gray-600">Tidak ada video yang diupload</p>
              </div>
            <?php endif; ?>
          </div>

          <!-- Upload New Video -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Upload Video Baru (Opsional)
            </label>
            <div class="file-upload-area p-6 rounded-lg text-center cursor-pointer">
              <i class="fas fa-cloud-upload-alt text-2xl text-gray-400 mb-3"></i>
              <p class="text-sm font-medium text-gray-700 mb-2">Klik untuk memilih video baru</p>
              <p class="text-xs text-gray-500 mb-3">atau drag & drop video di sini</p>
              <input type="file" name="video" accept="video/*" 
                     class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
              <p class="text-xs text-gray-500 mt-2">Format yang didukung: MP4, AVI, MOV. Maksimal 50MB.</p>
            </div>
          </div>
        </div>

        <!-- Upload Gambar Baru -->
        <div class="form-section p-6">
          <div class="flex items-center mb-6">
            <div class="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center mr-4">
              <i class="fas fa-images text-yellow-600"></i>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-900">Gambar Tambahan</h3>
              <p class="text-sm text-gray-600">Upload gambar baru untuk mobil ini</p>
            </div>
          </div>

          <div class="file-upload-area p-8 rounded-lg text-center cursor-pointer mb-4" id="dropZone">
            <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-4"></i>
            <p class="text-lg font-medium text-gray-700 mb-2">Drag & drop gambar di sini</p>
            <p class="text-sm text-gray-500 mb-4">atau</p>
            <label for="gambarUpload" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 cursor-pointer inline-block">
              <i class="fas fa-folder-open mr-2"></i>
              Pilih File
            </label>
            <input type="file" id="gambarUpload" name="gambar[]" accept="image/*" multiple 
                   class="hidden" onchange="previewImages(this)">
            <p class="text-xs text-gray-500 mt-4">Format yang didukung: JPG, PNG, WebP. Maksimal 5MB per file.</p>
          </div>

          <!-- Image Preview -->
          <div id="imagePreview" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mt-6 hidden"></div>
        </div>

        <!-- Action Buttons -->
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
          <div class="flex justify-between items-center">
            <a href="dataMobil.php" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors flex items-center">
              <i class="fas fa-arrow-left mr-2"></i>
              Kembali
            </a>
            <button type="submit" class="bg-gradient-to-r from-green-600 to-green-700 text-white px-8 py-3 rounded-lg hover:from-green-700 hover:to-green-800 transition-all flex items-center shadow-md">
              <i class="fas fa-save mr-2"></i>
              Update Mobil
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <script>
    // Drag and drop functionality for images
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('gambarUpload');

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
      dropZone.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
      e.preventDefault();
      e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
      dropZone.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
      dropZone.addEventListener(eventName, unhighlight, false);
    });

    function highlight() {
      dropZone.classList.add('dragover');
    }

    function unhighlight() {
      dropZone.classList.remove('dragover');
    }

    dropZone.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
      const dt = e.dataTransfer;
      const files = dt.files;
      fileInput.files = files;
      previewImages(fileInput);
    }

    // Image preview functionality
    function previewImages(input) {
      const preview = document.getElementById('imagePreview');
      preview.innerHTML = '';
      
      if (input.files && input.files.length > 0) {
        preview.classList.remove('hidden');
        
        for (let i = 0; i < input.files.length; i++) {
          const file = input.files[i];
          const reader = new FileReader();
          
          reader.onload = function(e) {
            const div = document.createElement('div');
            div.className = 'relative group';
            div.innerHTML = `
              <img src="${e.target.result}" class="w-full h-32 object-cover rounded-lg">
              <div class="text-xs text-gray-600 mt-2 truncate">${file.name}</div>
            `;
            preview.appendChild(div);
          }
          
          reader.readAsDataURL(file);
        }
      } else {
        preview.classList.add('hidden');
      }
    }

    // Success message handling
    <?php if ($success): ?>
    document.addEventListener('DOMContentLoaded', function() {
      Swal.fire({
        title: 'Berhasil!',
        text: 'Data mobil berhasil diperbarui.',
        icon: 'success',
        confirmButtonColor: '#10b981',
        confirmButtonText: 'OK'
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = 'dataMobil.php';
        }
      });
    });
    <?php endif; ?>
  </script>
</body>
</html>