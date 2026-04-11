<?php
// filepath: coursework_scrum1.0/views/pages/payment_gateway.php

/** @var array $bookingInfo */
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Checkout - Born Car</title>
    <link rel="stylesheet" href="css/style.css">

</head>

<body>
    <?php require_once __DIR__ . '/../layouts/header.php'; ?>

    <div class="payment-container">
        <a href="index.php?action=my_bookings" class="btn-back" onclick="if (window.history.length > 1) { event.preventDefault(); window.history.back(); }">&larr; Back</a>
        <h2 class="checkout-title">Select Payment Method</h2>

        <div class="amount-box">
            <label>Total Amount to Pay</label>
            <div class="price"><?= number_format($bookingInfo['total_price'], 0, '.', ',') ?> đ</div>
        </div>

        <!-- Payment Form -->
        <form method="POST" action="index.php?action=process_payment">
            <input type="hidden" name="booking_id" value="<?= htmlspecialchars($bookingInfo['id']) ?>">
            <input type="hidden" name="payment_token" value="<?= htmlspecialchars($_SESSION['pending_payment_token'] ?? '') ?>">

            <div class="payment-methods">
                <!-- COD -->
                <label class="method-card">
                    <input type="radio" name="payment_method" value="cash" checked>
                    <div class="icon">💵</div>
                    <div class="info">
                        <strong>Cash on Delivery</strong>
                        <span>Pay when you take the car</span>
                    </div>
                </label>

                <!-- Bank Transfer -->
                <label class="method-card">
                    <input type="radio" name="payment_method" value="bank_transfer">
                    <div class="icon">🏦</div>
                    <div class="info">
                        <strong>Bank Transfer</strong>
                        <span>Direct via Banking app</span>
                    </div>
                </label>

                <!-- Momo -->
                <label class="method-card">
                    <input type="radio" name="payment_method" value="momo">
                    <div class="icon">📱</div>
                    <div class="info">
                        <strong>MoMo Wallet</strong>
                        <span>Scan QR Code to pay</span>
                    </div>
                </label>

                <!-- Credit Card -->
                <label class="method-card">
                    <input type="radio" name="payment_method" value="credit_card">
                    <div class="icon">💳</div>
                    <div class="info">
                        <strong>Credit / Debit Card</strong>
                        <span>Visa, Master, JCB</span>
                    </div>
                </label>
            </div>

            <button type="submit" class="btn-pay">Confirm & Pay Now 🔒</button>
            <div class="secure-badge">✔ 100% Secure & Encrypted Payment</div>
        </form>
    </div>

    <?php include __DIR__ . '/../layouts/footer.php'; ?>

</body>

</html>