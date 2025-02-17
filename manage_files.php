<?php
session_start();
require 'includes/db.php';
require 'includes/functions.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch uploaded files for the user
$stmt = $pdo->prepare("SELECT * FROM files WHERE user_id = ? ORDER BY uploaded_at DESC");
$stmt->execute([$user_id]);
$files = $stmt->fetchAll();

// Handle File Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_file_id'])) {
    $file_id = $_POST['delete_file_id'];
    
    // Fetch file details
    $stmt = $pdo->prepare("SELECT file_path FROM files WHERE id = ? AND user_id = ?");
    $stmt->execute([$file_id, $user_id]);
    $file = $stmt->fetch();

    if ($file && file_exists($file['file_path'])) {
        unlink($file['file_path']); // Delete the file from the server
    }

    // Delete file record from the database
    $stmt = $pdo->prepare("DELETE FROM files WHERE id = ? AND user_id = ?");
    $stmt->execute([$file_id, $user_id]);

    // Redirect to refresh the page
    header("Location: manage_files.php");
    exit;
}
?>

<?php require 'includes/header.php'; ?>

<div class="container-fluid mt-4">
    <h1>إدارة الملفات</h1>
    <p>يمكنك إدارة جميع الملفات التي قمت برفعها من هنا:</p>

    <!-- Display Uploaded Files -->
    <?php if (count($files) > 0): ?>
        <div class="row">
            <?php foreach ($files as $file): ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($file['file_name']) ?></h5>
                            <p class="card-text"><?= htmlspecialchars($file['description']) ?></p>
                            <p class="text-muted">الحجم: <?= formatBytes($file['file_size']) ?></p>
                            <p class="text-muted">التصنيف: <?= htmlspecialchars($file['category']) ?></p>
                            <p class="text-muted">تاريخ الرفع: <?= htmlspecialchars(date('Y-m-d H:i', strtotime($file['uploaded_at']))) ?></p>
                            <div class="d-flex gap-2">
                                <a href="serve_file.php?id=<?= $file['id'] ?>" class="btn btn-primary btn-sm" download>
                                    <i class="fas fa-download"></i> تنزيل
                                </a>
                                <form method="POST" action="manage_files.php" style="display: inline;">
                                    <input type="hidden" name="delete_file_id" value="<?= $file['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('هل أنت متأكد من حذف هذا الملف؟')">
                                        <i class="fas fa-trash"></i> حذف
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            لم تقم برفع أي ملفات بعد.
        </div>
    <?php endif; ?>
</div>

<?php require 'includes/footer.php'; ?>