<?php
session_start();
include 'conn.php';

// تحديد معرف الطبيب الحالي
$selected_doctor_id = $_SESSION['doctor_id'] ?? $_GET['doctor_id'] ?? 1;
$doctor_name = $_SESSION['doctor_name'] ?? 'الطبيب المعالج';

$appointments = [];
$total_appointments = 0;
$total_patients = 0;

try {
    // جلب جميع الحجوزات بطريقة مباشرة ومضمونة لضمان عدم ضياع أي حجز
    $stmt_app = $pdo->prepare("
        SELECT a.*, 
               COALESCE(p.full_name, u.username, 'مريض رقم ' . a.patient_id) as patient_name, 
               COALESCE(p.phone, 'غير متوفر') as phone 
        FROM appointments a 
        LEFT JOIN patients p ON a.patient_id = p.id OR a.patient_id = p.user_id
        LEFT JOIN users u ON a.patient_id = u.id
        ORDER BY a.id DESC
    ");
    $stmt_app->execute();
    $appointments = $stmt_app->fetchAll(PDO::FETCH_ASSOC);

    $total_appointments = count($appointments);
    
    // حساب عدد المرضى الفريدين
    $unique_patients = [];
    foreach ($appointments as $app) {
        $unique_patients[$app['patient_id']] = true;
    }
    $total_patients = count($unique_patients);

} catch (PDOException $e) {
    // في حال حدث خطأ في الجدول المرتبط، جلب البيانات مباشرة من جدول الحجوزات الأساسي
    $stmt_fallback = $pdo->query("SELECT * FROM appointments ORDER BY id DESC");
    $appointments = $stmt_fallback->fetchAll(PDO::FETCH_ASSOC);
    $total_appointments = count($appointments);
    $total_patients = $total_appointments;
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة تحكم الطبيب - مستشفى ميكو</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f4f6f9; font-family: 'Tahoma', sans-serif; padding: 20px; }
        .dashboard-card { background: #fff; border-radius: 1rem; box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.05); padding: 25px; margin-bottom: 25px; }
        .stat-box { border-right: 4px solid #0d6efd; background: #fff; border-radius: 0.5rem; padding: 20px; box-shadow: 0 0.25rem 0.5rem rgba(0,0,0,0.02); }
    </style>
</head>
<body>

<div class="container">
    <!-- رأس الصفحة -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fa-solid fa-user-doctor text-primary me-2"></i> لوحة تحكم الطبيب</h2>
        <div>
            <a href="index.php" class="btn btn-outline-primary btn-sm me-2"><i class="fa-solid fa-house me-1"></i> الرئيسية</a>
            <a href="logout.php" class="btn btn-danger btn-sm"><i class="fa-solid fa-right-from-bracket me-1"></i> خروج</a>
        </div>
    </div>

    <!-- إحصائيات سريعة -->
    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <div class="stat-box">
                <h5 class="text-muted"><i class="fa-solid fa-calendar-check text-primary me-2"></i> إجمالي الحجوزات</h5>
                <h3 class="fw-bold text-dark mt-2"><?= $total_appointments; ?></h3>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="stat-box" style="border-color: #198754;">
                <h5 class="text-muted"><i class="fa-solid fa-users text-success me-2"></i> المرضى المسجلين</h5>
                <h3 class="fw-bold text-dark mt-2"><?= $total_patients; ?></h3>
            </div>
        </div>
    </div>

    <!-- جدول الحجوزات -->
    <div class="dashboard-card">
        <h4 class="text-primary mb-4"><i class="fa-solid fa-list-check me-2"></i> قائمة الحجوزات الواردة من المرضى</h4>

        <?php if (!empty($appointments)): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>رقم المريض</th>
                            <th>تاريخ الموعد</th>
                            <th>وقت الموعد</th>
                            <th>الحالة</th>
                            <th>الإجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $index => $app): ?>
                            <tr>
                                <td><?= $index + 1; ?></td>
                                <td class="fw-bold">مريض رقم #<?= htmlspecialchars($app['patient_id']); ?></td>
                                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($app['appointment_date'] ?? 'غير محدد'); ?></span></td>
                                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($app['appointment_time'] ?? 'غير محدد'); ?></span></td>
                                <td><span class="badge bg-warning text-dark"><?= htmlspecialchars($app['status'] ?? 'قيد الانتظار'); ?></span></td>
                                <td>
                                    <!-- رابط الانتقال لكتابة التشخيص أو الوصفة للمريض -->
                                    <a href="add_prescription.php?patient_id=<?= $app['patient_id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fa-solid fa-file-medical me-1"></i> كتابة تشخيص
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-light text-center py-4 border mb-0">
                <i class="fa-solid fa-calendar-xmark fa-2x text-muted mb-2"></i>
                <h5 class="text-secondary">لا توجد حجوزات مسجلة حالياً في قاعدة البيانات.</h5>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>