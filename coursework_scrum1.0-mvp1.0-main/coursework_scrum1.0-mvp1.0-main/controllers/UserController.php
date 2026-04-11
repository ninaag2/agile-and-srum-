<?php
// filepath: coursework_scrum1.0/controllers/UserController.php

// Ensure database connection and functions are available
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../datafunctions/booking_functions.php';

$action = $_GET['action'] ?? 'my_bookings';

if (!isset($_SESSION['user'])) {
    header("Location: index.php?action=login_form");
    exit;
}

if ($action === 'my_bookings' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $user_id = $_SESSION['user']['id'];
    $db = getConnection();

    // Get user's bookings
    $bookings = getUserBookings($db, $user_id);

    // Include view
    require_once __DIR__ . '/../views/pages/my_bookings.php';
    exit;
}

if ($action === 'cancel_booking' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user']['id'];
    $booking_id = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;

    if ($booking_id > 0) {
        $db = getConnection();

        // Log để debug (có thể xóa sau)
        error_log("Attempting to cancel booking #$booking_id for user #$user_id");

        $success = cancelBooking($db, $booking_id, $user_id);

        if ($success) {
            $_SESSION['success_message'] = "Your booking #$booking_id has been cancelled.";
        } else {
            // Kiểm tra xem booking có tồn tại không hoặc trạng thái hiện tại
            $checkStmt = $db->prepare("SELECT status FROM bookings WHERE id = :id AND user_id = :uid");
            $checkStmt->execute([':id' => $booking_id, ':uid' => $user_id]);
            $currentStatus = $checkStmt->fetchColumn();

            if (!$currentStatus) {
                $_SESSION['error_message'] = "Booking not found.";
            } else {
                $_SESSION['error_message'] = "Cannot cancel booking. Current status: " . ucfirst($currentStatus);
            }
        }
    } else {
        $_SESSION['error_message'] = "Invalid request.";
    }

    header("Location: index.php?action=my_bookings");
    exit;
}
if ($action === 'edit_booking' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $user_id = $_SESSION['user']['id'];
    $booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($booking_id > 0) {
        $db = getConnection();
        // Nhận luôn mảng Booking (hoặc false) thay vì mảng bọc logic
        $booking = getUserBookingsById($db, $booking_id, $user_id);

        // Nếu $booking là false hoặc null
        if (!$booking) {
            $_SESSION['error_message'] = "Booking not found or you don't have permission to edit it.";
            header("Location: index.php?action=my_bookings");
            exit;
        }

        // Get Car Details for Edit Form Price Calculation
        require_once __DIR__ . '/../datafunctions/car_functions.php';
        $carResult = getCarById($db, $booking['car_id']);
        $car = $carResult['success'] ? $carResult['data'] : null;

        // Get booked slots to exclude (but exclude current booking so we can re-select same dates)
        $bookedSlots = getBookedSlots($db, $booking['car_id'], $booking_id);

        // Fix tên file: Cần trỏ đúng trang user_edit_booking.php mà bạn vừa tạo
        require_once __DIR__ . '/../views/pages/user_edit_booking.php';
        exit;
    } else {
        $_SESSION['error_message'] = "Invalid booking ID.";
        header("Location: index.php?action=my_bookings");
        exit;
    }
}
if ($action === 'update_booking' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user']['id'];
    $booking_id = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;

    if ($booking_id > 0) {
        $db = getConnection();
        require_once __DIR__ . '/../datafunctions/car_functions.php';

        $booking = getUserBookingsById($db, $booking_id, $user_id);
        if ($booking) {
            $pickup_datetime = $_POST['pickup_datetime'];
            $dropoff_datetime = $_POST['dropoff_datetime'];
            $service_type = $_POST['service_type'];

            $carResult = getCarById($db, $booking['car_id']);
            if ($carResult['success']) {
                $car = $carResult['data'];
                $priceData = calculateBookingPrice($car, $pickup_datetime, $dropoff_datetime, $service_type);
                $new_total_price = $priceData['subtotal'];

                $success = updateUserBooking(
                    $db,
                    $pickup_datetime,
                    $dropoff_datetime,
                    $service_type,
                    $new_total_price,
                    $booking_id,
                    $user_id
                );

                if ($success) {
                    $_SESSION['success_message'] = "Booking updated successfully. Note: Price may have changed based on your new dates: " . number_format($new_total_price, 0, ',', '.') . " VND";
                } else {
                    $_SESSION['error_message'] = "Failed to update booking. Please ensure the booking is still pending or confirmed.";
                }
            } else {
                $_SESSION['error_message'] = "Error calculating new price: Car not found.";
            }
        } else {
            $_SESSION['error_message'] = "Booking not found.";
        }
    } else {
        $_SESSION['error_message'] = "Invalid booking ID.";
    }

    header("Location: index.php?action=my_bookings");
    exit;
}
