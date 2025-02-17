<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit("Unauthorized");
}

$file_id = $_POST['id'] ?? null;
$user_id = $_SESSION['user_id'];

if ($file_id) {
    $stmt = $pdo->prepare("SELECT file_path FROM files WHERE id = ? AND user_id = ?");
    $stmt->execute([$file_id, $user_id]);
    $file = $stmt->fetch();

    if ($file && file_exists($file['file_path'])) {
        unlink($file['file_path']); // Delete the file from the server
    }

    $stmt = $pdo->prepare("DELETE FROM files WHERE id = ? AND user_id = ?");
    $stmt->execute([$file_id, $user_id]);

    echo "تم حذف الملف بنجاح";
} else {
    echo "حدث خطأ أثناء الحذف";
}
?>