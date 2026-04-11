<?php
// filepath: coursework_scrum1.0/controllers/CarController.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../datafunctions/car_functions.php';

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'browse_cars') {
    try {
        $db = getConnection();

        // Nhận và Sanitize bộ lọc từ Request GET
        $filters = [
            'keyword'      => trim(htmlspecialchars($_GET['keyword'] ?? '')),
            'category'     => trim(htmlspecialchars($_GET['category'] ?? '')),
            'transmission' => trim(htmlspecialchars($_GET['transmission'] ?? '')),
            'service'      => trim(htmlspecialchars($_GET['service'] ?? '')),
            'district'     => trim(htmlspecialchars($_GET['district'] ?? ''))
        ];

        // Gọi DataFunction lấy dữ liệu với bộ lọc
        $result = getAllCars($db, $filters);

        // Gán vào biến để view sử dụng
        $carsList = $result['success'] ? $result['data'] : [];
        $errorMessage = !$result['success'] ? $result['message'] : '';

        // Truyền lại biến filter cho view để giữ nguyên trạng thái form tìm kiếm
        $activeFilters = $filters;

        // Trả về view
        require_once __DIR__ . '/../views/pages/cars.php';
        exit;
    } catch (Exception $e) {
        $carsList = [];
        $errorMessage = "System error: " . $e->getMessage();
        require_once __DIR__ . '/../views/pages/cars.php';
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'car_detail') {
    try {
        $db = getConnection();

        // Sanitize to integer
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            $_SESSION['error_message'] = "Invalid car ID.";
            header("Location: index.php?action=browse_cars");
            exit;
        }

        $result = getCarById($db, $id);

        if (!$result['success']) {
            $_SESSION['error_message'] = $result['message'];
            header("Location: index.php?action=browse_cars");
            exit;
        }

        // Car found
        $car = $result['data'];

        // Render detail view
        require_once __DIR__ . '/../views/pages/car_detail.php';
        exit;
    } catch (Exception $e) {
        $_SESSION['error_message'] = "System error: " . $e->getMessage();
        header("Location: index.php?action=browse_cars");
        exit;
    }
}
