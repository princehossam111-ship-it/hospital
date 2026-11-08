<?php
session_start();

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'conn.php';

$success_msg = "";
$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_patient'])) {
    $full_name = trim($_POST['full_name'] ?? '');
    $user_id = $_POST['user_id'] ?? null; // معرف المستخدم المرتبط بالمريض
    $age = trim($_POST['age'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $doctor_id = $_POST['doctor_id'] ?? null;
    $transaction_number = trim($_POST['transaction_number'] ?? '');
    $payment_method = trim($_POST['payment_method'] ?? '');

    if (!empty($full_name) && !empty($phone)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO patients (full_name, user_id, age, phone, address, gender, doctor_id, transaction_number, payment_method) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$full_name, $user_id, $age, $phone, $address, $gender, $doctor_id, $transaction_number, $payment_method]);
            
            $success_msg = "تم إضافة المريض وربطه بحساب المستخدم بنجاح!";
        } catch (PDOException $e) {
            $error_msg = "خطأ في قاعدة البيانات: " . $e->getMessage();
        }
    } else {
        $error_msg = "الرجاء إدخال اسم المريض ورقم الهاتف على الأقل.";
    }
}

// جلب قائمة الأطباء
try {
    $doctors = $pdo->query("SELECT * FROM doctors")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $doctors = [];
}

// جلب قائمة المستخدمين (Users) ليتم ربط المريض بحساب المستخدم
try {
    $users = $pdo->query("SELECT id, username FROM users")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $users = [];
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة مريض جديد وربطه بالمستخدم | EMR</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f8f9fa; font-family: 'Tahoma', sans-serif; padding: 30px; }
        .form-container { background: #ffffff; border-radius: 1rem; box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15); padding: 30px; max-width: 600px; margin: auto; }
    </style>
</head>
<body>

<div class="container">
    <div class="form-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="text-primary fw-bold mb-0">
                <i class="fa-solid fa-user-plus me-2"></i> إضافة مريض جديد
            </h3>
            <a href="logout.php" class="btn btn-outline-danger btn-sm">تسجيل الخروج</a>
        </div>

        <?php if (!empty($success_msg)): ?>
            <div class="alert alert-success text-center"><?= $success_msg; ?></div>
        <?php endif; ?>

        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger text-center"><?= $error_msg; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label fw-bold">اسم المريض الكامل:</label>
                <input type="text" name="full_name" class="form-control" placeholder="أدخل اسم المريض" required>
            </div>

            <!-- اختيار اسم المستخدم المرتبط -->
            <div class="mb-3">
                <label class="form-label fw-bold">حساب المستخدم المرتبط (User):</label>
                <select name="user_id" class="form-select" required>
                    <option value="">-- اختر حساب المستخدم --</option>
                    <?php foreach ($users as $usr): ?>
                        <option value="<?= $usr['id']; ?>"><?= htmlspecialchars($usr['username']); ?> (ID: <?= $usr['id']; ?>)</option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text">هذا الحساب سيمكن المريض من رؤية تشخيصاته في صفحته الخاصة لاحقاً.</div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">العمر:</label>
                    <input type="number" name="age" class="form-control" placeholder="أدخل العمر" min="0" max="120">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">الجنس:</label>
                    <select name="gender" class="form-select">
                        <option value="">-- اختر الجنس --</option>
                        <option value="ذكر">ذكر</option>
                        <option value="أنثى">أنثى</option>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">رقم الهاتف:</label>
                <input type="text" name="phone" class="form-control" placeholder="أدخل رقم الهاتف" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">العنوان:</label>
                <input type="text" name="address" class="form-control" placeholder="أدخل العنوان">
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">الطبيب المعالج:</label>
                <select name="doctor_id" class="form-select">
                    <option value="">-- اختر الطبيب --</option>
                    <?php foreach ($doctors as $doc): ?>
                        <option value="<?= $doc['id']; ?>">د. <?= htmlspecialchars($doc['doctor_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">رقم العملية:</label>
                    <input type="text" name="transaction_number" class="form-control" placeholder="رقم العملية أو الإيصال">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">طريقة الدفع:</label>
                    <select name="payment_method" class="form-select">
                        <option value="نقدي">بنك الخرطوم</option>
						                        <option value="نقدي">بنك النيل</option>
						                        <option value="نقدي">بنك الراجحي</option>

                        <option value="بطاقة بنكية">بنك فيصل الاسلامي</option>
                        <option value="تأمين طبي">تأمين طبي</option>
                    </select>
                </div>
            </div>

            <div class="d-grid gap-2 mt-4">
                <button type="submit" name="add_patient" class="btn btn-success btn-lg fw-bold">
                    <i class="fa-solid fa-floppy-disk me-1"></i> حفظ بيانات المريض
                </button>
                <a href="index.php" class="btn btn-outline-secondary">العودة لقائمة المرضى</a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>