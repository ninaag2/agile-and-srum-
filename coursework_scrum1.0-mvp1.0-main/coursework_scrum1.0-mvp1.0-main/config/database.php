<?php
// filepath: coursework_scrum1.0/config/database.php

/**
 * Khởi tạo và trả về đối tượng kết nối cơ sở dữ liệu (PDO).
 *
 * @return PDO Đối tượng PDO đã kết nối.
 * @throws Exception Nếu kết nối thất bại.
 */
function getConnection(): PDO {
    $host = '127.0.0.1';
    $db   = 'car_booking_db'; 
    $user = 'root';        
    $pass = '';            
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, 
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       
        PDO::ATTR_EMULATE_PREPARES   => false,                  
    ];

    try {
        return new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
        error_log("Database Connection Error: " . $e->getMessage());
        throw new Exception("Không thể kết nối đến hệ thống cơ sở dữ liệu. Vui lòng thử lại sau.");
    }
}
