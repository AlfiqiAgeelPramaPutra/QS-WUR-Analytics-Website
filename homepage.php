<?php
include('header.php'); // Pastikan header.php menangani pesan error login
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QS World University Rankings Analytic</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            font-size: 16px;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
            background-image: url('assets/gambartelkom1.jpg'); /* Gambar latar belakang */
            background-size: cover;
            background-position: center;
        }
    </style>
</head>
<body>

    <main class="main-content">
        <section class="intro">
            <p>Welcome to the QS World University Rankings Analytic website. Here you can explore detailed rankings and comparisons of universities worldwide based on the QS World University Ranking system.</p>
        </section>

        <section class="cards-container">
            <div class="card">
                <h2>QS WUR Rankings International</h2>
                <p>Learn more about the QS WUR Ranking International and how it evaluates universities worldwide.</p>
                <button onclick="window.location.href='qswur_overall.php'">Read More</button>
            </div>
        
            <div class="card">
                <h2>QS WUR Rankings by Filter</h2>
                <p>Discover the QS World Rankings for universities based on specific parameters and criteria.</p>
                <button onclick="window.location.href='univ_filter.php'">Read More</button>
            </div>
        
            <div class="card">
                <h2>University Comparison by QS WUR</h2>
                <p>Explore the comparison between universities based on the QS World rating system.</p>
                <button onclick="window.location.href='univ_comparison.php'">Read More</button>
            </div>

            <div class="card">
                <h2>QS WUR University Annual Growth</h2>
                <p>Analyze Telkom University's and other universities' performance over the years based on QS WUR parameters.</p>
                <button onclick="window.location.href='telu_comparison.php'">Read More</button>
            </div>

            <div class="card">
                <h2>Insert New Rankings Data by Year</h2>
                <p>Add new data to the QS WUR rankings by accessing the data input form.</p>
                
                <!-- PHP untuk memeriksa apakah pengguna sudah login -->
                <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                    <!-- Jika pengguna sudah login, langsung arahkan ke crawl.php -->
                    <button onclick="window.location.href='crawl.php'">Add Data</button>
                <?php else: ?>
                    <!-- Jika belum login, tampilkan modal login -->
                    <button id="addDataButton">Add Data</button>
                <?php endif; ?>
            </div>
        </section>        
    </main>

    <footer class="footer">
        <p>&copy; 2024 QS World University Rankings Analytic. All rights reserved.</p>
    </footer>

    <!-- JavaScript untuk menangani modal login -->
    <script>
        // Ambil elemen-elemen dari DOM
        var loginModal = document.getElementById("loginModal"); // Modal login dari header
        var loginButton = document.getElementById("loginButton"); // Tombol login dari header
        var addDataButton = document.getElementById("addDataButton"); // Tombol Add Data
        var closeModal = document.getElementsByClassName("close")[0];

        // Fungsi untuk menampilkan modal login
        function showLoginModal() {
            loginModal.classList.add("show");
        }

        // Tampilkan modal saat tombol Login di header diklik
        if (loginButton) {
            loginButton.onclick = function() {
                showLoginModal();
            }
        }

        // Tampilkan modal saat tombol Add Data diklik, jika ada
        if (addDataButton) {
            addDataButton.onclick = function() {
                showLoginModal();
            }
        }

        // Tutup modal ketika tombol "X" diklik
        if (closeModal) {
            closeModal.onclick = function() {
                loginModal.classList.remove("show");
            }
        }

        // Tutup modal ketika pengguna mengklik di luar modal
        window.onclick = function(event) {
            if (event.target == loginModal) {
                loginModal.classList.remove("show");
            }
        }

        // Jika ada error login, otomatis buka modal login
        <?php if (isset($_SESSION['login_error'])): ?>
            window.onload = function() {
                showLoginModal(); // Tampilkan modal jika ada error
            };
        <?php unset($_SESSION['login_error']); // Hapus error setelah ditampilkan ?>
        <?php endif; ?>
    </script>

</body>
</html>
