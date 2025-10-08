<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul     = trim($_POST['judul']);
    $isi       = trim($_POST['isi']);
    $tanggal   = $_POST['tanggal_publikasi'];
    $penulis   = trim($_POST['penulis']);
    $status    = $_POST['status']; 

    // Handle file upload dengan validasi
    $gambar = '';
    if (!empty($_FILES['gambar']['name']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $gambar_name = $_FILES['gambar']['name'];
        $tmp = $_FILES['gambar']['tmp_name'];
        
        // Validasi tipe file
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = $_FILES['gambar']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            // Generate unique filename
            $file_extension = pathinfo($gambar_name, PATHINFO_EXTENSION);
            $gambar = time() . '_' . uniqid() . '.' . $file_extension;
            $upload_path = '../uploads/' . $gambar;
            
            // Buat direktori jika belum ada
            if (!is_dir('../uploads/')) {
                mkdir('../uploads/', 0755, true);
            }
            
            if (move_uploaded_file($tmp, $upload_path)) {
                // Upload success
            } else {
                $_SESSION['berita_status'] = 'upload_failed';
                header('Location: tambahBerita.php');
                exit();
            }
        } else {
            $_SESSION['berita_status'] = 'invalid_file_type';
            header('Location: tambahBerita.php');
            exit();
        }
    } else {
        $_SESSION['berita_status'] = 'no_image';
        header('Location: tambahBerita.php');
        exit();
    }

    // Insert data ke database
    $stmt = $conn->prepare("INSERT INTO berita (judul, isi, gambar, tanggal_publikasi, penulis, status) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("ssssss", $judul, $isi, $gambar, $tanggal, $penulis, $status);

        if ($stmt->execute()) {
            $_SESSION['berita_status'] = 'success';
        } else {
            $_SESSION['berita_status'] = 'error';
        }
        $stmt->close();
    } else {
        $_SESSION['berita_status'] = 'error';
    }

    header('Location: tambahBerita.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tambah Berita - AdminMobil</title>
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
          <a href="dataBerita.php" class="text-blue-600 hover:text-blue-800 mr-4">
            <i class="fas fa-arrow-left"></i>
          </a>
          <div>
            <h1 class="text-xl font-bold text-gray-900">Tambah Berita Baru</h1>
            <p class="text-sm text-gray-600">Buat berita baru untuk website</p>
          </div>
        </div>
        <div class="flex items-center space-x-4">
          <span class="text-sm text-gray-700"><?= htmlspecialchars($_SESSION['admin_username']) ?></span>
          <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold">
            <?= strtoupper(substr(htmlspecialchars($_SESSION['admin_username']), 0, 1)) ?>
          </div>
        </div>
      </div>
    </div>
  </header>

  <!-- Main Form -->
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
      <form action="" method="POST" enctype="multipart/form-data" id="tambahBeritaForm">
        <!-- Informasi Berita -->
        <div class="form-section p-6 border-b border-gray-200">
          <div class="flex items-center mb-6">
            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center mr-4">
              <i class="fas fa-newspaper text-blue-600"></i>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-900">Informasi Berita</h3>
              <p class="text-sm text-gray-600">Informasi utama tentang berita</p>
            </div>
          </div>

          <div class="grid grid-cols-1 gap-6">
            <!-- Judul -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-heading mr-2 text-blue-500"></i>
                Judul Berita <span class="text-red-500">*</span>
              </label>
              <input type="text" name="judul" required 
                     class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus focus:outline-none focus:ring-2 focus:ring-blue-500"
                     placeholder="Masukkan judul berita yang menarik"
                     value="<?= isset($_POST['judul']) ? htmlspecialchars($_POST['judul']) : '' ?>">
            </div>

            <!-- Isi Berita -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-align-left mr-2 text-green-500"></i>
                Isi Berita <span class="text-red-500">*</span>
              </label>
              <textarea name="isi" required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus focus:outline-none focus:ring-2 focus:ring-blue-500 min-h-[200px]"
                        placeholder="Tulis isi berita yang lengkap dan informatif..."><?= isset($_POST['isi']) ? htmlspecialchars($_POST['isi']) : '' ?></textarea>
              <p class="text-xs text-gray-500 mt-1">Gunakan format yang jelas dan mudah dibaca.</p>
            </div>

            <!-- Penulis -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-user-edit mr-2 text-purple-500"></i>
                Penulis <span class="text-red-500">*</span>
              </label>
              <input type="text" name="penulis" required 
                     class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus focus:outline-none focus:ring-2 focus:ring-blue-500"
                     placeholder="Nama penulis berita"
                     value="<?= isset($_POST['penulis']) ? htmlspecialchars($_POST['penulis']) : '' ?>">
            </div>
          </div>
        </div>

        <!-- Gambar dan Pengaturan -->
        <div class="form-section p-6 border-b border-gray-200">
          <div class="flex items-center mb-6">
            <div class="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center mr-4">
              <i class="fas fa-cog text-yellow-600"></i>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-900">Pengaturan</h3>
              <p class="text-sm text-gray-600">Kelola gambar dan pengaturan berita</p>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Upload Gambar -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-image mr-2 text-blue-500"></i>
                Gambar Berita <span class="text-red-500">*</span>
              </label>
              <div class="file-upload-area p-6 rounded-lg text-center cursor-pointer" id="dropZone">
                <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-3"></i>
                <p class="text-sm font-medium text-gray-700 mb-2" id="fileText">Klik untuk memilih gambar</p>
                <p class="text-xs text-gray-500 mb-3">atau drag & drop gambar di sini</p>
                <input type="file" name="gambar" accept="image/*" required 
                       class="hidden" id="gambarInput">
                <label for="gambarInput" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 cursor-pointer inline-block text-sm">
                  <i class="fas fa-folder-open mr-2"></i>
                  Pilih File
                </label>
                <p class="text-xs text-gray-500 mt-2">Format: JPG, PNG, WebP. Maksimal 2MB.</p>
              </div>
              <div id="imagePreview" class="mt-3 hidden">
                <img src="" alt="Preview" class="w-full h-32 object-cover rounded-lg shadow-sm">
              </div>
            </div>

            <!-- Tanggal dan Status -->
            <div class="space-y-6">
              <!-- Tanggal Publikasi -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  <i class="fas fa-calendar-alt mr-2 text-purple-500"></i>
                  Tanggal Publikasi <span class="text-red-500">*</span>
                </label>
                <input type="date" name="tanggal_publikasi" required 
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus focus:outline-none focus:ring-2 focus:ring-blue-500"
                       value="<?= isset($_POST['tanggal_publikasi']) ? $_POST['tanggal_publikasi'] : date('Y-m-d') ?>">
              </div>

              <!-- Status -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  <i class="fas fa-toggle-on mr-2 text-orange-500"></i>
                  Status <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-2 gap-3">
                  <label class="relative flex cursor-pointer">
                    <input type="radio" name="status" value="draft" class="peer sr-only" checked>
                    <div class="w-full py-3 px-4 text-center border-2 border-gray-300 rounded-lg peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:text-blue-700 transition-all">
                      <i class="fas fa-save mr-2"></i>
                      Draft
                    </div>
                  </label>
                  <label class="relative flex cursor-pointer">
                    <input type="radio" name="status" value="publikasi" class="peer sr-only">
                    <div class="w-full py-3 px-4 text-center border-2 border-gray-300 rounded-lg peer-checked:border-green-500 peer-checked:bg-green-50 peer-checked:text-green-700 transition-all">
                      <i class="fas fa-globe mr-2"></i>
                      Publikasikan
                    </div>
                  </label>
                </div>
                <p class="text-xs text-gray-500 mt-2">
                  <strong>Draft:</strong> Disimpan tanpa dipublikasikan<br>
                  <strong>Publikasikan:</strong> Langsung tampil di website
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
          <div class="flex justify-between items-center">
            <a href="dataBerita.php" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors flex items-center">
              <i class="fas fa-arrow-left mr-2"></i>
              Kembali
            </a>
            <div class="flex space-x-3">
              <button type="button" onclick="previewBerita()" class="px-6 py-3 border border-blue-300 text-blue-700 rounded-lg hover:bg-blue-50 transition-colors flex items-center">
                <i class="fas fa-eye mr-2"></i>
                Preview
              </button>
              <button type="submit" name="status" value="draft" class="px-6 py-3 bg-gradient-to-r from-gray-600 to-gray-700 text-white rounded-lg hover:from-gray-700 hover:to-gray-800 transition-all flex items-center shadow-md">
                <i class="fas fa-save mr-2"></i>
                Simpan Draft
              </button>
              <button type="submit" name="status" value="publikasi" class="px-6 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg hover:from-green-700 hover:to-green-800 transition-all flex items-center shadow-md">
                <i class="fas fa-paper-plane mr-2"></i>
                Simpan & Publikasikan
              </button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>

  <script>
    // Drag and drop functionality
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('gambarInput');
    const fileText = document.getElementById('fileText');
    const imagePreview = document.getElementById('imagePreview');
    const previewImg = imagePreview.querySelector('img');

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
      handleFileSelect(files[0]);
    }

    fileInput.addEventListener('change', function(e) {
      if (this.files.length > 0) {
        handleFileSelect(this.files[0]);
      }
    });

    function handleFileSelect(file) {
      if (file) {
        fileText.textContent = `File: ${file.name}`;
        
        // Show preview
        const reader = new FileReader();
        reader.onload = function(e) {
          previewImg.src = e.target.result;
          imagePreview.classList.remove('hidden');
        }
        reader.readAsDataURL(file);
      }
    }

    // Preview functionality
    function previewBerita() {
      const form = document.getElementById('tambahBeritaForm');
      const formData = new FormData(form);
      
      const judul = formData.get('judul') || '[Judul Belum Diisi]';
      const isi = formData.get('isi') || '[Isi berita belum diisi]';
      const penulis = formData.get('penulis') || '[Penulis belum diisi]';
      const status = formData.get('status') || 'draft';
      
      Swal.fire({
        title: 'Preview Berita',
        html: `
          <div class="text-left max-h-96 overflow-y-auto">
            <div class="mb-4 p-3 ${status === 'publikasi' ? 'bg-green-100 border border-green-300' : 'bg-yellow-100 border border-yellow-300'} rounded-lg">
              <strong>Status:</strong> ${status === 'publikasi' ? '📢 Akan Dipublikasikan' : '💾 Disimpan sebagai Draft'}
            </div>
            <h3 class="text-xl font-bold mb-4 text-gray-800">${judul}</h3>
            <div class="text-gray-600 mb-4 whitespace-pre-line leading-relaxed">${isi}</div>
            <div class="border-t pt-3">
              <p class="text-sm text-gray-500"><strong>Penulis:</strong> ${penulis}</p>
            </div>
          </div>
        `,
        icon: 'info',
        confirmButtonColor: '#3085d6',
        confirmButtonText: 'Tutup Preview',
        width: '600px'
      });
    }

    // Form validation
    document.getElementById('tambahBeritaForm').addEventListener('submit', function(e) {
      const judul = this.querySelector('input[name="judul"]').value.trim();
      const isi = this.querySelector('textarea[name="isi"]').value.trim();
      const gambar = this.querySelector('input[name="gambar"]').files.length;
      
      if (!judul || !isi || !gambar) {
        e.preventDefault();
        Swal.fire({
          title: 'Form Tidak Lengkap',
          text: 'Harap isi semua field yang wajib diisi termasuk gambar',
          icon: 'warning',
          confirmButtonText: 'OK'
        });
      }
    });

    // Set default date to today
    document.querySelector('input[type="date"]').value = new Date().toISOString().split('T')[0];
  </script>

  <!-- Notifikasi SweetAlert -->
  <?php if (isset($_SESSION['berita_status'])): ?>
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        <?php 
        $notif = $_SESSION['berita_status'];
        unset($_SESSION['berita_status']);
        
        if ($notif === 'success'): ?>
          Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Berita berhasil ditambahkan.',
            confirmButtonColor: '#10b981',
            confirmButtonText: 'OK'
          }).then(() => {
            window.location.href = 'dataBerita.php';
          });
        <?php elseif ($notif === 'error'): ?>
          Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: 'Terjadi kesalahan saat menyimpan berita.',
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'OK'
          });
        <?php elseif ($notif === 'upload_failed'): ?>
          Swal.fire({
            icon: 'error',
            title: 'Upload Gagal!',
            text: 'Gagal mengupload gambar. Pastikan file valid dan coba lagi.',
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'OK'
          });
        <?php elseif ($notif === 'invalid_file_type'): ?>
          Swal.fire({
            icon: 'warning',
            title: 'File Tidak Valid!',
            text: 'Format file gambar tidak didukung. Gunakan JPG, PNG, atau WebP.',
            confirmButtonColor: '#f59e0b',
            confirmButtonText: 'OK'
          });
        <?php elseif ($notif === 'no_image'): ?>
          Swal.fire({
            icon: 'warning',
            title: 'Gambar Diperlukan!',
            text: 'Harap pilih gambar untuk berita.',
            confirmButtonColor: '#f59e0b',
            confirmButtonText: 'OK'
          });
        <?php endif; ?>
      });
    </script>
  <?php endif; ?>
</body>
</html>