<?php
$incidents = $incidents ?? [];
$stats = $stats ?? [];
$filters = $filters ?? [];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Incident Management - Born Car</title>
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
            <a href="index.php?action=admin_incidents" class="active"><i class="fas fa-headset"></i> Manage Incidents</a>
            <a href="index.php?action=admin_callbacks"><i class="fas fa-phone"></i> Callback Requests</a>
        </div>
        <div class="sidebar-footer">
            <a href="index.php?action=home" class="btn-back">&larr; Back</a>
        </div>
    </div>

    <div class="main-content support-page">
        <div class="top-navbar">
            <h1>US38 Incident and Problem Management</h1>
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

        <div class="support-list-grid admin-stats-grid">
            <div class="incident-card"><strong>Total</strong>
                <p><?= (int) ($stats['total'] ?? 0) ?></p>
            </div>
            <div class="incident-card"><strong>New</strong>
                <p><?= (int) ($stats['new'] ?? 0) ?></p>
            </div>
            <div class="incident-card"><strong>In Progress</strong>
                <p><?= (int) ($stats['in_progress'] ?? 0) ?></p>
            </div>
            <div class="incident-card"><strong>Resolved</strong>
                <p><?= (int) ($stats['resolved'] ?? 0) ?></p>
            </div>
            <div class="incident-card"><strong>Closed</strong>
                <p><?= (int) ($stats['closed'] ?? 0) ?></p>
            </div>
        </div>

        <form method="GET" action="index.php" class="admin-filter-bar">
            <input type="hidden" name="action" value="admin_incidents">

            <select name="status" class="form-control">
                <option value="">All Status</option>
                <?php $statusFilter = (string) ($filters['status'] ?? ''); ?>
                <option value="new" <?= $statusFilter === 'new' ? 'selected' : '' ?>>new</option>
                <option value="open" <?= $statusFilter === 'open' ? 'selected' : '' ?>>open</option>
                <option value="in_progress" <?= $statusFilter === 'in_progress' ? 'selected' : '' ?>>in_progress</option>
                <option value="pending_user" <?= $statusFilter === 'pending_user' ? 'selected' : '' ?>>pending_user</option>
                <option value="resolved" <?= $statusFilter === 'resolved' ? 'selected' : '' ?>>resolved</option>
                <option value="closed" <?= $statusFilter === 'closed' ? 'selected' : '' ?>>closed</option>
            </select>

            <select name="priority" class="form-control">
                <option value="">All Priority</option>
                <?php $priorityFilter = (string) ($filters['priority'] ?? ''); ?>
                <option value="low" <?= $priorityFilter === 'low' ? 'selected' : '' ?>>low</option>
                <option value="medium" <?= $priorityFilter === 'medium' ? 'selected' : '' ?>>medium</option>
                <option value="high" <?= $priorityFilter === 'high' ? 'selected' : '' ?>>high</option>
                <option value="urgent" <?= $priorityFilter === 'urgent' ? 'selected' : '' ?>>urgent</option>
            </select>

            <select name="channel" class="form-control">
                <option value="">All Channel</option>
                <?php $channelFilter = (string) ($filters['channel'] ?? ''); ?>
                <option value="web_form" <?= $channelFilter === 'web_form' ? 'selected' : '' ?>>web_form</option>
                <option value="phone" <?= $channelFilter === 'phone' ? 'selected' : '' ?>>phone</option>
                <option value="callback" <?= $channelFilter === 'callback' ? 'selected' : '' ?>>callback</option>
                <option value="live_chat" <?= $channelFilter === 'live_chat' ? 'selected' : '' ?>>live_chat</option>
            </select>

            <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars((string) ($filters['date_from'] ?? '')) ?>">
            <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars((string) ($filters['date_to'] ?? '')) ?>">

            <button type="submit" class="btn-submit">Filter</button>
        </form>

        <div class="incident-table-wrap">
            <table class="incident-admin-table">
                <thead>
                    <tr>
                        <th>Ticket</th>
                        <th>Requester</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Channel</th>
                        <th>Assigned</th>
                        <th>Created</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($incidents)): ?>
                        <tr>
                            <td colspan="9">No incidents found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($incidents as $incident): ?>
                            <tr>
                                <td><?= htmlspecialchars((string) ($incident['ticket_code'] ?? '')) ?></td>
                                <td><?= htmlspecialchars((string) ($incident['requester_name'] ?? '')) ?></td>
                                <td><?= htmlspecialchars((string) ($incident['subject'] ?? '')) ?></td>
                                <td>
                                    <span class="status-badge status-<?= htmlspecialchars((string) ($incident['status'] ?? 'new')) ?>">
                                        <?= htmlspecialchars((string) ($incident['status'] ?? 'new')) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="priority-badge priority-<?= htmlspecialchars((string) ($incident['priority'] ?? 'medium')) ?>">
                                        <?= htmlspecialchars((string) ($incident['priority'] ?? 'medium')) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars((string) ($incident['channel'] ?? 'web_form')) ?></td>
                                <td><?= htmlspecialchars((string) ($incident['assigned_admin_name'] ?? 'Unassigned')) ?></td>
                                <td><?= htmlspecialchars((string) ($incident['created_at'] ?? '')) ?></td>
                                <td>
                                    <a class="btn-book" href="index.php?action=admin_incident_detail&id=<?= (int) ($incident['id'] ?? 0) ?>">Manage</a>
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