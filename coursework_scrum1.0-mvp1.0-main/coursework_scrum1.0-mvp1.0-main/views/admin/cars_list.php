<?php
// filepath: coursework_scrum1.0/views/admin/cars_list.php

/** @var array $cars */
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Cars - Admin Panel</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">

</head>

<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h2><i class="fas fa-car-side"></i> Bon Bon Admin</h2>
            <span>Management Panel</span>
        </div>

        <div class="sidebar-nav">
            <a href="index.php?action=admin_dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard Overview</a>
            <a href="index.php?action=admin_cars" class="active"><i class="fas fa-car"></i> Manage Cars</a>
            <a href="index.php?action=admin_bookings"><i class="fas fa-calendar-alt"></i> Manage Bookings</a>
            <a href="index.php?action=admin_users"><i class="fas fa-users"></i> Manage Users</a>
            <a href="index.php?action=admin_incidents"><i class="fas fa-headset"></i> Manage Incidents</a>
            <a href="index.php?action=admin_callbacks"><i class="fas fa-phone"></i> Callback Requests</a>
        </div>

        <div class="sidebar-footer">
            <a href="index.php?action=home" class="btn-back" onclick="if (window.history.length > 1) { event.preventDefault(); window.history.back(); }">&larr; Back</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="top-navbar">
            <h1>Manage Cars</h1>
            <div class="admin-profile">
                <span><i class="fas fa-user-shield"></i> <?= htmlspecialchars($_SESSION['user']['fullname'] ?? '') ?></span>
            </div>
        </div>

        <div class="content-body">

            <div class="messages">
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="success-msg"><?= htmlspecialchars($_SESSION['success_message'] ?? '') ?></div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="error-msg"><?= htmlspecialchars($_SESSION['error_message'] ?? '') ?></div>
                    <?php unset($_SESSION['error_message']); ?>
                <?php endif; ?>
            </div>

            <div class="page-actions">
                <p>Manage your vehicle inventory below.</p>
                <a href="index.php?action=admin_add_car" class="btn-add" style="background-color: #2ecc71; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block; transition: 0.3s;"><i class="fas fa-plus"></i> Add New Car</a>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Model Name</th>
                            <th>Category</th>
                            <th>Price / Day</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($cars)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 30px;">No cars found in the database.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($cars as $car): ?>
                                <?php if (!is_array($car)) continue; ?>
                                <tr>
                                    <td>#<?= htmlspecialchars($car['id'] ?? '') ?></td>
                                    <td>
                                        <img src="<?= !empty($car['image_url']) ? htmlspecialchars($car['image_url'] ?? '') : 'https://images.unsplash.com/photo-1494976388531-d1058494cdd8?auto=format&fit=crop&w=50&q=80' ?>"
                                            alt="Car Image" class="car-img">
                                    </td>
                                    <td><strong><?= htmlspecialchars($car['model_name'] ?? '') ?></strong></td>
                                    <td><?= htmlspecialchars(ucfirst($car['category'] ?? '')) ?></td>
                                    <td><?= number_format((float)($car['price_per_day'] ?? 0), 0, '.', ',') ?> VND</td>
                                    <td>
                                        <?php
                                        if (!empty($car['booked_from']) && !empty($car['booked_until'])) {
                                            $now = time();
                                            $from = strtotime($car['booked_from']);
                                            $until = strtotime($car['booked_until']);

                                            // Nếu ngày hiện tại nằm trong khoảng thuê -> Đang đi khách
                                            if ($now >= $from && $now <= $until) {
                                                echo '<span style="background:#ff9800; color:#fff; padding:5px 10px; border-radius:15px; font-size:0.85em; display:inline-block; line-height:1.4;">On Trip<br><small>' . date('d/m H:i', $from) . ' - ' . date('d/m H:i', $until) . '</small></span>';
                                            }
                                            // Nếu ngày hiện tại nhỏ hơn ngày thuê -> Đã được đặt trước
                                            else {
                                                echo '<span style="background:#03a9f4; color:#fff; padding:5px 10px; border-radius:15px; font-size:0.85em; display:inline-block; line-height:1.4;">Booked<br><small>' . date('d/m H:i', $from) . ' - ' . date('d/m H:i', $until) . '</small></span>';
                                            }
                                        } else {
                                            // Không vướng đơn hàng nào, xét trạng thái xe do Admin set
                                            if (isset($car['is_available']) && $car['is_available'] == 1) {
                                                echo '<span style="background:#4caf50; color:#fff; padding:5px 10px; border-radius:15px; font-size:0.85em;">Available</span>';
                                            } else {
                                                echo '<span style="background:#e74c3c; color:#fff; padding:5px 10px; border-radius:15px; font-size:0.85em;">Maintenance</span>';
                                            }
                                        }
                                        ?>
                                    </td>
                                    <td class="action-btns">
                                        <a href="index.php?action=admin_edit_car&id=<?= htmlspecialchars($car['id'] ?? 0) ?>" class="btn-edit" style="background:#f48f0c; color:#000; padding:5px 10px; text-decoration:none; border-radius:5px;"><i class="fas fa-edit"></i> Edit</a>

                                        <form method="POST" action="index.php?action=admin_delete_car" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this car?');">
                                            <input type="hidden" name="car_id" value="<?= htmlspecialchars($car['id'] ?? '') ?>">
                                            <button type="submit" class="btn-delete"><i class="fas fa-trash-alt"></i> Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</body>

</html>