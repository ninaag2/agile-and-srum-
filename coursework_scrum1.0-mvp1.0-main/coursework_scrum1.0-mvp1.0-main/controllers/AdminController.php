<?php
// filepath: coursework_scrum1.0/controllers/AdminController.php

// Mức bảo mật tối cao: Phải đăng nhập VÀ phải là admin
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'admin') {
    $_SESSION['error_message'] = "Access Denied. You do not have permission to view this page.";
    header("Location: index.php?action=home");
    exit;
}

require_once __DIR__ . '/../datafunctions/car_functions.php';
require_once __DIR__ . '/../datafunctions/booking_functions.php';
require_once __DIR__ . '/../datafunctions/user_functions.php';

$action = $_GET['action'] ?? 'admin_dashboard';

switch ($action) {
    case 'admin_dashboard':
        $db = getConnection();
        // Dữ liệu thật từ CSDL
        $total_cars = $db->query("SELECT COUNT(*) FROM cars")->fetchColumn();
        $total_bookings = $db->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
        $revenue = $db->query("SELECT SUM(total_price) FROM bookings WHERE status IN ('confirmed', 'completed')")->fetchColumn() ?? 0;
        $total_users = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();

        require_once __DIR__ . '/../views/admin/dashboard.php';
        break;

    case 'admin_cars':
        $db = getConnection();
        // Dùng hàm API mới cho giao diện Admin để xem ngày trả xe
        $cars = getAdminCarsWithStatus($db);
        require_once __DIR__ . '/../views/admin/cars_list.php';
        break;

    case 'admin_add_car':
        $db = getConnection();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $success = createCar($db, $_POST);
            if ($success) {
                $_SESSION['success_message'] = "Car added successfully!";
            } else {
                $_SESSION['error_message'] = "Failed to add car.";
            }
            header("Location: index.php?action=admin_cars");
            exit;
        }
        
        $car = [
            'id' => '', 'model_name' => '', 'category' => '', 'seats' => 4, 
            'transmission' => 'automatic', 'fuel_type' => 'petrol', 
            'price_per_day' => '', 'price_per_hour' => '', 'image_url' => '', 'description' => ''
        ];
        $form_action = "index.php?action=admin_add_car";
        $form_title = "Add New Car";
        require_once __DIR__ . '/../views/admin/car_form.php';
        break;

    case 'admin_delete_car':
        $car_id = (int)($_POST['car_id'] ?? 0);
        if ($car_id > 0) {
            try {
                $db = getConnection();
                
                // Kiểm tra xem xe có đang nằm trong Bookings dạng pending, confirmed không
                $checkStmt = $db->prepare("SELECT COUNT(*) FROM bookings WHERE car_id = ? AND status IN ('pending', 'confirmed')");
                $checkStmt->execute([$car_id]);
                $activeBookings = $checkStmt->fetchColumn();

                if ($activeBookings > 0) {
                    $_SESSION['error_message'] = "Cannot delete: Car is currently booked (Pending/Confirmed status).";
                } else {
                    $stmt = $db->prepare("DELETE FROM cars WHERE id = ?");
                    $stmt->execute([$car_id]);
                    $_SESSION['success_message'] = "Car deleted successfully!";
                }
            } catch (PDOException $e) {
                $_SESSION['error_message'] = "Cannot delete: Car is linked to existing data or database error.";
            }
        }
        header("Location: index.php?action=admin_cars");
        exit;

    case 'admin_bookings':
        $db = getConnection();
        $bookings = getAllBookingsAdmin($db);
        require_once __DIR__ . '/../views/admin/bookings_list.php';
        break;

    case 'admin_update_booking':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $booking_id = (int)($_POST['booking_id'] ?? 0);
            $new_status = $_POST['status'] ?? '';
            $allowed_statuses = ['pending', 'confirmed', 'completed', 'cancelled'];
            
            if ($booking_id > 0 && in_array($new_status, $allowed_statuses)) {
                $db = getConnection();
                $success = updateBookingStatusAdmin($db, $booking_id, $new_status);
                if ($success) {
                    $_SESSION['success_message'] = "Booking #$booking_id status updated to " . ucfirst($new_status) . ".";
                } else {
                    $_SESSION['error_message'] = "Failed to update booking status.";
                }
            } else {
                $_SESSION['error_message'] = "Invalid booking ID or status.";
            }
        }
        header("Location: index.php?action=admin_bookings");
        exit;

    case 'admin_edit_car':
        $db = getConnection();
        $car_id = (int)($_GET['id'] ?? ($_POST['id'] ?? 0));
        
        if ($car_id <= 0) {
            $_SESSION['error_message'] = "Invalid Car ID.";
            header("Location: index.php?action=admin_cars");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $success = updateCar($db, $car_id, $_POST);
            if ($success) {
                $_SESSION['success_message'] = "Car updated successfully!";
            } else {
                $_SESSION['error_message'] = "Failed to update car.";
            }
            header("Location: index.php?action=admin_cars");
            exit;
        } else {
            $carResult = getCarById($db, $car_id);
            if (!$carResult || empty($carResult['success'])) {
                $_SESSION['error_message'] = "Car not found in database.";
                header("Location: index.php?action=admin_cars");
                exit;
            }
            $car = $carResult['data'];
            $form_action = "index.php?action=admin_edit_car";
            $form_title = "Edit Car";
            require_once __DIR__ . '/../views/admin/car_form.php';
            break;
        }

    case 'admin_users':
        $db = getConnection();
        $users = getAllUsers($db);
        require_once __DIR__ . '/../views/admin/users_list.php';
        break;

    case 'admin_edit_user':
        $db = getConnection();
        $user_id = (int)($_GET['id'] ?? ($_POST['id'] ?? 0));
        
        if ($user_id <= 0) {
            $_SESSION['error_message'] = "Invalid User ID.";
            header("Location: index.php?action=admin_users");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $role = $_POST['role'] ?? 'customer';
            $tier = $_POST['membership_tier'] ?? 'new';
            $success = updateUserAdmin($db, $user_id, $role, $tier);
            if ($success) {
                $_SESSION['success_message'] = "User updated successfully!";
            } else {
                $_SESSION['error_message'] = "Failed to update user.";
            }
            header("Location: index.php?action=admin_users");
            exit;
        } else {
            $user = getUserById($db, $user_id);
            if (!$user) {
                $_SESSION['error_message'] = "User not found.";
                header("Location: index.php?action=admin_users");
                exit;
            }
            require_once __DIR__ . '/../views/admin/user_form.php';
            break;
        }

    case 'admin_delete_user':
        $user_id = (int)($_POST['user_id'] ?? 0);
        if ($user_id > 0) {
            // Kiểm tra không cho Admin tự xóa chính mình
            if ($user_id == $_SESSION['user']['id']) {
                $_SESSION['error_message'] = "Bạn không thể tự xóa chính mình!";
            } else {
                $db = getConnection();
                if (deleteUserAdmin($db, $user_id)) {
                    $_SESSION['success_message'] = "User deleted successfully!";
                } else {
                    $_SESSION['error_message'] = "Không thể xóa User này (Có thể họ đang có đơn hàng).";
                }
            }
        }
        header("Location: index.php?action=admin_users");
        exit;

    default:
        echo "Admin Action Not Found";
        break;
}
