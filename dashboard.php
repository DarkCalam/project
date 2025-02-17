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

// Fetch user data
try {
    $stmt = $pdo->prepare("SELECT username, email, profile_picture FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        session_destroy();
        header("Location: login.php");
        exit;
    }
} catch (PDOException $e) {
    die("حدث خطأ أثناء جلب بيانات المستخدم: " . $e->getMessage());
}

// Fetch all uploaded files (from all users)
try {
    $stmt = $pdo->prepare("
        SELECT files.*, users.username AS uploader_name 
        FROM files 
        INNER JOIN users ON files.user_id = users.id 
        ORDER BY files.uploaded_at DESC
    ");
    $stmt->execute();
    $files = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("حدث خطأ أثناء جلب الملفات: " . $e->getMessage());
}
?>
<?php require 'includes/header.php'; ?>
<div class="container-fluid mt-4">
    <!-- Header Section -->
    <script src="js/script.js"></script>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="text-primary fw-bold">مرحبًا <?= htmlspecialchars($user['username']) ?></h1>
            <p class="text-muted">هذه هي الملفات التي تم رفعها من قبل جميع المستخدمين.</p>
        </div>
    </div>

    <!-- Search Input -->
    <div class="mb-4">
        <input type="text" id="search-input" class="form-control form-control-lg" placeholder="ابحث عن ملف...">
    </div>

    <!-- Display Uploaded Files -->
    <div id="file-container">
        <?php if (count($files) > 0): ?>
            <div class="row g-4">
                <?php foreach ($files as $file): ?>
                    <div class="col-md-4 file-card" 
                         data-file-name="<?= htmlspecialchars(strtolower($file['file_name'])) ?>" 
                         data-description="<?= htmlspecialchars(strtolower($file['description'])) ?>">
                        <div class="card h-100 shadow-sm border-0 rounded-3">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title text-primary"><?= htmlspecialchars($file['file_name']) ?></h5>
                                <p class="card-text text-muted"><?= htmlspecialchars($file['description']) ?: 'لا يوجد وصف' ?></p>
                                <div class="mt-auto">
                                    <ul class="list-unstyled small text-muted mb-3">
                                        <li><i class="fas fa-user me-2"></i> <?= htmlspecialchars($file['uploader_name']) ?></li>
                                        <li><i class="fas fa-calendar me-2"></i> <?= htmlspecialchars(date('Y-m-d H:i', strtotime($file['uploaded_at']))) ?></li>
                                        <li><i class="fas fa-file me-2"></i> <?= formatBytes($file['file_size']) ?></li>
                                        <li><i class="fas fa-tag me-2"></i> <?= htmlspecialchars($file['category']) ?></li>
                                    </ul>
                                    <div class="d-flex gap-2">
                                        <!-- Download Button -->
                                        <a href="serve_file.php?id=<?= $file['id'] ?>" class="btn btn-primary w-100">
                                            <i class="fas fa-download me-2"></i> تنزيل
                                        </a>
                                        <!-- Copy Link Button -->
                                        <button class="btn btn-secondary w-100 copy-link-btn" data-file-id="<?= $file['id'] ?>">
                                            <i class="fas fa-link me-2"></i> نسخ الرابط
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center">
                <i class="fas fa-folder-open me-2"></i> لم يتم رفع أي ملفات بعد.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require 'includes/footer.php'; ?>