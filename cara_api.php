<?php
include('header.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cara Mendapatkan API Token</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .api-content {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .api-content h1 {
            font-size: 24px;
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }

        .api-content p {
            font-size: 16px;
            line-height: 1.5;
            color: #666;
        }

        .api-content ul {
            list-style-type: disc;
            margin: 20px 0;
            padding-left: 20px;
        }

        .api-content a {
            color: #fff;
            text-decoration: none;
        }

        .api-content a:hover {
            text-decoration: underline;
        }

        /* Kelas khusus untuk warna link */
        .api-content .special-link {
            color: #1a73e8; /* Ganti dengan warna yang diinginkan */
        }

        .api-content .special-link:hover {
            text-decoration: underline;
        }

        .api-image-container {
            text-align: center;
            margin: 20px 0;
        }

        .api-image-container img {
            max-width: 100%;
            height: auto;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .api-back-link {
            display: inline-block;
            margin-top: 20px;
            font-size: 16px;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            background-color: #ed1e28;
            text-decoration: none;
            text-align: center;
            transition: background-color 0.3s ease;
        }

        .api-back-link:hover {
            background-color: #c8102e;
        }

        .api-content .button-container {
            text-align: center;
        }
    </style>
</head>
<body class="api-container">
    <div class="api-content">
        <h1>Cara Mendapatkan API Token</h1>
        <p>Ikuti langkah-langkah berikut untuk mendapatkan token API dari situs QS World University Ranking:</p>
        <ul>
            <li><strong>Kunjungi Situs Web QS World University Ranking:</strong> Akses situs di <a href="https://www.topuniversities.com/world-university-rankings" target="_blank" class="special-link">www.topuniversities.com/world-university-rankings</a>.</li>
            
            <li><strong>Pilih Filter Tahun:</strong> Pilih filter tahun dari data yang ingin diambil.</li>
            
            <div class="api-image-container">
                <img src="assets/cara1.png" alt="Langkah 1: Pilih tahun">
            </div>
            
            <li><strong>Klik Kanan dan Pilih Inspeksi:</strong> Klik kanan pada area kosong di halaman dan pilih opsi "Inspeksi" atau "Inspect".</li>
            
            <div class="api-image-container">
                <img src="assets/cara2.png" alt="Langkah 2: inspect">
            </div>
            
            <li><strong>Pilih Network dan Refresh Halaman:</strong> Di jendela "Inspeksi", pilih tab "Network" dan tekan <kbd>Ctrl + R</kbd> untuk me-refresh halaman.</li>
            
            <div class="api-image-container">
                <img src="assets/cara3.png" alt="Langkah 3: mencari sumber Token API">
            </div>
            
            <li><strong>Pilih File XHR:</strong> Pilih file dengan nama yang dimulai dengan "endpoint?nid=" dan klik dua kali.</li>
            
            <div class="api-image-container">
                <img src="assets/cara4.png" alt="Langkah 4: Pilih file xhr">
            </div>
            
            <li><strong>Temukan NID:</strong> Setelah berpindah halaman, cari bagian dengan <code>nid=</code>. Token API yang muncul di sini adalah token yang harus Anda masukkan di aplikasi Anda.</li>
            
            <div class="api-image-container">
                <img src="assets/cara5.png" alt="Langkah 5: Salin kode API">
            </div>
        </ul>
        <div class="button-container">
            <a href="crawl.php" class="api-back-link">Kembali ke Halaman Crawling</a>
        </div>
    </div>
</body>
</html>
