<?php

require_once __DIR__ . '/../config/database.php';

function generateTicketCode(): string
{
    $db = getConnection();
    $year = date('Y');

    for ($i = 0; $i < 10; $i++) {
        $random = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $ticketCode = 'INC-' . $year . '-' . $random;

        $stmt = $db->prepare('SELECT id FROM incidents WHERE ticket_code = :ticket_code LIMIT 1');
        $stmt->execute([':ticket_code' => $ticketCode]);

        if (!$stmt->fetch()) {
            return $ticketCode;
        }
    }

    return 'INC-' . $year . '-' . str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

function createIncident(array $data): int
{
    $db = getConnection();

    $sql = 'INSERT INTO incidents (ticket_code, user_id, booking_id, channel, category, priority, subject, description, status)
            VALUES (:ticket_code, :user_id, :booking_id, :channel, :category, :priority, :subject, :description, :status)';

    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':ticket_code' => $data['ticket_code'] ?? generateTicketCode(),
        ':user_id' => (int) ($data['user_id'] ?? 0),
        ':booking_id' => !empty($data['booking_id']) ? (int) $data['booking_id'] : null,
        ':channel' => $data['channel'] ?? 'web_form',
        ':category' => $data['category'] ?? 'other',
        ':priority' => $data['priority'] ?? 'medium',
        ':subject' => $data['subject'] ?? '',
        ':description' => $data['description'] ?? '',
        ':status' => $data['status'] ?? 'new'
    ]);

    return (int) $db->lastInsertId();
}

function getIncidentsByUserId(int $userId): array
{
    $db = getConnection();

    $sql = 'SELECT i.*, u.fullname AS assigned_admin_name
            FROM incidents i
            LEFT JOIN users u ON u.id = i.assigned_admin_id
            WHERE i.user_id = :user_id
            ORDER BY i.created_at DESC';

    $stmt = $db->prepare($sql);
    $stmt->execute([':user_id' => $userId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getIncidentByIdForUser(int $incidentId, int $userId)
{
    $db = getConnection();

    $sql = 'SELECT i.*, u.fullname AS assigned_admin_name
            FROM incidents i
            LEFT JOIN users u ON u.id = i.assigned_admin_id
            WHERE i.id = :incident_id AND i.user_id = :user_id
            LIMIT 1';

    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':incident_id' => $incidentId,
        ':user_id' => $userId
    ]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getIncidentByIdForAdmin(int $incidentId)
{
    $db = getConnection();

    $sql = 'SELECT i.*, requester.fullname AS requester_name, requester.email AS requester_email,
                   assignee.fullname AS assigned_admin_name
            FROM incidents i
            INNER JOIN users requester ON requester.id = i.user_id
            LEFT JOIN users assignee ON assignee.id = i.assigned_admin_id
            WHERE i.id = :incident_id
            LIMIT 1';

    $stmt = $db->prepare($sql);
    $stmt->execute([':incident_id' => $incidentId]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function addIncidentMessage(int $incidentId, string $senderType, ?int $senderId, string $message): bool
{
    $db = getConnection();

    $sql = 'INSERT INTO incident_messages (incident_id, sender_type, sender_id, message)
            VALUES (:incident_id, :sender_type, :sender_id, :message)';

    $stmt = $db->prepare($sql);

    return $stmt->execute([
        ':incident_id' => $incidentId,
        ':sender_type' => $senderType,
        ':sender_id' => $senderId,
        ':message' => $message
    ]);
}

function getIncidentMessages(int $incidentId): array
{
    $db = getConnection();

    $sql = 'SELECT im.*, u.fullname AS sender_name
            FROM incident_messages im
            LEFT JOIN users u ON u.id = im.sender_id
            WHERE im.incident_id = :incident_id
            ORDER BY im.created_at ASC, im.id ASC';

    $stmt = $db->prepare($sql);
    $stmt->execute([':incident_id' => $incidentId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function createCallbackRequest(array $data): int
{
    $db = getConnection();

    $sql = 'INSERT INTO callback_requests (user_id, incident_id, phone_number, note, preferred_time, status)
            VALUES (:user_id, :incident_id, :phone_number, :note, :preferred_time, :status)';

    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':user_id' => (int) ($data['user_id'] ?? 0),
        ':incident_id' => !empty($data['incident_id']) ? (int) $data['incident_id'] : null,
        ':phone_number' => $data['phone_number'] ?? '',
        ':note' => $data['note'] ?? null,
        ':preferred_time' => $data['preferred_time'] ?? null,
        ':status' => $data['status'] ?? 'new'
    ]);

    return (int) $db->lastInsertId();
}

function getAllCallbackRequestsForAdmin(array $filters = []): array
{
    $db = getConnection();

    $sql = 'SELECT c.*, u.fullname AS requester_name, u.email AS requester_email,
                   i.ticket_code AS incident_ticket_code, i.subject AS incident_subject
            FROM callback_requests c
            INNER JOIN users u ON u.id = c.user_id
            LEFT JOIN incidents i ON i.id = c.incident_id
            WHERE 1=1';

    $params = [];

    if (!empty($filters['status'])) {
        $sql .= ' AND c.status = :status';
        $params[':status'] = $filters['status'];
    }

    if (!empty($filters['date_from'])) {
        $sql .= ' AND DATE(c.created_at) >= :date_from';
        $params[':date_from'] = $filters['date_from'];
    }

    if (!empty($filters['date_to'])) {
        $sql .= ' AND DATE(c.created_at) <= :date_to';
        $params[':date_to'] = $filters['date_to'];
    }

    $sql .= ' ORDER BY c.created_at DESC';

    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getCallbackRequestByIdForAdmin(int $callbackId)
{
    $db = getConnection();

    $sql = 'SELECT c.*, u.fullname AS requester_name, u.email AS requester_email
            FROM callback_requests c
            INNER JOIN users u ON u.id = c.user_id
            WHERE c.id = :callback_id
            LIMIT 1';

    $stmt = $db->prepare($sql);
    $stmt->execute([':callback_id' => $callbackId]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function updateCallbackRequestStatusByAdmin(int $callbackId, string $status): bool
{
    $db = getConnection();

    $stmt = $db->prepare('UPDATE callback_requests SET status = :status WHERE id = :callback_id');

    return $stmt->execute([
        ':status' => $status,
        ':callback_id' => $callbackId
    ]);
}

function getAllIncidentsForAdmin(array $filters = []): array
{
    $db = getConnection();

    $sql = 'SELECT i.*, requester.fullname AS requester_name, requester.email AS requester_email,
                   assignee.fullname AS assigned_admin_name
            FROM incidents i
            INNER JOIN users requester ON requester.id = i.user_id
            LEFT JOIN users assignee ON assignee.id = i.assigned_admin_id
            WHERE 1=1';

    $params = [];

    if (!empty($filters['status'])) {
        $sql .= ' AND i.status = :status';
        $params[':status'] = $filters['status'];
    }

    if (!empty($filters['priority'])) {
        $sql .= ' AND i.priority = :priority';
        $params[':priority'] = $filters['priority'];
    }

    if (!empty($filters['channel'])) {
        $sql .= ' AND i.channel = :channel';
        $params[':channel'] = $filters['channel'];
    }

    if (!empty($filters['date_from'])) {
        $sql .= ' AND DATE(i.created_at) >= :date_from';
        $params[':date_from'] = $filters['date_from'];
    }

    if (!empty($filters['date_to'])) {
        $sql .= ' AND DATE(i.created_at) <= :date_to';
        $params[':date_to'] = $filters['date_to'];
    }

    $sql .= ' ORDER BY i.created_at DESC';

    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function updateIncidentByAdmin(int $incidentId, array $payload): bool
{
    $db = getConnection();

    $currentStmt = $db->prepare('SELECT status, priority, assigned_admin_id, resolved_at FROM incidents WHERE id = :incident_id LIMIT 1');
    $currentStmt->execute([':incident_id' => $incidentId]);
    $current = $currentStmt->fetch(PDO::FETCH_ASSOC);

    if (!$current) {
        return false;
    }

    $newStatus = $payload['status'] ?? $current['status'];
    $newPriority = $payload['priority'] ?? $current['priority'];
    $newAssignedAdminId = array_key_exists('assigned_admin_id', $payload)
        ? ($payload['assigned_admin_id'] !== null && $payload['assigned_admin_id'] !== '' ? (int) $payload['assigned_admin_id'] : null)
        : $current['assigned_admin_id'];

    $resolvedAt = $current['resolved_at'];

    if ($newStatus === 'resolved') {
        $resolvedAt = date('Y-m-d H:i:s');
    } elseif (in_array($current['status'], ['resolved', 'closed'], true) && !in_array($newStatus, ['resolved', 'closed'], true)) {
        $resolvedAt = null;
    }

    $updateSql = 'UPDATE incidents
                  SET status = :status,
                      priority = :priority,
                      assigned_admin_id = :assigned_admin_id,
                      resolved_at = :resolved_at
                  WHERE id = :incident_id';

    $updateStmt = $db->prepare($updateSql);

    return $updateStmt->execute([
        ':status' => $newStatus,
        ':priority' => $newPriority,
        ':assigned_admin_id' => $newAssignedAdminId,
        ':resolved_at' => $resolvedAt,
        ':incident_id' => $incidentId
    ]);
}

function getIncidentStatsForAdmin(): array
{
    $db = getConnection();

    $stmt = $db->prepare("SELECT status, COUNT(*) AS total FROM incidents GROUP BY status");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stats = [
        'total' => 0,
        'new' => 0,
        'open' => 0,
        'in_progress' => 0,
        'pending_user' => 0,
        'resolved' => 0,
        'closed' => 0
    ];

    foreach ($rows as $row) {
        $status = (string) ($row['status'] ?? '');
        $total = (int) ($row['total'] ?? 0);
        $stats['total'] += $total;
        if (array_key_exists($status, $stats)) {
            $stats[$status] = $total;
        }
    }

    return $stats;
}
