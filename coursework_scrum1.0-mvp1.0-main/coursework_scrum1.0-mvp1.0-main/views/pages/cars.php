<?php
// filepath: coursework_scrum1.0/views/pages/cars.php

// Dữ liệu lấy từ CarController

/** @var array $carsList */
/** @var string $errorMessage */
/** @var array $activeFilters */
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Cars - Born Car</title>
    <link rel="stylesheet" href="css/style.css">

</head>

<body>
    <?php require_once __DIR__ . '/../layouts/header.php'; ?>

    <div class="container">
        <h2 class="page-title">Available Cars for Rent</h2>

        <!-- Search & Filter Section -->
        <div class="filter-section">
            <form method="GET" action="index.php" class="filter-form">
                <input type="hidden" name="action" value="browse_cars">

                <div class="form-group">
                    <input type="text" name="keyword" placeholder="Search car model..."
                        value="<?= htmlspecialchars($activeFilters['keyword'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <select name="category">
                        <option value="all">All Categories</option>
                        <option value="Sedan" <?= (isset($activeFilters['category']) && $activeFilters['category'] === 'Sedan') ? 'selected' : '' ?>>Sedan</option>
                        <option value="SUV" <?= (isset($activeFilters['category']) && $activeFilters['category'] === 'SUV') ? 'selected' : '' ?>>SUV</option>
                        <option value="Hatchback" <?= (isset($activeFilters['category']) && $activeFilters['category'] === 'Hatchback') ? 'selected' : '' ?>>Hatchback</option>
                    </select>
                </div>

                <div class="form-group">
                    <select name="transmission">
                        <option value="all">All Transmissions</option>
                        <option value="auto" <?= (isset($activeFilters['transmission']) && $activeFilters['transmission'] === 'auto') ? 'selected' : '' ?>>Auto</option>
                        <option value="manual" <?= (isset($activeFilters['transmission']) && $activeFilters['transmission'] === 'manual') ? 'selected' : '' ?>>Manual</option>
                    </select>
                </div>
                <div class="form-group">
                    <select name="service">
                        <option value="all">All Services</option>
                        <option value="self-drive" <?= (isset($activeFilters['service']) && $activeFilters['service'] === 'self-drive') ? 'selected' : '' ?>>Self-Drive</option>
                        <option value="with-driver" <?= (isset($activeFilters['service']) && $activeFilters['service'] === 'with-driver') ? 'selected' : '' ?>>With Driver</option>
                    </select>
                </div>

                <button type="submit">Search</button>
                <?php if (!empty($activeFilters['keyword']) || !empty($activeFilters['category']) || !empty($activeFilters['transmission']) || !empty($activeFilters['district'])): ?>
                    <a href="index.php?action=browse_cars" class="reset-btn">Reset</a>
                <?php endif; ?>
            </form>
        </div>

        <?php if (!empty($errorMessage)): ?>
            <div class="alert-danger"><?= htmlspecialchars($errorMessage) ?></div>
        <?php endif; ?>

        <?php if (empty($carsList) && empty($errorMessage)): ?>
            <p style="text-align: center; color: #6c757d; font-size: 18px;">No cars match your search criteria.</p>
        <?php else: ?>
            <div class="car-grid">
                <?php foreach ($carsList as $car): ?>
                    <div class="car-card">
                        <div class="car-image car-list-image-wrap">
                            <img
                                class="car-list-image"
                                src="<?= !empty($car['image_url']) ? htmlspecialchars($car['image_url']) : 'https://images.unsplash.com/photo-1494976388531-d1058494cdd8?auto=format&fit=crop&w=600&q=80' ?>"
                                alt="<?= htmlspecialchars($car['model_name']) ?>"
                                onerror="this.src='https://images.unsplash.com/photo-1494976388531-d1058494cdd8?auto=format&fit=crop&w=600&q=80'">
                        </div>
                        <div class="car-info">
                            <h3 class="car-title"><?= htmlspecialchars($car['model_name']) ?></h3>

                            <div class="car-meta">
                                <span><?= htmlspecialchars($car['category']) ?></span>
                                <span><?= htmlspecialchars($car['seats']) ?> Seats</span>
                                <span><?= strtoupper(htmlspecialchars($car['transmission'])) ?></span>
                                <span><?= htmlspecialchars($car['fuel_type']) ?></span>
                            </div>

                            <div class="pricing-box">
                                <div class="car-price"><?= number_format($car['price_per_day'], 0) ?> ₫<span class="car-price-sub">/ Day</span></div>
                                <div class="car-price-sub"><?= number_format($car['price_per_hour'], 0) ?> ₫/ Hour</div>
                            </div>

                            <div class="card-actions">
                                <a href="index.php?action=car_detail&id=<?= htmlspecialchars($car['id']) ?>" class="btn-book btn-view car-list-btn">Details</a>
                                <a href="index.php?action=book_form&car_id=<?= htmlspecialchars($car['id']) ?>" class="btn-book btn-instant car-list-btn">Book Now</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php include __DIR__ . '/../layouts/footer.php'; ?>

</body>

</html>