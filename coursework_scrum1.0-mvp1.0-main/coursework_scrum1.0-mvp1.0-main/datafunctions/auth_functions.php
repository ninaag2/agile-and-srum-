<?php
// filepath: coursework_scrum1.0/datafunctions/auth_functions.php

require_once __DIR__ . '/../config/database.php';

/**
 * Đăng ký người dùng mới.
 * 
 * @param PDO $db Đối tượng kết nối PDO
 * @param string $fullname Họ và tên
 * @param string $email Địa chỉ email
 * @param string $phone Số điện thoại
 * @param string $password Mật khẩu (chưa mã hóa)
 * @param string $otp_code Mã OTP để xác thực
 * @return array Mảng kết quả gồm trạng thái và thông báo
 */
function registerUser(PDO $db, string $fullname, string $email, string $phone, string $password, string $otp_code): array
{
    try {
        // 1. Kiểm tra email hoặc số điện thoại đã tồn tại chưa
        $stmtCheck = $db->prepare("SELECT id FROM users WHERE email = :email OR phone = :phone LIMIT 1");
        $stmtCheck->execute([
            ':email' => $email,
            ':phone' => $phone
        ]);

        if ($stmtCheck->fetch()) {
            return [
                'success' => false,
                'message' => 'Email or Phone number is already in use.'
            ];
        }

        // 2. Nếu chưa tồn tại, tiến hành băm mật khẩu
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // 3. Thực hiện INSERT người dùng mới
        // is_verified = 0 (chưa xác thực), is_active = 0 (chưa kích hoạt)
        // role='customer', membership_tier='new'
        $stmtInsert = $db->prepare("
            INSERT INTO users (fullname, email, phone, password_hash, role, membership_tier, otp_code, is_verified, is_active) 
            VALUES (:fullname, :email, :phone, :password_hash, 'customer', 'new', :otp_code, 0, 0)
        ");

        $inserted = $stmtInsert->execute([
            ':fullname'      => $fullname,
            ':email'         => $email,
            ':phone'         => $phone,
            ':password_hash' => $passwordHash,
            ':otp_code'      => $otp_code
        ]);

        if ($inserted) {
            return [
                'success' => true,
                'message' => 'Registration successful! Please verify your email.'
            ];
        }

        return [
            'success' => false,
            'message' => 'An error occurred while saving data. Please try again.'
        ];
    } catch (PDOException $e) {
        error_log("registerUser Error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'System error. Please try again later.'
        ];
    }
}

/**
 * Kiểm tra email/phone đã tồn tại trong hệ thống chưa.
 */
function isRegistrationIdentityAvailable(PDO $db, string $email, string $phone): bool
{
    try {
        $stmtCheck = $db->prepare("SELECT id FROM users WHERE email = :email OR phone = :phone LIMIT 1");
        $stmtCheck->execute([
            ':email' => $email,
            ':phone' => $phone
        ]);

        return !$stmtCheck->fetch();
    } catch (PDOException $e) {
        error_log("isRegistrationIdentityAvailable Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Tạo tài khoản đã xác thực sau khi người dùng nhập OTP hợp lệ.
 */
function createVerifiedUser(PDO $db, string $fullname, string $email, string $phone, string $passwordHash): array
{
    try {
        if (!isRegistrationIdentityAvailable($db, $email, $phone)) {
            return [
                'success' => false,
                'message' => 'Email or Phone number is already in use.'
            ];
        }

        $stmtInsert = $db->prepare(
            "INSERT INTO users (fullname, email, phone, password_hash, role, membership_tier, otp_code, is_verified, is_active)
             VALUES (:fullname, :email, :phone, :password_hash, 'customer', 'new', NULL, 1, 1)"
        );

        $inserted = $stmtInsert->execute([
            ':fullname' => $fullname,
            ':email' => $email,
            ':phone' => $phone,
            ':password_hash' => $passwordHash
        ]);

        if ($inserted) {
            return [
                'success' => true,
                'message' => 'Account verified successfully! You can login now.'
            ];
        }

        return [
            'success' => false,
            'message' => 'An error occurred while creating account. Please try again.'
        ];
    } catch (PDOException $e) {
        error_log("createVerifiedUser Error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'System error. Please try again later.'
        ];
    }
}

/**
 * Xác thực mã OTP
 *
 * @param PDO $db
 * @param string $email
 * @param string $otp
 * @return bool
 */
function verifyOTP(PDO $db, string $email, string $otp): bool
{
    try {
        // Kiểm tra OTP có khớp với email không
        // (Có thể thêm điều kiện thời gian hết hạn nếu muốn, ở đây làm simple MVP)
        $stmt = $db->prepare("SELECT id FROM users WHERE email = :email AND otp_code = :otp AND is_verified = 0 LIMIT 1");
        $stmt->execute([':email' => $email, ':otp' => $otp]);

        if ($stmt->fetch()) {
            // Nếu khớp, update is_verified = 1, is_active = 1, xóa otp_code
            $update = $db->prepare("UPDATE users SET is_verified = 1, is_active = 1, otp_code = NULL WHERE email = :email");
            return $update->execute([':email' => $email]);
        }

        return false;
    } catch (PDOException $e) {
        error_log("verifyOTP Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Đăng nhập người dùng.
 * 
 * @param PDO $db Đối tượng kết nối PDO
 * @param string $email Địa chỉ email
 * @param string $password Mật khẩu (chưa mã hóa)
 * @return array Mảng kết quả gồm trạng thái, thông báo và dữ liệu user (nếu thành công)
 */
function loginUser(PDO $db, string $email, string $password): array
{
    try {
        // 1. Tìm user theo email
        $stmt = $db->prepare("SELECT id, fullname, password_hash, role, membership_tier FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        // 2. Kiểm tra nếu email không tồn tại
        if (!$user) {
            return [
                'success' => false,
                'message' => 'Email or password is incorrect.'
            ];
        }

        // 3. Kiểm tra mật khẩu bằng password_verify
        if (!password_verify($password, $user['password_hash'])) {
            return [
                'success' => false,
                'message' => 'Email or password is incorrect.'
            ];
        }

        // 4. Nếu đúng, trả về thông tin user (loại bỏ password_hash)
        return [
            'success' => true,
            'message' => 'Login successful.',
            'user' => [
                'id'       => $user['id'],
                'fullname' => $user['fullname'],
                'role'     => $user['role'],
                'membership_tier' => $user['membership_tier']
            ]
        ];
    } catch (PDOException $e) {
        error_log("loginUser Error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'System error. Please try again later.'
        ];
    }
}

/**
 * Tìm người dùng theo email.
 */
function getUserByEmail(PDO $db, string $email): ?array
{
    try {
        $stmt = $db->prepare("SELECT id, fullname, email FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();
        return $user ?: null;
    } catch (PDOException $e) {
        error_log("getUserByEmail Error: " . $e->getMessage());
        return null;
    }
}

/**
 * Tạo OTP reset password mới.
 */
function createResetOtp(int $userId, string $email, string $otpCode, ?string $expiresAt, string $ip): bool
{
    try {
        $db = getConnection();
        $stmt = $db->prepare(
            "INSERT INTO password_reset_otps (user_id, email, otp_code, expires_at, request_ip, attempts)
             VALUES (:user_id, :email, :otp_code, COALESCE(:expires_at, DATE_ADD(NOW(), INTERVAL 5 MINUTE)), :request_ip, 0)"
        );

        return $stmt->execute([
            ':user_id' => $userId,
            ':email' => $email,
            ':otp_code' => $otpCode,
            ':expires_at' => $expiresAt,
            ':request_ip' => $ip
        ]);
    } catch (PDOException $e) {
        error_log("createResetOtp Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Lấy OTP reset hợp lệ mới nhất theo email.
 */
function getLatestValidOtpByEmail(string $email): ?array
{
    try {
        $db = getConnection();
        $stmt = $db->prepare(
            "SELECT id, user_id, email, otp_code, expires_at, used_at, attempts, created_at
             FROM password_reset_otps
             WHERE email = :email
               AND used_at IS NULL
               AND expires_at >= NOW()
             ORDER BY created_at DESC
             LIMIT 1"
        );
        $stmt->execute([':email' => $email]);
        $otp = $stmt->fetch();
        return $otp ?: null;
    } catch (PDOException $e) {
        error_log("getLatestValidOtpByEmail Error: " . $e->getMessage());
        return null;
    }
}

/**
 * Tăng số lần nhập OTP sai.
 */
function incrementOtpAttempts(int $otpId): bool
{
    try {
        $db = getConnection();
        $stmt = $db->prepare("UPDATE password_reset_otps SET attempts = attempts + 1 WHERE id = :id LIMIT 1");
        return $stmt->execute([':id' => $otpId]);
    } catch (PDOException $e) {
        error_log("incrementOtpAttempts Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Đánh dấu OTP đã sử dụng.
 */
function markOtpUsed(int $otpId): bool
{
    try {
        $db = getConnection();
        $stmt = $db->prepare("UPDATE password_reset_otps SET used_at = NOW() WHERE id = :id AND used_at IS NULL LIMIT 1");
        return $stmt->execute([':id' => $otpId]);
    } catch (PDOException $e) {
        error_log("markOtpUsed Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Cập nhật password hash cho người dùng.
 */
function updateUserPassword(int $userId, string $passwordHash): bool
{
    try {
        $db = getConnection();
        $stmt = $db->prepare("UPDATE users SET password_hash = :password_hash, updated_at = NOW() WHERE id = :id LIMIT 1");
        return $stmt->execute([
            ':password_hash' => $passwordHash,
            ':id' => $userId
        ]);
    } catch (PDOException $e) {
        error_log("updateUserPassword Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Vô hiệu hóa các OTP reset cũ chưa dùng của user.
 */
function clearOldOtpByUser(int $userId): bool
{
    try {
        $db = getConnection();
        $stmt = $db->prepare("UPDATE password_reset_otps SET used_at = NOW() WHERE user_id = :user_id AND used_at IS NULL");
        return $stmt->execute([':user_id' => $userId]);
    } catch (PDOException $e) {
        error_log("clearOldOtpByUser Error: " . $e->getMessage());
        return false;
    }
}
