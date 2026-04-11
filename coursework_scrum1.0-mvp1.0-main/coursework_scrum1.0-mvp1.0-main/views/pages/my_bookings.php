<?php
// filepath: coursework_scrum1.0/views/pages/my_bookings.php

/** @var array $bookings */
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - Born Car</title>
    <link rel="stylesheet" href="css/style.css">

</head>

<body>
    <?php require_once __DIR__ . '/../layouts/header.php'; ?>

    <div class="container">
        <h1 class="page-title">My Bookings</h1>

        <div class="messages">
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="msg-success"><?= htmlspecialchars($_SESSION['success_message']) ?></div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="msg-error"><?= htmlspecialchars($_SESSION['error_message']) ?></div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>
        </div>

        <?php if (empty($bookings)): ?>
            <div class="no-data">
                <p>You haven't made any bookings yet.</p>
                <a href="index.php?action=home">Browse Cars</a>
            </div>
        <?php else: ?>
            <div class="booking-list">
                <?php foreach ($bookings as $booking): ?>
                    <div class="booking-card">
                        <div class="car-image">
                            <img src="<?= !empty($booking['image_url']) ? htmlspecialchars($booking['image_url']) : 'https://images.unsplash.com/photo-1494976388531-d1058494cdd8?auto=format&fit=crop&w=600&q=80' ?>" alt="<?= htmlspecialchars($booking['model_name']) ?>">
                        </div>

                        <div class="booking-details">
                            <div>
                                <div class="booking-header">
                                    <div>
                                        <h3><?= htmlspecialchars($booking['model_name']) ?></h3>
                                        <div class="booking-meta">
                                            Booking ID: <strong>#<?= htmlspecialchars($booking['id']) ?></strong> |
                                            Placed on: <?= date('M d, Y H:i', strtotime($booking['created_at'])) ?>
                                        </div>
                                    </div>
                                    <span class="badge <?= htmlspecialchars($booking['status']) ?>">
                                        <?= htmlspecialchars($booking['status']) ?>
                                    </span>
                                </div>

                                <div class="info-grid">
                                    <div class="info-item">
                                        <strong>Pick-up</strong>
                                        <?= date('M d, Y H:i', strtotime($booking['pickup_datetime'])) ?>
                                    </div>
                                    <div class="info-item">
                                        <strong>Drop-off</strong>
                                        <?= date('M d, Y H:i', strtotime($booking['dropoff_datetime'])) ?>
                                    </div>
                                    <div class="info-item">
                                        <strong>Service Type</strong>
                                        <?= htmlspecialchars(ucfirst($booking['service_type'])) ?>
                                    </div>
                                    <div class="info-item">
                                        <strong>Total Price</strong>
                                        <span class="price-total"><?= number_format($booking['total_price'], 0, '.', ',') ?> VND</span>
                                    </div>
                                </div>
                            </div>

                            <?php if ($booking['status'] === 'pending' || $booking['status'] === 'confirmed'): ?>
                                <div class="booking-actions">
                                    <a href="index.php?action=edit_booking&id=<?= htmlspecialchars($booking['id']) ?>" class="btn-edit" ...>Edit booking</a>
                                    <form method="POST" action="index.php?action=cancel_booking" onsubmit="return confirm('Are you sure you want to cancel this booking? This action cannot be undone.');">
                                        <input type="hidden" name="booking_id" value="<?= htmlspecialchars($booking['id']) ?>">
                                        <button type="submit" class="btn-cancel">Cancel Booking</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php include __DIR__ . '/../layouts/footer.php'; ?>

</body>

</html>