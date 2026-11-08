<?php
session_start();
include 'conn.php';

$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($username) && !empty($password)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && ($password === $user['password'] || password_verify($password, $user['password']))) {
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'] ?? 'patient'; // تعيين دور الحساب (admin أو patient)

                // التوجيه بناءً على نوع الحساب
                if ($_SESSION['role'] === 'admin') {
                    header("Location: patients.php"); // الآدمن فقط يذهب لإدارة المرضى
                } else {
                    header("Location: user_profile.php"); // العملاء يذهبون لرؤية حالتهم فقط
                }
                exit();

            } else {
                $error_msg = "اسم المستخدم أو كلمة المرور غير صحيحة!";
            }
        } catch (PDOException $e) {
            $error_msg = "خطأ في قاعدة البيانات: " . $e->getMessage();
        }
    } else {
        $error_msg = "الرجاء إدخال بيانات الدخول كاملة.";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - نظام مستشفى ميكو</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #0d6efd, #0dcaf0); font-family: 'Tahoma', sans-serif; height: 100vh; display: flex; align-items: center; justify-content: center; margin: 0; }
        .login-card { background: #fff; border-radius: 1rem; box-shadow: 0 1rem 3rem rgba(0,0,0,0.15); padding: 40px; width: 100%; max-width: 450px; }
    </style>
</head>
<body>

<div class="container">
    <div class="login-card mx-auto">
        <div class="text-center mb-4">
            <div class="mb-3 text-primary"><i class="fa-solid fa-hospital-user fa-3x"></i></div>
            <h3 class="text-primary fw-bold">مستشفى ميكو الطبي</h3>
            <p class="text-muted small">تسجيل الدخول للنظام</p>
        </div>

        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger text-center py-2 mb-3 small"><?= htmlspecialchars($error_msg); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label for="username" class="form-label fw-bold small"><i class="fa-solid fa-user me-1 text-primary"></i> اسم المستخدم</label>
                <input type="text" class="form-control" id="username" name="username" required placeholder="أدخل اسم المستخدم">
            </div>

            <div class="mb-4">
                <label for="password" class="form-label fw-bold small"><i class="fa-solid fa-lock me-1 text-primary"></i> كلمة المرور</label>
                <input type="password" class="form-control" id="password" name="password" required placeholder="أدخل كلمة المرور">
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm mb-3">
                <i class="fa-solid fa-right-to-bracket me-1"></i> دخول
            </button>
            <div class="text-center">
                <a href="index.php" class="text-secondary small text-decoration-none"><i class="fa-solid fa-house me-1"></i> الرئيسية</a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>