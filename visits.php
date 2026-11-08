<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'conn.php';

$msg = "";
$error = "";

// معالجة إضافة زيارة أو مقابلة جديدة
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_visit'])) {
    $patient_id = $_POST['patient_id'];
    $visit_date = $_POST['visit_date'];
    $visit_type = trim($_POST['visit_type']);
    $status = trim($_POST['status']);

    if (!empty($patient_id) && !empty($visit_date)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO visits (patient_id, visit_date, visit_type, status) VALUES (?, ?, ?, ?)");
            $stmt->execute([$patient_id, $visit_date, $visit_type, $status]);
            $msg = "تمت إضافة الموعد أو الزيارة بنجاح!";
        } catch (PDOException $e) {
            $error = "خطأ في قاعدة البيانات: " . $e->getMessage();
        }
    } else {
        $error = "الرجاء اختيار المريض وتحديد تاريخ وزيارة المقابلة.";
    }
}

// جلب قائمة المرضى لربطهم بالزيارة
$patients_stmt = $pdo->query("SELECT id, full_name FROM patients ORDER BY full_name ASC");
$patients = $patients_stmt->fetchAll(PDO::FETCH_ASSOC);

// جلب جدول الزيارات والمقابلات مع اسم المريض
$visits_query = "SELECT visits.*, patients.full_name 
                 FROM visits 
                 JOIN patients ON visits.patient_id = patients.id 
                 ORDER BY visits.visit_date DESC";
$visits_stmt = $pdo->query($visits_query);
$visits = $visits_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الزيارات والمقابلات | EMR</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { 
            background: #f8f9fa;
            font-family: 'Tahoma', sans-serif;
            padding: 20px;
        }
        .main-container { 
            background: #ffffff; 
            border-radius: 1rem; 
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15); 
            padding: 25px; 
        }
    </style>
</head>
<body>

<div class="container">
    <div class="main-container">
        
        <!-- رأس الصفحة -->
        <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
            <h3 class="text-primary fw-bold mb-0">
                <i class="fa-solid fa-calendar-check me-2"></i> إدارة الزيارات والمقابلات
            </h3>
            <a href="index.php" class="btn btn-outline-secondary fw-bold">
                <i class="fa-solid fa-arrow-right me-1"></i> العودة للرئيسية
            </a>
        </div>

        <!-- رسائل النجاح أو الخطأ -->
        <?php if (!empty($msg)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- نموذج جدولة زيارة أو مقابلة جديدة -->
        <div class="card shadow-sm mb-5 border-0 bg-light">
            <div class="card-body p-4">
                <h5 class="card-title fw-bold text-dark mb-3">
                    <i class="fa-solid fa-calendar-plus text-success me-1"></i> جدولة زيارة أو مقابلة جديدة
                </h5>
                <form method="POST" action="">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="patient_id" class="form-label fw-bold">المريض</label>
                            <select name="patient_id" id="patient_id" class="form-select" required>
                                <option value="">-- اختر المريض --</option>
                                <?php foreach ($patients as $pat): ?>
                                    <option value="<?= $pat['id']; ?>"><?= htmlspecialchars($pat['full_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="visit_date" class="form-label fw-bold">تاريخ ووقت الزيارة</label>
                            <input type="datetime-local" name="visit_date" id="visit_date" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label for="visit_type" class="form-label fw-bold">نوع الزيارة</label>
                            <select name="visit_type" id="visit_type" class="form-select">
                                <option value="كشف طبي">كشف طبي</option>
                                <option value="متابعة">متابعة</option>
                                <option value="استشارة">استشارة</option>
                                <option value="طوارئ">طوارئ</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="status" class="form-label fw-bold">حالة الموعد</label>
                            <select name="status" id="status" class="form-select">
                                <option value="pending">قيد الانتظار (Pending)</option>
                                <option value="completed">مكتملة (Completed)</option>
                                <option value="cancelled">ملغاة (Cancelled)</option>
                            </select>
                        </div>
                        <div class="col-md-12 text-end mt-3">
                            <button type="submit" name="add_visit" class="btn btn-success fw-bold px-4">
                                <i class="fa-solid fa-floppy-disk me-1"></i> حفظ الموعد
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- جدول عرض الزيارات والمقابلات -->
        <h5 class="fw-bold text-secondary mb-3">سجل المواعيد والزيارات</h5>
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle text-center">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>اسم المريض</th>
                        <th>تاريخ ووقت الزيارة</th>
                        <th>نوع الزيارة</th>
                        <th>الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($visits) > 0): ?>
                        <?php $i = 1; foreach ($visits as $vis): ?>
                            <tr>
                                <th scope="row"><?= $i++; ?></th>
                                <td class="fw-bold text-start"><?= htmlspecialchars($vis['full_name']); ?></td>
                                <td class="font-monospace"><?= htmlspecialchars($vis['visit_date']); ?></td>
                                <td><span class="badge bg-info text-dark"><?= htmlspecialchars($vis['visit_type']); ?></span></td>
                                <td>
                                    <?php 
                                        if ($vis['status'] == 'completed') {
                                            echo '<span class="badge bg-success">مكتملة</span>';
                                        } elseif ($vis['status'] == 'cancelled') {
                                            echo '<span class="badge bg-danger">ملغاة</span>';
                                        } else {
                                            echo '<span class="badge bg-warning text-dark">قيد الانتظار</span>';
                                        }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-muted py-4">لا توجد زيارات أو مقابلات مسجلة حتى الآن.</td>
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