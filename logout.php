<?php
session_start();
session_destroy();  // Hapus semua sesi

// Redirect ke halaman utama
header("Location: homepage.php");
exit();
?>
