<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
// filepath: coursework_scrum1.0/index.php
// Router chính điều hướng các request

session_start();

$action = $_GET['action'] ?? 'home';

function indexBuildSamePageAuthRedirect(array $extraParams): string
{
    $defaultUrl = 'index.php?action=browse_cars';
    $referer = trim((string) ($_SERVER['HTTP_REFERER'] ?? ''));

    if ($referer === '') {
        $baseUrl = $defaultUrl;
    } else {
        $path = parse_url($referer, PHP_URL_PATH);
        $query = parse_url($referer, PHP_URL_QUERY);

        if (empty($path) || strpos((string) $path, 'index.php') === false) {
            $baseUrl = $defaultUrl;
        } else {
            $baseUrl = 'index.php';
            if (!empty($query)) {
                $baseUrl .= '?' . $query;
            }
        }
    }

    $parsed = parse_url($baseUrl);
    $path = $parsed['path'] ?? 'index.php';
    parse_str($parsed['query'] ?? '', $queryParams);
    unset($queryParams['auth'], $queryParams['auth_modal'], $queryParams['forgot_step'], $queryParams['auth_success']);

    foreach ($extraParams as $key => $value) {
        if ($value === null || $value === '') {
            continue;
        }
        $queryParams[$key] = $value;
    }

    $queryString = http_build_query($queryParams);
    return $path . (!empty($queryString) ? ('?' . $queryString) : '');
}

// Định tuyến cơ bản
switch ($action) {
    case 'home':
        require_once 'config/database.php';
        require_once 'controllers/HomeController.php';
        break;

    case 'browse_cars':
    case 'car_detail':
        require_once 'config/database.php';
        require_once 'controllers/CarController.php';
        break;

    case 'book_form':
    case 'book_preview':
    case 'update_booking':
    case 'checkout':
    case 'payment_gateway':
    case 'process_payment':
    case 'booking_success':
        require_once 'config/database.php';
        require_once 'controllers/BookingController.php';
        break;

    case 'register_form':
        header('Location: ' . indexBuildSamePageAuthRedirect(['auth_modal' => 'register']));
        exit;

    case 'register_submit':
    case 'verify_otp':
    case 'verify_otp_submit':
    case 'send_reset_otp':
    case 'verify_reset_otp_code':
    case 'verify_reset_otp':
        // Khởi tạo kết nối DB và xử lý form submit
        require_once 'config/database.php';
        require_once 'controllers/AuthController.php';
        break;

    case 'forgot_password':
        header('Location: ' . indexBuildSamePageAuthRedirect(['auth_modal' => 'forgot', 'forgot_step' => 'send']));
        exit;

    case 'login_form':
        header('Location: ' . indexBuildSamePageAuthRedirect(['auth_modal' => 'login']));
        exit;

    case 'login_submit':
    case 'login':
    case 'logout':
        require_once 'config/database.php';
        require_once 'controllers/AuthController.php';
        break;

    case 'my_bookings':
    case 'cancel_booking':
    case 'edit_booking':    // Hiển thị Form để sửa
        require_once 'config/database.php';
        require_once 'controllers/UserController.php';
        break;

    case 'support_create':
    case 'support_list':
    case 'support_detail':
    case 'support_add_message':
    case 'callback_request':
    case 'admin_incidents':
    case 'admin_incident_detail':
    case 'admin_incident_update':
    case 'admin_incident_add_message':
    case 'admin_callbacks':
    case 'admin_callback_update':
        require_once 'config/database.php';
        require_once 'controllers/IncidentController.php';
        break;


    case 'admin_dashboard':
    case 'admin_cars':
    case 'admin_delete_car':
    case 'admin_add_car':
    case 'admin_edit_car':
    case 'admin_users':
    case 'admin_edit_user':
    case 'admin_delete_user':
    case 'admin_bookings':
    case 'admin_update_booking':
        require_once 'config/database.php';
        require_once 'controllers/AdminController.php';
        break;

    default:
        http_response_code(404);
        echo "<h1>404 - Page not found</h1>";
        break;
}
