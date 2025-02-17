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
$error = '';
$success = '';

// Fetch user data
$stmt = $pdo->prepare("SELECT username, email, profile_picture FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Validation
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die("Invalid CSRF Token");
    }

    // Update Profile Picture
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png','image/gif'];
        $max_size = 2 * 1024 * 1024; // 10MB

        $file_name = $_FILES['profile_picture']['name'];
        $file_type = $_FILES['profile_picture']['type'];
        $file_size = $_FILES['profile_picture']['size'];
        $file_tmp = $_FILES['profile_picture']['tmp_name'];

        if (!in_array($file_type, $allowed_types)) {
            $error = "نوع الملف غير مسموح به. يرجى رفع صورة بتنسيق JPEG أو PNG.";
        } elseif ($file_size > $max_size) {
            $error = "حجم الملف يتجاوز الحد المسموح (10MB).";
        } else {
            $file_path = 'uploads/' . uniqid() . '_' . basename($file_name);
            if (move_uploaded_file($file_tmp, $file_path)) {
                // Delete old profile picture if it exists
                if ($user['profile_picture'] && file_exists($user['profile_picture'])) {
                    unlink($user['profile_picture']);
                }

                // Update profile picture in the database
                $stmt = $pdo->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
                $stmt->execute([$file_path, $user_id]);

                $success = "تم تحديث صورة الملف الشخصي بنجاح.";
                $user['profile_picture'] = $file_path; // Update the current session's profile picture
            } else {
                $error = "حدث خطأ أثناء رفع الصورة.";
            }
        }
    }

    // Update Username and Email
    $new_username = trim($_POST['username'] ?? '');
    $new_email = trim($_POST['email'] ?? '');

    if ($new_username !== $user['username'] || $new_email !== $user['email']) {
        // Check if the new username or email already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE (username = ? OR email = ?) AND id != ?");
        $stmt->execute([$new_username, $new_email, $user_id]);
        $exists = $stmt->fetchColumn();

        if ($exists > 0) {
            $error = "اسم المستخدم أو البريد الإلكتروني موجود بالفعل.";
        } else {
            // Update username and email in the database
            $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
            $stmt->execute([$new_username, $new_email, $user_id]);

            $success = "تم تحديث بياناتك بنجاح.";
            $user['username'] = $new_username;
            $user['email'] = $new_email;
        }
    }

    // Update Password
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (!empty($current_password) && !empty($new_password) && !empty($confirm_password)) {
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $hashed_password = $stmt->fetchColumn();

        if (!password_verify($current_password, $hashed_password)) {
            $error = "كلمة المرور الحالية غير صحيحة.";
        } elseif ($new_password !== $confirm_password) {
            $error = "كلمة المرور الجديدة لا تتطابق مع التأكيد.";
        } else {
            // Hash and update the new password
            $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$new_hashed_password, $user_id]);

            $success = "تم تحديث كلمة المرور بنجاح.";
        }
    }
}
?>

<?php require 'includes/header.php'; ?>

<div class="container-fluid mt-4">
    <h1>إعدادات الحساب</h1>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <!-- Profile Picture -->
    <div class="card mb-4">
        <div class="card-header">
            <h5>صورة الملف الشخصي</h5>
        </div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                
                <div class="mb-3">
                    <?php if ($user['profile_picture']): ?>
                        <img src="<?= htmlspecialchars($user['profile_picture']) ?>" alt="صورة الملف الشخصي" class="rounded-circle" style="width: 100px; height: 100px;">
                    <?php else: ?>
                        <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                            <i class="fas fa-user"></i>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="mb-3">
                    <label for="profile_picture" class="form-label">اختر صورة جديدة:</label>
                    <input type="file" class="form-control" id="profile_picture" name="profile_picture">
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> تحديث الصورة
                </button>
            </form>
        </div>
    </div>

    <!-- Update Username and Email -->
    <div class="card mb-4">
        <div class="card-header">
            <h5>تحديث اسم المستخدم والبريد الإلكتروني</h5>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                
                <div class="mb-3">
                    <label for="username" class="form-label">اسم المستخدم</label>
                    <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">البريد الإلكتروني</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> تحديث البيانات
                </button>
            </form>
        </div>
    </div>

    <!-- Update Password -->
    <div class="card">
        <div class="card-header">
            <h5>تغيير كلمة المرور</h5>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                
                <div class="mb-3">
                    <label for="current_password" class="form-label">كلمة المرور الحالية</label>
                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                </div>
                
                <div class="mb-3">
                    <label for="new_password" class="form-label">كلمة المرور الجديدة</label>
                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                </div>
                
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">تأكيد كلمة المرور الجديدة</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> تغيير كلمة المرور
                </button>
            </form>
        </div>
    </div>
</div>

<?php require 'includes/footer.php'; ?>