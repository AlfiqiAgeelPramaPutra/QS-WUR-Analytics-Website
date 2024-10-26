<?php
session_start();
require_once 'koneksidb.php'; // Menghubungkan ke file koneksi database

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Query untuk mengambil data user berdasarkan username
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param('s', $username); // Bind parameter username
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Cek apakah user ditemukan dan verifikasi password
    if ($user && password_verify($password, $user['password'])) {
        // Set session jika login berhasil
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $user['username'];
        unset($_SESSION['login_error']); // Hapus pesan error setelah login berhasil

        // Redirect ke halaman yang dimaksud setelah login
        if (isset($_SESSION['redirect_to'])) {
            $redirect_to = $_SESSION['redirect_to'];
            unset($_SESSION['redirect_to']); // Hapus redirect setelah digunakan
            header("Location: $redirect_to");
        } else {
            header("Location: homepage.php");
        }
        exit();
    } else {
        // Login gagal, simpan pesan error di session
        $_SESSION['login_error'] = "Invalid username or password. Please try again.";

        // Redirect kembali ke halaman login (atau halaman sebelumnya)
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }
}
