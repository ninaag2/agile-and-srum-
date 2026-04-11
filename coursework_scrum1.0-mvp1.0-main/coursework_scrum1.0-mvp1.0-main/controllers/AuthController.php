<?php
// filepath: coursework_scrum1.0/controllers/AuthController.php

// Khai báo thư viện PHPMailer thủ công
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Nhúng trực tiếp các file từ thư mục PHPMailer
require_once __DIR__ . '/../PHPMailer/Exception.php';
require_once __DIR__ . '/../PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/SMTP.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../datafunctions/auth_functions.php';

// Xử lý POST request cho action 'register_submit'
$action = $_GET['action'] ?? '';

const RESET_OTP_TTL_SECONDS = 300;
const RESET_OTP_MAX_ATTEMPTS = 5;
const RESET_OTP_COOLDOWN_SECONDS = 0;
const RESET_OTP_RATE_LIMIT_PER_HOUR = 5;
const RESET_PASSWORD_AFTER_OTP_SECONDS = 900;
const REGISTER_OTP_TTL_SECONDS = 300;
const REGISTER_OTP_MAX_ATTEMPTS = 5;

function authGetClientIp(): string
{
    $forwarded = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
    if (!empty($forwarded)) {
        $parts = explode(',', $forwarded);
        return trim($parts[0]);
    }

    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function authGetCsrfToken(string $key): string
{
    if (empty($_SESSION['csrf_tokens'][$key])) {
        $_SESSION['csrf_tokens'][$key] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_tokens'][$key];
}

function authValidateCsrfToken(string $key, ?string $token): bool
{
    if (empty($token) || empty($_SESSION['csrf_tokens'][$key])) {
        return false;
    }

    return hash_equals($_SESSION['csrf_tokens'][$key], $token);
}

function authCheckResetOtpThrottle(string $email, string $ip): array
{
    $now = time();
    $emailKey = hash('sha256', strtolower($email));

    $_SESSION['reset_otp_cooldown_until'] = $_SESSION['reset_otp_cooldown_until'] ?? [];
    $_SESSION['reset_otp_rate_by_email'] = $_SESSION['reset_otp_rate_by_email'] ?? [];
    $_SESSION['reset_otp_rate_by_ip'] = $_SESSION['reset_otp_rate_by_ip'] ?? [];

    $cooldownUntil = (int) ($_SESSION['reset_otp_cooldown_until'][$emailKey] ?? 0);
    if ($cooldownUntil > $now) {
        return [
            'allowed' => false,
            'message' => 'Please wait before requesting another OTP.'
        ];
    }

    $emailRequests = array_filter(
        $_SESSION['reset_otp_rate_by_email'][$emailKey] ?? [],
        static fn($timestamp): bool => ((int) $timestamp) > ($now - 3600)
    );
    $ipRequests = array_filter(
        $_SESSION['reset_otp_rate_by_ip'][$ip] ?? [],
        static fn($timestamp): bool => ((int) $timestamp) > ($now - 3600)
    );

    $_SESSION['reset_otp_rate_by_email'][$emailKey] = array_values($emailRequests);
    $_SESSION['reset_otp_rate_by_ip'][$ip] = array_values($ipRequests);

    if (count($_SESSION['reset_otp_rate_by_email'][$emailKey]) >= RESET_OTP_RATE_LIMIT_PER_HOUR || count($_SESSION['reset_otp_rate_by_ip'][$ip]) >= RESET_OTP_RATE_LIMIT_PER_HOUR) {
        return [
            'allowed' => false,
            'message' => 'Too many OTP requests. Please try again later.'
        ];
    }

    $_SESSION['reset_otp_rate_by_email'][$emailKey][] = $now;
    $_SESSION['reset_otp_rate_by_ip'][$ip][] = $now;
    $_SESSION['reset_otp_cooldown_until'][$emailKey] = $now + RESET_OTP_COOLDOWN_SECONDS;

    return ['allowed' => true, 'message' => ''];
}

function authResolveReturnToUrl(): string
{
    $defaultUrl = 'index.php?action=browse_cars';
    $returnTo = trim((string) ($_POST['return_to'] ?? ($_GET['return_to'] ?? '')));

    if ($returnTo === '') {
        $referer = trim((string) ($_SERVER['HTTP_REFERER'] ?? ''));
        if ($referer !== '') {
            $refererPath = parse_url($referer, PHP_URL_PATH);
            $refererQuery = parse_url($referer, PHP_URL_QUERY);
            if (!empty($refererPath) && strpos($refererPath, 'index.php') !== false) {
                $returnTo = 'index.php';
                if (!empty($refererQuery)) {
                    $returnTo .= '?' . $refererQuery;
                }
            }
        }
    }

    if ($returnTo === '' || preg_match('/^https?:\/\//i', $returnTo) || strpos($returnTo, '//') === 0) {
        return $defaultUrl;
    }

    if (strpos($returnTo, 'index.php') !== 0) {
        $indexPos = strpos($returnTo, 'index.php');
        if ($indexPos === false) {
            return $defaultUrl;
        }
        $returnTo = substr($returnTo, $indexPos);
    }

    return $returnTo;
}

function authBuildRedirectUrl(string $baseUrl, array $extraParams = []): string
{
    $parsed = parse_url($baseUrl);
    $path = $parsed['path'] ?? 'index.php';

    parse_str($parsed['query'] ?? '', $queryParams);
    unset($queryParams['auth_modal'], $queryParams['forgot_step'], $queryParams['auth_success']);

    foreach ($extraParams as $key => $value) {
        if ($value === null || $value === '') {
            continue;
        }
        $queryParams[$key] = $value;
    }

    $queryString = http_build_query($queryParams);
    return $path . (!empty($queryString) ? ('?' . $queryString) : '');
}

function authSetForgotVerifiedSession(int $userId, string $email): void
{
    $_SESSION['forgot_reset_verified'] = [
        'user_id' => $userId,
        'email' => strtolower($email),
        'expires_at' => time() + RESET_PASSWORD_AFTER_OTP_SECONDS
    ];
}

function authGetForgotVerifiedSession(): ?array
{
    $verified = $_SESSION['forgot_reset_verified'] ?? null;
    if (empty($verified) || empty($verified['user_id']) || empty($verified['email']) || empty($verified['expires_at'])) {
        return null;
    }

    if ((int) $verified['expires_at'] < time()) {
        unset($_SESSION['forgot_reset_verified']);
        return null;
    }

    return $verified;
}

function sendPasswordResetOtpEmail(string $email, string $fullname, string $otp): bool
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'tan0979876976@gmail.com';
        $mail->Password   = 'cuigwyrdskymibyy';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];

        $mail->setFrom('tan0979876976@gmail.com', 'Car Booking System');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset OTP - Born Car';
        $mail->Body = '<h2>Hello ' . htmlspecialchars($fullname) . ',</h2>' .
            '<p>Your password reset OTP is:</p>' .
            '<p><b style="color:#dc3545;font-size:24px;">' . htmlspecialchars($otp) . '</b></p>' .
            '<p>This OTP is valid for 5 minutes.</p>';

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('sendPasswordResetOtpEmail Error: ' . $mail->ErrorInfo);
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'register_submit') {
    $returnToUrl = authResolveReturnToUrl();
    $csrfToken = $_POST['csrf_token'] ?? '';
    $submitIntent = $_POST['register_submit_intent'] ?? '';

    if ($submitIntent !== '1' || !authValidateCsrfToken('register_submit', $csrfToken)) {
        $_SESSION['register_errors'] = ['Invalid register request. Please try again.'];
        $_SESSION['register_old_data'] = $_POST;
        header('Location: ' . authBuildRedirectUrl($returnToUrl, ['auth_modal' => 'register']));
        exit;
    }

    $fullname = trim($_POST['fullname'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone    = trim($_POST['phone'] ?? '');

    // Validation cơ bản để bắt lỗi input rỗng
    $errors = [];
    if (empty($fullname)) $errors[] = "Full Name is required.";
    if (empty($email)) $errors[] = "Email is required.";
    if (empty($password)) $errors[] = "Password is required.";
    if (!preg_match('/^\d{10}$/', $phone)) $errors[] = "Phone number is wrong.";

    // Nếu có lỗi validation input
    if (!empty($errors)) {
        $_SESSION['register_errors'] = $errors;
        $_SESSION['register_old_data'] = $_POST;
        header('Location: ' . authBuildRedirectUrl($returnToUrl, ['auth_modal' => 'register']));
        exit;
    }

    if ($fullname && $email && $password) {
        $db = getConnection();
        if (!isRegistrationIdentityAvailable($db, $email, $phone)) {
            $_SESSION['register_errors'] = ['Email or Phone number is already in use.'];
            $_SESSION['register_old_data'] = $_POST;
            header('Location: ' . authBuildRedirectUrl($returnToUrl, ['auth_modal' => 'register']));
            exit;
        }

        // 1. Tạo OTP 6 số ngẫu nhiên
        $otp = sprintf("%06d", random_int(0, 999999));

        // 2. Chỉ lưu tạm dữ liệu đăng ký vào session, chưa ghi vào database.
        $_SESSION['pending_registration'] = [
            'fullname' => $fullname,
            'email' => strtolower($email),
            'phone' => $phone,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'otp_code' => $otp,
            'attempts' => 0,
            'expires_at' => time() + REGISTER_OTP_TTL_SECONDS
        ];

        // 3. Khởi tạo PHPMailer gửi mail thật
        try {
            require_once __DIR__ . '/../PHPMailer/Exception.php';
            require_once __DIR__ . '/../PHPMailer/PHPMailer.php';
            require_once __DIR__ . '/../PHPMailer/SMTP.php';

            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'tan0979876976@gmail.com';
            $mail->Password   = 'cuigwyrdskymibyy';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';

            // THÊM ĐOẠN NÀY ĐỂ FIX LỖI XAMPP LOCALHOST
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            $mail->setFrom('tan0979876976@gmail.com', 'Car Booking System');
            $mail->addAddress($email); // Gửi tới email khách đăng ký
            $mail->isHTML(true);
            $mail->Subject = 'Xác thực tài khoản - Car Booking';
            $mail->Body    = "<h2>Chào $fullname,</h2><p>Mã OTP xác thực tài khoản của bạn là: <b style='color:red;font-size:24px;'>$otp</b></p>";

            $mail->send();
            $_SESSION['success_message'] = "Vui lòng kiểm tra Email để lấy mã OTP!";

            // Mail gửi thành công -> Xóa mock OTP nếu có
            unset($_SESSION['mock_email_otp']);
        } catch (Exception $e) {
            // Nếu cấu hình email sai, fallback hiện OTP ra màn hình
            $mailError = isset($mail) ? $mail->ErrorInfo : $e->getMessage();
            $_SESSION['mock_email_otp'] = "LỖI MAIL: " . $mailError . " - MÃ TEST LÀ: $otp";
        }

        // Rotate token sau lần submit thành công để giảm nguy cơ resubmit ngoài ý muốn.
        unset($_SESSION['csrf_tokens']['register_submit']);

        // 4. BẮT BUỘC CHUYỂN HƯỚNG SANG TRANG NHẬP OTP
        header('Location: ' . authBuildRedirectUrl($returnToUrl, ['auth_modal' => 'register_otp', 'verify_email' => strtolower($email)]));
        exit;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'verify_otp') {
    // Hiển thị form nhập OTP
    $email = $_GET['email'] ?? '';
    require_once __DIR__ . '/../views/pages/verify_otp.php'; // Lưu ý tôi dùng pages để đồng bộ
    exit;
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'verify_otp_submit') {
    // Xử lý xác thực OTP
    $returnToUrl = authResolveReturnToUrl();
    $email = $_POST['email'] ?? '';
    $otp_input = $_POST['otp'] ?? '';
    $pendingRegistration = $_SESSION['pending_registration'] ?? null;

    if (empty($email) || empty($otp_input)) {
        $_SESSION['otp_error'] = "Please enter the OTP sent to your email.";
        header('Location: ' . authBuildRedirectUrl($returnToUrl, ['auth_modal' => 'register_otp', 'verify_email' => strtolower($email)]));
        exit;
    }

    if (empty($pendingRegistration)) {
        $_SESSION['otp_error'] = "Registration session has expired. Please register again.";
        header('Location: ' . authBuildRedirectUrl($returnToUrl, ['auth_modal' => 'register']));
        exit;
    }

    if (strtolower($email) !== (string) ($pendingRegistration['email'] ?? '')) {
        $_SESSION['otp_error'] = "Invalid verification session for this email.";
        header('Location: ' . authBuildRedirectUrl($returnToUrl, ['auth_modal' => 'register_otp', 'verify_email' => strtolower($email)]));
        exit;
    }

    if ((int) ($pendingRegistration['expires_at'] ?? 0) < time()) {
        unset($_SESSION['pending_registration']);
        $_SESSION['otp_error'] = "OTP has expired. Please register again.";
        header('Location: ' . authBuildRedirectUrl($returnToUrl, ['auth_modal' => 'register']));
        exit;
    }

    $attempts = (int) ($pendingRegistration['attempts'] ?? 0);
    if ($attempts >= REGISTER_OTP_MAX_ATTEMPTS) {
        unset($_SESSION['pending_registration']);
        $_SESSION['otp_error'] = "Too many invalid OTP attempts. Please register again.";
        header('Location: ' . authBuildRedirectUrl($returnToUrl, ['auth_modal' => 'register']));
        exit;
    }

    if (!hash_equals((string) ($pendingRegistration['otp_code'] ?? ''), (string) $otp_input)) {
        $_SESSION['pending_registration']['attempts'] = $attempts + 1;
        $_SESSION['otp_error'] = "Invalid OTP code.";
        header('Location: ' . authBuildRedirectUrl($returnToUrl, ['auth_modal' => 'register_otp', 'verify_email' => strtolower($email)]));
        exit;
    }

    try {
        $db = getConnection();
        $createUserResult = createVerifiedUser(
            $db,
            (string) $pendingRegistration['fullname'],
            (string) $pendingRegistration['email'],
            (string) $pendingRegistration['phone'],
            (string) $pendingRegistration['password_hash']
        );

        if (!empty($createUserResult['success'])) {
            $_SESSION['login_success'] = (string) ($createUserResult['message'] ?? "Account verified successfully! You can login now.");
            // Xóa session mock otp
            unset($_SESSION['mock_email_otp']);
            unset($_SESSION['pending_registration']);
            header('Location: ' . authBuildRedirectUrl($returnToUrl, ['auth_modal' => 'login']));
            exit;
        } else {
            $_SESSION['otp_error'] = (string) ($createUserResult['message'] ?? "Unable to create account right now.");
            header('Location: ' . authBuildRedirectUrl($returnToUrl, ['auth_modal' => 'register_otp', 'verify_email' => strtolower($email)]));
            exit;
        }
    } catch (Exception $e) {
        $_SESSION['otp_error'] = "System error: " . $e->getMessage();
        header('Location: ' . authBuildRedirectUrl($returnToUrl, ['auth_modal' => 'register_otp', 'verify_email' => strtolower($email)]));
        exit;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'send_reset_otp') {
    $email = trim($_POST['email'] ?? '');
    $csrfToken = $_POST['csrf_token'] ?? '';
    $returnToUrl = authResolveReturnToUrl();

    $_SESSION['forgot_reset_old_email'] = $email;
    unset($_SESSION['forgot_reset_verified']);

    if (!authValidateCsrfToken('send_reset_otp', $csrfToken)) {
        $_SESSION['forgot_reset_errors'] = ['Invalid request token. Please refresh and try again.'];
        $_SESSION['forgot_reset_step'] = 'send';
        header('Location: ' . authBuildRedirectUrl($returnToUrl, ['auth_modal' => 'forgot', 'forgot_step' => 'send']));
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['forgot_reset_errors'] = ['Please enter a valid email address.'];
        $_SESSION['forgot_reset_step'] = 'send';
        header('Location: ' . authBuildRedirectUrl($returnToUrl, ['auth_modal' => 'forgot', 'forgot_step' => 'send']));
        exit;
    }

    $ip = authGetClientIp();
    $throttle = authCheckResetOtpThrottle($email, $ip);
    if (!$throttle['allowed']) {
        $_SESSION['forgot_reset_errors'] = [$throttle['message']];
        $_SESSION['forgot_reset_step'] = 'send';
        header('Location: ' . authBuildRedirectUrl($returnToUrl, ['auth_modal' => 'forgot', 'forgot_step' => 'send']));
        exit;
    }

    try {
        $db = getConnection();
        $user = getUserByEmail($db, $email);

        if (empty($user)) {
            $_SESSION['forgot_reset_errors'] = ['Email not found in our system.'];
            $_SESSION['forgot_reset_step'] = 'send';
            unset($_SESSION['mock_email_otp']);
            header('Location: ' . authBuildRedirectUrl($returnToUrl, ['auth_modal' => 'forgot', 'forgot_step' => 'send']));
            exit;
        }

        $otp = sprintf('%06d', random_int(0, 999999));

        clearOldOtpByUser((int) $user['id']);

        if (createResetOtp((int) $user['id'], $email, $otp, null, $ip)) {
            if (!sendPasswordResetOtpEmail($email, (string) $user['fullname'], $otp)) {
                $_SESSION['mock_email_otp'] = 'MAIL ERROR. TEST OTP: ' . $otp;
            } else {
                unset($_SESSION['mock_email_otp']);
            }
        }
    } catch (Exception $e) {
        error_log('send_reset_otp Error: ' . $e->getMessage());
    }

    $_SESSION['forgot_reset_success'] = 'OTP has been sent successfully. Please check your inbox.';
    $_SESSION['forgot_reset_step'] = 'otp';
    header('Location: ' . authBuildRedirectUrl($returnToUrl, ['auth_modal' => 'forgot', 'forgot_step' => 'otp']));
    exit;
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'verify_reset_otp_code') {
    $email = trim($_POST['email'] ?? '');
    $otpInputRaw = (string) ($_POST['otp'] ?? '');
    $otpInput = preg_replace('/\D+/', '', $otpInputRaw) ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';
    $returnToUrl = authResolveReturnToUrl();

    $_SESSION['forgot_reset_old_email'] = $email;
    $errors = [];

    if (!authValidateCsrfToken('verify_reset_otp_code', $csrfToken)) {
        $errors[] = 'Invalid request token. Please refresh and try again.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (!preg_match('/^\d{6}$/', $otpInput)) {
        $errors[] = 'OTP must be exactly 6 digits.';
    }

    if (!empty($errors)) {
        $_SESSION['forgot_reset_errors'] = $errors;
        $_SESSION['forgot_reset_step'] = 'otp';
        header('Location: ' . authBuildRedirectUrl($returnToUrl, ['auth_modal' => 'forgot', 'forgot_step' => 'otp']));
        exit;
    }

    try {
        $db = getConnection();
        $user = getUserByEmail($db, $email);
        if (empty($user)) {
            $_SESSION['forgot_reset_errors'] = ['Email not found in our system.'];
            $_SESSION['forgot_reset_step'] = 'otp';
            header('Location: ' . authBuildRedirectUrl($returnToUrl, ['auth_modal' => 'forgot', 'forgot_step' => 'otp']));
            exit;
        }

        $otpRecord = getLatestValidOtpByEmail($email);

        if (empty($otpRecord)) {
            $_SESSION['forgot_reset_errors'] = ['Invalid or expired OTP. Please request a new code.'];
            $_SESSION['forgot_reset_step'] = 'otp';
            header('Location: ' . authBuildRedirectUrl($returnToUrl, ['auth_modal' => 'forgot', 'forgot_step' => 'otp']));
            exit;
        }

        if ((int) $otpRecord['attempts'] >= RESET_OTP_MAX_ATTEMPTS) {
            $_SESSION['forgot_reset_errors'] = ['Too many invalid attempts. Please request a new OTP.'];
            $_SESSION['forgot_reset_step'] = 'otp';
            header('Location: ' . authBuildRedirectUrl($returnToUrl, ['auth_modal' => 'forgot', 'forgot_step' => 'otp']));
            exit;
        }

        if (!hash_equals((string) $otpRecord['otp_code'], $otpInput)) {
            incrementOtpAttempts((int) $otpRecord['id']);
            $_SESSION['forgot_reset_errors'] = ['Invalid OTP code. Please try again.'];
            $_SESSION['forgot_reset_step'] = 'otp';
            header('Location: ' . authBuildRedirectUrl($returnToUrl, ['auth_modal' => 'forgot', 'forgot_step' => 'otp']));
            exit;
        }

        if (!markOtpUsed((int) $otpRecord['id'])) {
            $_SESSION['forgot_reset_errors'] = ['Unable to verify OTP right now. Please try again.'];
            $_SESSION['forgot_reset_step'] = 'otp';
            header('Location: ' . authBuildRedirectUrl($returnToUrl, ['auth_modal' => 'forgot', 'forgot_step' => 'otp']));
            exit;
        }

        authSetForgotVerifiedSession((int) $otpRecord['user_id'], $email);
        $_SESSION['forgot_reset_success'] = 'OTP verified successfully. Please set your new password.';
        $_SESSION['forgot_reset_step'] = 'password';
        header('Location: ' . authBuildRedirectUrl($returnToUrl, ['auth_modal' => 'forgot', 'forgot_step' => 'password']));
        exit;
    } catch (Exception $e) {
        error_log('verify_reset_otp_code Error: ' . $e->getMessage());
        $_SESSION['forgot_reset_errors'] = ['System error. Please try again later.'];
        $_SESSION['forgot_reset_step'] = 'otp';
        header('Location: ' . authBuildRedirectUrl($returnToUrl, ['auth_modal' => 'forgot', 'forgot_step' => 'otp']));
        exit;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'verify_reset_otp') {
    $email = trim($_POST['email'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';
    $returnToUrl = authResolveReturnToUrl();

    $_SESSION['forgot_reset_old_email'] = $email;
    $errors = [];

    if (!authValidateCsrfToken('reset_password_after_otp', $csrfToken)) {
        $errors[] = 'Invalid request token. Please refresh and try again.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (strlen($newPassword) < 8) {
        $errors[] = 'New password must be at least 8 characters.';
    }

    if ($newPassword !== $confirmPassword) {
        $errors[] = 'Password confirmation does not match.';
    }

    if (!empty($errors)) {
        $_SESSION['forgot_reset_errors'] = $errors;
        $_SESSION['forgot_reset_step'] = 'password';
        header('Location: ' . authBuildRedirectUrl($returnToUrl, ['auth_modal' => 'forgot', 'forgot_step' => 'password']));
        exit;
    }

    try {
        $verifiedSession = authGetForgotVerifiedSession();

        if (empty($verifiedSession)) {
            $_SESSION['forgot_reset_errors'] = ['Please verify OTP first before setting a new password.'];
            $_SESSION['forgot_reset_step'] = 'otp';
            header('Location: ' . authBuildRedirectUrl($returnToUrl, ['auth_modal' => 'forgot', 'forgot_step' => 'otp']));
            exit;
        }

        if (strtolower($email) !== (string) $verifiedSession['email']) {
            $_SESSION['forgot_reset_errors'] = ['Email does not match the verified OTP session.'];
            $_SESSION['forgot_reset_step'] = 'otp';
            header('Location: ' . authBuildRedirectUrl($returnToUrl, ['auth_modal' => 'forgot', 'forgot_step' => 'otp']));
            exit;
        }

        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $passwordUpdated = updateUserPassword((int) $verifiedSession['user_id'], $passwordHash);

        if (!$passwordUpdated) {
            $_SESSION['forgot_reset_errors'] = ['Unable to reset password right now. Please try again.'];
            $_SESSION['forgot_reset_step'] = 'password';
            header('Location: ' . authBuildRedirectUrl($returnToUrl, ['auth_modal' => 'forgot', 'forgot_step' => 'password']));
            exit;
        }

        $_SESSION['login_prefill_email'] = $email;
        $_SESSION['login_prefill_password'] = $newPassword;

        unset($_SESSION['forgot_reset_verified']);
        unset($_SESSION['forgot_reset_old_email']);
        header('Location: ' . authBuildRedirectUrl($returnToUrl, ['auth_modal' => 'login', 'auth_success' => 'Password reset successful']));
        exit;
    } catch (Exception $e) {
        error_log('verify_reset_otp Error: ' . $e->getMessage());
        $_SESSION['forgot_reset_errors'] = ['System error. Please try again later.'];
        $_SESSION['forgot_reset_step'] = 'password';
        header('Location: ' . authBuildRedirectUrl($returnToUrl, ['auth_modal' => 'forgot', 'forgot_step' => 'password']));
        exit;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && ($action === 'login_submit' || $action === 'login')) {
    $returnToUrl = authResolveReturnToUrl();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    if (empty($email) || empty($password)) {
        $_SESSION['login_error'] = "Please enter both Email and Password.";
        header('Location: ' . authBuildRedirectUrl($returnToUrl, ['auth_modal' => 'login']));
        exit;
    }

    try {
        $db = getConnection();
        $result = loginUser($db, $email, $password);

        if ($result['success']) {
            // Lưu thông tin user vào Session
            $_SESSION['user'] = $result['user'];
            unset($_SESSION['login_prefill_email'], $_SESSION['login_prefill_password']);

            // Xử lý Remember Me (Lưu cookie 30 ngày)
            if ($remember) {
                setcookie('remember_email', $email, time() + (30 * 24 * 60 * 60), "/");
            } else {
                // Xóa cookie nếu không tick
                if (isset($_COOKIE['remember_email'])) {
                    setcookie('remember_email', '', time() - 3600, "/");
                }
            }

            $redirectAfterLogin = $_SESSION['after_login_redirect'] ?? $returnToUrl;
            unset($_SESSION['after_login_redirect']);

            // Chỉ cho phép redirect nội bộ để tránh open redirect
            if (strpos($redirectAfterLogin, 'index.php') !== 0) {
                $redirectAfterLogin = 'index.php?action=browse_cars';
            }

            header("Location: " . $redirectAfterLogin);
            exit;
        } else {
            $_SESSION['login_error'] = $result['message'];
            header('Location: ' . authBuildRedirectUrl($returnToUrl, ['auth_modal' => 'login']));
            exit;
        }
    } catch (Exception $e) {
        $_SESSION['login_error'] = 'System error: ' . $e->getMessage();
        header('Location: ' . authBuildRedirectUrl($returnToUrl, ['auth_modal' => 'login']));
        exit;
    }
} elseif ($action === 'logout') {
    $returnToUrl = authResolveReturnToUrl();
    // Xóa toàn bộ Session
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_unset();
        session_destroy();
    }

    // Xóa Cookie "Remember Me" nếu có
    if (isset($_COOKIE['remember_email'])) {
        setcookie('remember_email', '', time() - 3600, "/");
    }

    // Quay về đúng trang hiện tại nếu hợp lệ
    if (strpos($returnToUrl, 'index.php') !== 0) {
        $returnToUrl = 'index.php?action=browse_cars';
    }

    header("Location: " . $returnToUrl);
    exit;
}
