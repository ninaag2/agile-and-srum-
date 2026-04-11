<?php
// filepath: coursework_scrum1.0/views/admin/car_form.php

/** @var array $car */
/** @var string $form_action */
/** @var string $form_title */
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($form_title) ?> - Admin Panel</title>
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
            <a href="index.php?action=home" class="btn-back-store" onclick="if (window.history.length > 1) { event.preventDefault(); window.history.back(); }">&larr; Back</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="top-navbar">
            <h1><?= htmlspecialchars($form_title) ?></h1>
            <div class="admin-profile">
                <span><i class="fas fa-user-shield"></i> <?= htmlspecialchars($_SESSION['user']['fullname'] ?? '') ?></span>
            </div>
        </div>

        <div class="content-body">

            <div class="form-container">
                <form action="<?= htmlspecialchars($form_action) ?>" method="POST">

                    <?php if (!empty($car['id'])): ?>
                        <input type="hidden" name="id" value="<?= htmlspecialchars($car['id']) ?>">
                    <?php endif; ?>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="model_name">Car Model Name *</label>
                            <input type="text" id="model_name" name="model_name" required value="<?= htmlspecialchars($car['model_name'] ?? '') ?>" placeholder="e.g. Toyota Camry 2023">
                        </div>
                        <div class="form-group">
                            <label for="category">Category *</label>
                            <select id="category" name="category" required>
                                <option value="sedan" <?= ($car['category'] ?? '') === 'sedan' ? 'selected' : '' ?>>Sedan</option>
                                <option value="suv" <?= ($car['category'] ?? '') === 'suv' ? 'selected' : '' ?>>SUV</option>
                                <option value="hatchback" <?= ($car['category'] ?? '') === 'hatchback' ? 'selected' : '' ?>>Hatchback</option>
                                <option value="luxury" <?= ($car['category'] ?? '') === 'luxury' ? 'selected' : '' ?>>Luxury</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="seats">Seats</label>
                            <input type="number" id="seats" name="seats" required value="<?= htmlspecialchars($car['seats'] ?? 4) ?>" min="2" max="15">
                        </div>
                        <div class="form-group">
                            <label for="transmission">Transmission</label>
                            <select id="transmission" name="transmission">
                                <option value="automatic" <?= ($car['transmission'] ?? '') === 'automatic' ? 'selected' : '' ?>>Automatic</option>
                                <option value="manual" <?= ($car['transmission'] ?? '') === 'manual' ? 'selected' : '' ?>>Manual</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="fuel_type">Fuel Type</label>
                            <select id="fuel_type" name="fuel_type">
                                <option value="petrol" <?= ($car['fuel_type'] ?? '') === 'petrol' ? 'selected' : '' ?>>Petrol</option>
                                <option value="diesel" <?= ($car['fuel_type'] ?? '') === 'diesel' ? 'selected' : '' ?>>Diesel</option>
                                <option value="electric" <?= ($car['fuel_type'] ?? '') === 'electric' ? 'selected' : '' ?>>Electric</option>
                                <option value="hybrid" <?= ($car['fuel_type'] ?? '') === 'hybrid' ? 'selected' : '' ?>>Hybrid</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="price_per_day">Price per Day (VND) *</label>
                            <input type="number" id="price_per_day" name="price_per_day" required value="<?= htmlspecialchars($car['price_per_day'] ?? '') ?>" step="1000" placeholder="1000000">
                        </div>
                        <div class="form-group">
                            <label for="price_per_hour">Price per Hour (VND) *</label>
                            <input type="number" id="price_per_hour" name="price_per_hour" required value="<?= htmlspecialchars($car['price_per_hour'] ?? '') ?>" step="1000" placeholder="100000">
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="image_url">Image URL *</label>
                        <input type="text" id="image_url" name="image_url" required value="<?= htmlspecialchars($car['image_url'] ?? '') ?>" placeholder="https://example.com/car.jpg">
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="is_available">Status</label>
                        <select id="is_available" name="is_available">
                            <option value="1" <?= isset($car['is_available']) && $car['is_available'] == 1 ? 'selected' : '' ?>>Available - Active in Store</option>
                            <option value="0" <?= isset($car['is_available']) && $car['is_available'] == 0 ? 'selected' : '' ?>>Maintenance - Hidden</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4" placeholder="Enter car details..."><?= htmlspecialchars($car['description'] ?? '') ?></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Save Car</button>
                        <a href="index.php?action=admin_cars" class="btn-cancel">Cancel</a>
                    </div>
                </form>
            </div>

        </div>
    </div>

</body>

</html>