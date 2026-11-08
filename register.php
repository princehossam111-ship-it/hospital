<?php
session_start();
include 'conn.php';

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($username) && !empty($full_name) && !empty($password)) {
        try {
            // التحقق مما إذا كان اسم المستخدم مستخدماً من قبل
            $check_stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $check_stmt->execute([$username]);
            
            if ($check_stmt->rowCount() > 0) {
                $error = "اسم المستخدم موجود مسبقاً، يججي اختيار اسم آخر.";
            } else {
                // تشفير كلمة المرور للأمان
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // إدخال المستخدم الجديد بدور مريض (patient) افتراضياً مع الاسم الكامل
                $stmt = $pdo->prepare("INSERT INTO users (username, full_name, password, role) VALUES (?, ?, ?, 'patient')");
                $stmt->execute([$username, $full_name, $hashed_password]);

                $success = "تم إنشاء الحساب بنجاح! يمكنك الآن تسجيل الدخول.";
            }
        } catch (PDOException $e) {
            $error = "خطأ في قاعدة البيانات: " . $e->getMessage();
        }
    } else {
        $error = "الرجاء ملء كافة الحقول المطلوبة.";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إنشاء حساب جديد | Miko Hospital</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f8f9fa; font-family: 'Tahoma', sans-serif; display: flex; align-items: center; justify-content: height: 100vh; height: 100vh; }
        .card { border: none; border-radius: 1rem; box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15); }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card p-4 bg-white">
                <div class="text-center mb-4">
                    <i class="fa-solid fa-hospital-user fa-3x text-primary mb-2"></i>
                    <h3 class="fw-bold">إنشاء حساب مريض جديد</h3>
                    <p class="text-muted small">مستشفى ميكو - Miko Hospital</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger text-center"><?= $error; ?></div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success text-center">
                        <?= $success; ?><br>
                        <a href="login.php" class="btn btn-sm btn-primary mt-2">تسجيل الدخول الآن</a>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label">الاسم الكامل</label>
                        <input type="text" name="full_name" class="form-control" placeholder="أدخل اسمك الكامل" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">اسم المستخدم</label>
                        <input type="text" name="username" class="form-control" placeholder="أدخل اسم المستخدم لتسجيل الدخول" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">كلمة المرور</label>
                        <input type="password" name="password" class="form-control" placeholder="أدخل كلمة المرور" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">تسجيل الحساب</button>
                </form>

                <div class="text-center mt-3">
                    <p class="small text-muted">لديك حساب بالفعل؟ <a href="login.php" class="text-decoration-none fw-bold">سجل دخولك هنا</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>