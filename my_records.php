<?php
session_start();
include 'conn.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['full_name'] ?? $_SESSION['username'] ?? 'مريضنا العزيز';

try {
    // جلب التشخيصات والسجلات الطبية الخاصة بالمريض
    $rec_stmt = $pdo->prepare("SELECT * FROM medical_records WHERE patient_id = ? OR patient_id = ? ORDER BY id DESC");
    $rec_stmt->execute([$user_id, $user_id]);
    $medical_records = $rec_stmt->fetchAll(PDO::FETCH_ASSOC);

    // جلب الزيارات والمقابلات الخاصة بالمريض
    $appt_stmt = $pdo->prepare("SELECT * FROM appointments WHERE user_id = ? OR patient_id = ? ORDER BY id DESC");
    $appt_stmt->execute([$user_id, $user_id]);
    $appointments = $appt_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = "حدث خطأ أثناء جلب البيانات: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>السجل الطبي والزيارات | Miko Hospital</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f4f6f9; font-family: 'Tahoma', sans-serif; }
        .welcome-banner { background: linear-gradient(135deg, #0d6efd, #0dcaf0); color: white; padding: 25px; border-radius: 1rem; margin-bottom: 30px; }
        .card { border: none; border-radius: 1rem; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); margin-bottom: 25px; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php"><i class="fa-solid fa-hospital text-success me-2"></i> مستشفى ميكو - بوابة المريض</a>
        <a href="logout.php" class="btn btn-outline-danger btn-sm">تسجيل الخروج</a>
    </div>
</nav>

<div class="container">
    <div class="welcome-banner text-center">
        <h2>مرحباً بك، <?= htmlspecialchars($user_name); ?></h2>
        <p class="mb-0">هنا سجلك الطبي الشامل والزيارات الخاصة بك (للقراءة فقط).</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger text-center"><?= $error; ?></div>
    <?php endif; ?>

    <!-- التشخيصات والوصفات الطبية -->
    <div class="card p-4 bg-white">
        <h4 class="text-primary mb-3"><i class="fa-solid fa-file-medical me-2"></i> التشخيصات والوصفات الطبية</h4>
        <div class="table-responsive">
            <table class="table table-hover align-middle text-center">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>التشخيص / الحالة</th>
                        <th>الوصفة الطبية (الأدوية)</th>
                        <th>التاريخ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($medical_records)): ?>
                        <?php $i = 1; foreach ($medical_records as $row): ?>
                            <tr>
                                <td><?= $i++; ?></td>
                                <td class="fw-bold text-danger"><?= htmlspecialchars($row['diagnosis'] ?? 'غير متوفر'); ?></td>
                                <td class="text-success"><?= htmlspecialchars($row['prescription'] ?? 'لا توجد'); ?></td>
                                <td class="text-muted small"><?= htmlspecialchars($row['created_at'] ?? ''); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-muted py-3">لا توجد تشخيصات أو وصفات طبية مسجلة باسمك حالياً.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- الزيارات والمقابلات الطبية -->
    <div class="card p-4 bg-white">
        <h4 class="text-success mb-3"><i class="fa-solid fa-calendar-check me-2"></i> الزيارات والمقابلات الطبية</h4>
        <div class="table-responsive">
            <table class="table table-hover align-middle text-center">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>سبب الزيارة / المقابلة</th>
                        <th>التاريخ والوقت</th>
                        <th>الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($appointments)): ?>
                        <?php $j = 1; foreach ($appointments as $appt): ?>
                            <tr>
                                <td><?= $j++; ?></td>
                                <td class="fw-bold"><?= htmlspecialchars($appt['reason'] ?? $appt['appointment_reason'] ?? 'مقابلة عامة'); ?></td>
                                <td><?= htmlspecialchars($appt['appointment_date'] ?? $appt['date'] ?? 'غير محدد'); ?></td>
                                <td><span class="badge bg-info text-dark">مؤكد / مسجل</span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-muted py-3">لا توجد زيارات أو مقابلات مسجلة حالياً.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>