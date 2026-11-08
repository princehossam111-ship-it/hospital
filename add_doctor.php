<?php
session_start();
// التحقق من تسجيل الدخول والصلاحية
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'conn.php';

$message = "";
$status = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $doctor_name = trim($_POST['doctor_name']);
    $specialization = trim($_POST['specialization']);

    if (!empty($doctor_name) && !empty($specialization)) {
        $sql = "INSERT INTO doctors (doctor_name, specialization) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$doctor_name, $specialization])) {
            $message = "تم إضافة الطبيب والتخصص بنجاح!";
            $status = "success";
        } else {
            $message = "حدث خطأ أثناء حفظ بيانات الطبيب.";
            $status = "danger";
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
    <title>إضافة طبيب جديد | Miko Hospital</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { 
            background: url('https://images.unsplash.com/photo-1586773860418-d37222d8fce3?auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
            font-family: 'Tahoma', sans-serif;
            padding: 40px 15px;
        }
        .note-card { 
            background: rgba(255, 255, 255, 0.98); 
            border: 1px solid #dee2e6;
            border-radius: 1rem; 
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.35); 
            max-width: 650px; 
            margin: auto; 
            padding: 40px; 
        }
        .note-header {
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .form-control {
            background-color: #f8f9fa;
            border: 1px solid #ced4da;
            padding: 12px;
            border-radius: 0.5rem;
        }
        .form-control:focus {
            background-color: #fff;
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
        }
    </style>
</head>
<body>

<div class="container">
    <div class="note-card">
        
        <!-- رأس الصفحة وأزرار التحكم -->
        <div class="note-header d-flex justify-content-between align-items-center">
            <div>
                <h3 class="text-primary fw-bold mb-1"><i class="fa-solid fa-user-doctor me-2"></i> إضافة طبيب جديد</h3>
                <p class="text-muted small mb-0">إدخال طبيب جديد وتخصصه للنظام</p>
            </div>
            <div>
                <a href="add_patient.php" class="btn btn-outline-primary btn-sm fw-bold me-1">
                    <i class="fa-solid fa-arrow-right"></i> صفحة الإضافة
                </a>
                <a href="logout.php" class="btn btn-outline-danger btn-sm fw-bold">
                    <i class="fa-solid fa-power-off"></i> خروج
                </a>
            </div>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= $status; ?> text-center mb-4" role="alert">
                <?= $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label fw-bold text-dark">اسم الطبيب:</label>
                <input type="text" name="doctor_name" class="form-control" required placeholder="أدخل اسم الطبيب (مثال: د. محمد علي)...">
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold text-dark">التخصص الطبي:</label>
                <input type="text" name="specialization" class="form-control" required placeholder="أدخل التخصص (مثال: باطنية، أسنان، أطفال)...">
            </div>

            <!-- أزرار الإجراءات -->
            <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                <a href="add_patient.php" class="btn btn-secondary px-4">
                    <i class="fa-solid fa-arrow-right me-1"></i> رجوع
                </a>
                <button type="submit" class="btn btn-success px-5 fw-bold">
                    <i class="fa-solid fa-save me-1"></i> حفظ الطبيب
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>