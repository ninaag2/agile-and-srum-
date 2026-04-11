<?php
$incident = $incident ?? [];
$messages = $messages ?? [];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Detail - Born Car</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php require_once __DIR__ . '/../layouts/header.php'; ?>

    <div class="container support-page">
        <a href="index.php?action=support_list" class="btn-back">&larr; Back to list</a>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="msg-success"><?= htmlspecialchars($_SESSION['success_message']) ?></div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="msg-error"><?= htmlspecialchars($_SESSION['error_message']) ?></div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <div class="incident-card">
            <div class="incident-card-head">
                <h2><?= htmlspecialchars((string) ($incident['subject'] ?? '')) ?></h2>
                <span class="status-badge status-<?= htmlspecialchars((string) ($incident['status'] ?? 'new')) ?>">
                    <?= htmlspecialchars((string) ($incident['status'] ?? 'new')) ?>
                </span>
            </div>
            <p><strong>Ticket:</strong> <?= htmlspecialchars((string) ($incident['ticket_code'] ?? '')) ?></p>
            <p><strong>Category:</strong> <?= htmlspecialchars((string) ($incident['category'] ?? '')) ?></p>
            <p>
                <strong>Priority:</strong>
                <span class="priority-badge priority-<?= htmlspecialchars((string) ($incident['priority'] ?? 'medium')) ?>">
                    <?= htmlspecialchars((string) ($incident['priority'] ?? 'medium')) ?>
                </span>
            </p>
            <p><strong>Channel:</strong> <?= htmlspecialchars((string) ($incident['channel'] ?? 'web_form')) ?></p>
            <p><strong>Description:</strong> <?= nl2br(htmlspecialchars((string) ($incident['description'] ?? ''))) ?></p>
        </div>

        <div class="support-thread" id="supportThread">
            <h3>Message Timeline</h3>
            <?php if (empty($messages)): ?>
                <p>No messages yet.</p>
            <?php else: ?>
                <?php foreach ($messages as $message): ?>
                    <?php $senderType = (string) ($message['sender_type'] ?? 'system'); ?>
                    <div class="support-message support-message-<?= htmlspecialchars($senderType) ?>">
                        <div class="support-message-meta">
                            <strong><?= htmlspecialchars((string) ($message['sender_name'] ?? ucfirst($senderType))) ?></strong>
                            <span><?= htmlspecialchars((string) ($message['created_at'] ?? '')) ?></span>
                        </div>
                        <div class="support-message-body"><?= nl2br(htmlspecialchars((string) ($message['message'] ?? ''))) ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if (($incident['status'] ?? '') !== 'closed'): ?>
            <div class="incident-card">
                <h3>Add Message</h3>
                <form action="index.php?action=support_add_message" method="POST" class="support-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($messageCsrfToken) ?>">
                    <input type="hidden" name="incident_id" value="<?= (int) ($incident['id'] ?? 0) ?>">
                    <div class="form-group">
                        <label for="message">Your Message</label>
                        <textarea id="message" name="message" class="form-control" rows="4" required></textarea>
                    </div>
                    <button type="submit" class="btn-submit">Send Message</button>
                </form>
            </div>
        <?php endif; ?>

        <div class="callback-box">
            <h3>Telephone Incident Support</h3>
            <p>Hotline: <strong>1900 8888</strong> (24/7)</p>
        </div>
    </div>

    <?php require_once __DIR__ . '/../layouts/footer.php'; ?>

    <script>
        (function() {
            var thread = document.getElementById('supportThread');
            if (!thread) {
                return;
            }
            thread.scrollTop = thread.scrollHeight;
        })();
    </script>
</body>

</html>