<?php
session_start();
include('koneksidb.php');

// Ambil path dari session
$csvDirectory = isset($_SESSION['output_dir']) ? $_SESSION['output_dir'] : null;

// Jika path tidak ditemukan, tampilkan pesan error
if (!$csvDirectory) {
    die(json_encode(['status' => 'error', 'message' => "Direktori CSV tidak ditemukan."]));
}

// Daftar tabel yang akan diisi dengan CSV
$tables = [
    'univ_info', 'Academic_Reputation', 'Employer_Reputation', 
    'Faculty_Student_Ratio', 'Citations_Per_Faculty', 'International_Faculty_Ratio', 
    'International_Students_Ratio', 'International_Research_Network', 
    'Employment_Outcomes', 'Sustainability', 'overall'
];

// Fungsi untuk memeriksa apakah kolom dalam CSV sesuai dengan kolom di database
function validateCSV($header, $tableName, $conn) {
    $result = $conn->query("SHOW COLUMNS FROM $tableName");
    if ($result) {
        $columns = [];
        while ($row = $result->fetch_assoc()) {
            $columns[] = $row['Field'];
        }
        $result->free();
        return $header == $columns;
    }
    return false;
}

// Fungsi untuk mengupload file CSV dan menginsert data ke database
function processCSV($file, $tableName, $conn) {
    if (($handle = fopen($file, "r")) !== FALSE) {
        $header = fgetcsv($handle);

        if (!validateCSV($header, $tableName, $conn)) {
            fclose($handle);
            return "Kolom pada file CSV tidak sesuai dengan kolom di tabel $tableName";
        }

        $columns = implode(", ", array_map(function($col) { return "`$col`"; }, $header));
        $placeholders = implode(", ", array_fill(0, count($header), '?'));
        $sql = "INSERT INTO $tableName ($columns) VALUES ($placeholders)";
        
        if ($stmt = $conn->prepare($sql)) {
            while (($data = fgetcsv($handle)) !== FALSE) {
                try {
                    $stmt->bind_param(str_repeat('s', count($data)), ...$data);
                    if (!$stmt->execute()) {
                        if ($conn->errno == 1452) { // Foreign key constraint fails
                            $errorMsg = "Foreign key constraint fails. Data yang dimasukkan: " . implode(", ", $data);
                            throw new Exception($errorMsg);
                        } else {
                            throw new Exception("Error executing statement: " . $stmt->error);
                        }
                    }
                } catch (Exception $e) {
                    fclose($handle);
                    return "Data gagal dimasukkan ke tabel $tableName: " . $e->getMessage();
                }
            }
            $stmt->close();
        } else {
            fclose($handle);
            return "Error preparing statement: " . $conn->error;
        }
        fclose($handle);
        return "Data dari $file berhasil diinputkan ke tabel $tableName";
    } else {
        return "Error: Gagal membuka file $file";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['upload_to_db'])) {
    $errors = [];
    $successMessages = [];

    foreach ($tables as $tableName) {
        $filePath = "$csvDirectory/$tableName.csv";
        if (file_exists($filePath)) {
            $result = processCSV($filePath, $tableName, $conn);
            if (strpos($result, 'gagal') !== false || strpos($result, 'Error') !== false) {
                $errors[] = $result;
            } else {
                $successMessages[] = $result;
            }
        } else {
            $errors[] = "File $tableName.csv tidak ditemukan di direktori $csvDirectory";
        }
    }

    $conn->close();
    $message = count($errors) > 0
        ? "Proses gagal: " . implode(", ", $errors)
        : "Proses berhasil: " . implode(", ", $successMessages);
    
    // Kirimkan hasil kembali ke klien
    echo json_encode(['status' => 'done', 'message' => $message]);
    exit();
}
?>
