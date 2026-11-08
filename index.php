<?php
session_start();
include 'conn.php';

// التحقق من حالة تسجيل الدخول للمستخدم
$is_logged_in = isset($_SESSION['user_id']);
$username = $_SESSION['username'] ?? 'زائر';
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الرئيسية - نظام مستشفى ميكو</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f4f6f9; font-family: 'Tahoma', sans-serif; }
        .hero-section { background: linear-gradient(135deg, #0d6efd, #0dcaf0); color: white; border-radius: 1rem; padding: 50px 20px; margin-bottom: 30px; box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.1); }
        .feature-card { background: #fff; border-radius: 1rem; padding: 25px; box-shadow: 0 0.25rem 0.5rem rgba(0,0,0,0.02); transition: transform 0.2s; height: 100%; }
        .feature-card:hover { transform: translateY(-5px); box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.08); }
    </style>
</head>
<body>

<!-- شريط التنقل العلوي -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 py-3 shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php"><i class="fa-solid fa-hospital text-info me-2"></i> مستشفى ميكو</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link active" href="index.php"><i class="fa-solid fa-house me-1"></i> الرئيسية</a></li>
                <li class="nav-item"><a class="nav-link" href="doctors.php"><i class="fa-solid fa-user-doctor me-1"></i> الأطباء</a></li>
                <?php if ($is_logged_in): ?>
                    <li class="nav-item"><a class="nav-link" href="user_profile.php"><i class="fa-solid fa-id-card me-1"></i> الملف الطبي والـ QR</a></li>
                    <li class="nav-item"><a class="nav-link" href="doctor_dashboard.php"><i class="fa-solid fa-stethoscope me-1"></i> لوحة الطبيب</a></li>
                <?php endif; ?>
            </ul>
            <div class="d-flex align-items-center">
                <?php if ($is_logged_in): ?>
                    <span class="text-light me-3"><i class="fa-solid fa-user me-1"></i> أهلاً، <?= htmlspecialchars($username); ?></span>
                    <a href="logout.php" class="btn btn-outline-danger btn-sm"><i class="fa-solid fa-right-from-bracket me-1"></i> خروج</a>
                <?php else: ?>
                    <!-- زر تسجيل الدخول يوجه مباشرة لصفحة login.php -->
                    <a href="login.php" class="btn btn-outline-light btn-sm me-2"><i class="fa-solid fa-right-to-bracket me-1"></i> تسجيل دخول</a>
                    <a href="register.php" class="btn btn-info btn-sm text-dark fw-bold"><i class="fa-solid fa-user-plus me-1"></i> حساب جديد</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<div class="container">
    <!-- واجهة الترحيب الكبرى -->
    <div class="hero-section text-center">
        <h1 class="display-5 fw-bold mb-3">مرحباً بك في نظام مستشفى ميكو الطبي</h1>
        <p class="lead mb-4">منصة متكاملة لإدارة المواعيد، سجلات المرضى، التشخيصات الفورية، ورمز الاستجابة السريع (QR Code).</p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="doctors.php" class="btn btn-light btn-lg fw-bold text-primary shadow px-4">
                <i class="fa-solid fa-user-doctor me-2"></i> تصفح الأطباء
            </a>
            <?php if ($is_logged_in): ?>
                <a href="user_profile.php" class="btn btn-outline-light btn-lg fw-bold shadow px-4">
                    <i class="fa-solid fa-qrcode me-2"></i> الانتقال لملفي الطبي
                </a>
            <?php else: ?>
                <!-- زر تسجيل الدخول الرئيسي -->
                <a href="login.php" class="btn btn-warning btn-lg fw-bold text-dark shadow px-4">
                    <i class="fa-solid fa-right-to-bracket me-2"></i> تسجيل الدخول للنظام
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- بطاقات الخدمات -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="feature-card text-center">
                <div class="text-primary mb-3"><i class="fa-solid fa-user-doctor fa-3x"></i></div>
                <h4 class="fw-bold mb-2">قائمة الأطباء</h4>
                <p class="text-muted small">تعرف على الأطباء المتاحين في المستشفى، تخصصاتهم، وقم بحجز المواعيد معهم بسهولة تامة.</p>
                <a href="doctors.php" class="btn btn-primary btn-sm mt-2"><i class="fa-solid fa-arrow-left me-1"></i> عرض الأطباء</a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="feature-card text-center">
                <div class="text-success mb-3"><i class="fa-solid fa-qrcode fa-3x"></i></div>
                <h4 class="fw-bold mb-2">الملف الطبي والباركود</h4>
                <p class="text-muted small">استعرض سجلك الطبي والتشخيصات والوصفات وأبرز رمز الـ QR المربع للطبيب للفحص الفوري.</p>
                <?php if ($is_logged_in): ?>
                    <a href="user_profile.php" class="btn btn-success btn-sm mt-2"><i class="fa-solid fa-arrow-left me-1"></i> فتح البروفايل</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-success btn-sm mt-2"><i class="fa-solid fa-lock me-1"></i> سجل دخول أولاً</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-md-4">
            <div class="feature-card text-center">
                <div class="text-warning mb-3"><i class="fa-solid fa-stethoscope fa-3x"></i></div>
                <h4 class="fw-bold mb-2">لوحة تحكم الطبيب</h4>
                <p class="text-muted small">إدارة الحجوزات الواردة، كتابة التشخيصات الطبية، ووصفات الأدوية للمرضى بدقة واحترافية.</p>
                <a href="doctor_dashboard.php" class="btn btn-warning text-dark btn-sm mt-2"><i class="fa-solid fa-arrow-left me-1"></i> لوحة التحكم</a>
            </div>
        </div>
    </div>
</div>

<!-- تذييل الصفحة -->
<footer class="bg-dark text-white text-center py-3 mt-4">
    <p class="mb-0 small">&copy; 2026 نظام مستشفى ميكو الإلكتروني. جميع الحقوق محفوظة.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>