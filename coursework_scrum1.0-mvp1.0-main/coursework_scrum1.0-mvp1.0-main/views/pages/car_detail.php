<?php
// filepath: coursework_scrum1.0/views/pages/car_detail.php

/** @var array $car */
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($car['model_name']) ?> - Details | Born Car</title>
    <!-- Use Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">

</head>

<body>
    <?php require_once __DIR__ . '/../layouts/header.php'; ?>

    <div class="car-detail-container">
        <div class="top-actions">
            <a href="index.php?action=browse_cars" class="btn-back" onclick="if (window.history.length > 1) { event.preventDefault(); window.history.back(); }">&larr; Back</a>
        </div>

        <div class="detail-wrapper">
            <!-- Left Column: Image -->
            <div class="car-image-col">
                <img
                    src="<?= !empty($car['image_url']) ? htmlspecialchars($car['image_url']) : 'https://images.unsplash.com/photo-1494976388531-d1058494cdd8?auto=format&fit=crop&w=1200&q=80' ?>"
                    alt="<?= htmlspecialchars($car['model_name']) ?>"
                    onerror="this.src='https://images.unsplash.com/photo-1494976388531-d1058494cdd8?auto=format&fit=crop&w=1200&q=80'">

                <!-- Additional Service Details -->
                <div class="essential-services">
                    <div class="services-title">Our Premium Services</div>
                    <div class="services-grid">
                        <div class="service-item">
                            <div class="service-icon"><i class="fas fa-car-side"></i></div>
                            <div class="service-text">
                                <h4>Self-Drive Rental</h4>
                                <p>Experience total freedom and flexibility behind the wheel on your own journey.</p>
                            </div>
                        </div>
                        <div class="service-item">
                            <div class="service-icon"><i class="fas fa-user-tie"></i></div>
                            <div class="service-text">
                                <h4>Private Driver- Driven Service</h4>
                                <p>Professional, polite drivers committed to your comfort and safety every mile.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Info Box -->
            <div class="car-info-col">
                <h1 class="car-title"><?= htmlspecialchars($car['model_name']) ?></h1>

                <?php if ($car['is_available'] == 1): ?>
                    <span class="availability-badge badge-available">Available</span>
                <?php else: ?>
                    <span class="availability-badge badge-unavailable">Not Available</span>
                <?php endif; ?>

                <div class="price-box car-detail-price-box">
                    <div class="car-detail-price-row">
                        <p class="price-day\"><?= number_format($car['price_per_day'], 0, '.', ',') ?> đ <span>/ Day</span></p>
                        <p class="price-hour\"><?= number_format($car['price_per_hour'], 0, '.', ',') ?> đ / Hour</p>
                    </div>
                </div>

                <div class="car-specs">
                    <div class="spec-item">
                        <strong>Category</strong>
                        <?= htmlspecialchars($car['category']) ?>
                    </div>
                    <div class="spec-item">
                        <strong>Seats</strong>
                        <?= htmlspecialchars($car['seats']) ?> Passengers
                    </div>
                    <div class="spec-item">
                        <strong>Transmission</strong>
                        <?= strtoupper(htmlspecialchars($car['transmission'])) ?>
                    </div>
                    <div class="spec-item">
                        <strong>Fuel Type</strong>
                        <?= htmlspecialchars($car['fuel_type']) ?>
                    </div>
                </div>

                <div class="car-desc">
                    <strong>Description:</strong><br>
                    <?= !empty($car['description']) ? nl2br(htmlspecialchars($car['description'])) : 'No additional information available for this vehicle. Perfect condition and ready to drive.' ?>
                </div>

                <?php if ($car['is_available'] == 1): ?>
                    <a href="index.php?action=book_form&car_id=<?= htmlspecialchars($car['id']) ?>" class="btn-book">Book This Car Now</a>
                <?php else: ?>
                    <a href="#" class="btn-book disabled">Currently Unavailable</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../layouts/footer.php'; ?>

</body>

</html>