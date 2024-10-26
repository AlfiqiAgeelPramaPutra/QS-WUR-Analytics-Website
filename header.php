<?php
session_start(); // Pastikan session dimulai

$current_file = basename($_SERVER['PHP_SELF']); // Dapatkan nama file saat ini

// Tentukan URL halaman sebelumnya untuk tombol back
switch ($current_file) {
    case 'detailuniv.php':
        $back_url = 'qswur_overall.php';
        break;
    case 'inputdata.php':
    case 'sukses.php':
        $back_url = 'homepage.php';
        break;
    default:
        $back_url = 'homepage.php';
        break;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QS World University Rankings Analytic</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="title-container">
    <h1>QS World University Rankings Analytic</h1>

    <!-- Tampilkan tombol back jika halaman bukan homepage -->
    <?php if ($current_file != 'homepage.php'): ?>
        <a href="<?php echo htmlspecialchars($back_url); ?>" class="back-button">&#9665;</a>
    <?php endif; ?>
</div>

<div class="nav-menu">
    <div class="nav-links">
        <a href="homepage.php" class="<?php echo ($current_file == 'homepage.php') ? 'active' : ''; ?>">Home</a>
        <a href="qswur_overall.php" class="<?php echo ($current_file == 'qswur_overall.php') ? 'active' : ''; ?>">Rankings</a>
        <a href="univ_filter.php" class="<?php echo ($current_file == 'univ_filter.php') ? 'active' : ''; ?>">Filter</a>
        <a href="univ_comparison.php" class="<?php echo ($current_file == 'univ_comparison.php') ? 'active' : ''; ?>">Compare</a>
        <a href="telu_comparison.php" class="<?php echo ($current_file == 'telu_comparison.php') ? 'active' : ''; ?>">Annual Growth</a>

        <!-- Tombol Input New Data -->
        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
            <!-- Jika sudah login, arahkan langsung ke crawl.php -->
            <a href="crawl.php" class="<?php echo ($current_file == 'crawl.php') ? 'active' : ''; ?>">Input New Data</a>
        <?php else: ?>
            <!-- Simpan halaman crawl.php ke session untuk redirect setelah login -->
            <?php $_SESSION['redirect_to'] = 'crawl.php'; ?>
            <!-- Jika belum login, munculkan modal login -->
            <a href="#" id="inputDataButton" class="nav-link">Input New Data</a>
        <?php endif; ?>
    </div>

    <div class="nav-right">
        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
            <div class="dropdown">
                <button class="dropbtn">Welcome, 
                    <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'User'; ?>!
                </button>
                <div class="dropdown-content">
                    <a href="logout.php">Logout</a>
                </div>
            </div>
        <?php else: ?>
            <!-- Simpan halaman saat ini ke session kecuali saat klik Input New Data -->
            <?php if ($current_file != 'crawl.php'): ?>
                <?php $_SESSION['redirect_to'] = $current_file; ?>
            <?php endif; ?>
            <!-- Tombol untuk memunculkan modal login dengan logo -->
            <a href="#" id="loginButton" class="login-button">
                <img src="assets/login.png" alt="" class="login-logo"> Login
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- Modal login yang sama digunakan di seluruh halaman -->
<div id="loginModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Login</h2>
        <form id="loginForm" class="login-form" method="post" action="login.php">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
            
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            
            <input type="submit" value="Login">
        </form>
        <!-- Tampilkan pesan error jika ada -->
        <?php if (isset($_SESSION['login_error'])): ?>
            <div id="errorMessage" class="error-message" style="color: red; display: block;">
                <?php echo $_SESSION['login_error']; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- JavaScript untuk menampilkan modal login -->
<script>
    var loginModal = document.getElementById("loginModal");
    var loginButton = document.getElementById("loginButton");
    var inputDataButton = document.getElementById("inputDataButton"); // Tombol Input Data
    var closeModal = document.getElementsByClassName("close")[0];
    var errorMessage = document.getElementById("errorMessage");

    // Tampilkan modal saat tombol Login diklik
    if (loginButton && loginModal) {
        loginButton.onclick = function() {
            loginModal.classList.add("show"); // Tambahkan kelas 'show' untuk menampilkan modal
        };
    }

    // Tampilkan modal saat tombol Input Data diklik jika belum login
    if (inputDataButton && loginModal) {
        inputDataButton.onclick = function() {
            loginModal.classList.add("show"); // Tambahkan kelas 'show' untuk menampilkan modal
        };
    }

    // Tutup modal ketika tombol "X" diklik
    if (closeModal && loginModal) {
        closeModal.onclick = function() {
            loginModal.classList.remove("show"); // Hapus kelas 'show' untuk menyembunyikan modal
        };
    }

    // Tutup modal ketika pengguna mengklik di luar modal
    window.onclick = function(event) {
        if (event.target == loginModal) {
            loginModal.classList.remove("show"); // Hapus kelas 'show' ketika klik di luar modal
        }
    };

    // Jika ada error, otomatis buka modal dan tampilkan error message
    <?php if (isset($_SESSION['login_error'])): ?>
        window.onload = function() {
            loginModal.classList.add("show"); // Otomatis buka modal jika ada error
            if (errorMessage) {
                errorMessage.style.display = 'block'; // Tampilkan pesan error
            }
        };
    <?php endif; ?>
</script>

</body>
</html>
