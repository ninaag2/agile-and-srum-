<?php
// filepath: coursework_scrum1.0/views/admin/dashboard.php
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Bon Bon Car</title>
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
            <a href="index.php?action=admin_dashboard" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard Overview</a>
            <a href="index.php?action=admin_cars"><i class="fas fa-car"></i> Manage Cars</a>
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
            <h1>Dashboard Overview</h1>
            <div class="admin-profile">
                <span><i class="fas fa-user-shield"></i> <?= htmlspecialchars($_SESSION['user']['fullname']) ?> (Admin)</span>
            </div>
        </div>

        <div class="content-body">
            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-info">
                        <h3>Total Cars</h3>
                        <h2><?= number_format($total_cars) ?></h2>
                    </div>
                    <div class="stat-icon icon-blue"><i class="fas fa-car"></i></div>
                </div>

                <div class="stat-card">
                    <div class="stat-info">
                        <h3>Total Bookings</h3>
                        <h2><?= number_format($total_bookings) ?></h2>
                    </div>
                    <div class="stat-icon icon-green"><i class="fas fa-calendar-check"></i></div>
                </div>

                <div class="stat-card">
                    <div class="stat-info">
                        <h3>Total Revenue</h3>
                        <h2><?= number_format($revenue, 0, '.', ',') ?> <small>VND</small></h2>
                    </div>
                    <div class="stat-icon icon-orange"><i class="fas fa-money-bill-wave"></i></div>
                </div>

                <div class="stat-card">
                    <div class="stat-info">
                        <h3>Total Users</h3>
                        <h2><?= number_format($total_users) ?></h2>
                    </div>
                    <div class="stat-icon icon-purple"><i class="fas fa-users"></i></div>
                </div>
            </div>

            <div style="background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                <h3 style="color: #2c3e50; margin-bottom: 15px;">Welcome to Admin Panel</h3>
                <p style="color: #7f8c8d; line-height: 1.6;">This is the control center for Bon Bon Car Platform. From here, you can manage the entire system workflow, monitor revenues, update vehicle stock, and resolve user bookings.</p>
            </div>
        </div>
    </div>

</body>

</html>