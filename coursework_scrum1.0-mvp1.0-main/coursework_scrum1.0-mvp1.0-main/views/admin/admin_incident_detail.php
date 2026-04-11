<?php
$incident = $incident ?? [];
$messages = $messages ?? [];
$admins = $admins ?? [];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Incident Detail - Born Car</title>
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
            <a href="index.php?action=admin_dashboard" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard Overview</a>
            <a href="index.php?action=admin_cars"><i class="fas fa-car"></i> Manage Cars</a>
            <a href="index.php?action=admin_bookings"><i class="fas fa-calendar-alt"></i> Manage Bookings</a>
            <a href="index.php?action=admin_users"><i class="fas fa-users"></i> Manage Users</a>
            <a href="index.php?action=admin_incidents"><i class="fas fa-headset"></i> Manage Incidents</a>
            <a href="index.php?action=admin_callbacks"><i class="fas fa-phone"></i> Callback Requests</a>
        </div>
        <div class="sidebar-footer">
            <a href="index.php?action=admin_incidents" class="btn-back">&larr; Back</a>
        </div>
    </div>

    <div class="main-content support-page">
        <div class="top-navbar">
            <h1>Ticket Detail: <?= htmlspecialchars((string) ($incident['ticket_code'] ?? '')) ?></h1>
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

        <div class="incident-card">
            <h3><?= htmlspecialchars((string) ($incident['subject'] ?? '')) ?></h3>
            <p><strong>Requester:</strong> <?= htmlspecialchars((string) ($incident['requester_name'] ?? '')) ?> (<?= htmlspecialchars((string) ($incident['requester_email'] ?? '')) ?>)</p>
            <p><strong>Channel:</strong> <?= htmlspecialchars((string) ($incident['channel'] ?? 'web_form')) ?></p>
            <p><strong>Category:</strong> <?= htmlspecialchars((string) ($incident['category'] ?? '')) ?></p>
            <p><strong>Description:</strong> <?= nl2br(htmlspecialchars((string) ($incident['description'] ?? ''))) ?></p>
        </div>

        <div class="incident-card">
            <h3>Update Ticket</h3>
            <form action="index.php?action=admin_incident_update" method="POST" class="support-form" id="adminIncidentUpdateForm">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($updateCsrfToken) ?>">
                <input type="hidden" name="incident_id" value="<?= (int) ($incident['id'] ?? 0) ?>">

                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="form-control" required>
                        <?php $status = (string) ($incident['status'] ?? 'new'); ?>
                        <option value="new" <?= $status === 'new' ? 'selected' : '' ?>>new</option>
                        <option value="open" <?= $status === 'open' ? 'selected' : '' ?>>open</option>
                        <option value="in_progress" <?= $status === 'in_progress' ? 'selected' : '' ?>>in_progress</option>
                        <option value="pending_user" <?= $status === 'pending_user' ? 'selected' : '' ?>>pending_user</option>
                        <option value="resolved" <?= $status === 'resolved' ? 'selected' : '' ?>>resolved</option>
                        <option value="closed" <?= $status === 'closed' ? 'selected' : '' ?>>closed</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="priority">Priority</label>
                    <select id="priority" name="priority" class="form-control" required>
                        <?php $priority = (string) ($incident['priority'] ?? 'medium'); ?>
                        <option value="low" <?= $priority === 'low' ? 'selected' : '' ?>>low</option>
                        <option value="medium" <?= $priority === 'medium' ? 'selected' : '' ?>>medium</option>
                        <option value="high" <?= $priority === 'high' ? 'selected' : '' ?>>high</option>
                        <option value="urgent" <?= $priority === 'urgent' ? 'selected' : '' ?>>urgent</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="assigned_admin_id">Assigned Admin</label>
                    <select id="assigned_admin_id" name="assigned_admin_id" class="form-control">
                        <option value="">Unassigned</option>
                        <?php $assignedAdmin = (string) ($incident['assigned_admin_id'] ?? ''); ?>
                        <?php foreach ($admins as $admin): ?>
                            <?php $adminId = (string) ($admin['id'] ?? ''); ?>
                            <option value="<?= htmlspecialchars($adminId) ?>" <?= $assignedAdmin === $adminId ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string) ($admin['fullname'] ?? '')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn-submit">Save Changes</button>
            </form>
        </div>

        <div class="support-thread" id="supportThread">
            <h3>Message Timeline (US34 foundation)</h3>
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

        <div class="incident-card">
            <h3>Add Admin Message</h3>
            <form action="index.php?action=admin_incident_add_message" method="POST" class="support-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($messageCsrfToken) ?>">
                <input type="hidden" name="incident_id" value="<?= (int) ($incident['id'] ?? 0) ?>">

                <div class="form-group">
                    <label for="admin_message">Message</label>
                    <textarea id="admin_message" name="message" class="form-control" rows="4" required></textarea>
                </div>

                <button type="submit" class="btn-submit">Send Admin Message</button>
            </form>
        </div>
    </div>

    <script>
        (function() {
            var updateForm = document.getElementById('adminIncidentUpdateForm');
            var statusSelect = document.getElementById('status');

            if (updateForm && statusSelect) {
                updateForm.addEventListener('submit', function(e) {
                    if (statusSelect.value === 'closed') {
                        var ok = window.confirm('Are you sure you want to close this ticket?');
                        if (!ok) {
                            e.preventDefault();
                        }
                    }
                });
            }
        })();

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