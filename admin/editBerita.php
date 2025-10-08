<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Ambil data berita dengan error handling
$result = mysqli_query($conn, "SELECT * FROM berita WHERE id = $id");
if (!$result) {
    die("Error: " . mysqli_error($conn));
}

$data = mysqli_fetch_assoc($result);

// Jika tidak ada data
if (!$data) {
    echo "<script>
        Swal.fire({
            title: 'Error!',
            text: 'Data berita tidak ditemukan.',
            icon: 'error',
            confirmButtonText: 'OK'
        }).then(() => {
            window.location.href = 'dataBerita.php';
        });
    </script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul   = trim($_POST['judul']);
    $isi     = trim($_POST['isi']);
    $tanggal = $_POST['tanggal_publikasi'];
    $penulis = trim($_POST['penulis']);
    $status  = $_POST['status'];

    // Cek apakah ada gambar baru
    if (!empty($_FILES['gambar']['name']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $gambar = time() . '_' . basename($_FILES['gambar']['name']);
        $tmp = $_FILES['gambar']['tmp_name'];
        $upload_dir = '../uploads/';
        
        // Buat direktori jika belum ada
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Validasi tipe file
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = $_FILES['gambar']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            if (move_uploaded_file($tmp, $upload_dir . $gambar)) {
                // Hapus gambar lama jika ada
                if (!empty($data['gambar']) && file_exists($upload_dir . $data['gambar'])) {
                    unlink($upload_dir . $data['gambar']);
                }
            } else {
                $gambar = $data['gambar']; // Tetap gunakan gambar lama jika upload gagal
            }
        } else {
            $gambar = $data['gambar']; // Tetap gunakan gambar lama jika tipe tidak valid
        }
    } else {
        $gambar = $data['gambar'];
    }

    // Update data termasuk status
    $stmt = $conn->prepare("UPDATE berita SET judul = ?, isi = ?, gambar = ?, tanggal_publikasi = ?, penulis = ?, status = ? WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("ssssssi", $judul, $isi, $gambar, $tanggal, $penulis, $status, $id);
        
        if ($stmt->execute()) {
            $_SESSION['berita_status'] = 'updated';
            header('Location: dataBerita.php');
            exit();
        } else {
            echo "<script>
                Swal.fire({
                    title: 'Error!',
                    text: 'Gagal memperbarui berita.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            </script>";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Edit Berita - AdminMobil</title>
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
            <h1 class="text-xl font-bold text-gray-900">Edit Berita</h1>
            <p class="text-sm text-gray-600">Edit berita "<?= htmlspecialchars($data['judul']) ?>"</p>
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
      <form action="" method="POST" enctype="multipart/form-data" id="editBeritaForm">
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
                     placeholder="Masukkan judul berita"
                     value="<?= htmlspecialchars($data['judul']) ?>">
            </div>

            <!-- Isi Berita -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-align-left mr-2 text-green-500"></i>
                Isi Berita <span class="text-red-500">*</span>
              </label>
              <textarea name="isi" required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus focus:outline-none focus:ring-2 focus:ring-blue-500 min-h-[200px]"
                        placeholder="Tulis isi berita di sini..."><?= htmlspecialchars($data['isi']) ?></textarea>
            </div>

            <!-- Penulis -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-user-edit mr-2 text-purple-500"></i>
                Penulis <span class="text-red-500">*</span>
              </label>
              <input type="text" name="penulis" required 
                     class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus focus:outline-none focus:ring-2 focus:ring-blue-500"
                     placeholder="Nama penulis"
                     value="<?= htmlspecialchars($data['penulis']) ?>">
            </div>
          </div>
        </div>

        <!-- Gambar dan Status -->
        <div class="form-section p-6 border-b border-gray-200">
          <div class="flex items-center mb-6">
            <div class="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center mr-4">
              <i class="fas fa-cog text-yellow-600"></i>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-900">Pengaturan</h3>
              <p class="text-sm text-gray-600">Kelola gambar dan status berita</p>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Gambar Saat Ini -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-image mr-2 text-blue-500"></i>
                Gambar Saat Ini
              </label>
              <?php if (!empty($data['gambar'])): ?>
                <div class="bg-gray-100 p-4 rounded-lg">
                  <img src="../uploads/<?= htmlspecialchars($data['gambar']) ?>" 
                       alt="Gambar Berita" 
                       class="w-full h-48 object-cover rounded-lg mb-2">
                  <p class="text-sm text-gray-600 text-center"><?= htmlspecialchars($data['gambar']) ?></p>
                </div>
              <?php else: ?>
                <div class="bg-gray-100 p-8 text-center rounded-lg">
                  <i class="fas fa-image text-gray-400 text-4xl mb-3"></i>
                  <p class="text-gray-600">Tidak ada gambar</p>
                </div>
              <?php endif; ?>
            </div>

            <!-- Upload Gambar Baru -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-upload mr-2 text-green-500"></i>
                Upload Gambar Baru (Opsional)
              </label>
              <div class="file-upload-area p-6 rounded-lg text-center cursor-pointer" id="dropZone">
                <i class="fas fa-cloud-upload-alt text-2xl text-gray-400 mb-3"></i>
                <p class="text-sm font-medium text-gray-700 mb-2">Klik untuk memilih gambar baru</p>
                <p class="text-xs text-gray-500 mb-3">atau drag & drop gambar di sini</p>
                <input type="file" name="gambar" accept="image/*" 
                       class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                <p class="text-xs text-gray-500 mt-2">Format yang didukung: JPG, PNG, WebP. Maksimal 2MB.</p>
              </div>
            </div>

            <!-- Tanggal Publikasi -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-calendar-alt mr-2 text-purple-500"></i>
                Tanggal Publikasi <span class="text-red-500">*</span>
              </label>
              <input type="date" name="tanggal_publikasi" required 
                     class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus focus:outline-none focus:ring-2 focus:ring-blue-500"
                     value="<?= $data['tanggal_publikasi'] ?>">
            </div>

            <!-- Status -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-toggle-on mr-2 text-orange-500"></i>
                Status <span class="text-red-500">*</span>
              </label>
              <select name="status" required 
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="draft" <?= $data['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                <option value="publikasi" <?= $data['status'] === 'publikasi' ? 'selected' : '' ?>>Publikasi</option>
              </select>
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
              <button type="submit" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all flex items-center shadow-md">
                <i class="fas fa-save mr-2"></i>
                Simpan Perubahan
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
    const fileInput = document.querySelector('input[type="file"]');

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
      
      // Tampilkan nama file yang diupload
      if (files.length > 0) {
        const fileName = files[0].name;
        dropZone.querySelector('p.text-sm').textContent = `File: ${fileName}`;
      }
    }

    // Preview functionality
    function previewBerita() {
      const form = document.getElementById('editBeritaForm');
      const formData = new FormData(form);
      
      const judul = formData.get('judul');
      const isi = formData.get('isi');
      const penulis = formData.get('penulis');
      
      Swal.fire({
        title: 'Preview Berita',
        html: `
          <div class="text-left max-h-96 overflow-y-auto">
            <h3 class="text-xl font-bold mb-4">${judul}</h3>
            <div class="text-gray-600 mb-4 whitespace-pre-line">${isi}</div>
            <p class="text-sm text-gray-500"><strong>Penulis:</strong> ${penulis}</p>
          </div>
        `,
        icon: 'info',
        confirmButtonColor: '#3085d6',
        confirmButtonText: 'Tutup',
        width: '600px'
      });
    }

    // Form validation
    document.getElementById('editBeritaForm').addEventListener('submit', function(e) {
      const judul = this.querySelector('input[name="judul"]').value.trim();
      const isi = this.querySelector('textarea[name="isi"]').value.trim();
      
      if (!judul || !isi) {
        e.preventDefault();
        Swal.fire({
          title: 'Form Tidak Lengkap',
          text: 'Harap isi semua field yang wajib diisi',
          icon: 'warning',
          confirmButtonText: 'OK'
        });
      }
    });

    // File input change handler
    fileInput.addEventListener('change', function(e) {
      if (this.files.length > 0) {
        const fileName = this.files[0].name;
        dropZone.querySelector('p.text-sm').textContent = `File: ${fileName}`;
      }
    });
  </script>
</body>
</html>