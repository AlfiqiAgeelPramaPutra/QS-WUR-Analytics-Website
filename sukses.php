<?php
include('koneksidb.php');
include('header.php');  

// Retrieve the message from the URL parameter
$message = isset($_GET['message']) ? $_GET['message'] : 'No message available.';

// Determine the back URL and button text based on the message content
if (strpos($message, 'Proses berhasil') !== false) {
    $back_url = 'homepage.php'; // Redirect to homepage on success
    $back_text = 'Back to Homepage';
} else {
    $back_url = 'inputdata.php'; // Redirect to inputdata.php on failure
    $back_text = 'Input Data Again';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Status</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="status-container">
        <h2>Status of CSV Upload</h2>
        <div class="message-box <?php echo (strpos($message, 'Proses berhasil') !== false) ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
        <a href="<?php echo htmlspecialchars($back_url); ?>" class="back-button-sukses">
            <?php echo htmlspecialchars($back_text); ?>
        </a>
    </div>
</body>
</html>
