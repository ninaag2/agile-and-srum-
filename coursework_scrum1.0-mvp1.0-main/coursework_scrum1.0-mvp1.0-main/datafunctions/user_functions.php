<?php
// filepath: coursework_scrum1.0/datafunctions/user_functions.php

require_once __DIR__ . '/../config/database.php';

/**
 * Lấy danh sách toàn bộ Users
 */
function getAllUsers(PDO $db) {
    try {
        $stmt = $db->prepare("SELECT * FROM users ORDER BY id DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Lỗi SQL: " . $e->getMessage());
    }
}

/**
 * Lấy thông tin 1 User theo ID
 */
function getUserById(PDO $db, int $id) {
    try {
        $stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("getUserById Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Cập nhật User từ Admin (Role, Tier)
 */
function updateUserAdmin(PDO $db, int $id, string $role, string $tier): bool {
    try {
        $sql = "UPDATE users SET role = :role, membership_tier = :tier WHERE id = :id";
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            ':role' => $role,
            ':tier' => $tier,
            ':id'   => $id
        ]);
    } catch (PDOException $e) {
        error_log("updateUserAdmin Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Xóa User
 */
function deleteUserAdmin(PDO $db, int $id): bool {
    try {
        $sql = "DELETE FROM users WHERE id = :id";
        $stmt = $db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    } catch (PDOException $e) {
        error_log("deleteUserAdmin Error: " . $e->getMessage());
        return false;
    }
}
