<?php
include 'conn.php';

$message = "";
$status = "";
$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = $_POST['full_name'];
    $birth_date = $_POST['birth_date'];
    $gender = $_POST['gender'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    $sql = "INSERT INTO patients (full_name, birth_date, gender, phone, address) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$full_name, $birth_date, $gender, $phone, $address])) {
        $message = "تم حفظ بيانات المريض بنجاح!";
        $status = "success";
        $success = true;
    } else {
        $message = "حدث خطأ أثناء حفظ البيانات، يرجى المحاولة مرة أخرى.";
        $status = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة مريض جديد | Miko Hospital</title>
    <!-- استدعاء Bootstrap 5 RTL -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <!-- أيقونات FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { 
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); 
            min-height: 100vh;
            font-family: 'Tahoma', sans-serif; 
            padding: 40px 20px; 
        }
        .main-card { 
            border: none; 
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.1); 
            border-radius: 1rem; 
            background: #ffffff; 
            overflow: hidden;
        }
        .card-header-custom {
            background-color: #0d6efd;
            color: white;
            padding: 20px 30px;
        }
        .form-control, .form-select {
            padding: 12px;
            border-radius: 0.5rem;
            border: 1px solid #ced4da;
        }
        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
        }
        .btn-action {
            padding: 10px 25px;
            border-radius: 0.5rem;
            font-weight: 600;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            
            <div class="main-card">
                <!-- رأس البطاقة بتصميم أنيق -->
                <div class="card-header-custom d-flex justify-content-between align-items-center">
                    <h4 class="mb-0 fw-bold"><i class="fa-solid fa-user-plus me-2"></i> تسجيل مريض جديد</h4>
                    <a href="index.php" class="btn btn-light btn-sm text-primary fw-bold px-3">
                        <i class="fa-solid fa-arrow-right me-1"></i> العودة للقائمة
                    </a>
                </div>

                <div class="p-4 p-md-5">
                    <?php if (!empty($message)): ?>
                        <div class="alert alert-<?= $status; ?> alert-dismissible fade show d-flex justify-content-between align-items-center mb-4" role="alert">
                            <div>
                                <i class="fa-solid fa-circle-check me-2"></i> <?= $message; ?>
                            </div>
                            <?php if ($success): ?>
                                <a href="index.php" class="btn btn-dark btn-sm">عرض قائمة المرضى ←</a>
                            <?php endif; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary">اسم المريض الرباعي:</label>
                            <input type="text" name="full_name" class="form-control" required placeholder="أدخل الاسم الكامل هنا...">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold text-secondary">تاريخ الميلاد:</label>
                                <input type="date" name="birth_date" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold text-secondary">الجنس:</label>
                                <select name="gender" class="form-select">
                                    <option value="Male">ذكر</option>
                                    <option value="Female">أنثى</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary">رقم الهاتف:</label>
                            <input type="text" name="phone" class="form-control" required placeholder="أدخل رقم الهاتف (مثال: 0912345678)...">
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-secondary">العنوان:</label>
                            <textarea name="address" class="form-control" rows="3" placeholder="أدخل العنوان التفصيلي (المدينة، الحي)..."></textarea>
                        </div>

                        <!-- أزرار الحفظ والرجوع بتنسيق متناسق -->
                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <a href="index.php" class="btn btn-outline-secondary btn-action">
                                <i class="fa-solid fa-arrow-right me-1"></i> إلغاء ورجوع
                            </a>
                            <button type="submit" class="btn btn-success btn-action px-5">
                                <i class="fa-solid fa-save me-1"></i> حفظ المريض
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>