<?php
include('header.php');

// Mengecek apakah data tersedia di sesi
if (!isset($_SESSION['api_token']) || !isset($_SESSION['output_dir'])) {
    echo "<p>Data tidak tersedia. Silakan kembali ke halaman input.</p>";
    exit;
}

$api_token = $_SESSION['api_token'];
$output_dir = $_SESSION['output_dir'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proses Scraping</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .crawler-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background-color: rgba(255, 255, 255, 0.8); /* Warna mirip dengan card */
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        /* Crawler Title */
        .crawler-title {
            font-size: 32px;
            color: #333;
            margin-bottom: 20px;
        }

        /* Crawler Submit Button */
        .crawler-submit {
            padding: 10px 20px;
            background-color: #ed1e28;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
            text-decoration: none;
            display: inline-block;
            margin: 10px;
        }

        .crawler-submit:hover {
            background-color: #b6252a;
        }

        /* Progress Bar Container */
        .progress-bar-container {
            width: 100%;
            background-color: #f3f3f3;
            border: 1px solid #ddd;
            margin: 20px 0;
            border-radius: 4px;
        }

        /* Progress Bar */
        .progress-bar {
            height: 30px;
            width: 0%;
            background-color: #4CAF50;
            text-align: center;
            color: white;
            line-height: 30px;
            border-radius: 4px;
        }

        /* Hidden Class */
        .hidden {
            display: none;
        }

        /* Crawler Output */
        .crawler-output {
            margin-top: 20px;
            padding: 15px;
            background-color: #fff;
            border-radius: 4px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border: 1px solid #ddd;
            max-height: 300px;
            overflow-y: auto;
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        /* Crawler Subtitle */
        .crawler-subtitle {
            margin-top: 30px;
            font-size: 24px;
            color: #333;
            text-align: center;
        }

    </style>
</head>
<body>
    <div class="crawler-container">
        <h1 class="crawler-title">Proses Scraping</h1>

        <p>Token API: <?php echo htmlspecialchars($api_token); ?></p>
        <p>Direktori Output: <?php echo htmlspecialchars($output_dir); ?></p>
        <p>Data siap untuk dilakukan Scraping.</p>

        <!-- Form untuk memulai Scraping -->
        <form id="startCrawlingForm" action="" method="POST" class="crawler-form">
            <input type="hidden" name="api_token" value="<?php echo htmlspecialchars($api_token); ?>">
            <input type="hidden" name="output_dir" value="<?php echo htmlspecialchars($output_dir); ?>">
            <input type="submit" name="start_crawling" value="Start Scraping" class="crawler-submit">
        </form>

        <div id="progress-container" class="hidden">
            <div id="progress-bar" class="progress-bar-container">
                <div id="progress" class="progress-bar">0%</div>
            </div>
            <p id="status-message">Menjalankan Scraping...</p>
        </div>
        
        <div id="result" class="hidden">
            <h2>Proses Scraping Selesai</h2>
            <!-- Form untuk langsung input data ke database -->
            <form id="inputToDatabaseForm" action="inputdata.php" method="POST">
                <input type="submit" name="upload_to_db" value="Input data ke database" class="crawler-submit">
            </form>
        </div>

    </div>

    <script>
        document.getElementById('startCrawlingForm').onsubmit = function(event) {
            event.preventDefault(); // Mencegah form dari submit default
            
            // Menampilkan progress bar dan menyembunyikan form
            document.getElementById('progress-container').classList.remove('hidden');
            document.getElementById('startCrawlingForm').classList.add('hidden');
            
            // Mengirim permintaan AJAX untuk memulai Scraping
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'run_crawling.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    // Menampilkan hasil dan menyembunyikan progress bar
                    document.getElementById('progress-container').classList.add('hidden');
                    document.getElementById('result').classList.remove('hidden');
                }
            };

            var formData = new FormData(document.getElementById('startCrawlingForm'));
            xhr.send(new URLSearchParams(formData).toString());

            // Simulasi progress bar (misalnya, Anda bisa memperbarui ini berdasarkan status nyata)
            var progress = 0;
            var progressInterval = setInterval(function() {
                progress += 10; // Ubah sesuai dengan progress nyata
                document.getElementById('progress').style.width = progress + '%';
                document.getElementById('progress').textContent = progress + '%';
                
                if (progress >= 100) {
                    clearInterval(progressInterval);
                }
            }, 1000);
        };

        // Menghandle submit form inputToDatabaseForm untuk mengirimkan data ke database
        document.getElementById('inputToDatabaseForm').onsubmit = function(event) {
            event.preventDefault(); // Mencegah form dari submit default

            // Menampilkan progress bar dan menyembunyikan tombol
            var progressContainer = document.createElement('div');
            progressContainer.classList.add('progress-bar-container');
            var progressBar = document.createElement('div');
            progressBar.classList.add('progress-bar');
            progressBar.textContent = '0%';
            progressContainer.appendChild(progressBar);
            document.getElementById('result').appendChild(progressContainer);

            var statusMessage = document.createElement('p');
            statusMessage.id = 'status-message';
            statusMessage.textContent = 'Sedang menginput data ke database...';
            document.getElementById('result').appendChild(statusMessage);

            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'inputdata.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    var response = JSON.parse(xhr.responseText);
                    if (response.status === 'done') {
                        // Redirect ke halaman sukses dengan pesan
                        window.location.href = 'sukses.php?message=' + encodeURIComponent(response.message);
                    }
                }
            };

            var formData = 'upload_to_db=true';
            xhr.send(formData);

            // Simulasi progress bar
            var progress = 0;
            var progressInterval = setInterval(function() {
                progress += 10;
                progressBar.style.width = progress + '%';
                progressBar.textContent = progress + '%';

                if (progress >= 100) {
                    clearInterval(progressInterval);
                }
            }, 500);
        };
    </script>
</body>
</html>
