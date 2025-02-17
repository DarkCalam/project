<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'db.php';
require_once 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT username, email, profile_picture FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">Dark Storage</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#uploadModal">
                            <i class="fas fa-cloud-upload-alt"></i> رفع ملف
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="settings.php">
                            <i class="fas fa-cog"></i> الإعدادات
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_files.php">
                            <i class="fa fa-file"></i> الملفات
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> تسجيل الخروج
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Upload Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data" action="upload_file.php">
                    <div class="modal-header">
                        <h5 class="modal-title">رفع ملف جديد</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                        
                        <div class="mb-3">
                            <label for="file" class="form-label">اختر ملف:</label>
                            <input type="file" class="form-control" id="file" name="file" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">وصف الملف:</label>
                            <textarea class="form-control" id="description" name="description" placeholder="وصف الملف..."></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="category" class="form-label">التصنيف:</label>
                            <select class="form-select" id="category" name="category" required>
                                <option value="work">عمل</option>
                                <option value="education">تعليم</option>
                                <option value="photos">صور</option>
                                <option value="other">أخرى</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload"></i> رفع الملف
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>