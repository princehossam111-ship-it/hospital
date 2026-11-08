<?php
session_start();
include 'conn.php'; // ملف الاتصال بقاعدة البيانات

$success_msg = "";
$error_msg = "";

// معالجة تحديث حالة الصرف عند الضغط على زر التحديث
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $prescription_id = $_POST['prescription_id'];
    $dispense_status = $_POST['dispense_status']; // 'تم الصرف' أو 'لم يتم الصرف'

    try {
        $stmt_update = $pdo->prepare("UPDATE medical_records SET dispense_status = ? WHERE id = ?");
        $stmt_update->execute([$dispense_status, $prescription_id]);
        $success_msg = "تم تحديث حالة الصرف للوصفة بنجاح!";
    } catch (PDOException $e) {
        $error_msg = "خطأ أثناء التحديث: " . $e->getMessage();
    }
}

// جلب الوصفات الطبية مع بيانات المرضى واسم الطبيب من جدول medical_records (أو prescriptions)
$prescriptions = [];
try {
    $stmt = $pdo->prepare("
        SELECT mr.*, p.full_name as patient_name, p.age, p.gender, p.phone 
        FROM medical_records mr 
        JOIN patients p ON mr.patient_id = p.id 
        ORDER BY mr.created_at DESC
    ");
    $stmt->execute();
    $prescriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $prescriptions = [];
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة تحكم الصيدلية | EMR</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f4f6f9; font-family: 'Tahoma', sans-serif; padding: 20px; }
        .pharmacy-header { background: linear-gradient(135deg, #198754, #20c997); color: white; border-radius: 1rem; padding: 20px; margin-bottom: 20px; }
        .card-custom { background: #ffffff; border-radius: 1rem; box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05); padding: 25px; }
    </style>
</head>
<body>

<div class="container">
    <!-- رأس الصفحة -->
    <div class="pharmacy-header d-flex justify-content-between align-items-center shadow-sm">
        <div>
            <h2><i class="fa-solid fa-pills me-2"></i> قسم الصيدلية - الوصفات الطبية</h2>
            <p class="mb-0 text-white-50">متابعة وصفات المرضى وتحديث حالة صرف الأدوية</p>
        </div>
        <div>
            <a href="index.php" class="btn btn-light text-success btn-sm fw-bold"><i class="fa-solid fa-house me-1"></i> الرئيسية</a>
        </div>
    </div>

    <?php if (!empty($success_msg)): ?>
        <div class="alert alert-success text-center"><?= $success_msg; ?></div>
    <?php endif; ?>

    <?php if (!empty($error_msg)): ?>
        <div class="alert alert-danger text-center"><?= $error_msg; ?></div>
    <?php endif; ?>

    <!-- جدول الوصفات الطبية -->
    <div class="card-custom">
        <h4 class="text-success mb-3"><i class="fa-solid fa-file-prescription me-2"></i> قائمة الوصفات الطبية القادمة من الأطباء</h4>

        <?php if (!empty($prescriptions)): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>اسم المريض</th>
                            <th>الطبيب المعالج</th>
                            <th>التشخيص</th>
                            <th>الوصفة الطبية (الأدوية)</th>
                            <th>تاريخ الوصفة</th>
                            <th>حالة الصرف</th>
                            <th>إجراءات التحديث</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($prescriptions as $index => $prescription): ?>
                            <tr>
                                <td><?= $index + 1; ?></td>
                                <td class="fw-bold"><?= htmlspecialchars($prescription['patient_name']); ?></td>
                                <td>د. <?= htmlspecialchars($prescription['doctor_name']); ?></td>
                                <td><?= htmlspecialchars($prescription['diagnosis']); ?></td>
                                <td class="text-primary fw-bold"><?= nl2br(htmlspecialchars($prescription['prescription'])); ?></td>
                                <td><?= htmlspecialchars($prescription['created_at']); ?></td>
                                <td>
                                    <?php 
                                        $status = $prescription['dispense_status'] ?? 'لم يتم الصرف';
                                        $badge_class = ($status == 'تم الصرف') ? 'bg-success' : 'bg-danger';
                                    ?>
                                    <span class="badge <?= $badge_class; ?> fs-6">
                                        <?= htmlspecialchars($status); ?>
                                    </span>
                                </td>
                                <td>
                                    <!-- نموذج تغيير حالة الصرف -->
                                    <form method="POST" action="" class="d-flex gap-1">
                                        <input type="hidden" name="prescription_id" value="<?= $prescription['id']; ?>">
                                        <select name="dispense_status" class="form-select form-select-sm" style="width: 130px;">
                                            <option value="لم يتم الصرف" <?= ($status == 'لم يتم الصرف') ? 'selected' : ''; ?>>لم يتم الصرف</option>
                                            <option value="تم الصرف" <?= ($status == 'تم الصرف') ? 'selected' : ''; ?>>تم الصرف</option>
                                        </select>
                                        <button type="submit" name="update_status" class="btn btn-sm btn-outline-success">
                                            <i class="fa-solid fa-check"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center py-4 mb-0">
                <i class="fa-solid fa-circle-info fa-2x mb-2"></i>
                <h5>لا توجد وصفات طبية مسجلة حالياً.</h5>
                <p class="text-muted mb-0">ستظهر الوصفات فور قيام الأطباء بإضافتها للمرضى.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>