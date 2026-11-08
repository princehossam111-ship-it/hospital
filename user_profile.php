<?php
session_start();
include 'conn.php';

$msg = "";
$patients = [];

// 1. معالجة إضافة مريض جديد عند إرسال النموذج
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_patient'])) {
    $full_name = trim($_POST['full_name'] ?? '');
    $age = trim($_POST['age'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $gender = trim($_POST['gender'] ?? 'محدد');

    if (!empty($full_name)) {
        try {
            // إدخال المريض في جدول patients
            $stmt = $pdo->prepare("INSERT INTO patients (full_name, age, phone, gender) VALUES (?, ?, ?, ?)");
            $stmt->execute([$full_name, $age, $phone, $gender]);
            $msg = "<div class='alert alert-success py-2'><i class='fa-solid fa-circle-check me-1'></i> تمت إضافة المريض بنجاح!</div>";
        } catch (PDOException $e) {
            $msg = "<div class='alert alert-danger py-2'><i class='fa-solid fa-triangle-exclamation me-1'></i> خطأ في الإضافة: " . $e->getMessage() . "</div>";
        }
    } else {
        $msg = "<div class='alert alert-warning py-2'>الرجاء إدخال اسم المريض على الأقل.</div>";
    }
}

// 2. جلب قائمة المرضى المسجلين من قاعدة البيانات
try {
    $stmt_patients = $pdo->query("SELECT * FROM patients ORDER BY id DESC");
    $patients = $stmt_patients->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // في حال حدث خطأ في الجدول أو لم يكن موجوداً
    $patients = [];
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المرضى - مستشفى ميكو</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f4f6f9; font-family: 'Tahoma', sans-serif; padding: 20px; }
        .main-card { background: #fff; border-radius: 1rem; box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.05); padding: 25px; margin-bottom: 25px; }
    </style>
</head>
<body>

<div class="container">
    <!-- رأس الصفحة -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fa-solid fa-hospital-user text-primary me-2"></i> إدارة المرضى المسجلين</h2>
        <div>
            <a href="index.php" class="btn btn-outline-primary btn-sm me-2"><i class="fa-solid fa-house me-1"></i> الرئيسية</a>
            <a href="doctor_dashboard.php" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-stethoscope me-1"></i> لوحة الطبيب</a>
        </div>
    </div>

    <?= $msg; ?>

    <div class="row">
        <!-- نموذج إضافة مريض جديد (اليسار / الأعلى) -->
        <div class="col-md-4 mb-4">
            <div class="main-card">
                <h4 class="text-primary mb-3"><i class="fa-solid fa-user-plus me-2"></i> إضافة مريض جديد</h4>
                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">اسم المريض</label>
                        <input type="text" class="form-control" name="full_name" required placeholder="اسم المريض الرباعي">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">العمر</label>
                        <input type="number" class="form-control" name="age" placeholder="العمر بالسنوات">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">رقم الهاتف</label>
                        <input type="text" class="form-control" name="phone" placeholder="رقم الهاتف للتواصل">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">الجنس</label>
                        <select class="form-select" name="gender">
                            <option value="ذكر">ذكر</option>
                            <option value="أنثى">أنثى</option>
                        </select>
                    </div>
                    <button type="submit" name="add_patient" class="btn btn-primary w-100 fw-bold">
                        <i class="fa-solid fa-plus me-1"></i> حفظ المريض
                    </button>
                </form>
            </div>
        </div>

        <!-- جدول قائمة المرضى المسجلين (اليمين) -->
        <div class="col-md-8">
            <div class="main-card">
                <h4 class="text-secondary mb-3"><i class="fa-solid fa-users me-2"></i> قائمة المرضى المسجلين في النظام</h4>

                <?php if (!empty($patients)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>اسم المريض</th>
                                    <th>العمر</th>
                                    <th>الهاتف</th>
                                    <th>الجنس</th>
                                    <th>إجراء</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($patients as $index => $patient): ?>
                                    <tr>
                                        <td><?= $index + 1; ?></td>
                                        <td class="fw-bold"><?= htmlspecialchars($patient['full_name'] ?? 'مبدئي'); ?></td>
                                        <td><?= htmlspecialchars($patient['age'] ?? 'غير متوفر'); ?></td>
                                        <td><?= htmlspecialchars($patient['phone'] ?? 'غير متوفر'); ?></td>
                                        <td><?= htmlspecialchars($patient['gender'] ?? 'غير محدد'); ?></td>
                                        <td>
                                            <!-- زر لعرض الملف الطبي أو كتابة تشخيص لهذا المريض مباشرة -->
                                            <a href="add_prescription.php?patient_id=<?= $patient['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fa-solid fa-file-medical me-1"></i> تشخيص
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info text-center py-4 mb-0">
                        <i class="fa-solid fa-circle-info fa-2x mb-2"></i>
                        <h5>لا يوجد مرضى مسجلين حتى الآن.</h5>
                        <p class="text-muted mb-0">قم بإضافة أول مريض باستخدام النموذج المجاور.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>