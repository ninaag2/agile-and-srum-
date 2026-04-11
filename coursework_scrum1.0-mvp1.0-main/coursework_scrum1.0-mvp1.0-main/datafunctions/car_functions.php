<?php
// filepath: coursework_scrum1.0/datafunctions/car_functions.php

require_once __DIR__ . '/../config/database.php';

/**
 * Lấy danh sách các xe rảnh (is_available = 1).
 * Tương lai có thể mở rộng array $filters để thêm logic lọc xe.
 * 
 * @param PDO $db Đối tượng PDO
 * @param array $filters Các bộ lọc (nếu có)
 * @return array Trả về mảng chứa kết quả success và danh sách dataset
 */
function getAllCars(PDO $db, array $filters = []): array
{
    try {
        // SQL cơ bản lấy những xe rảnh
        $sql = "SELECT id, model_name, category, seats, fuel_type, transmission, price_per_day, price_per_hour, image_url 
                FROM cars 
                WHERE is_available = 1";

        $params = [];

        // Xử lý bộ lọc (Filter & Search) một cách linh hoạt bằng mảng và bind PDO
        if (!empty($filters['keyword'])) {
            $sql .= " AND model_name LIKE :keyword";
            $params[':keyword'] = '%' . $filters['keyword'] . '%';
        }

        if (!empty($filters['category']) && $filters['category'] !== 'all') {
            $sql .= " AND category = :category";
            $params[':category'] = $filters['category'];
        }

        if (!empty($filters['transmission']) && $filters['transmission'] !== 'all') {
            $sql .= " AND transmission = :transmission";
            $params[':transmission'] = $filters['transmission'];
        }

        if (!empty($filters['district']) && $filters['district'] !== 'all') {
            $sql .= " AND district = :district";
            $params[':district'] = $filters['district'];
        }

        // Thêm sắp xếp mặc định
        $sql .= " ORDER BY created_at DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        $cars = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'success' => true,
            'data'    => $cars,
            'message' => 'Cars retrieved successfully.'
        ];
    } catch (PDOException $e) {
        error_log("getAllCars Error: " . $e->getMessage());
        return [
            'success' => false,
            'data'    => [],
            'message' => 'System error while retrieving cars.'
        ];
    }
}

/**
 * Lấy danh sách quận/huyện nổi bật dựa trên xe còn sẵn.
 * Trả về tên district, số lượng xe và ảnh đại diện cho district.
 *
 * @param PDO $db
 * @param int $limit
 * @return array
 */
function getFeaturedDistricts(PDO $db, int $limit = 8): array
{
    try {
        $sql = "SELECT c1.district,
                                             COUNT(*) AS car_count,
                                             COALESCE(
                                                 (
                                                     SELECT c2.image_url
                                                     FROM cars c2
                                                     WHERE c2.is_available = 1
                                                         AND c2.district = c1.district
                                                         AND c2.image_url IS NOT NULL
                                                         AND c2.image_url != ''
                                                     ORDER BY RAND()
                                                     LIMIT 1
                                                 ),
                                                 ''
                                             ) AS image_url
                                FROM cars c1
                                WHERE c1.is_available = 1
                                    AND c1.district IS NOT NULL
                                    AND c1.district != ''
                                GROUP BY c1.district
                                ORDER BY car_count DESC, c1.district ASC
                                LIMIT :limit";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'success' => true,
            'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'message' => 'Featured districts retrieved successfully.'
        ];
    } catch (PDOException $e) {
        error_log("getFeaturedDistricts Error: " . $e->getMessage());
        return [
            'success' => false,
            'data' => [],
            'message' => 'System error while retrieving featured districts.'
        ];
    }
}

/**
 * Lấy ngẫu nhiên 6 xe có sẵn để hiển thị section "Xe có ngay".
 * SQL pattern theo yêu cầu: ORDER BY RAND() LIMIT 6
 *
 * @param PDO $db
 * @param int $limit
 * @return array
 */
function getRandomAvailableCars(PDO $db, int $limit = 6): array
{
    try {
        $sql = "SELECT id, model_name, category, seats, fuel_type, transmission, price_per_day, price_per_hour, image_url
                FROM cars
                WHERE is_available = 1
                ORDER BY RAND()
                LIMIT :limit";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'success' => true,
            'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'message' => 'Random available cars retrieved successfully.'
        ];
    } catch (PDOException $e) {
        error_log("getRandomAvailableCars Error: " . $e->getMessage());
        return [
            'success' => false,
            'data' => [],
            'message' => 'System error while retrieving random available cars.'
        ];
    }
}

/**
 * Lấy ngẫu nhiên xe phù hợp gia đình nhỏ (tối đa 5 chỗ) và còn sẵn.
 *
 * @param PDO $db
 * @param int $limit
 * @return array
 */
function getRandomSmallFamilyCars(PDO $db, int $limit = 6): array
{
    try {
        $sql = "SELECT id, model_name, category, seats, fuel_type, transmission, price_per_day, price_per_hour, image_url
                FROM cars
                WHERE is_available = 1
                  AND seats <= 5
                ORDER BY RAND()
                LIMIT :limit";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'success' => true,
            'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'message' => 'Small family cars retrieved successfully.'
        ];
    } catch (PDOException $e) {
        error_log("getRandomSmallFamilyCars Error: " . $e->getMessage());
        return [
            'success' => false,
            'data' => [],
            'message' => 'System error while retrieving small family cars.'
        ];
    }
}

/**
 * Lấy ngẫu nhiên xe premium: còn sẵn và giá thuê theo ngày trên 1 triệu.
 *
 * @param PDO $db
 * @param int $limit
 * @return array
 */
function getRandomPremiumCars(PDO $db, int $limit = 6): array
{
    try {
        $sql = "SELECT id, model_name, category, seats, fuel_type, transmission, price_per_day, price_per_hour, image_url
                FROM cars
                WHERE is_available = 1
                  AND price_per_day > 1000000
                ORDER BY RAND()
                LIMIT :limit";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'success' => true,
            'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'message' => 'Premium cars retrieved successfully.'
        ];
    } catch (PDOException $e) {
        error_log("getRandomPremiumCars Error: " . $e->getMessage());
        return [
            'success' => false,
            'data' => [],
            'message' => 'System error while retrieving premium cars.'
        ];
    }
}

/**
 * Lấy chi tiết một xe dựa trên ID.
 * 
 * @param PDO $db Đối tượng PDO
 * @param int $car_id ID của xe
 * @return array Trả về mảng chứa kết quả success và dữ liệu chi tiết xe
 */
function getCarById(PDO $db, int $car_id): array
{
    try {
        $sql = "SELECT id, model_name, category, seats, fuel_type, transmission, price_per_day, price_per_hour, image_url, description, is_available 
                FROM cars 
                WHERE id = :id";

        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $car_id]);

        $car = $stmt->fetch();

        if ($car) {
            return [
                'success' => true,
                'data'    => $car,
                'message' => 'Car retrieved successfully.'
            ];
        } else {
            return [
                'success' => false,
                'data'    => null,
                'message' => 'Car not found.'
            ];
        }
    } catch (PDOException $e) {
        error_log("getCarById Error: " . $e->getMessage());
        return [
            'success' => false,
            'data'    => null,
            'message' => 'System error while retrieving car details.'
        ];
    }
}

/**
 * Lấy danh sách xe kèm trạng thái đặt xe cho Admin
 * (Chứa Subquery tìm ngày trả xe gần nhất nếu có)
 */
function getAdminCarsWithStatus(PDO $db): array
{
    try {
        $sql = "SELECT c.*, 
                (SELECT pickup_datetime FROM bookings b WHERE b.car_id = c.id AND b.status IN ('pending', 'confirmed') AND b.dropoff_datetime >= NOW() ORDER BY b.pickup_datetime ASC LIMIT 1) as booked_from,
                (SELECT dropoff_datetime FROM bookings b WHERE b.car_id = c.id AND b.status IN ('pending', 'confirmed') AND b.dropoff_datetime >= NOW() ORDER BY b.pickup_datetime ASC LIMIT 1) as booked_until 
                FROM cars c ORDER BY c.id DESC";
        $stmt = $db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("getAdminCarsWithStatus Error: " . $e->getMessage());
        return [];
    }
}

/**
 * Thêm xe mới vào CSDL
 */
function createCar(PDO $db, array $data): bool
{
    try {
        $sql = "INSERT INTO cars (model_name, category, seats, transmission, fuel_type, price_per_day, price_per_hour, image_url, description) 
                VALUES (:model_name, :category, :seats, :transmission, :fuel_type, :price_per_day, :price_per_hour, :image_url, :description)";
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            ':model_name'     => $data['model_name'] ?? '',
            ':category'       => $data['category'] ?? '',
            ':seats'          => (int)($data['seats'] ?? 4),
            ':transmission'   => $data['transmission'] ?? 'automatic',
            ':fuel_type'      => $data['fuel_type'] ?? 'petrol',
            ':price_per_day'  => (float)($data['price_per_day'] ?? 0),
            ':price_per_hour' => (float)($data['price_per_hour'] ?? 0),
            ':image_url'      => $data['image_url'] ?? '',
            ':description'    => $data['description'] ?? ''
        ]);
    } catch (PDOException $e) {
        error_log("createCar Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Cập nhật thông tin xe
 */
function updateCar(PDO $db, int $car_id, array $data): bool
{
    try {
        $sql = "UPDATE cars SET 
                    model_name = :model_name, 
                    category = :category, 
                    seats = :seats, 
                    transmission = :transmission, 
                    fuel_type = :fuel_type, 
                    price_per_day = :price_per_day, 
                    price_per_hour = :price_per_hour, 
                    description = :description, 
                    image_url = :image_url,
                    is_available = :is_available
                WHERE id = :id";
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            ':model_name'     => trim($data['model_name'] ?? ''),
            ':category'       => trim($data['category'] ?? ''),
            ':seats'          => (int)($data['seats'] ?? 4),
            ':transmission'   => trim($data['transmission'] ?? 'automatic'),
            ':fuel_type'      => trim($data['fuel_type'] ?? 'petrol'),
            ':price_per_day'  => (float)($data['price_per_day'] ?? 0),
            ':price_per_hour' => (float)($data['price_per_hour'] ?? 0),
            ':description'    => trim($data['description'] ?? ''),
            ':image_url'      => trim($data['image_url'] ?? ''),
            ':is_available'   => isset($data['is_available']) ? (int)$data['is_available'] : 1,
            ':id'             => $car_id
        ]);
    } catch (PDOException $e) {
        error_log("updateCar Error: " . $e->getMessage());
        return false;
    }
}
