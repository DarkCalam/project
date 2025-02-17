<?php
require 'includes/db.php';

if (isset($_GET['id'])) {
    $file_id = $_GET['id'];

    $stmt = $pdo->prepare("SELECT * FROM files WHERE id = ?");
    $stmt->execute([$file_id]);
    $file = $stmt->fetch();

    if ($file) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file['file_name']) . '"');
        readfile($file['file_path']);
        exit;
    } else {
        echo "الملف غير موجود.";
    }
}
?>