<?php
// filepath: coursework_scrum1.0/controllers/BookingController.php

require_once __DIR__ . '/../datafunctions/booking_functions.php';

// Controller xử lý các chức năng đặt xe
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../datafunctions/car_functions.php';
require_once __DIR__ . '/../datafunctions/booking_functions.php';

$action = $_GET['action'] ?? '';

// Hiển thị Booking Preview theo mô hình GET (PRG)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'book_preview') {
    if (!isset($_SESSION['user'])) {
        header("Location: index.php?action=login_form");
        exit;
    }

    if (!isset($_SESSION['pending_booking'])) {
        $_SESSION['error_message'] = "No pending booking found.";
        header("Location: index.php?action=browse_cars");
        exit;
    }

    try {
        $db = getConnection();
        $user_id = $_SESSION['user']['id'] ?? 0;

        $stmtTier = $db->prepare("SELECT membership_tier FROM users WHERE id = ?");
        $stmtTier->execute([$user_id]);
        $current_tier = $stmtTier->fetchColumn();

        if (!$current_tier) {
            $current_tier = 'new';
        }

        $_SESSION['user']['membership_tier'] = $current_tier;
        $available_vouchers = getAvailableVouchers($db, $current_tier);

        require_once __DIR__ . '/../views/pages/book_preview.php';
        exit;
    } catch (Exception $e) {
        $_SESSION['error_message'] = "System error: " . $e->getMessage();
        header("Location: index.php?action=browse_cars");
        exit;
    }
}

// Hiển thị Form Đặt xe
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'book_form') {
    $car_id = isset($_GET['car_id']) ? (int)$_GET['car_id'] : 0;

    if (!isset($_SESSION['user'])) {
        $_SESSION['login_error'] = "Please login to book this car.";
        if ($car_id > 0) {
            $_SESSION['after_login_redirect'] = "index.php?action=car_detail&id=" . $car_id;
            header("Location: index.php?action=car_detail&id=" . $car_id . "&auth=login");
        } else {
            $_SESSION['after_login_redirect'] = "index.php?action=browse_cars";
            header("Location: index.php?action=browse_cars&auth=login");
        }
        exit;
    }

    if ($car_id <= 0) {
        $_SESSION['error_message'] = "Invalid car ID.";
        header("Location: index.php?action=browse_cars");
        exit;
    }

    try {
        $db = getConnection();
        $carResult = getCarById($db, $car_id);

        if (!$carResult['success']) {
            $_SESSION['error_message'] = "Car not found.";
            header("Location: index.php?action=browse_cars");
            exit;
        }

        $car = $carResult['data'];

        // --- Added for Time Blocking ---
        // Fetch booked slots to disable invalid times in the UI
        $bookedSlots = getBookedSlots($db, $car_id);
        // -------------------------------

        require_once __DIR__ . '/../views/pages/book_form.php';
        exit;
    } catch (Exception $e) {
        $_SESSION['error_message'] = "System error: " . $e->getMessage();
        header("Location: index.php?action=browse_cars");
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update_booking') {
    if (!isset($_SESSION['user'])) {
        header("Location: index.php?action=login_form");
        exit;
    }

    $booking_id = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;
    $car_id = isset($_POST['car_id']) ? (int)$_POST['car_id'] : 0;
    $pickup_datetime = $_POST['pickup_datetime'] ?? '';
    $dropoff_datetime = $_POST['dropoff_datetime'] ?? '';
    $service_type = $_POST['service_type'] ?? 'self-drive';

    if ($booking_id <= 0 || $car_id <= 0) {
        $_SESSION['error_message'] = "Invalid booking information.";
        header("Location: index.php?action=my_bookings");
        exit;
    }

    // Validation Basic
    $now = time();
    $pickup_time = strtotime($pickup_datetime);
    $dropoff_time = strtotime($dropoff_datetime);

    // Allowing same-day modifications if needed, but ensure logic holds
    if (!$pickup_time || !$dropoff_time || $dropoff_time <= $pickup_time) {
        $_SESSION['error_message'] = "Invalid dates. Drop-off must be after Pick-up.";
        header("Location: index.php?action=edit_booking&id=" . $booking_id);
        exit;
    }

    try {
        $db = getConnection();

        // Check Permissions
        $existing = getBookingById($db, $booking_id);
        if (!$existing || $existing['user_id'] != $_SESSION['user']['id']) {
            $_SESSION['error_message'] = "You do not have permission to update this booking.";
            header("Location: index.php?action=my_bookings");
            exit;
        }

        // Validate Overlap (EXCLUDING current booking)
        $isAvailable = checkCarAvailability($db, $car_id, $pickup_datetime, $dropoff_datetime, $booking_id);
        if (!$isAvailable) {
            $_SESSION['error_message'] = "Sorry, this car is already booked for the selected dates.";
            header("Location: index.php?action=edit_booking&id=" . $booking_id);
            exit;
        }

        // Recalculate Price
        $carResult = getCarById($db, $car_id);
        if (!$carResult['success']) {
            throw new Exception("Car not found");
        }
        $car = $carResult['data'];

        $priceBreakdown = calculateBookingPrice($car, $pickup_datetime, $dropoff_datetime, $service_type);
        $total_price = $priceBreakdown['subtotal'];

        // Execute Update
        $success = updateUserBooking($db, $pickup_datetime, $dropoff_datetime, $service_type, $total_price, $booking_id, $_SESSION['user']['id']);

        if ($success) {
            $_SESSION['success_message'] = "Booking #$booking_id updated successfully!";
            header("Location: index.php?action=my_bookings");
        } else {
            $_SESSION['error_message'] = "Failed to update booking. Please try again.";
            header("Location: index.php?action=edit_booking&id=" . $booking_id);
        }
        exit;
    } catch (Exception $e) {
        $_SESSION['error_message'] = "System Error: " . $e->getMessage();
        header("Location: index.php?action=edit_booking&id=" . $booking_id);
        exit;
    }
}

// Xử lý tạo Booking Preview
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'book_preview') {

    // Bắt buộc đăng nhập
    if (!isset($_SESSION['user'])) {
        $_SESSION['error_message'] = "Please login to preview and book a car.";
        header("Location: index.php?action=login_form");
        exit;
    }

    $car_id = isset($_POST['car_id']) ? (int)$_POST['car_id'] : 0;
    $pickup_datetime = $_POST['pickup_datetime'] ?? '';
    $dropoff_datetime = $_POST['dropoff_datetime'] ?? '';
    $service_type = $_POST['service_type'] ?? 'self-drive'; // 'self-drive' hoặc 'with-driver'

    // Validation ngày giờ
    $now = time();
    $pickup_time = strtotime($pickup_datetime);
    $dropoff_time = strtotime($dropoff_datetime);

    if (!$pickup_time || !$dropoff_time || $pickup_time < $now || $dropoff_time <= $pickup_time) {
        $_SESSION['error_message'] = "Invalid pickup or dropoff dates. Drop-off must be after Pick-up, and both must be in the future.";
        header("Location: index.php?action=book_form&car_id=" . $car_id);
        exit;
    }

    try {
        $db = getConnection();

        // 1. Kiểm tra tồn tại xe
        $carResult = getCarById($db, $car_id);
        if (!$carResult['success']) {
            $_SESSION['error_message'] = "Car not found.";
            header("Location: index.php?action=browse_cars");
            exit;
        }
        $car = $carResult['data'];

        // 2. Validate Overlap Booking (Check xe trùng lịch)
        $isAvailable = checkCarAvailability($db, $car_id, $pickup_datetime, $dropoff_datetime);
        if (!$isAvailable) {
            $_SESSION['error_message'] = "Sorry, this car is already booked for the selected dates. Please choose different dates.";
            header("Location: index.php?action=book_form&car_id=" . $car_id);
            exit;
        }

        // 3. Tính toán tổng tiền (Breakdown)
        $priceBreakdown = calculateBookingPrice($car, $pickup_datetime, $dropoff_datetime, $service_type);

        // Xử lý Voucher
        $voucher_code = trim($_POST['voucher_code'] ?? '');
        $discount_amount = 0;

        if (!empty($voucher_code)) {
            $voucher = getVoucherByCode($db, $voucher_code);
            if ($voucher) {
                // Áp dụng tính phần trăm giảm giá dựa trên Subtotal
                $discount_amount = ($priceBreakdown['subtotal'] * $voucher['discount_percent']) / 100;
            } else {
                $_SESSION['error_message'] = "Invalid or Expired Voucher Code.";
                $voucher_code = ''; // Xóa mã sai để không lưu
            }
        }

        // Gộp kết quả Breakdown
        $priceBreakdown['discount_amount'] = $discount_amount;
        $priceBreakdown['final_total'] = $priceBreakdown['subtotal'] - $discount_amount;
        $priceBreakdown['voucher_code'] = $voucher_code;

        // 4. Lưu cache vào Session để làm hóa đơn nháp
        $_SESSION['pending_booking'] = [
            'car_id' => $car_id,
            'car_name' => $car['model_name'],
            'car_image' => $car['image_url'],
            'price_per_day' => $car['price_per_day'],
            'price_per_hour' => $car['price_per_hour'],
            'pickup_datetime' => $pickup_datetime,
            'dropoff_datetime' => $dropoff_datetime,
            'service_type' => $service_type,
            'breakdown' => $priceBreakdown
        ];

        // Lấy danh sách voucher hợp lệ để hiển thị trong select dropdown
        // Cập nhật Hạng thành viên mới nhất từ DB để tránh Stale Session
        $user_id = $_SESSION['user']['id'] ?? 0;
        $stmtTier = $db->prepare("SELECT membership_tier FROM users WHERE id = ?");
        $stmtTier->execute([$user_id]);
        $current_tier = $stmtTier->fetchColumn();

        if (!$current_tier) {
            $current_tier = 'new';
        }

        // Cập nhật lại Session
        $_SESSION['user']['membership_tier'] = $current_tier;

        $available_vouchers = getAvailableVouchers($db, $current_tier);

        // Mỗi lần cập nhật preview thì reset token thanh toán để tránh dùng lại token cũ.
        unset($_SESSION['pending_payment_token']);

        // 5. POST-Redirect-GET: tránh cảnh báo resubmit khi back/refresh
        header("Location: index.php?action=book_preview");
        exit;
    } catch (Exception $e) {
        $_SESSION['error_message'] = "System error: " . $e->getMessage();
        header("Location: index.php?action=book_form&car_id=" . $car_id);
        exit;
    }
}

// Xử lý tạo thanh toán / Đẩy vào DB (Thực tế)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'checkout') {
    if (!isset($_SESSION['user'])) {
        header("Location: index.php?action=login_form");
        exit;
    }
    if (!isset($_SESSION['pending_booking'])) {
        header("Location: index.php?action=browse_cars");
        exit;
    }

    // Chỉ tạo token và chuyển sang trang payment, KHÔNG insert DB ở bước này.
    $_SESSION['pending_payment_token'] = bin2hex(random_bytes(16));
    header("Location: index.php?action=payment_gateway");
    exit;
}

// Hiển thị Cổng Thanh Toán (Payment Gateway)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'payment_gateway') {
    if (!isset($_SESSION['user'])) {
        header("Location: index.php?action=login_form");
        exit;
    }

    $booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;

    try {
        // Luồng cũ: truy cập payment bằng booking_id đã tồn tại (vẫn giữ tương thích)
        if ($booking_id > 0) {
            $db = getConnection();
            $bookingInfo = getBookingById($db, $booking_id);

            if (!$bookingInfo || $bookingInfo['user_id'] != $_SESSION['user']['id'] || $bookingInfo['status'] !== 'pending') {
                $_SESSION['error_message'] = "Booking not found or already processed.";
                header("Location: index.php?action=browse_cars");
                exit;
            }
        } else {
            // Luồng chuẩn: dùng pending_booking trong session, chưa ghi DB
            if (!isset($_SESSION['pending_booking'])) {
                $_SESSION['error_message'] = "No pending booking found for payment.";
                header("Location: index.php?action=browse_cars");
                exit;
            }

            if (empty($_SESSION['pending_payment_token'])) {
                $_SESSION['pending_payment_token'] = bin2hex(random_bytes(16));
            }

            $bookingTemp = $_SESSION['pending_booking'];
            $bookingInfo = [
                'id' => 0,
                'total_price' => $bookingTemp['breakdown']['final_total'] ?? 0
            ];
        }

        require_once __DIR__ . '/../views/pages/payment_gateway.php';
        exit;
    } catch (Exception $e) {
        die("Database Error: " . $e->getMessage());
    }
}

// Xử lý nộp tiền (Process Payment)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'process_payment') {
    if (!isset($_SESSION['user'])) {
        header("Location: index.php?action=login_form");
        exit;
    }

    $booking_id = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;
    $payment_method = $_POST['payment_method'] ?? 'cash'; // 'cash', 'bank_transfer', 'momo', 'credit_card'
    $payment_token = $_POST['payment_token'] ?? '';

    try {
        if (empty($_SESSION['pending_payment_token']) || !hash_equals($_SESSION['pending_payment_token'], $payment_token)) {
            $_SESSION['error_message'] = "Invalid or expired payment session.";
            header("Location: index.php?action=browse_cars");
            exit;
        }

        // One-time token: ngăn refresh/resubmit tạo trùng dữ liệu.
        unset($_SESSION['pending_payment_token']);

        $db = getConnection();

        // Nếu chưa có booking_id thì tạo booking tại đây (người dùng đã bấm Confirm/Pay)
        if ($booking_id <= 0) {
            if (!isset($_SESSION['pending_booking'])) {
                $_SESSION['error_message'] = "No pending booking found.";
                header("Location: index.php?action=browse_cars");
                exit;
            }

            $bookingTemp = $_SESSION['pending_booking'];
            $car_id = (int)($bookingTemp['car_id'] ?? 0);
            $pickup_datetime = (string)($bookingTemp['pickup_datetime'] ?? '');
            $dropoff_datetime = (string)($bookingTemp['dropoff_datetime'] ?? '');

            if (!checkCarAvailability($db, $car_id, $pickup_datetime, $dropoff_datetime)) {
                $_SESSION['error_message'] = "Sorry, this car is no longer available for the selected time.";
                header("Location: index.php?action=book_form&car_id=" . $car_id);
                exit;
            }

            $data = [
                'user_id' => $_SESSION['user']['id'],
                'car_id' => $car_id,
                'pickup_datetime' => $pickup_datetime,
                'dropoff_datetime' => $dropoff_datetime,
                'service_type' => (string)($bookingTemp['service_type'] ?? 'self-drive'),
                'total_price' => (float)($bookingTemp['breakdown']['final_total'] ?? 0)
            ];

            $booking_id = createBooking($db, $data);
            unset($_SESSION['pending_booking']);
        }

        $bookingInfo = getBookingById($db, $booking_id);

        if (!$bookingInfo || $bookingInfo['user_id'] != $_SESSION['user']['id']) {
            die("Invalid Booking.");
        }

        // 1. Transaction ID dummy cho Mock Payment
        $transaction_id = 'TXN' . strtoupper(uniqid()) . time();

        // 2. Insert vào bảng Payments
        $paymentData = [
            'booking_id' => $booking_id,
            'payment_method' => $payment_method,
            'amount' => $bookingInfo['total_price'],
            'payment_status' => 'completed',
            'transaction_id' => $transaction_id
        ];
        createPayment($db, $paymentData);

        // 3. Update Booking -> 'confirmed' or 'pending' depending on payment method
        $final_status = ($payment_method === 'cash') ? 'confirmed' : 'pending';
        updateBookingStatus($db, $booking_id, $final_status);

        // Chuyển tới Success
        header("Location: index.php?action=booking_success&order_id=" . $booking_id);
        exit;
    } catch (Exception $e) {
        die("Payment Processing Error: " . $e->getMessage());
    }
}

// Hiển thị UI Đặt xe thành công
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'booking_success') {
    // Hỗ trợ cả id cũ và order_id mới
    $booking_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);

    echo "<!DOCTYPE html>";
    echo "<html lang='en'>";
    echo "<head>";
    echo "<meta charset='UTF-8'>";
    echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
    echo "<title>Booking Success - Born Car</title>";
    echo "<link rel='stylesheet' href='css/style.css'>";
    echo "</head>";
    echo "<body>";

    echo "<div style='text-align:center; padding: 100px 20px; font-family: sans-serif;'>";
    echo "<h1 style='color: #28a745;'>🎉 Booking Successful!</h1>";
    echo "<p style='font-size:18px; margin-bottom: 30px;'>Thank you for choosing Bon Bon Car. Your booking ID is: <strong>#{$booking_id}</strong>.</p>";
    echo "<a href='index.php?action=browse_cars' style='padding:15px 30px; background:#ffc107; color:#1a1a1a; text-decoration:none; border-radius:6px; font-weight:bold;'>Continue Browsing</a>";
    echo "</div>";

    require_once __DIR__ . '/../views/layouts/footer.php';
    echo "</body>";
    echo "</html>";
    exit;
}
