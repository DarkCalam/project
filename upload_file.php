<?php
session_start();
require 'includes/db.php';
require 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Validation
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die("Invalid CSRF Token");
    }

    // Validate File Upload
    $max_size = 10 * 1024 * 1024; // 10000MB
    $file_name = $_FILES['file']['name'] ?? '';
    $file_type = $_FILES['file']['type'] ?? '';
    $file_size = $_FILES['file']['size'] ?? 0;
    $file_tmp = $_FILES['file']['tmp_name'] ?? '';

    // Allow any file type but validate file size
    if ($file_size > $max_size) {
        die("حجم الملف يتجاوز الحد المسموح (10000MB)");
    }

    $file_path = 'uploads/' . uniqid() . '_' . basename($file_name);
    if (move_uploaded_file($file_tmp, $file_path)) {
        // Insert file details into the database
        $stmt = $pdo->prepare("INSERT INTO files (user_id, file_name, file_path, description, category, file_size) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_SESSION['user_id'],
            $file_name,
            $file_path,
            $_POST['description'] ?? '',
            $_POST['category'] ?? '',
            $file_size
        ]);

        echo "تم رفع الملف بنجاح";
    } else {
        die("حدث خطأ أثناء رفع الملف");
    }
}
?>