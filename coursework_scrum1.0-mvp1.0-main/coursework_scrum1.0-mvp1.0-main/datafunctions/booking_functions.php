<?php
// filepath: coursework_scrum1.0/datafunctions/booking_functions.php

require_once __DIR__ . '/../config/database.php';

// Data Function cho phần Booking

function checkCarAvailability(PDO $db, int $car_id, string $pickup, string $dropoff, int $exclude_booking_id = 0): bool
{
    try {
        // Logic SQL Overlap:
        // Hai khoảng thời gian (A1, A2) và (B1, B2) trùng nhau khi và chỉ khi: A1 < B2 VÀ A2 > B1
        $sql = "SELECT COUNT(*) FROM bookings 
                WHERE car_id = :car_id 
                  AND status NOT IN ('cancelled', 'completed') 
                  AND pickup_datetime < :new_dropoff 
                  AND dropoff_datetime > :new_pickup";

        $params = [
            ':car_id'      => $car_id,
            ':new_dropoff' => $dropoff,
            ':new_pickup'  => $pickup
        ];

        if ($exclude_booking_id > 0) {
            $sql .= " AND id != :exclude_id";
            $params[':exclude_id'] = $exclude_booking_id;
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        $count = $stmt->fetchColumn();

        // Nếu count > 0 nghĩa là có ít nhất 1 booking trùng lịch -> Trả về false
        return $count == 0;
    } catch (PDOException $e) {
        // Fallback an toàn nếu DB thiếu table bookings trong lúc Dev
        error_log("checkCarAvailability Error: " . $e->getMessage());
        return true;
    }
}

/**
 * Lấy thông tin voucher theo mã code
 *
 * @param PDO $db
 * @param string $code
 * @return array|false
 */
function getVoucherByCode(PDO $db, string $code)
{
    try {
        $stmt = $db->prepare("SELECT * FROM vouchers WHERE code = :code AND is_active = 1");
        $stmt->execute([':code' => $code]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("getVoucherByCode Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Lấy danh sách voucher hợp lệ theo hạng thành viên (Phân cấp: VIP > Loyal > New)
 *
 * @param PDO $db
 * @param string $tier
 * @return array
 */
function getAvailableVouchers(PDO $db, string $tier): array
{
    try {
        $tier = strtolower(trim($tier));

        // Xây dựng danh sách các hạng được phép
        $allowed_tiers = ['all', 'new']; // Mặc định ai cũng có 'all' và 'new'

        if ($tier === 'loyal') {
            $allowed_tiers[] = 'loyal';
        } elseif ($tier === 'vip') {
            $allowed_tiers[] = 'loyal';
            $allowed_tiers[] = 'vip';
        }

        // Tạo placeholder cho câu lệnh IN (...)
        $placeholders = implode(',', array_fill(0, count($allowed_tiers), '?'));

        $sql = "SELECT * FROM vouchers WHERE is_active = 1 AND required_tier IN ($placeholders)";
        $stmt = $db->prepare($sql);
        $stmt->execute($allowed_tiers);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("getAvailableVouchers Error: " . $e->getMessage());
        return [];
    }
}

/**
 * Tính toán giá trị cuốc xe dựa trên bảng giá xe, thời gian và dịch vụ.
 *
 * @param array $car Dữ liệu xe lấy từ bảng cars
 * @param string $pickup Thời gian nhận xe
 * @param string $dropoff Thời gian trả xe
 * @param string $service_type Loại dịch vụ ('self-drive' hoặc 'with-driver')
 * @return array Mảng chứa bóc tách giá cả (car_fee, driver_fee, subtotal)
 */
function calculateBookingPrice(array $car, string $pickup, string $dropoff, string $service_type): array
{
    $pickupTime = strtotime($pickup);
    $dropoffTime = strtotime($dropoff);
    $diffSeconds = $dropoffTime - $pickupTime;

    if ($diffSeconds <= 0) {
        return ['car_fee' => 0, 'driver_fee' => 0, 'subtotal' => 0];
    }

    $diffHours = ceil($diffSeconds / 3600);
    $diffDays = ceil($diffSeconds / 86400); // 1 ngày = 24h

    $driver_fee_per_day = 500000;
    $car_fee = 0;
    $driver_fee = 0;

    // Nếu thuê theo giờ (dưới 24h) tính theo giá giờ
    if ($diffHours < 24) {
        $car_fee = $diffHours * $car['price_per_hour'];

        // Dịch vụ tài xế tính ít nhất 1 ngày nếu có
        if ($service_type === 'with-driver') {
            $driver_fee = $driver_fee_per_day;
        }
    } else {
        // Nếu thuê lớn hơn hoặc bằng 24h tính theo số ngày (làm tròn lên)
        $car_fee = $diffDays * $car['price_per_day'];

        if ($service_type === 'with-driver') {
            $driver_fee = $diffDays * $driver_fee_per_day;
        }
    }

    return [
        'car_fee' => $car_fee,
        'driver_fee' => $driver_fee,
        'subtotal' => $car_fee + $driver_fee
    ];
}

/**
 * Tạo một bản ghi booking mới vào CSDL
 *
 * @param PDO $db
 * @param array $data Dữ liệu đơn hàng
 * @return int Trả về ID của booking vừa tạo
 */
function createBooking(PDO $db, array $data): int
{
    $sql = "INSERT INTO bookings (user_id, car_id, pickup_datetime, dropoff_datetime, service_type, total_price, status) 
            VALUES (:user_id, :car_id, :pickup_datetime, :dropoff_datetime, :service_type, :total_price, 'pending')";

    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':user_id' => $data['user_id'],
        ':car_id' => $data['car_id'],
        ':pickup_datetime' => $data['pickup_datetime'],
        ':dropoff_datetime' => $data['dropoff_datetime'],
        ':service_type' => $data['service_type'],
        ':total_price' => $data['total_price']
    ]);

    return (int)$db->lastInsertId();
}

/**
 * Lấy thông tin đặt xe theo ID
 */
function getBookingById(PDO $db, int $booking_id)
{
    try {
        $stmt = $db->prepare("SELECT * FROM bookings WHERE id = :id");
        $stmt->execute([':id' => $booking_id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Cập nhật trạng thái của Booking
 */
function updateBookingStatus(PDO $db, int $booking_id, string $status): bool
{
    try {
        $stmt = $db->prepare("UPDATE bookings SET status = :status WHERE id = :id");
        return $stmt->execute([':status' => $status, ':id' => $booking_id]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Tạo bản ghi thanh toán mới
 */
function createPayment(PDO $db, array $data): bool
{
    try {
        $sql = "INSERT INTO payments (booking_id, payment_method, amount, payment_status, transaction_id) 
                VALUES (:booking_id, :payment_method, :amount, :payment_status, :transaction_id)";
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            ':booking_id' => $data['booking_id'],
            ':payment_method' => $data['payment_method'],
            ':amount' => $data['amount'],
            ':payment_status' => $data['payment_status'],
            ':transaction_id' => $data['transaction_id']
        ]);
    } catch (PDOException $e) {
        error_log("createPayment Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Lấy toàn bộ danh sách đơn hàng cho Admin quản lý
 */
function getAllBookingsAdmin(PDO $db): array
{
    try {
        // Tự động kiểm tra và cập nhật trạng thái trước khi lấy dữ liệu cho Admin
        autoUpdateCompletedBookings($db);

        $sql = "SELECT b.*, c.model_name, u.fullname, u.email 
                FROM bookings b 
                LEFT JOIN cars c ON b.car_id = c.id 
                LEFT JOIN users u ON b.user_id = u.id 
                ORDER BY b.created_at DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Lỗi SQL SQL trong getAllBookingsAdmin: " . $e->getMessage());
    }
}

/**
 * Cập nhật trạng thái đơn hàng từ phía Admin
 */
function updateBookingStatusAdmin(PDO $db, int $booking_id, string $new_status): bool
{
    try {
        $stmt = $db->prepare("UPDATE bookings SET status = :status WHERE id = :id");
        return $stmt->execute([':status' => $new_status, ':id' => $booking_id]);
    } catch (PDOException $e) {
        error_log("updateBookingStatusAdmin Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Tự động cập nhật trạng thái đơn hàng sang 'completed' nếu đã qua giờ trả xe.
 */
function autoUpdateCompletedBookings(PDO $db)
{
    try {
        // Cập nhật các đơn pending hoặc confirmed đã quá giờ trả xe
        $sql = "UPDATE bookings 
                SET status = 'completed' 
                WHERE status IN ('pending', 'confirmed') 
                AND dropoff_datetime < NOW()";
        $db->exec($sql);
    } catch (PDOException $e) {
        error_log("autoUpdateCompletedBookings Error: " . $e->getMessage());
    }
}

/**
 * Lấy danh sách booking của một user cụ thể
 */
function getUserBookings(PDO $db, int $user_id): array
{
    try {
        // Tự động kiểm tra và cập nhật trạng thái trước khi lấy dữ liệu cho User
        autoUpdateCompletedBookings($db);

        $sql = "SELECT b.id, b.pickup_datetime, b.dropoff_datetime, b.service_type, b.total_price, b.status, b.created_at, 
                       c.model_name, c.image_url 
                FROM bookings b 
                JOIN cars c ON b.car_id = c.id 
                WHERE b.user_id = :user_id 
                ORDER BY b.created_at DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("getUserBookings Error: " . $e->getMessage());
        return [];
    }
}
function getUserBookingsById(PDO $db, int $booking_id, int $user_id)
{
    try {
        $sql = "SELECT b.*, c.model_name, c.image_url, c.price_per_day, c.price_per_hour
                FROM bookings b
                JOIN cars c ON b.car_id = c.id
                WHERE b.user_id = :user_id AND b.id = :booking_id
                LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->execute([':user_id' => $user_id, ':booking_id' => $booking_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("getUserBookingsById Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Lấy danh sách các khoảng thời gian đã được đặt của một xe.
 * Hỗ trợ loại trừ một booking_id cụ thể (dùng khi edit booking)
 */
function getBookedSlots(PDO $db, int $car_id, int $exclude_booking_id = 0): array
{
    try {
        $sql = "SELECT pickup_datetime, dropoff_datetime 
                FROM bookings 
                WHERE car_id = :car_id 
                  AND status NOT IN ('cancelled', 'completed', 'rejected')";

        if ($exclude_booking_id > 0) {
            $sql .= " AND id != :exclude_id";
        }

        $stmt = $db->prepare($sql);
        $params = [':car_id' => $car_id];

        if ($exclude_booking_id > 0) {
            $params[':exclude_id'] = $exclude_booking_id;
        }

        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("getBookedSlots Error: " . $e->getMessage());
        return [];
    }
}

function updateUserBooking(PDO $db, $pickup_datetime, $dropoff_datetime, $service_type, $total_price, $booking_id, $user_id)
{
    try {
        $sql = "UPDATE bookings
                SET pickup_datetime = :pickup_datetime,
                    dropoff_datetime = :dropoff_datetime,
                    service_type = :service_type,
                    total_price = :total_price,
                    status = 'pending'
                WHERE id = :booking_id AND user_id = :user_id";
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            ':pickup_datetime' => $pickup_datetime,
            ':dropoff_datetime' => $dropoff_datetime,
            ':service_type' => $service_type,
            ':total_price' => $total_price,
            ':booking_id' => $booking_id,
            ':user_id' => $user_id
        ]);
    } catch (PDOException $e) {
        error_log("updateUserBooking Error: " . $e->getMessage());
        return false;
    }
}
function cancelBooking(PDO $db, int $booking_id, int $user_id)
{
    try {
        // Chỉ cho phép hủy nếu đơn hàng thuộc về user đó và đang ở trạng thái pending hoặc confirmed
        $stmt = $db->prepare("UPDATE bookings SET status = 'cancelled' 
                             WHERE id = :id AND user_id = :user_id 
                             AND status IN ('pending', 'confirmed')");
        $stmt->execute([
            ':id' => $booking_id,
            ':user_id' => $user_id
        ]);

        // Trả về true nếu có dòng được cập nhật thành công
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log("cancelBooking Error: " . $e->getMessage());
        return false;
    }
}
