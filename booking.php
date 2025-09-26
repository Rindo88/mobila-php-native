<?php
require './config/db.php';
session_start();

$success = false;
$error = "";

// Cek apakah ada id mobil dari URL
$id_mobil = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_mobil <= 0) {
    die("Mobil tidak valid.");
}

// Ambil nama mobil berdasarkan id
$query = $conn->prepare("SELECT nama_mobil FROM mobil WHERE id_mobil = ?");
$query->bind_param("i", $id_mobil);
$query->execute();
$result = $query->get_result();
if ($result->num_rows === 0) {
    die("Mobil tidak ditemukan.");
}
$mobil = $result->fetch_assoc();
$nama_mobil = $mobil['nama_mobil'];

// Proses form saat disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $nama_lengkap     = trim($_POST['nama_lengkap']);
    $email            = trim($_POST['email']);
    $no_hp            = trim($_POST['no_hp']);
    $tanggal_lahir    = $_POST['tanggal_lahir'];
    $gender           = $_POST['gender'];
    $alamat           = trim($_POST['alamat']);
    $kota             = trim($_POST['kota']);
    $waktu_testdrive  = $_POST['waktu_testdrive'];
    $pertanyaan       = !empty($_POST['pertanyaan']) ? trim($_POST['pertanyaan']) : null;

    if (!isset($_POST['agree'])) {
        $error = "Anda harus menyetujui Kebijakan Privasi.";
    } elseif (!preg_match('/^[0-9]{1,12}$/', $no_hp)) {
        $error = "Nomor HP tidak valid. Harus berupa angka dan maksimal 12 digit.";
    } else {
        $sql = "INSERT INTO booking_test_drive (
                    id_mobil, nama_lengkap, email, no_hp, tanggal_lahir, gender, alamat, kota, 
                    waktu_testdrive, pertanyaan, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "isssssssss",
            $id_mobil, $nama_lengkap, $email, $no_hp, $tanggal_lahir, $gender,
            $alamat, $kota, $waktu_testdrive, $pertanyaan
        );

        if ($stmt->execute()) {
            $_SESSION['booking_success'] = true;
            $_SESSION['success_message'] = "Terima kasih! Booking test drive Anda untuk {$nama_mobil} berhasil. Kami akan segera menghubungi Anda.";
            header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $id_mobil . "&success=1");
            exit;
        } else {
            $error = "Gagal menyimpan data: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Test Drive - <?= htmlspecialchars($nama_mobil) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .form-input {
            transition: all 0.3s ease;
        }
        .form-input:focus {
            transform: translateY(-1px);
            box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.3);
        }
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #1d4ed8, #1e40af);
            transform: translateY(-2px);
            box-shadow: 0 15px 30px -5px rgba(59, 130, 246, 0.4);
        }
        .card {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }
        .gradient-bg {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 50%, #bae6fd 100%);
        }
        .input-valid {
            border-color: #10b981 !important;
            background-color: #f0fdf4;
        }
        .floating-label {
            transition: all 0.3s ease;
        }
    </style>
</head>
<body class="gradient-bg min-h-screen">
    <!-- Header -->
    <div class="bg-white/80 backdrop-blur-md shadow-sm border-b border-blue-100">
        <div class="max-w-4xl mx-auto px-4 py-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-blue-500 rounded-lg">
                    <i class="fas fa-car text-white text-xl"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-800">Test Drive Booking</h1>
                    <p class="text-blue-600 font-medium"><?= htmlspecialchars($nama_mobil) ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-2xl mx-auto px-4 py-8">
        <!-- Error Alert -->
        <?php if (!empty($error)): ?>
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-400 rounded-r-lg">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle text-red-400 mr-3"></i>
                    <p class="text-red-700"><?= htmlspecialchars($error) ?></p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Main Form Card -->
        <div class="card rounded-2xl shadow-2xl border border-white/50 p-8 mb-8">
            <form method="post" action="" id="bookingForm" class="space-y-8">
                <!-- Personal Information Section -->
                <div class="space-y-6">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <i class="fas fa-user text-blue-600"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800">Informasi Pribadi</h2>
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        <!-- Nama Lengkap -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700" for="nama_lengkap">
                                <i class="fas fa-user-circle mr-2 text-blue-500"></i>Nama Lengkap
                            </label>
                            <input type="text" 
                                   class="form-input w-full px-4 py-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg" 
                                   id="nama_lengkap" 
                                   name="nama_lengkap" 
                                   placeholder="Masukkan nama lengkap Anda"
                                   required>
                        </div>

                        <!-- Email -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700" for="email">
                                <i class="fas fa-envelope mr-2 text-blue-500"></i>Email
                            </label>
                            <input type="email" 
                                   class="form-input w-full px-4 py-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg" 
                                   id="email" 
                                   name="email" 
                                   placeholder="nama@email.com"
                                   required>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        <!-- Nomor HP -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700" for="no_hp">
                                <i class="fas fa-phone mr-2 text-blue-500"></i>Nomor HP
                            </label>
                            <input type="text" 
                                   class="form-input w-full px-4 py-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg" 
                                   name="no_hp" 
                                   id="no_hp"
                                   pattern="[0-9]{1,12}" 
                                   maxlength="12" 
                                   inputmode="numeric" 
                                   placeholder="081234567890"
                                   required>
                            <p class="text-xs text-gray-500">Maksimal 12 digit angka</p>
                        </div>

                        <!-- Tanggal Lahir -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700" for="tanggal_lahir">
                                <i class="fas fa-calendar mr-2 text-blue-500"></i>Tanggal Lahir
                            </label>
                            <input type="date" 
                                   class="form-input w-full px-4 py-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg" 
                                   id="tanggal_lahir" 
                                   name="tanggal_lahir" 
                                   required>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        <!-- Gender -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700" for="gender">
                                <i class="fas fa-venus-mars mr-2 text-blue-500"></i>Jenis Kelamin
                            </label>
                            <select class="form-input w-full px-4 py-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg" 
                                    id="gender" 
                                    name="gender" 
                                    required>
                                <option value="">-- Pilih Jenis Kelamin --</option>
                                <option value="Laki-laki">Laki-laki</option>
                                <option value="Perempuan">Perempuan</option>
                            </select>
                        </div>

                        <!-- Kota -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700" for="kota">
                                <i class="fas fa-map-marker-alt mr-2 text-blue-500"></i>Kota
                            </label>
                            <input type="text" 
                                   class="form-input w-full px-4 py-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg" 
                                   id="kota" 
                                   name="kota" 
                                   placeholder="Jakarta, Bandung, Surabaya..."
                                   required>
                        </div>
                    </div>

                    <!-- Alamat -->
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700" for="alamat">
                            <i class="fas fa-home mr-2 text-blue-500"></i>Alamat Lengkap
                        </label>
                        <textarea class="form-input w-full px-4 py-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg resize-none" 
                                  id="alamat" 
                                  name="alamat" 
                                  rows="3" 
                                  placeholder="Masukkan alamat lengkap Anda"
                                  required></textarea>
                    </div>
                </div>

                <!-- Booking Details Section -->
                <div class="space-y-6 border-t border-gray-100 pt-8">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="p-2 bg-green-100 rounded-lg">
                            <i class="fas fa-calendar-check text-green-600"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800">Detail Booking</h2>
                    </div>

                    <!-- Waktu Test Drive -->
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700" for="waktu_testdrive">
                            <i class="fas fa-clock mr-2 text-green-500"></i>Waktu Test Drive
                        </label>
                        <input type="datetime-local" 
                               class="form-input w-full px-4 py-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 text-lg" 
                               id="waktu_testdrive" 
                               name="waktu_testdrive" 
                               required>
                        <p class="text-xs text-gray-500">Pilih tanggal dan waktu yang diinginkan</p>
                    </div>

                    <!-- Pertanyaan Tambahan -->
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700" for="pertanyaan">
                            <i class="fas fa-question-circle mr-2 text-green-500"></i>Pertanyaan atau Catatan Tambahan
                        </label>
                        <textarea class="form-input w-full px-4 py-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 text-lg resize-none" 
                                  id="pertanyaan" 
                                  name="pertanyaan" 
                                  rows="4" 
                                  placeholder="Ada pertanyaan khusus? Atau request tertentu? (Opsional)"></textarea>
                    </div>
                </div>

                <!-- Agreement Section -->
                <div class="border-t border-gray-100 pt-8">
                    <div class="flex items-start space-x-3 p-4 bg-blue-50 rounded-xl border border-blue-200">
                        <input class="mt-1 h-5 w-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500" 
                               type="checkbox" 
                               id="agree" 
                               name="agree" 
                               required>
                        <div>
                            <label class="text-sm font-medium text-gray-700" for="agree">
                                Saya setuju dengan <a href="#" class="text-blue-600 hover:text-blue-800 underline font-semibold">Kebijakan Privasi</a> 
                                dan <a href="#" class="text-blue-600 hover:text-blue-800 underline font-semibold">Syarat & Ketentuan</a>
                            </label>
                            <p class="text-xs text-gray-500 mt-1">Data pribadi Anda akan kami jaga kerahasiaannya sesuai kebijakan privasi kami.</p>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-6">
                    <button type="submit" 
                            name="submit" 
                            class="btn-primary w-full py-4 px-8 text-white font-bold rounded-xl text-lg shadow-lg border-0 cursor-pointer">
                        <i class="fas fa-paper-plane mr-3"></i>
                        Kirim Booking Test Drive
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Input validation untuk nomor HP
        document.getElementById('no_hp').addEventListener('input', function () {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 12);
            validateInput(this);
        });

        // Real-time validation untuk semua input
        const inputs = document.querySelectorAll('input[required], textarea[required], select[required]');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateInput(this);
            });
            input.addEventListener('input', function() {
                if (this.value.trim() !== '') {
                    validateInput(this);
                }
            });
        });

        function validateInput(input) {
            if (input.checkValidity() && input.value.trim() !== '') {
                input.classList.add('input-valid');
                input.classList.remove('border-red-300', 'bg-red-50');
            } else if (input.value.trim() !== '') {
                input.classList.remove('input-valid');
                input.classList.add('border-red-300', 'bg-red-50');
            }
        }

        // Set minimum date untuk test drive (besok)
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        document.getElementById('waktu_testdrive').min = tomorrow.toISOString().slice(0, 16);

        // Form submit animation
        document.getElementById('bookingForm').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalContent = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-3"></i>Mengirim...';
            submitBtn.disabled = true;
            
            // Reset button jika ada error (form tidak redirect)
            setTimeout(() => {
                if (!document.hidden) {
                    submitBtn.innerHTML = originalContent;
                    submitBtn.disabled = false;
                }
            }, 5000);
        });

        // Clear form setelah berhasil (jika diperlukan)
        if (window.location.search.includes('success=1')) {
            // Reset form jika ada parameter success
            setTimeout(() => {
                if (!document.querySelector('.swal2-container')) {
                    document.getElementById('bookingForm').reset();
                }
            }, 100);
        }
    </script>

    <!-- SweetAlert Success Message -->
    <?php if (isset($_GET['success']) && isset($_SESSION['booking_success'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Booking Berhasil!',
                    text: '<?= isset($_SESSION['success_message']) ? addslashes($_SESSION['success_message']) : "Terima kasih! Kami akan segera menghubungi Anda untuk konfirmasi jadwal test drive." ?>',
                    confirmButtonText: 'Kembali ke Halaman Utama',
                    confirmButtonColor: '#3b82f6',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    customClass: {
                        popup: 'rounded-2xl',
                        title: 'text-xl font-bold',
                        content: 'text-gray-600'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'pengguna.php';
                    }
                });
            });
        </script>
        <?php 
        unset($_SESSION['booking_success']); 
        unset($_SESSION['success_message']);
        ?>
    <?php endif; ?>
</body>
</html>