<?php
include 'conn.php';

$message = "";
$status = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $new_password = trim($_POST['new_password']);

    if (!empty($username) && !empty($new_password)) {
        // التحقق من وجود اسم المستخدم في قاعدة البيانات
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        
        if ($stmt->rowCount() > 0) {
            // تحديث كلمة المرور مباشرة
            $update = $pdo->prepare("UPDATE users SET password = ? WHERE username = ?");
            if ($update->execute([$new_password, $username])) {
                $message = "تم إعادة تعيين كلمة المرور بنجاح! يمكنك تسجيل الدخول الآن.";
                $status = "success";
            } else {
                $message = "حدث خطأ أثناء تحديث كلمة المرور.";
                $status = "danger";
            }
        } else {
            $message = "اسم المستخدم غير موجود في النظام.";
            $status = "warning";
        }
    } else {
        $message = "الرجاء ملء جميع الحقول المطلوبة.";
        $status = "warning";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>استعادة كلمة المرور | Miko Hospital</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { 
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Tahoma', sans-serif;
        }
        .reset-card { background: #fff; border-radius: 1rem; padding: 40px; box-shadow: 0 1rem 3rem rgba(0,0,0,0.1); width: 100%; max-width: 450px; }
    </style>
</head>
<body>

<div class="reset-card">
    <div class="text-center mb-4">
        <i class="fa-solid fa-key text-warning fa-3x mb-2"></i>
        <h3 class="fw-bold text-dark">إعادة تعيين كلمة المرور</h3>
        <p class="text-muted small">أدخل اسم المستخدم وكلمة المرور الجديدة</p>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= $status; ?> text-center" role="alert"><?= $message; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label fw-bold">اسم المستخدم:</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                <input type="text" name="username" class="form-control" required placeholder="أدخل اسم المستخدم...">
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label fw-bold">كلمة المرور الجديدة:</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                <input type="password" name="new_password" class="form-control" required placeholder="أدخل كلمة المرور الجديدة...">
            </div>
        </div>

        <button type="submit" class="btn btn-warning w-100 py-2 fw-bold mb-3 text-dark">
            <i class="fa-solid fa-rotate-right me-1"></i> تحديث كلمة المرور
        </button>
        
        <div class="text-center">
            <a href="login.php" class="text-decoration-none small"><i class="fa-solid fa-arrow-right me-1"></i> العودة لتسجيل الدخول</a>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>