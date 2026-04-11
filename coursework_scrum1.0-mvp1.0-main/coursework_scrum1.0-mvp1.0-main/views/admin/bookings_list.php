<?php
// filepath: coursework_scrum1.0/views/admin/bookings_list.php

/** @var array $bookings */
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - Admin Panel</title>
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
            <a href="index.php?action=admin_cars"><i class="fas fa-car"></i> Manage Cars</a>
            <a href="index.php?action=admin_bookings" class="active"><i class="fas fa-calendar-alt"></i> Manage Bookings</a>
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
            <h1>Manage Bookings</h1>
            <div class="admin-profile">
                <span><i class="fas fa-user-shield"></i> <?= htmlspecialchars($_SESSION['user']['fullname'] ?? '') ?></span>
            </div>
        </div>

        <div class="content-body">

            <?php if (empty($bookings)): ?>
                <p style="text-align: center; font-size: 18px; color: #777; margin-top: 20px;">No bookings found in the database.</p>
            <?php else: ?>

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

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Customer</th>
                                <th>Car Model</th>
                                <th>Period</th>
                                <th>Total Price</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $booking): ?>
                                <?php if (!is_array($booking)) continue; ?>
                                <tr>
                                    <td><strong>#<?= htmlspecialchars($booking['id'] ?? '') ?></strong></td>
                                    <td class="customer-info">
                                        <?= htmlspecialchars($booking['fullname'] ?? 'User Deleted') ?>
                                        <small><?= htmlspecialchars($booking['email'] ?? 'User Deleted') ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($booking['model_name'] ?? 'Car Deleted') ?></td>
                                    <td class="period-info">
                                        <strong>Fr:</strong> <?= date('d/m/Y H:i', strtotime($booking['pickup_datetime'] ?? '')) ?><br>
                                        <strong>To:</strong> <?= date('d/m/Y H:i', strtotime($booking['dropoff_datetime'] ?? '')) ?>
                                    </td>
                                    <td style="font-weight: bold; color: #d32f2f;">
                                        <?= number_format((float)($booking['total_price'] ?? 0), 0, '.', ',') ?> VND
                                    </td>
                                    <td>
                                        <span class="badge <?= htmlspecialchars($booking['status'] ?? '') ?>">
                                            <?= htmlspecialchars($booking['status'] ?? '') ?>
                                        </span>
                                    </td>
                                    <td class="action-btns">
                                        <?php if (($booking['status'] ?? '') === 'pending'): ?>
                                            <form method="POST" action="index.php?action=admin_update_booking" style="display:inline-block;" onsubmit="return confirm('Confirm this booking?');">
                                                <input type="hidden" name="booking_id" value="<?= htmlspecialchars($booking['id'] ?? '') ?>">
                                                <input type="hidden" name="status" value="confirmed">
                                                <button type="submit" class="btn-confirm"><i class="fas fa-check"></i> Confirm</button>
                                            </form>

                                            <form method="POST" action="index.php?action=admin_update_booking" style="display:inline-block;" onsubmit="return confirm('Reject this booking?');">
                                                <input type="hidden" name="booking_id" value="<?= htmlspecialchars($booking['id'] ?? '') ?>">
                                                <input type="hidden" name="status" value="cancelled">
                                                <button type="submit" class="btn-reject"><i class="fas fa-times"></i> Reject</button>
                                            </form>
                                        <?php elseif (($booking['status'] ?? '') === 'confirmed'): ?>
                                            <form method="POST" action="index.php?action=admin_update_booking" style="display:inline-block;" onsubmit="return confirm('Mark this booking as completed?');">
                                                <input type="hidden" name="booking_id" value="<?= htmlspecialchars($booking['id'] ?? '') ?>">
                                                <input type="hidden" name="status" value="completed">
                                                <button type="submit" class="btn-complete"><i class="fas fa-flag-checkered"></i> Complete</button>
                                            </form>
                                        <?php else: ?>
                                            <span style="color: #999; font-size: 13px; font-style: italic;">No actions</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php endif; ?>

        </div>
    </div>

</body>

</html>