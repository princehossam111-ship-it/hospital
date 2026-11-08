<?php
session_start();
include 'conn.php';

// التأكد من أن المستخدم مسجل الدخول
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['full_name'] ?? $_SESSION['username'] ?? 'مستخدم';

try {
    // جلب السجلات الطبية والتشخيصات الخاصة بالمستخدم الحالي فقط
    $stmt = $pdo->prepare("SELECT * FROM medical_records WHERE user_id = ? ORDER BY id DESC");
    $stmt->execute([$user_id]);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = "خطأ في جلب البيانات: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>السجل الطبي والتشخيصات | Miko Hospital</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f8f9fa; font-family: 'Tahoma', sans-serif; }
        .header-box { background: linear-gradient(135deg, #0d6efd, #0dcaf0); color: white; padding: 30px; border-radius: 1rem; margin-bottom: 30px; }
    </style>
</head>
<body>

<!-- شريط التنقل -->
<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php"><i class="fa-solid fa-hospital text-success me-2"></i> Miko Hospital</a>
        <a href="index.php" class="btn btn-outline-light btn-sm fw-bold">العودة للرئيسية</a>
    </div>
</nav>

<div class="container">
    <div class="header-box text-center">
        <h2><i class="fa-solid fa-file-medical-alt me-2"></i> السجل الطبي والتشخيصات</h2>
        <p class="mb-0">مرحباً بك يا <strong><?= htmlspecialchars($user_name); ?></strong>، هذه هي كافة التشخيصات والتقارير الطبية الخاصة بك.</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger text-center"><?= $error; ?></div>
    <?php endif; ?>

    <!-- جدول عرض التشخيصات والسجلات الطبية -->
    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
        <div class="table-responsive">
            <table class="table table-hover align-middle text-center">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>التشخيص / الحالة المرضية</th>
                        <th>الوصفة الطبية (الأدوية)</th>
                        <th>الطبيب المعالج</th>
                        <th>تاريخ التسجيل</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($records)): ?>
                        <?php $i = 1; foreach ($records as $row): ?>
                            <tr>
                                <th scope="row"><?= $i++; ?></th>
                                <td class="fw-bold text-danger"><?= htmlspecialchars($row['diagnosis'] ?? 'غير متوفر'); ?></td>
                                <td class="text-success"><?= htmlspecialchars($row['prescription'] ?? 'لا توجد أدوية'); ?></td>
                                <td><?= htmlspecialchars($row['doctor_name'] ?? 'طبيب عام'); ?></td>
                                <td class="text-muted small"><?= htmlspecialchars($row['created_at'] ?? ''); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-muted py-4">لا توجد تشخيصات أو سجلات طبية مسجلة باسمك حتى الآن.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>