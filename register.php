<?php
require_once 'koneksidb.php'; // Menghubungkan ke file koneksi database

// Variabel untuk menyimpan pesan
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validasi apakah password dan konfirmasi password cocok
    if ($password !== $confirm_password) {
        $message = "Passwords do not match.";
    } else {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Cek apakah username sudah ada
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $message = "Username already exists.";
        } else {
            // Query untuk menyimpan data user ke database
            $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->bind_param('ss', $username, $hashed_password);

            if ($stmt->execute()) {
                // Redirect ke homepage setelah berhasil registrasi
                header("Location: homepage.php");
                exit();
            } else {
                $message = "Error: " . $stmt->error;
            }
        }

        $stmt->close();
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <style>
        /* Styling untuk halaman pendaftaran */
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f4f4f4;
        }

        .register-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .register-container h2 {
            margin-bottom: 20px;
            color: #333;
            font-size: 24px;
            font-weight: bold;
        }

        .register-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .register-form label {
            text-align: left;
            font-weight: bold;
            font-size: 14px;
            color: #555;
        }

        .register-form input {
            padding: 12px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 6px;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .register-form input:focus {
            border-color: #28a745;
            box-shadow: 0 0 8px rgba(40, 167, 69, 0.3);
            outline: none;
        }

        .register-form input[type="submit"] {
            background-color: #28a745;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
            border: none;
            padding: 12px;
            font-size: 18px;
            font-weight: bold;
            border-radius: 6px;
        }

        .register-form input[type="submit"]:hover {
            background-color: #218838;
            box-shadow: 0 4px 12px rgba(33, 136, 56, 0.3);
        }

        .error-message {
            color: red;
            font-size: 14px;
            display: <?php echo isset($message) ? 'block' : 'none'; ?>;
            margin-top: 10px;
        }

        .back-to-home {
            margin-top: 20px;
            font-size: 16px;
        }

        .back-to-home a {
            color: #007bff;
            text-decoration: none;
        }

        .back-to-home a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>Register</h2>
        <form class="register-form" action="register.php" method="post">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>

            <input type="submit" value="Register">

            <p class="error-message"><?php echo isset($message) ? htmlspecialchars($message) : ''; ?></p>
        </form>
        <div class="back-to-home">
            <a href="homepage.php">Back to Homepage</a>
        </div>
    </div>
</body>
</html>
