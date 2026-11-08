<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'conn.php';

$msg = "";
$error = "";

// 1. معالجة إضافة طبيب جديد
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_doctor'])) {
    $doctor_name = trim($_POST['doctor_name'] ?? '');
    $specialization = trim($_POST['specialization'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if (!empty($doctor_name) && !empty($specialization)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO doctors (doctor_name, specialization, phone) VALUES (?, ?, ?)");
            $stmt->execute([$doctor_name, $specialization, $phone]);
            $msg = "تمت إضافة الطبيب بنجاح!";
        } catch (PDOException $e) {
            $error = "خطأ في قاعدة البيانات (إضافة طبيب): " . $e->getMessage();
        }
    } else {
        $error = "الرجاء إدخال اسم الطبيب والتخصص على الأقل.";
    }
}

// 2. معالجة حجز موعد جديد لمريض عند طبيب مع تحديد الزمن
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_appointment'])) {
    $doctor_id = intval($_POST['doctor_id'] ?? 0);
    $patient_id = intval($_POST['patient_id'] ?? 0);
    $appointment_date = trim($_POST['appointment_date'] ?? ''); // الزمن والتاريخ

    if ($doctor_id > 0 && $patient_id > 0 && !empty($appointment_date)) {
        try {
            // التأكد من وجود جدول appointments أو reservations
            $stmt = $pdo->prepare("INSERT INTO appointments (doctor_id, patient_id, appointment_date, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$doctor_id, $patient_id, $appointment_date]);
            $msg = "تم حجز الموعد وتسجيل المريض للطبيب بنجاح!";
        } catch (PDOException $e) {
            try {
                // محاولة حفظ في جدول reservations إذا كان هو المستخدم لديك
                $stmt = $pdo->prepare("INSERT INTO reservations (doctor_id, patient_id, appointment_date) VALUES (?, ?, ?)");
                $stmt->execute([$doctor_id, $patient_id, $appointment_date]);
                $msg = "تم حجز الموعد بنجاح!";
            } catch (PDOException $ex) {
                $error = "خطأ في حجز الموعد: تأكد من وجود جدول الحجوزات في قاعدة البيانات.";
            }
        }
    } else {
        $error = "الرجاء اختيار الطبيب، المريض، وتحديد زمن الحجز.";
    }
}

// جلب قائمة الأطباء
$doctors = [];
try {
    $stmt_doc = $pdo->query("SELECT * FROM doctors ORDER BY doctor_name ASC");
    $doctors = $stmt_doc->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $doctors = [];
}

// جلب قائمة المرضى للقائمة المنسدلة للجز
$patients = [];
try {
    $stmt_pat = $pdo->query("SELECT * FROM patients ORDER BY id DESC");
    $patients = $stmt_pat->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    try {
        $stmt_pat = $pdo->query("SELECT * FROM patient ORDER BY id DESC");
        $patients = $stmt_pat->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $ex) {
        $patients = [];
    }
}

// جلب الحجوزات والمرضى المرتبطين بالطبيب المختار لعرضهم
$selected_doctor_id = isset($_GET['view_doctor_id']) ? intval($_GET['view_doctor_id']) : 0;
$selected_doctor_name = "";
$doctor_appointments = [];

if ($selected_doctor_id > 0) {
    // جلب اسم الطبيب
    foreach ($doctors as $d) {
        if ($d['id'] == $selected_doctor_id) {
            $selected_doctor_name = $d['doctor_name'];
            break;
        }
    }

    // جلب المرضى الحاجزين لهذا الطبيب مع الزمن
    try {
        $stmt_app = $pdo->prepare("
            SELECT a.*, 
                   COALESCE(p.full_name, p.name, p.patient_name, 'مريض') as patient_name, 
                   COALESCE(p.phone, '-') as patient_phone 
            FROM appointments a 
            LEFT JOIN patients p ON a.patient_id = p.id 
            WHERE a.doctor_id = ? 
            ORDER BY a.id DESC
        ");
        $stmt_app->execute([$selected_doctor_id]);
        $doctor_appointments = $stmt_app->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        try {
            // محاولة جلب من جدول reservations إذا وُجد
            $stmt_app = $pdo->prepare("
                SELECT r.*, 
                       COALESCE(p.full_name, p.name, p.patient_name, 'مريض') as patient_name, 
                       COALESCE(p.phone, '-') as patient_phone 
                FROM reservations r 
                LEFT JOIN patients p ON r.patient_id = p.id 
                WHERE r.doctor_id = ? 
                ORDER BY r.id DESC
            ");
            $stmt_app->execute([$selected_doctor_id]);
            $doctor_appointments = $stmt_app->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            $doctor_appointments = [];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الأطباء وحجوزات المرضى | Miko Hospital</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f8f9fa; font-family: 'Tahoma', sans-serif; padding: 20px; }
        .main-container { background: #ffffff; border-radius: 1rem; box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15); padding: 25px; margin-bottom: 30px; }
    </style>
</head>
<body>

<div class="container">
    <div class="main-container">

        <!-- رأس الصفحة -->
        <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
            <h3 class="text-primary fw-bold mb-0">
                <i class="fa-solid fa-user-doctor me-2"></i> إدارة الأطباء وحجوزات المرضى
            </h3>
            <a href="index.php" class="btn btn-outline-secondary fw-bold">
                <i class="fa-solid fa-arrow-right me-1"></i> العودة للرئيسية
            </a>
        </div>

        <!-- رسائل النجاح والخطأ -->
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

        <div class="row">
            <!-- نموذج تسجيل طبيب جديد -->
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm border-0 bg-light h-100">
                    <div class="card-body p-3">
                        <h5 class="card-title fw-bold text-dark mb-3">
                            <i class="fa-solid fa-user-plus text-success me-1"></i> تسجيل طبيب جديد
                        </h5>
                        <form method="POST" action="">
                            <div class="mb-2">
                                <label class="form-label fw-bold">اسم الطبيب</label>
                                <input type="text" name="doctor_name" class="form-control" placeholder="مثال: د. أحمد" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label fw-bold">التخصص</label>
                                <input type="text" name="specialization" class="form-control" placeholder="مثال: باطنة" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">رقم الهاتف</label>
                                <input type="text" name="phone" class="form-control" placeholder="09xxxxxxxx">
                            </div>
                            <button type="submit" name="add_doctor" class="btn btn-success w-100 fw-bold">
                                <i class="fa-solid fa-floppy-disk me-1"></i> حفظ الطبيب
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- نموذج حجز موعد لمريض عند طبيب -->
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm border-0 bg-light h-100">
                    <div class="card-body p-3">
                        <h5 class="card-title fw-bold text-dark mb-3">
                            <i class="fa-solid fa-calendar-check text-primary me-1"></i> حجز موعد لمريض عند طبيب
                        </h5>
                        <form method="POST" action="">
                            <div class="mb-2">
                                <label class="form-label fw-bold">اختر الطبيب</label>
                                <select name="doctor_id" class="form-select" required>
                                    <option value="">-- اختر الطبيب --</option>
                                    <?php foreach ($doctors as $doc): ?>
                                        <option value="<?= $doc['id']; ?>"><?= htmlspecialchars($doc['doctor_name']); ?> (<?= htmlspecialchars($doc['specialization']); ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label fw-bold">اختر المريض</label>
                                <select name="patient_id" class="form-select" required>
                                    <option value="">-- اختر المريض --</option>
                                    <?php foreach ($patients as $pat): ?>
                                        <?php 
                                            $p_id = $pat['id'] ?? $pat['patient_id'] ?? 0;
                                            $p_name = $pat['name'] ?? $pat['full_name'] ?? $pat['patient_name'] ?? 'مريض';
                                        ?>
                                        <option value="<?= $p_id; ?>"><?= htmlspecialchars($p_name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">تاريخ وزمن الحجز</label>
                                <input type="datetime-local" name="appointment_date" class="form-control" required>
                            </div>
                            <button type="submit" name="add_appointment" class="btn btn-primary w-100 fw-bold">
                                <i class="fa-solid fa-check me-1"></i> تأكيد الحجز
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- جدول عرض الأطباء -->
        <h5 class="fw-bold text-secondary mb-3 mt-4">قائمة الأطباء (اضغط على زر "عرض المرضى والحجوزات" لمعرفة من حجز ومتى)</h5>
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle text-center">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>اسم الطبيب</th>
                        <th>التخصص</th>
                        <th>رقم الهاتف</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($doctors) > 0): ?>
                        <?php $i = 1; foreach ($doctors as $doc): ?>
                            <tr>
                                <th scope="row"><?= $i++; ?></th>
                                <td class="fw-bold text-start"><?= htmlspecialchars($doc['doctor_name']); ?></td>
                                <td><span class="badge bg-info text-dark"><?= htmlspecialchars($doc['specialization']); ?></span></td>
                                <td class="font-monospace"><?= !empty($doc['phone']) ? htmlspecialchars($doc['phone']) : '-'; ?></td>
                                <td>
                                    <a href="?view_doctor_id=<?= $doc['id']; ?>#patient-details" class="btn btn-sm btn-primary fw-bold">
                                        <i class="fa-solid fa-users me-1"></i> عرض المرضى والحجوزات والزمن
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-muted py-4">لا توجد بيانات للأطباء مسجلة حتى الآن.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

    <!-- قسم عرض المرضى الحاجزين للطبيب المحدد والزمن -->
    <?php if ($selected_doctor_id > 0): ?>
        <div id="patient-details" class="main-container border border-primary">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="text-primary fw-bold mb-0">
                    <i class="fa-solid fa-clipboard-user me-2"></i> المرضى الحاجزون عند الطبيب: <span class="text-dark"><?= htmlspecialchars($selected_doctor_name); ?></span>
                </h4>
                <a href="?" class="btn btn-outline-danger btn-sm">إخفاء النتائج</a>
            </div>

            <?php if (count($doctor_appointments) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle text-center">
                        <thead class="table-success text-dark">
                            <tr>
                                <th>#</th>
                                <th>اسم المريض</th>
                                <th>رقم هاتف المريض</th>
                                <th>تاريخ وزمن الحجز</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($doctor_appointments as $index => $app): ?>
                                <tr>
                                    <td><?= $index + 1; ?></td>
                                    <td class="fw-bold text-start"><?= htmlspecialchars($app['patient_name']); ?></td>
                                    <td><?= htmlspecialchars($app['patient_phone']); ?></td>
                                    <td class="text-danger fw-bold">
                                        <i class="fa-regular fa-clock me-1"></i> 
                                        <?= htmlspecialchars($app['appointment_date'] ?? $app['created_at'] ?? 'غير متوفر'); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-warning text-center py-4 mb-0">
                    <i class="fa-solid fa-circle-info fa-2x mb-2"></i>
                    <h5>لا توجد أي حجوزات مسجلة لهذا الطبيب حتى الآن.</h5>
                    <p class="small text-muted mt-1 mb-0">يمكنك إضافة حجز جديد باستخدام نموذج "حجز موعد لمريض عند طبيب" بالأعلى.</p>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>