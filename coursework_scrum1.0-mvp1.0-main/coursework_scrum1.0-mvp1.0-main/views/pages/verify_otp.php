<?php
// filepath: coursework_scrum1.0/views/pages/verify_otp.php
// View để nhập mã OTP xác thực email

$email = $_GET['email'] ?? '';
$returnTo = $_GET['return_to'] ?? 'index.php?action=browse_cars';
$error = '';
if (isset($_SESSION['otp_error'])) {
    $error = $_SESSION['otp_error'];
    unset($_SESSION['otp_error']);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Verify Email OTP - Born Car</title>
    <link rel="stylesheet" href="css/style.css">

</head>

<body>

    <?php require_once __DIR__ . '/../layouts/header.php'; ?>

    <div class="otp-container">
        <h2>Verify Your Email</h2>
        <p>We have sent a 6-digit verification code to <strong><?= htmlspecialchars($email) ?></strong>. Please enter the code below to activate your account.</p>

        <!-- HIỂN THỊ MOCK OTP (CHỈ DÀNH CHO LOCALHOST/TESTING) -->
        <?php if (isset($_SESSION['mock_email_otp'])): ?>
            <div class="mock-otp">
                <?= $_SESSION['mock_email_otp'] ?>
            </div>
            <!-- Không unset ngay để user refresh vẫn thấy nếu chưa nhập -->
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form action="index.php?action=verify_otp_submit" method="POST">
            <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
            <input type="hidden" name="return_to" value="<?= htmlspecialchars($returnTo) ?>">

            <div class="form-group">
                <label for="otp">Enter 6-Digit Code</label>
                <input type="text" id="otp" name="otp" placeholder="000000" maxlength="6" required pattern="[0-9]{6}" title="Please enter exactly 6 digits">
            </div>

            <button type="submit">Verify Account</button>
        </form>

        <a href="<?= htmlspecialchars($returnTo) ?>" class="back-link" onclick="if (window.history.length > 1) { event.preventDefault(); window.history.back(); }">&larr; Back</a>
    </div>

</body>

</html>