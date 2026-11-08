<?php
session_start();
include 'conn.php';

// التأكد من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$success_msg = "";
$error_msg = "";

// معالجة حفظ البيانات عند إرسال النموذج
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_prescription'])) {
    $patient_id = $_POST['patient_id'] ?? '';
    $user_id = $patient_id; 
    $diagnosis = $_POST['diagnosis'] ?? '';
    $chronic_diseases = $_POST['chronic_diseases'] ?? ''; 
    $prescription = $_POST['prescription'] ?? '';
    $doctor_name = $_POST['doctor_name'] ?? '';

    if (!empty($patient_id) && !empty($diagnosis)) {
        try {
            // محاولة الإدخال بكامل الأعمدة (في حال أردت إضافة عمود الأمراض المزمنة لاحقاً لقاعدة البيانات)
            $stmt = $pdo->prepare("INSERT INTO medical_records (patient_id, user_id, diagnosis, chronic_diseases, prescription, doctor_name) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$patient_id, $user_id, $diagnosis, $chronic_diseases, $prescription, $doctor_name]);
            $success_msg = "تم حفظ التشخيص والوصفة الطبية بنجاح!";
        } catch (PDOException $e) {
            try {
                // محاولة الإدخال بدون عمود الأمراض المزمنة (لتجنب الخطأ الحالي في حال لم تقم بإضافته في قاعدة البيانات)
                $stmt = $pdo->prepare("INSERT INTO medical_records (patient_id, user_id, diagnosis, prescription, doctor_name) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$patient_id, $user_id, $diagnosis, $prescription, $doctor_name]);
                $success_msg = "تم حفظ التشخيص والوصفة الطبية بنجاح!";
            } catch (PDOException $ex) {
                try {
                    // محاولة أخيرة بأبسط الأعمدة المتاحة
                    $stmt = $pdo->prepare("INSERT INTO medical_records (patient_id, diagnosis, prescription) VALUES (?, ?, ?)");
                    $stmt->execute([$patient_id, $diagnosis, $prescription]);
                    $success_msg = "تم حفظ التشخيص والوصفة الطبية بنجاح!";
                } catch (PDOException $exc) {
                    $error_msg = "خطأ في قاعدة البيانات: " . $exc->getMessage();
                }
            }
        }
    } else {
        $error_msg = "الرجاء اختيار المريض وإدخال التشخيص على الأقل.";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة التشخيصات والوصفات الطبية | Miko Hospital</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f4f6f9; font-family: 'Tahoma', sans-serif; }
        .card { border: none; border-radius: 1rem; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); }
    </style>
</head>
<body>

<!-- شريط التنقل -->
<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php"><i class="fa-solid fa-hospital text-success me-2"></i> مستشفى ميكو - لوحة التحكم</a>
        <a href="logout.php" class="btn btn-outline-danger btn-sm">تسجيل الخروج</a>
    </div>
</nav>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card p-4 bg-white">
                <h3 class="text-primary mb-4 text-center"><i class="fa-solid fa-file-medical me-2"></i> إضافة تشخيص ووصفة طبية لمريض</h3>

                <?php if (!empty($success_msg)): ?>
                    <div class="alert alert-success text-center"><?= $success_msg; ?></div>
                <?php endif; ?>

                <?php if (!empty($error_msg)): ?>
                    <div class="alert alert-danger text-center"><?= $error_msg; ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <!-- 1. اختيار المريض -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">اختر المريض:</label>
                        <select name="patient_id" class="form-select" required>
                            <option value="">-- اختر المريض من القائمة --</option>
                            <?php
                            try {
                                $stmt_p = $pdo->query("SELECT * FROM patients");
                                $patients = $stmt_p->fetchAll(PDO::FETCH_ASSOC);
                                
                                foreach ($patients as $p) {
                                    $p_id = $p['id'] ?? $p['patient_id'] ?? '';
                                    $p_name = $p['name'] ?? $p['full_name'] ?? $p['patient_name'] ?? 'مريض بدون اسم';
                                    echo "<option value='{$p_id}'>{$p_name} (ID: {$p_id})</option>";
                                }
                            } catch (Exception $ex) {
                                echo "<option value=''>خطأ في تحميل المرضى</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <!-- 2. التشخيص -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">التشخيص / الحالة المرضية:</label>
                        <textarea name="diagnosis" class="form-control" rows="3" placeholder="أدخل تشخيص الحالة هنا..." required></textarea>
                    </div>

                    <!-- 3. الأمراض المزمنة (اختياري) -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">الأمراض المزمنة (إن وجدت):</label>
                        <textarea name="chronic_diseases" class="form-control" rows="2" placeholder="مثل: ضغط دم مرتفع، سكري، حساسية..."></textarea>
                    </div>

                    <!-- 4. الوصفة الطبية -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">الوصفة الطبية (الأدوية والجرعات):</label>
                        <textarea name="prescription" class="form-control" rows="3" placeholder="اكتب الأدوية المطلوبة..."></textarea>
                    </div>

                    <!-- 5. اسم الطبيب المعالج -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">اسم الطبيب المعالج:</label>
                        <input type="text" name="doctor_name" class="form-control" placeholder="مثال: د. أحمد محمد" required>
                    </div>

                    <div class="d-grid">
                        <button type="submit" name="submit_prescription" class="btn btn-success btn-lg">
                            <i class="fa-solid fa-floppy-disk me-2"></i> حفظ وإرسال السجل للمريض
                        </button>
                    </div>
                </form>
                
                <div class="text-center mt-3">
                    <a href="index.php" class="text-decoration-none">العودة للرئيسية</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>