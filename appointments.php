<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'conn.php';

$message = "";
$status = "";

// جلب قائمة المرضى
$patients_stmt = $pdo->query("SELECT id, full_name FROM patients ORDER BY full_name ASC");
$patients = $patients_stmt->fetchAll(PDO::FETCH_ASSOC);

// جلب قائمة الأطباء
$doctors_stmt = $pdo->query("SELECT id, doctor_name, specialization FROM doctors ORDER BY doctor_name ASC");
$doctors = $doctors_stmt->fetchAll(PDO::FETCH_ASSOC);

// معالجة نموذج حجز المقابلة الجديدة
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $patient_id = $_POST['patient_id'];
    $doctor_id = $_POST['doctor_id'];
    $appointment_date = $_POST['appointment_date'];
    $notes = trim($_POST['notes']);

    if (!empty($patient_id) && !empty($doctor_id) && !empty($appointment_date)) {
        // التحقق من عدم تضارب الموعد لنفس الطبيب في نفس الوقت تماماً
        $check_conflict = $pdo->prepare("SELECT * FROM appointments WHERE doctor_id = ? AND appointment_date = ?");
        $check_conflict->execute([$doctor_id, $appointment_date]);

        if ($check_conflict->rowCount() > 0) {
            $message = "عذراً، يوجد حجز مسجل مسبقاً لهذا الطبيب في نفس هذا الموعد تماماً! اختر وقتاً آخر لتجنب التضارب.";
            $status = "danger";
        } else {
            // إدخال الموعد الجديد
            $insert = $pdo->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, notes) VALUES (?, ?, ?, ?)");
            if ($insert->execute([$patient_id, $doctor_id, $appointment_date, $notes])) {
                $message = "تم تسجيل المقابلة بنجاح بدون أي تضارب!";
                $status = "success";
            } else {
                $message = "حدث خطأ أثناء حفظ المقابلة.";
                $status = "danger";
            }
        }
    } else {
        $message = "الرجاء ملء الحقول الأساسية (المريض، الطبيب، وموعد المقابلة).";
        $status = "warning";
    }
}

// جلب قائمة المقابلات المحجوزة لعرضها في الجدول
$appointments_sql = "SELECT appointments.*, patients.full_name as patient_name, doctors.doctor_name, doctors.specialization 
                     FROM appointments 
                     JOIN patients ON appointments.patient_id = patients.id 
                     JOIN doctors ON appointments.doctor_id = doctors.id 
                     ORDER BY appointments.appointment_date ASC";
$appointments_stmt = $pdo->query($appointments_sql);
$appointments_list = $appointments_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المقابلات والمواعيد | Miko Hospital</title>
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
            padding: 30px 15px;
        }
        .main-card { 
            background: rgba(255, 255, 255, 0.98); 
            border: 1px solid #dee2e6;
            border-radius: 1rem; 
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.35); 
            padding: 30px; 
            max-width: 1000px;
            margin: auto;
        }
        .note-header {
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .form-control, .form-select {
            background-color: #f8f9fa;
            border: 1px solid #ced4da;
            padding: 10px;
            border-radius: 0.5rem;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="main-card">
        
        <!-- رأس الصفحة وأزرار التحكم -->
        <div class="note-header d-flex justify-content-between align-items-center">
            <div>
                <h3 class="text-primary fw-bold mb-1"><i class="fa-solid fa-calendar-check me-2"></i> جدول المقابلات والمواعيد</h3>
                <p class="text-muted small mb-0">حجز وتنظيم المقابلات لمنع التضارب بين الأطباء</p>
            </div>
            <div>
                <a href="index.php" class="btn btn-outline-primary btn-sm fw-bold me-1">
                    <i class="fa-solid fa-arrow-right"></i> الرئيسية
                </a>
                <a href="logout.php" class="btn btn-outline-danger btn-sm fw-bold">
                    <i class="fa-solid fa-power-off"></i> خروج
                </a>
            </div>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= $status; ?> text-center mb-4 fw-bold" role="alert">
                <?= $message; ?>
            </div>
        <?php endif; ?>

        <!-- نموذج تسجيل مقابلة جديدة -->
        <form method="POST" class="mb-5 p-4 border rounded bg-light">
            <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-plus-circle text-primary me-1"></i> حجز موعد مقابلة جديد</h5>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">اختر المريض:</label>
                    <select name="patient_id" class="form-select" required>
                        <option value="">اختر المريض...</option>
                        <?php foreach ($patients as $pat): ?>
                            <option value="<?= $pat['id']; ?>"><?= htmlspecialchars($pat['full_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">اختر الطبيب:</label>
                    <select name="doctor_id" class="form-select" required>
                        <option value="">اختر الطبيب المعالج...</option>
                        <?php foreach ($doctors as $doc): ?>
                            <option value="<?= $doc['id']; ?>">
                                <?= htmlspecialchars($doc['doctor_name']); ?> (<?= htmlspecialchars($doc['specialization']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">تاريخ ووقت المقابلة:</label>
                    <input type="datetime-local" name="appointment_date" class="form-control" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">ملاحظات إضافية:</label>
                <input type="text" name="notes" class="form-control" placeholder="أدخل أي ملاحظات حول المقابلة...">
            </div>

            <button type="submit" class="btn btn-primary fw-bold px-4">
                <i class="fa-solid fa-check me-1"></i> تأكيد وحجز المقابلة (منع التضارب)
            </button>
        </form>

        <!-- جدول عرض المقابلات المحجوزة مسبقاً -->
        <h4 class="fw-bold text-dark mb-3"><i class="fa-solid fa-list me-1"></i> المقابلات المحجوزة حالياً</h4>
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle text-center">
                <thead class="table-primary">
                    <tr>
                        <th>#</th>
                        <th>اسم المريض</th>
                        <th>الطبيب المعالج</th>
                        <th>التخصص</th>
                        <th>ميعاد المقابلة</th>
                        <th>ملاحظات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($appointments_list) > 0): ?>
                        <?php foreach ($appointments_list as $index => $app): ?>
                            <tr>
                                <td class="fw-bold"><?= $index + 1; ?></td>
                                <td class="fw-bold text-dark"><?= htmlspecialchars($app['patient_name']); ?></td>
                                <td class="text-success fw-bold"><?= htmlspecialchars($app['doctor_name']); ?></td>
                                <td><?= htmlspecialchars($app['specialization']); ?></td>
                                <td dir="ltr" class="fw-bold text-danger"><?= htmlspecialchars($app['appointment_date']); ?></td>
                                <td><?= htmlspecialchars($app['notes'] ?: 'لا توجد'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-3 text-muted">لا توجد مقابلات محجوزة حتى الآن.</td>
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