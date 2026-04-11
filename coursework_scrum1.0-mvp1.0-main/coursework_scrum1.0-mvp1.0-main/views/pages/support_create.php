<?php
$old = $old ?? [];
$bookings = $bookings ?? [];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Support Ticket - Born Car</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php require_once __DIR__ . '/../layouts/header.php'; ?>

    <div class="container support-page">
        <h1 class="page-title">Need help?</h1>
        <p class="support-subtitle">Report incidents quickly and track responses from support admins.</p>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="msg-success"><?= htmlspecialchars($_SESSION['success_message']) ?></div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="msg-error"><?= htmlspecialchars($_SESSION['error_message']) ?></div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <div class="incident-card">
            <h2>US37: Incident and Problem Reporting</h2>
            <form action="index.php?action=support_create" method="POST" class="support-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

                <div class="form-group">
                    <label for="subject">Subject</label>
                    <input id="subject" name="subject" type="text" class="form-control" maxlength="200" required value="<?= htmlspecialchars((string) ($old['subject'] ?? '')) ?>">
                </div>

                <div class="form-group">
                    <label for="category">Category</label>
                    <select id="category" name="category" class="form-control" required>
                        <?php $selectedCategory = (string) ($old['category'] ?? 'other'); ?>
                        <option value="booking_error" <?= $selectedCategory === 'booking_error' ? 'selected' : '' ?>>Booking Error</option>
                        <option value="vehicle_issue" <?= $selectedCategory === 'vehicle_issue' ? 'selected' : '' ?>>Vehicle Issue</option>
                        <option value="payment" <?= $selectedCategory === 'payment' ? 'selected' : '' ?>>Payment</option>
                        <option value="app_bug" <?= $selectedCategory === 'app_bug' ? 'selected' : '' ?>>App Bug</option>
                        <option value="other" <?= $selectedCategory === 'other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="priority">Priority</label>
                    <select id="priority" name="priority" class="form-control" required>
                        <?php $selectedPriority = (string) ($old['priority'] ?? 'medium'); ?>
                        <option value="low" <?= $selectedPriority === 'low' ? 'selected' : '' ?>>Low</option>
                        <option value="medium" <?= $selectedPriority === 'medium' ? 'selected' : '' ?>>Medium</option>
                        <option value="high" <?= $selectedPriority === 'high' ? 'selected' : '' ?>>High</option>
                        <option value="urgent" <?= $selectedPriority === 'urgent' ? 'selected' : '' ?>>Urgent</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="booking_id">Related Booking (optional)</label>
                    <select id="booking_id" name="booking_id" class="form-control">
                        <option value="">No booking selected</option>
                        <?php $selectedBookingId = (string) ($old['booking_id'] ?? ''); ?>
                        <?php foreach ($bookings as $booking): ?>
                            <?php $bookingId = (string) ($booking['id'] ?? ''); ?>
                            <option value="<?= htmlspecialchars($bookingId) ?>" <?= $selectedBookingId === $bookingId ? 'selected' : '' ?>>
                                #<?= htmlspecialchars($bookingId) ?> - <?= htmlspecialchars((string) ($booking['status'] ?? '')) ?> - <?= htmlspecialchars((string) ($booking['pickup_datetime'] ?? '')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" class="form-control" rows="6" required><?= htmlspecialchars((string) ($old['description'] ?? '')) ?></textarea>
                </div>

                <button type="submit" class="btn-submit">Submit Ticket</button>
            </form>
        </div>

        <div class="callback-box">
            <h2>US35: Immediate Callback Support</h2>
            <p>US36 Hotline: <strong>1900 8888</strong> (Telephone Incident Support)</p>
            <button type="button" id="toggleCallbackBtn" class="btn-confirm">Call me now</button>

            <form action="index.php?action=callback_request" method="POST" id="callbackForm" class="support-form callback-form-hidden">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(incidentGetCsrfToken('callback_request')) ?>">



                <div class="form-group">
                    <label for="callback_note">Note</label>
                    <textarea id="callback_note" name="note" class="form-control" rows="4" placeholder="Describe your issue for faster handling" required></textarea>
                </div>

                <button type="submit" class="btn-submit">Request Callback</button>
            </form>
        </div>
    </div>

    <?php require_once __DIR__ . '/../layouts/footer.php'; ?>

    <script>
        (function() {
            var toggleBtn = document.getElementById('toggleCallbackBtn');
            var callbackForm = document.getElementById('callbackForm');
            if (!toggleBtn || !callbackForm) {
                return;
            }

            toggleBtn.addEventListener('click', function() {
                callbackForm.classList.toggle('callback-form-hidden');
            });
        })();
    </script>
</body>

</html>