<?php
$callbacks = $callbacks ?? [];
$filters = $filters ?? [];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Callback Requests - Born Car</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>Born Car Admin</h2>
            <span>Support Center</span>
        </div>
        <div class="sidebar-nav">
            <a href="index.php?action=admin_dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard Overview</a>
            <a href="index.php?action=admin_cars"><i class="fas fa-car"></i> Manage Cars</a>
            <a href="index.php?action=admin_bookings"><i class="fas fa-calendar-alt"></i> Manage Bookings</a>
            <a href="index.php?action=admin_users"><i class="fas fa-users"></i> Manage Users</a>
            <a href="index.php?action=admin_incidents"><i class="fas fa-headset"></i> Manage Incidents</a>
            <a href="index.php?action=admin_callbacks" class="active"><i class="fas fa-phone"></i> Callback Requests</a>
        </div>
        <div class="sidebar-footer">
            <a href="index.php?action=home" class="btn-back">&larr; Back</a>
        </div>
    </div>

    <div class="main-content support-page">
        <div class="top-navbar">
            <h1>Callback Requests</h1>
            <div class="admin-profile">
                <span><?= htmlspecialchars($_SESSION['user']['fullname'] ?? '') ?> (Admin)</span>
            </div>
        </div>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="msg-success"><?= htmlspecialchars($_SESSION['success_message']) ?></div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="msg-error"><?= htmlspecialchars($_SESSION['error_message']) ?></div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <form method="GET" action="index.php" class="admin-filter-bar">
            <input type="hidden" name="action" value="admin_callbacks">

            <?php $statusFilter = (string) ($filters['status'] ?? ''); ?>
            <select name="status" class="form-control">
                <option value="">All Status</option>
                <option value="new" <?= $statusFilter === 'new' ? 'selected' : '' ?>>new</option>
                <option value="called" <?= $statusFilter === 'called' ? 'selected' : '' ?>>called</option>
                <option value="no_answer" <?= $statusFilter === 'no_answer' ? 'selected' : '' ?>>no_answer</option>
                <option value="completed" <?= $statusFilter === 'completed' ? 'selected' : '' ?>>completed</option>
            </select>

            <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars((string) ($filters['date_from'] ?? '')) ?>">
            <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars((string) ($filters['date_to'] ?? '')) ?>">
            <button type="submit" class="btn-submit">Filter</button>
        </form>

        <div class="incident-table-wrap">
            <table class="incident-admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Phone</th>
                        <th>Preferred Time</th>
                        <th>Note</th>
                        <th>Status</th>
                        <th>Incident</th>
                        <th>Created</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($callbacks)): ?>
                        <tr>
                            <td colspan="9">No callback requests found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($callbacks as $callback): ?>
                            <?php $callbackStatus = (string) ($callback['status'] ?? 'new'); ?>
                            <tr>
                                <td>#<?= (int) ($callback['id'] ?? 0) ?></td>
                                <td>
                                    <?= htmlspecialchars((string) ($callback['requester_name'] ?? '')) ?><br>
                                    <small><?= htmlspecialchars((string) ($callback['requester_email'] ?? '')) ?></small>
                                </td>
                                <td><strong><?= htmlspecialchars((string) ($callback['phone_number'] ?? '')) ?></strong></td>
                                <td><?= htmlspecialchars((string) ($callback['preferred_time'] ?? '-')) ?></td>
                                <td><?= nl2br(htmlspecialchars((string) ($callback['note'] ?? '-'))) ?></td>
                                <td>
                                    <span class="status-badge status-<?= htmlspecialchars($callbackStatus) ?>">
                                        <?= htmlspecialchars($callbackStatus) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($callback['incident_id'])): ?>
                                        <a href="index.php?action=admin_incident_detail&id=<?= (int) $callback['incident_id'] ?>" class="btn-book">
                                            <?= htmlspecialchars((string) ($callback['incident_ticket_code'] ?? ('#' . (int) $callback['incident_id']))) ?>
                                        </a>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars((string) ($callback['created_at'] ?? '')) ?></td>
                                <td>
                                    <form method="POST" action="index.php?action=admin_callback_update" class="callback-admin-action-form">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($updateCallbackCsrfToken) ?>">
                                        <input type="hidden" name="callback_id" value="<?= (int) ($callback['id'] ?? 0) ?>">
                                        <select name="status" class="form-control" required>
                                            <option value="new" <?= $callbackStatus === 'new' ? 'selected' : '' ?>>new</option>
                                            <option value="called" <?= $callbackStatus === 'called' ? 'selected' : '' ?>>called</option>
                                            <option value="no_answer" <?= $callbackStatus === 'no_answer' ? 'selected' : '' ?>>no_answer</option>
                                            <option value="completed" <?= $callbackStatus === 'completed' ? 'selected' : '' ?>>completed</option>
                                        </select>
                                        <button type="submit" class="btn-submit">Save</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>