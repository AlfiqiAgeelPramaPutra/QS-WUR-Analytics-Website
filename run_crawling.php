<?php
session_start(); // Memulai sesi

if (!isset($_SESSION['api_token']) || !isset($_SESSION['output_dir'])) {
    echo "Error: Data tidak tersedia.";
    exit;
}

$api_token = escapeshellarg($_SESSION['api_token']);
$output_dir = escapeshellarg($_SESSION['output_dir']);

// Path dari script Python
$python_script = 'crawl_qs_wur.py'; // Sesuaikan dengan path ke script Python Anda

// Command untuk menjalankan Python script dengan API Token dan direktori output sebagai argumen
$command = "python3 $python_script $api_token $output_dir";

// Eksekusi command dan simpan output serta statusnya
$output = shell_exec($command);

echo $output;
?>
