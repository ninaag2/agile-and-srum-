<?php
$incidents = $incidents ?? [];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Support Tickets - Born Car</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php require_once __DIR__ . '/../layouts/header.php'; ?>

    <div class="container support-page">
        <h1 class="page-title">My Support Tickets</h1>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="msg-success"><?= htmlspecialchars($_SESSION['success_message']) ?></div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="msg-error"><?= htmlspecialchars($_SESSION['error_message']) ?></div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <?php if (empty($incidents)): ?>
            <div class="no-data">
                <p>You have no support tickets yet.</p>
                <a href="index.php?action=support_create" class="btn-confirm">Create first ticket</a>
            </div>
        <?php else: ?>
            <div class="support-list-grid">
                <?php foreach ($incidents as $incident): ?>
                    <div class="incident-card">
                        <div class="incident-card-head">
                            <h3><?= htmlspecialchars((string) ($incident['subject'] ?? '')) ?></h3>
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
                        <p><strong>Created:</strong> <?= htmlspecialchars((string) ($incident['created_at'] ?? '')) ?></p>

                        <a href="index.php?action=support_detail&id=<?= (int) ($incident['id'] ?? 0) ?>" class="btn-book">View Ticket</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php require_once __DIR__ . '/../layouts/footer.php'; ?>
</body>

</html>