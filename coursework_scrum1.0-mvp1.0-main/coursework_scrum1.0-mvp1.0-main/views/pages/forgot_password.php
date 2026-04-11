<?php
// filepath: views/pages/forgot_password.php
$forgotErrors = $forgotErrors ?? [];
$forgotSuccess = $forgotSuccess ?? '';
$forgotOldEmail = $forgotOldEmail ?? '';
$sendResetOtpToken = $sendResetOtpToken ?? '';
$verifyResetOtpToken = $verifyResetOtpToken ?? '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Born Car</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php require_once __DIR__ . '/../layouts/header.php'; ?>

    <main class="forgot-page-main">
        <section class="forgot-password-card" aria-labelledby="forgotPasswordTitle">
            <header class="forgot-password-header">
                <h1 id="forgotPasswordTitle">Forgot Password</h1>
                <p>Request an OTP code and reset your account password securely.</p>
            </header>

            <?php if (!empty($forgotSuccess)): ?>
                <div class="forgot-password-alert forgot-password-alert-success">
                    <?= htmlspecialchars($forgotSuccess) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($forgotErrors)): ?>
                <div class="forgot-password-alert forgot-password-alert-danger">
                    <ul class="forgot-password-errors">
                        <?php foreach ($forgotErrors as $error): ?>
                            <li><?= htmlspecialchars((string) $error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['mock_email_otp'])): ?>
                <div class="forgot-password-alert forgot-password-alert-success">
                    <?= htmlspecialchars((string) $_SESSION['mock_email_otp']) ?>
                </div>
            <?php endif; ?>

            <div class="forgot-password-body">
                <div class="forgot-password-panel">
                    <h2>1. Send OTP</h2>
                    <p>Enter your email address to receive a 6-digit OTP. The code is valid for 5 minutes.</p>
                    <form action="index.php?action=send_reset_otp" method="POST" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($sendResetOtpToken) ?>">

                        <div class="forgot-password-field">
                            <label for="reset_email">Email Address</label>
                            <input
                                id="reset_email"
                                type="email"
                                name="email"
                                maxlength="150"
                                value="<?= htmlspecialchars($forgotOldEmail) ?>"
                                required>
                        </div>

                        <button type="submit" class="forgot-password-btn">Send OTP</button>
                    </form>
                </div>

                <div class="forgot-password-panel">
                    <h2>2. Verify OTP & Reset</h2>
                    <p>Enter your OTP and set a new password. Maximum 5 invalid attempts are allowed.</p>
                    <form action="index.php?action=verify_reset_otp" method="POST" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($verifyResetOtpToken) ?>">

                        <div class="forgot-password-field">
                            <label for="verify_email">Email Address</label>
                            <input
                                id="verify_email"
                                type="email"
                                name="email"
                                maxlength="150"
                                value="<?= htmlspecialchars($forgotOldEmail) ?>"
                                required>
                        </div>

                        <div class="forgot-password-field">
                            <label for="otp">OTP Code</label>
                            <input
                                id="otp"
                                type="text"
                                name="otp"
                                inputmode="numeric"
                                maxlength="6"
                                pattern="[0-9]{6}"
                                placeholder="Enter 6-digit OTP"
                                required>
                        </div>

                        <div class="forgot-password-field">
                            <label for="new_password">New Password</label>
                            <input
                                id="new_password"
                                type="password"
                                name="new_password"
                                minlength="8"
                                required>
                        </div>

                        <div class="forgot-password-field">
                            <label for="confirm_password">Confirm New Password</label>
                            <input
                                id="confirm_password"
                                type="password"
                                name="confirm_password"
                                minlength="8"
                                required>
                        </div>

                        <button type="submit" class="forgot-password-btn">Verify OTP & Reset Password</button>
                    </form>
                </div>
            </div>

            <div class="forgot-password-footer">
                <a href="index.php?action=login_form" class="forgot-password-login-link">Back to Login</a>
            </div>
        </section>
    </main>

    <?php include __DIR__ . '/../layouts/footer.php'; ?>
</body>

</html>