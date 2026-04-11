<?php
// filepath: coursework_scrum1.0/views/admin/user_form.php

/** @var array $user */
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Admin Panel</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">

</head>

<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h2><i class="fas fa-car-side"></i> Bon Bon Admin</h2>
            <span>Management Panel</span>
        </div>
        <div class="sidebar-nav">
            <a href="index.php?action=admin_dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard Overview</a>
            <a href="index.php?action=admin_cars"><i class="fas fa-car"></i> Manage Cars</a>
            <a href="index.php?action=admin_bookings"><i class="fas fa-calendar-alt"></i> Manage Bookings</a>
            <a href="index.php?action=admin_users" class="active"><i class="fas fa-users"></i> Manage Users</a>
            <a href="index.php?action=admin_incidents"><i class="fas fa-headset"></i> Manage Incidents</a>
            <a href="index.php?action=admin_callbacks"><i class="fas fa-phone"></i> Callback Requests</a>

        </div>
        <div class="sidebar-footer">
            <a href="index.php?action=home" class="btn-back" onclick="if (window.history.length > 1) { event.preventDefault(); window.history.back(); }">&larr; Back</a>
        </div>
    </div>

    <div class="main-content">
        <div class="top-navbar">
            <h1><i class="fas fa-user-edit"></i> Edit User Permissions</h1>
            <div class="admin-profile">
                <span style="font-weight: bold; color: #2c3e50;">Welcome, <?= htmlspecialchars($_SESSION['user']['username'] ?? 'Admin') ?></span>
            </div>
        </div>

        <div class="content-body">
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="msg-alert msg-error">
                    <?= htmlspecialchars($_SESSION['error_message']); ?>
                    <?php unset($_SESSION['error_message']); ?>
                </div>
            <?php endif; ?>

            <div class="form-container">
                <div class="form-header">
                    <h2>Edit Role & Membership Tier</h2>
                    <a href="index.php?action=admin_users" class="btn-cancel" onclick="if (window.history.length > 1) { event.preventDefault(); window.history.back(); }">&larr; Back</a>
                </div>

                <form action="index.php?action=admin_edit_user&id=<?= $user['id'] ?>" method="POST">

                    <div class="info-grid">
                        <div class="info-col">
                            <span class="info-label">Username</span>
                            <span class="info-val"><?= htmlspecialchars($user['username'] ?? '') ?></span>
                        </div>
                        <div class="info-col">
                            <span class="info-label">Full Name</span>
                            <span class="info-val"><?= htmlspecialchars($user['fullname'] ?? '') ?></span>
                        </div>
                        <div class="info-col">
                            <span class="info-label">Email</span>
                            <span class="info-val"><?= htmlspecialchars($user['email'] ?? '') ?></span>
                        </div>
                        <div class="info-col">
                            <span class="info-label">Registered At</span>
                            <span class="info-val"><?= date('d/m/Y H:i', strtotime($user['created_at'] ?? 'now')) ?></span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="role">User Role</label>
                        <select name="role" id="role" class="form-control" required>
                            <option value="customer" <?= (($user['role'] ?? '') === 'customer') ? 'selected' : '' ?>>Customer</option>
                            <option value="admin" <?= (($user['role'] ?? '') === 'admin') ? 'selected' : '' ?>>Administrator</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="tier">Membership Tier</label>
                        <select name="membership_tier" id="tier" class="form-control" required>
                            <option value="new" <?= (($user['membership_tier'] ?? '') === 'new') ? 'selected' : '' ?>>New</option>
                            <option value="loyal" <?= (($user['membership_tier'] ?? '') === 'loyal') ? 'selected' : '' ?>>Loyal</option>
                            <option value="vip" <?= (($user['membership_tier'] ?? '') === 'vip') ? 'selected' : '' ?>>VIP</option>
                        </select>
                    </div>

                    <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>