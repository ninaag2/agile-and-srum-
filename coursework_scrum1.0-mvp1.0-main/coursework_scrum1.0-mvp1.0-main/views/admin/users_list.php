<?php
// filepath: coursework_scrum1.0/views/admin/users_list.php

/** @var array $users */
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin Panel</title>
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
            <h1><i class="fas fa-users"></i> Manage Users</h1>
            <div class="admin-profile">
                <span style="font-weight: bold; color: #2c3e50;">Welcome, <?= htmlspecialchars($_SESSION['user']['username'] ?? 'Admin') ?></span>
            </div>
        </div>

        <div class="content-body">

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="msg-alert msg-success">
                    <?= htmlspecialchars($_SESSION['success_message']); ?>
                    <?php unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="msg-alert msg-error">
                    <?= htmlspecialchars($_SESSION['error_message']); ?>
                    <?php unset($_SESSION['error_message']); ?>
                </div>
            <?php endif; ?>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Tier</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 20px;">No users found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>#<?= htmlspecialchars($user['id'] ?? '') ?></td>
                                    <td><strong><?= htmlspecialchars($user['username'] ?? '') ?></strong></td>
                                    <td><?= htmlspecialchars($user['fullname'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($user['email'] ?? '') ?></td>
                                    <td>
                                        <span class="badge <?= ($user['role'] ?? '') === 'admin' ? 'bg-admin' : 'bg-customer' ?>">
                                            <?= ucfirst($user['role'] ?? '') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $tClass = 'bg-new';
                                        if (($user['membership_tier'] ?? '') == 'loyal') $tClass = 'bg-loyal';
                                        if (($user['membership_tier'] ?? '') == 'vip') $tClass = 'bg-vip';
                                        ?>
                                        <span class="badge <?= $tClass ?>">
                                            <?= ucfirst($user['membership_tier'] ?? '') ?>
                                        </span>
                                    </td>
                                    <td><?= date('d/m/Y H:i', strtotime($user['created_at'] ?? 'now')) ?></td>
                                    <td style="padding: 10px; border: 1px solid #ddd;">
                                        <!-- Nút Edit (Link GET) -->
                                        <a href="index.php?action=admin_edit_user&id=<?= $user['id'] ?>" style="background:#f48f0c; color:#000; padding:5px 10px; text-decoration:none; border-radius:5px; margin-right:5px;">Edit</a>

                                        <?php if (($user['id'] ?? '') !== $_SESSION['user']['id']): ?>
                                            <!-- Nút Delete (Form POST) -->
                                            <form method="POST" action="index.php?action=admin_delete_user" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                <button type="submit" style="background:#f44336; color:#fff; padding:5px 10px; border:none; border-radius:5px; cursor:pointer;">Delete</button>
                                            </form>
                                        <?php endif; ?>
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