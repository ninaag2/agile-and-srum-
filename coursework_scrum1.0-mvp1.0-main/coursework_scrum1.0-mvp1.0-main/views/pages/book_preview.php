<?php
// filepath: coursework_scrum1.0/views/pages/book_preview.php

if (!isset($_SESSION['pending_booking'])) {
    header("Location: index.php?action=browse_cars");
    exit;
}

$errorMessage = '';
if (isset($_SESSION['error_message'])) {
    $errorMessage = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

$booking = $_SESSION['pending_booking'];
$breakdown = $booking['breakdown'] ?? [
    'car_fee' => 0,
    'driver_fee' => 0,
    'subtotal' => 0,
    'discount_amount' => 0,
    'final_total' => 0,
    'voucher_code' => ''
];

$pickupFormatted = date('d M Y - H:i', strtotime($booking['pickup_datetime']));
$dropoffFormatted = date('d M Y - H:i', strtotime($booking['dropoff_datetime']));

$diffHours = ceil((strtotime($booking['dropoff_datetime']) - strtotime($booking['pickup_datetime'])) / 3600);
$diffDays = ceil((strtotime($booking['dropoff_datetime']) - strtotime($booking['pickup_datetime'])) / 86400);

$durationText = ($diffHours < 24) ? "{$diffHours} Hours" : "{$diffDays} Days";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Preview | Born Car</title>
    <link rel="stylesheet" href="css/style.css">


</head>

<body>
    <?php require_once __DIR__ . '/../layouts/header.php'; ?>

    <div class="preview-container">
        <h1 class="title">Booking Preview</h1>

        <?php if ($errorMessage): ?>
            <div class="alert-danger"><?= htmlspecialchars($errorMessage) ?></div>
        <?php endif; ?>

        <div class="car-summary">
            <img
                src="<?= !empty($booking['car_image']) ? htmlspecialchars($booking['car_image']) : 'https://images.unsplash.com/photo-1494976388531-d1058494cdd8?auto=format&fit=crop&w=600&q=80' ?>"
                onerror="this.src='https://images.unsplash.com/photo-1494976388531-d1058494cdd8?auto=format&fit=crop&w=600&q=80'"
                alt="<?= htmlspecialchars($booking['car_name']) ?>">
            <div>
                <h3><?= htmlspecialchars($booking['car_name']) ?></h3>
                <p><strong>Rate (Day):</strong> <?= number_format($booking['price_per_day'], 0, '.', ',') ?> đ</p>
                <p><strong>Rate (Hour):</strong> <?= number_format($booking['price_per_hour'], 0, '.', ',') ?> đ</p>
            </div>
        </div>

        <div class="details-grid">
            <div class="detail-item">
                <label>Pick-up Time</label>
                <span><?= $pickupFormatted ?></span>
            </div>
            <div class="detail-item">
                <label>Drop-off Time</label>
                <span><?= $dropoffFormatted ?></span>
            </div>
            <div class="detail-item">
                <label>Rental Duration</label>
                <span><?= $durationText ?></span>
            </div>
            <div class="detail-item">
                <label>Service Type</label>
                <span>
                    <?php if ($booking['service_type'] === 'with-driver'): ?>
                        🚗 Chauffeured (With Driver)
                    <?php else: ?>
                        🔑 Self-Drive
                    <?php endif; ?>
                </span>
            </div>
        </div>

        <!-- Price Breakdown -->
        <div class="breakdown-box">
            <div class="breakdown-row">
                <span>Car Rental Fee</span>
                <span><?= number_format($breakdown['car_fee'], 0, '.', ',') ?> đ</span>
            </div>
            <div class="breakdown-row">
                <span>Driver Fee (If applicable)</span>
                <span><?= number_format($breakdown['driver_fee'], 0, '.', ',') ?> đ</span>
            </div>
            <div class="breakdown-row subtotal-row">
                <span>Subtotal</span>
                <span><?= number_format($breakdown['subtotal'], 0, '.', ',') ?> đ</span>
            </div>
            <?php if ($breakdown['discount_amount'] > 0): ?>
                <div class="breakdown-row discount-row">
                    <span>Discount (<?= htmlspecialchars($breakdown['voucher_code']) ?>)</span>
                    <span>- <?= number_format($breakdown['discount_amount'], 0, '.', ',') ?> đ</span>
                </div>
            <?php endif; ?>
        </div>

        <div class="total-box">
            <label>Total Payment</label>
            <div class="price"><?= number_format($breakdown['final_total'], 0, '.', ',') ?> đ</div>
        </div>

        <!-- Voucher Form -->
        <form method="POST" action="index.php?action=book_preview" class="voucher-box" id="voucherAutoForm">
            <!-- Pass hidden fields to maintain booking state during POST -->
            <input type="hidden" name="car_id" value="<?= htmlspecialchars($booking['car_id']) ?>">
            <input type="hidden" name="pickup_datetime" value="<?= htmlspecialchars($booking['pickup_datetime']) ?>">
            <input type="hidden" name="dropoff_datetime" value="<?= htmlspecialchars($booking['dropoff_datetime']) ?>">
            <input type="hidden" name="service_type" value="<?= htmlspecialchars($booking['service_type']) ?>">

            <strong style="white-space: nowrap; color: #495057;">Got a Voucher?</strong>
            <select name="voucher_code" id="voucherSelect" style="flex: 1; padding: 12px 15px; border: 1px solid #ced4da; border-radius: 6px; font-size: 15px; outline: none;">
                <option value="">-- Select a Voucher --</option>
                <?php foreach ($available_vouchers ?? [] as $v): ?>
                    <option value="<?= htmlspecialchars($v['code']) ?>" <?= ($breakdown['voucher_code'] === $v['code']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($v['code']) ?> - Discount <?= htmlspecialchars($v['discount_percent']) ?>%
                    </option>
                <?php endforeach; ?>
            </select>
            <noscript><button type="submit">Apply</button></noscript>
        </form>

        <div class="action-buttons">
            <a href="index.php?action=book_form&car_id=<?= $booking['car_id'] ?>" class="btn-back" onclick="if (window.history.length > 1) { event.preventDefault(); window.history.back(); }">&larr; Back</a>

            <form method="POST" action="index.php?action=checkout" style="flex: 2; margin: 0; display: flex;">
                <!-- Checkout action will use the final data in $_SESSION['pending_booking'] -->
                <button type="submit" class="btn-confirm">Confirm & Go to Payment</button>
            </form>
        </div>
    </div>

    <script>
        (function() {
            var SCROLL_KEY = 'bookPreviewScrollY';
            var voucherForm = document.getElementById('voucherAutoForm');
            var voucherSelect = document.getElementById('voucherSelect');

            // Restore previous scroll position after voucher auto-submit reload.
            var savedY = sessionStorage.getItem(SCROLL_KEY);
            if (savedY !== null) {
                var y = parseInt(savedY, 10);
                if (!isNaN(y)) {
                    window.scrollTo(0, y);
                    requestAnimationFrame(function() {
                        window.scrollTo(0, y);
                    });
                }
                sessionStorage.removeItem(SCROLL_KEY);
            }

            if (!voucherForm || !voucherSelect) {
                return;
            }

            voucherSelect.addEventListener('change', function() {
                sessionStorage.setItem(SCROLL_KEY, String(window.scrollY || window.pageYOffset || 0));
                voucherForm.submit();
            });
        })();
    </script>

    <?php include __DIR__ . '/../layouts/footer.php'; ?>

</body>

</html>