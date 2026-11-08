<?php
session_start();
include 'conn.php';

$success_msg = "";
$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $specialty = trim($_POST['specialty'] ?? 'طب عام');
    $role = 'doctor'; // تثبيت الصلاحية كطبيب

    if (!empty($fullname) && !empty($email) && !empty($password)) {
        try {
            // التحقق مما إذا كان البريد الإلكتروني مسجلاً مسبقاً
            $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $check->execute([$email]);
            if ($check->rowCount() > 0) {
                $error_msg = "البريد الإلكتروني مستخدم بالفعل، يرجى استخدام بريد آخر.";
            } else {
                // تشفير كلمة المرور
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // إدخال الطبيب الجديد في قاعدة البيانات
                $stmt = $pdo->prepare("INSERT INTO users (fullname, email, password, specialty, role) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$fullname, $email, $hashed_password, $specialty, $role]);
                
                $success_msg = "تم إنشاء حساب الطبيب بنجاح! يمكنك الآن تسجيل الدخول.";
            }
        } catch (PDOException $e) {
            $error_msg = "خطأ في قاعدة البيانات: " . $e->getMessage();
        }
    } else {
        $error_msg = "الرجاء ملء جميع الحقول المطلوبة.";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل حساب طبيب جديد | مستشفى ميكو</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f4f6f9; font-family: 'Tahoma', sans-serif; }
        .card { border: none; border-radius: 1rem; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); }
    </style>
</head>
<body>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card p-4 bg-white">
                <h3 class="text-primary mb-4 text-center"><i class="fa-solid fa-user-doctor me-2"></i> تسجيل حساب طبيب جديد</h3>

                <?php if (!empty($success_msg)): ?>
                    <div class="alert alert-success text-center"><?= $success_msg; ?></div>
                <?php endif; ?>

                <?php if (!empty($error_msg)): ?>
                    <div class="alert alert-danger text-center"><?= $error_msg; ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label fw-bold">اسم الطبيب الرباعي:</label>
                        <input type="text" name="fullname" class="form-control" placeholder="مثال: د. محمد أحمد" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">التخصص الطبي:</label>
                        <input type="text" name="specialty" class="form-control" placeholder="مثال: باطنية، أطفال، جراحة..." required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">البريد الإلكتروني:</label>
                        <input type="email" name="email" class="form-control" placeholder="name@example.com" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">كلمة المرور:</label>
                        <input type="password" name="password" class="form-control" placeholder="********" required>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fa-solid fa-user-plus me-2"></i> إنشاء الحساب
                        </button>
                    </div>
                </form>

                <div class="text-center mt-3">
                    <a href="login.php" class="text-decoration-none">لديك حساب بالفعل؟ تسجيل الدخول</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>