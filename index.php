<?php
session_start();
require 'includes/db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? ''); // Username or Email
    $password = $_POST['password'] ?? '';

    if (empty($identifier) || empty($password)) {
        $error = "يرجى إدخال اسم المستخدم أو البريد الإلكتروني وكلمة المرور.";
    } else {
        // Query to check if the identifier matches either username or email
        $stmt = $pdo->prepare("SELECT id, username, email, password FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$identifier, $identifier]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Successful login
            $_SESSION['user_id'] = $user['id'];
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "اسم المستخدم أو البريد الإلكتروني أو كلمة المرور غير صحيحة.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>تسجيل الدخول</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label for="identifier" class="form-label">اسم المستخدم أو البريد الإلكتروني</label>
                                <input type="text" class="form-control" id="identifier" name="identifier" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">كلمة المرور</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary">دخول</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>