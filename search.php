<?php
session_start();
require 'includes/db.php';

$query = $_GET['q'] ?? '';
$stmt = $pdo->prepare("SELECT * FROM files WHERE file_name LIKE ? OR description LIKE ?");
$stmt->execute(["%$query%", "%$query%"]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($results);
?>