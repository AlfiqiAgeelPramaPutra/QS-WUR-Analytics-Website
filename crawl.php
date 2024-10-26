<?php
include('header.php');

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Jika belum login, redirect ke halaman login
    header("Location: homepage.php?error=not_logged_in");
    exit();
}

// Pastikan folder 'tokenjson' ada
$token_folder = 'tokenjson';
if (!is_dir($token_folder)) {
    mkdir($token_folder, 0777, true);
}

// Fungsi untuk membaca dan menulis token API ke file JSON di folder 'tokenjson'
function getApiTokens($file = 'tokenjson/api_tokens.json') {
    if (file_exists($file)) {
        $tokens = json_decode(file_get_contents($file), true);
        // Jika file JSON kosong atau tidak valid, kembalikan array kosong
        return is_array($tokens) ? $tokens : [];
    }
    return []; // Jika file tidak ada, kembalikan array kosong
}

function saveApiTokens($tokens, $file = 'tokenjson/api_tokens.json') {
    file_put_contents($file, json_encode($tokens));
}

// Menangani data dari form dan menyimpannya dalam sesi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $api_token = $_POST['api_token'];
    $output_dir = rtrim($_POST['output_dir'], '/'); 

    // Cek apakah token API sudah ada
    $api_tokens = getApiTokens();
    if (in_array($api_token, $api_tokens)) {
        // Jika token sudah ada, tampilkan peringatan
        echo "<script>alert('Data tahun pemeringkatan yang diinputkan sudah ada pada database, harap ganti token API dengan data terbaru');</script>";
    } else {
        // Jika token belum ada, simpan token dan data dalam sesi
        $_SESSION['api_token'] = $api_token;
        $_SESSION['output_dir'] = $output_dir;

        // Simpan token API baru ke file JSON di folder 'tokenjson'
        $api_tokens[] = $api_token;
        saveApiTokens($api_tokens);

        // Redirect ke proses.php
        header('Location: proses.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QS WUR Data Scraping</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="crawler-container">
        <h1 class="crawler-title">QS WUR Data Scraping</h1>

        <!-- Form input untuk API Token dan Direktori Output -->
        <form action="crawl.php" method="post" class="crawler-form">
            <label for="api_token" class="crawler-label">API Token:</label>
            <input type="text" id="api_token" name="api_token" class="crawler-input" required>
            
            <!-- Catatan kecil di bawah input API token -->
            <p class="api-note">
                Cara mendapatkan API data di website QS WUR: 
                <a href="cara_api.php" class="api-link" target="_blank">Klik di sini</a>
            </p>

            <label for="output_dir" class="crawler-label">Enter Output Directory Path:</label>
            <input type="text" id="output_dir" name="output_dir" class="crawler-input" required placeholder="/path/to/output/folder">

            <input type="submit" value="Submit" class="crawler-submit">
        </form>
    </div>
</body>
</html>
