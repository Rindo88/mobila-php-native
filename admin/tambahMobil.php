<?php
// Aktifkan error reporting untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require '../config/db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

if ($conn->connect_error) die('Koneksi gagal: '.$conn->connect_error);

$merek_result    = $conn->query("SELECT id_merek, nama_merek FROM merek");
$kategori_result = $conn->query("SELECT id_kategori, nama_kategori FROM kategori");

// Variabel untuk menampung error
$error = '';
$success = '';

/* ===========================  PROSES SUBMIT  =========================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        /* --- Tangkap input --- */
        $nama_mobil  = trim($_POST['nama_mobil']);
        $id_merek    = intval($_POST['id_merek']);
        $id_kategori = intval($_POST['id_kategori']);
        $harga       = intval($_POST['harga']);

        $video_url   = trim($_POST['video_url'] ?? '');
        $bahan_bakar = trim($_POST['bahan_bakar'] ?? '');
        $transmisi   = trim($_POST['transmisi'] ?? '');
        $kap_mesin   = trim($_POST['kapasitas_mesin'] ?? '');
        $tenaga      = trim($_POST['tenaga'] ?? '');
        $kap_kursi   = intval($_POST['kapasitas_tempat_duduk'] ?? 0);

        $totalGambar = count($_FILES['gambar']['name']);

        // Validasi input wajib
        if (empty($nama_mobil) || $id_merek <= 0 || $id_kategori <= 0 || $harga <= 0) {
            throw new Exception('Data wajib tidak boleh kosong');
        }

        // Validasi gambar (sementara di nonaktifkan untuk testing)
        // if ($totalGambar < 5) {
        //     throw new Exception('Minimal upload 5 gambar.');
        // }

        $spesifikasi = json_encode([
            'bahan_bakar'            => $bahan_bakar,
            'transmisi'              => $transmisi,
            'kapasitas_mesin'        => $kap_mesin,
            'tenaga'                 => $tenaga,
            'kapasitas_tempat_duduk' => $kap_kursi
        ], JSON_UNESCAPED_UNICODE);

        /* --- Folder upload --- */
        $webUploadDir  = 'uploads/';
        $diskUploadDir = dirname(__DIR__).'/'.$webUploadDir;

        if (!is_dir($diskUploadDir)) {
            mkdir($diskUploadDir, 0777, true);
        }

        /* =================  INSERT DATA MOBIL  ================= */
        $stmt = $conn->prepare("
            INSERT INTO mobil
            (nama_mobil, id_merek, id_kategori, harga,
             spesifikasi, video_url, bahan_bakar, transmisi,
             kapasitas_mesin, tenaga, kapasitas_tempat_duduk)
            VALUES (?,?,?,?,?,?,?,?,?,?,?)
        ");
        
        if (!$stmt) {
            throw new Exception('Prepare statement gagal: ' . $conn->error);
        }
        
        $stmt->bind_param(
            'siiissssssi',
            $nama_mobil, $id_merek, $id_kategori, $harga,
            $spesifikasi, $video_url, $bahan_bakar, $transmisi,
            $kap_mesin, $tenaga, $kap_kursi
        );
        
        if (!$stmt->execute()) {
            throw new Exception('Gagal insert mobil: '.$stmt->error);
        }

        $id_mobil = $stmt->insert_id;
        $stmt->close();

        /* =================  UPLOAD SEMUA GAMBAR  ================= */
        $uploaded_count = 0;
        for ($i = 0; $i < $totalGambar; $i++) {
            if ($_FILES['gambar']['error'][$i] !== 0) {
                continue;
            }

            // Validasi tipe file
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $file_type = $_FILES['gambar']['type'][$i];
            
            if (!in_array($file_type, $allowed_types)) {
                continue;
            }

            $original_name = basename($_FILES['gambar']['name'][$i]);
            $file_extension = pathinfo($original_name, PATHINFO_EXTENSION);
            $fileName = time().'_'.uniqid().'.'.$file_extension;
            $targetPath = $diskUploadDir.$fileName;

            if (move_uploaded_file($_FILES['gambar']['tmp_name'][$i], $targetPath)) {
                $stmtImg = $conn->prepare("
                    INSERT INTO gambar_mobil (id_mobil, gambar)
                    VALUES (?, ?)
                ");
                if ($stmtImg) {
                    $stmtImg->bind_param('is', $id_mobil, $fileName);
                    $stmtImg->execute();
                    $stmtImg->close();
                    $uploaded_count++;
                }
            }
        }

        // SET SESSION SUCCESS DAN REDIRECT
        $_SESSION['success_message'] = "Mobil berhasil ditambahkan!";
        header('Location: dataMobil.php');
        exit;

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tambah Mobil - AdminMobil</title>
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
    .step-indicator {
      transition: all 0.3s ease;
    }
    .step-indicator.active {
      background-color: #3b82f6;
      color: white;
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
            <h1 class="text-xl font-bold text-gray-900">Tambah Mobil Baru</h1>
            <p class="text-sm text-gray-600">Tambahkan mobil baru ke dalam sistem</p>
          </div>
        </div>
        <div class="flex items-center space-x-4">
          <span class="text-sm text-gray-700"><?= htmlspecialchars($_SESSION['admin_username']); ?></span>
          <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold">
            <?= strtoupper(substr(htmlspecialchars($_SESSION['admin_username']), 0, 1)) ?>
          </div>
        </div>
      </div>
    </div>
  </header>

  <!-- Progress Steps -->
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
    <div class="flex items-center justify-center space-x-8 mb-8">
      <div class="step-indicator flex items-center justify-center w-10 h-10 rounded-full bg-blue-100 text-blue-600 font-semibold active">
        <span>1</span>
      </div>
      <div class="h-1 w-16 bg-blue-200"></div>
      <div class="step-indicator flex items-center justify-center w-10 h-10 rounded-full bg-blue-100 text-blue-600 font-semibold">
        <span>2</span>
      </div>
      <div class="h-1 w-16 bg-blue-200"></div>
      <div class="step-indicator flex items-center justify-center w-10 h-10 rounded-full bg-blue-100 text-blue-600 font-semibold">
        <span>3</span>
      </div>
    </div>
  </div>

  <!-- Main Form -->
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Error Message -->
    <?php if (!empty($error)): ?>
    <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
      <strong class="font-bold">Error! </strong>
      <span class="block sm:inline"><?= htmlspecialchars($error) ?></span>
    </div>
    <?php endif; ?>

    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
      <form method="POST" enctype="multipart/form-data" id="mobilForm">
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
                     value="<?= isset($_POST['nama_mobil']) ? htmlspecialchars($_POST['nama_mobil']) : '' ?>">
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
                       value="<?= isset($_POST['harga']) ? htmlspecialchars($_POST['harga']) : '' ?>">
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
                $merek_result->data_seek(0); // Reset pointer
                while ($merek = $merek_result->fetch_assoc()): 
                ?>
                  <option value="<?= $merek['id_merek'] ?>" 
                    <?= (isset($_POST['id_merek']) && $_POST['id_merek'] == $merek['id_merek']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($merek['nama_merek']) ?>
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
                $kategori_result->data_seek(0); // Reset pointer
                while ($kategori = $kategori_result->fetch_assoc()): 
                ?>
                  <option value="<?= $kategori['id_kategori'] ?>"
                    <?= (isset($_POST['id_kategori']) && $_POST['id_kategori'] == $kategori['id_kategori']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($kategori['nama_kategori']) ?>
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
              <select name="bahan_bakar" 
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">-- Pilih Bahan Bakar --</option>
                <option value="Bensin" <?= (isset($_POST['bahan_bakar']) && $_POST['bahan_bakar'] == 'Bensin') ? 'selected' : '' ?>>Bensin</option>
                <option value="Solar" <?= (isset($_POST['bahan_bakar']) && $_POST['bahan_bakar'] == 'Solar') ? 'selected' : '' ?>>Solar</option>
                <option value="Listrik" <?= (isset($_POST['bahan_bakar']) && $_POST['bahan_bakar'] == 'Listrik') ? 'selected' : '' ?>>Listrik</option>
                <option value="Hybrid" <?= (isset($_POST['bahan_bakar']) && $_POST['bahan_bakar'] == 'Hybrid') ? 'selected' : '' ?>>Hybrid</option>
              </select>
            </div>

            <!-- Transmisi -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-exchange-alt mr-2 text-blue-500"></i>
                Transmisi
              </label>
              <select name="transmisi" 
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">-- Pilih Transmisi --</option>
                <option value="Manual" <?= (isset($_POST['transmisi']) && $_POST['transmisi'] == 'Manual') ? 'selected' : '' ?>>Manual</option>
                <option value="Automatic" <?= (isset($_POST['transmisi']) && $_POST['transmisi'] == 'Automatic') ? 'selected' : '' ?>>Automatic</option>
                <option value="CVT" <?= (isset($_POST['transmisi']) && $_POST['transmisi'] == 'CVT') ? 'selected' : '' ?>>CVT</option>
              </select>
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
                     value="<?= isset($_POST['kapasitas_mesin']) ? htmlspecialchars($_POST['kapasitas_mesin']) : '' ?>">
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
                     value="<?= isset($_POST['tenaga']) ? htmlspecialchars($_POST['tenaga']) : '' ?>">
            </div>

            <!-- Kapasitas Tempat Duduk -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-users mr-2 text-green-500"></i>
                Kapasitas Tempat Duduk
              </label>
              <select name="kapasitas_tempat_duduk" 
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">-- Pilih Kapasitas --</option>
                <option value="2" <?= (isset($_POST['kapasitas_tempat_duduk']) && $_POST['kapasitas_tempat_duduk'] == '2') ? 'selected' : '' ?>>2 Orang</option>
                <option value="4" <?= (isset($_POST['kapasitas_tempat_duduk']) && $_POST['kapasitas_tempat_duduk'] == '4') ? 'selected' : '' ?>>4 Orang</option>
                <option value="5" <?= (isset($_POST['kapasitas_tempat_duduk']) && $_POST['kapasitas_tempat_duduk'] == '5') ? 'selected' : '' ?>>5 Orang</option>
                <option value="7" <?= (isset($_POST['kapasitas_tempat_duduk']) && $_POST['kapasitas_tempat_duduk'] == '7') ? 'selected' : '' ?>>7 Orang</option>
                <option value="8" <?= (isset($_POST['kapasitas_tempat_duduk']) && $_POST['kapasitas_tempat_duduk'] == '8') ? 'selected' : '' ?>>8 Orang</option>
              </select>
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
                     value="<?= isset($_POST['video_url']) ? htmlspecialchars($_POST['video_url']) : '' ?>">
            </div>
          </div>
        </div>

        <!-- Upload Gambar -->
        <div class="form-section p-6">
          <div class="flex items-center mb-6">
            <div class="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center mr-4">
              <i class="fas fa-images text-yellow-600"></i>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-900">Gambar Mobil</h3>
              <p class="text-sm text-gray-600">Unggah foto mobil (minimal 1 gambar)</p>
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
            <input type="file" id="gambarUpload" name="gambar[]" accept="image/*" multiple required 
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
            <button type="submit" class="bg-gradient-to-r from-blue-600 to-blue-700 text-white px-8 py-3 rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all flex items-center shadow-md">
              <i class="fas fa-save mr-2"></i>
              Simpan Mobil
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <script>
    // Drag and drop functionality
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

    // Form validation (disederhanakan sementara)
    document.getElementById('mobilForm').addEventListener('submit', function(e) {
      const fileInput = document.getElementById('gambarUpload');
      if (!fileInput.files || fileInput.files.length === 0) {
        e.preventDefault();
        Swal.fire({
          title: 'Peringatan',
          text: 'Harap pilih minimal 1 gambar',
          icon: 'warning',
          confirmButtonColor: '#3085d6',
          confirmButtonText: 'OK'
        });
      }
    });
  </script>
</body>
</html>