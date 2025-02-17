<?php
session_start();
require 'includes/db.php';

$file_id = $_GET['id'] ?? null;

if ($file_id) {
    // Fetch file details
    $stmt = $pdo->prepare("SELECT file_path, file_name FROM files WHERE id = ?");
    $stmt->execute([$file_id]);
    $file = $stmt->fetch();

    if ($file && file_exists($file['file_path'])) {
        // Serve the file
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file['file_name']) . '"');
        readfile($file['file_path']);
        exit;
    } else {
        die("File not found or access denied.");
    }
}

die("Invalid request.");
?>