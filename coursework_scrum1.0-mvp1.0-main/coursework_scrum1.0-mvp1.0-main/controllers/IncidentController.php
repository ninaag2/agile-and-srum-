<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../datafunctions/incident_functions.php';

const INCIDENT_ALLOWED_CATEGORIES = ['booking_error', 'vehicle_issue', 'payment', 'app_bug', 'other'];
const INCIDENT_ALLOWED_PRIORITIES = ['low', 'medium', 'high', 'urgent'];
const INCIDENT_ALLOWED_STATUSES = ['new', 'open', 'in_progress', 'pending_user', 'resolved', 'closed'];
const INCIDENT_ALLOWED_CHANNELS = ['web_form', 'phone', 'callback', 'live_chat'];
const CALLBACK_ALLOWED_STATUSES = ['new', 'called', 'no_answer', 'completed'];

function incidentRequireLogin(): void
{
    if (!isset($_SESSION['user']['id'])) {
        $_SESSION['error_message'] = 'Please login to access support.';
        header('Location: index.php?action=login_form');
        exit;
    }
}

function incidentRequireAdmin(): void
{
    incidentRequireLogin();
    if (($_SESSION['user']['role'] ?? '') !== 'admin') {
        $_SESSION['error_message'] = 'Access denied. Admin only.';
        header('Location: index.php?action=home');
        exit;
    }
}

function incidentGetCsrfToken(string $key): string
{
    if (empty($_SESSION['csrf_tokens'][$key])) {
        $_SESSION['csrf_tokens'][$key] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_tokens'][$key];
}

function incidentValidateCsrfToken(string $key, ?string $token): bool
{
    if (empty($token) || empty($_SESSION['csrf_tokens'][$key])) {
        return false;
    }

    return hash_equals($_SESSION['csrf_tokens'][$key], $token);
}

function incidentNormalizeValue(string $value): string
{
    return trim((string) $value);
}

function incidentValidateEnum(string $value, array $allowed, string $default): string
{
    return in_array($value, $allowed, true) ? $value : $default;
}

function incidentFetchUserBookingsForSupport(int $userId): array
{
    $db = getConnection();
    $stmt = $db->prepare('SELECT id, car_id, pickup_datetime, dropoff_datetime, status FROM bookings WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 30');
    $stmt->execute([':user_id' => $userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'support_create':
        incidentRequireLogin();
        $userId = (int) $_SESSION['user']['id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $csrfToken = $_POST['csrf_token'] ?? '';
            if (!incidentValidateCsrfToken('support_create', $csrfToken)) {
                $_SESSION['error_message'] = 'Invalid CSRF token.';
                header('Location: index.php?action=support_create');
                exit;
            }

            $subject = incidentNormalizeValue($_POST['subject'] ?? '');
            $description = incidentNormalizeValue($_POST['description'] ?? '');
            $category = incidentValidateEnum(incidentNormalizeValue($_POST['category'] ?? ''), INCIDENT_ALLOWED_CATEGORIES, 'other');
            $priority = incidentValidateEnum(incidentNormalizeValue($_POST['priority'] ?? ''), INCIDENT_ALLOWED_PRIORITIES, 'medium');
            $bookingIdInput = incidentNormalizeValue($_POST['booking_id'] ?? '');

            $errors = [];
            if ($subject === '' || mb_strlen($subject) > 200) {
                $errors[] = 'Subject is required and must be less than 200 characters.';
            }
            if ($description === '') {
                $errors[] = 'Description is required.';
            }
            if (!in_array($category, INCIDENT_ALLOWED_CATEGORIES, true)) {
                $errors[] = 'Invalid category.';
            }
            if (!in_array($priority, INCIDENT_ALLOWED_PRIORITIES, true)) {
                $errors[] = 'Invalid priority.';
            }

            $bookingId = null;
            if ($bookingIdInput !== '') {
                $bookingId = (int) $bookingIdInput;
                $db = getConnection();
                $checkStmt = $db->prepare('SELECT id FROM bookings WHERE id = :booking_id AND user_id = :user_id LIMIT 1');
                $checkStmt->execute([
                    ':booking_id' => $bookingId,
                    ':user_id' => $userId
                ]);

                if (!$checkStmt->fetch()) {
                    $errors[] = 'Invalid booking selected.';
                }
            }

            if (!empty($errors)) {
                $_SESSION['error_message'] = implode(' ', $errors);
                $_SESSION['support_old'] = [
                    'subject' => $subject,
                    'description' => $description,
                    'category' => $category,
                    'priority' => $priority,
                    'booking_id' => $bookingIdInput
                ];
                header('Location: index.php?action=support_create');
                exit;
            }

            $incidentId = createIncident([
                'ticket_code' => generateTicketCode(),
                'user_id' => $userId,
                'booking_id' => $bookingId,
                'channel' => 'web_form',
                'category' => $category,
                'priority' => $priority,
                'subject' => $subject,
                'description' => $description,
                'status' => 'new'
            ]);

            addIncidentMessage($incidentId, 'system', null, 'Incident created via web form.');

            unset($_SESSION['support_old']);
            $_SESSION['success_message'] = 'Support ticket created successfully.';
            header('Location: index.php?action=support_detail&id=' . $incidentId);
            exit;
        }

        $csrfToken = incidentGetCsrfToken('support_create');
        $bookings = incidentFetchUserBookingsForSupport($userId);
        $old = $_SESSION['support_old'] ?? [];
        unset($_SESSION['support_old']);

        require_once __DIR__ . '/../views/pages/support_create.php';
        exit;

    case 'support_list':
        incidentRequireLogin();
        $userId = (int) $_SESSION['user']['id'];
        $incidents = getIncidentsByUserId($userId);

        require_once __DIR__ . '/../views/pages/support_list.php';
        exit;

    case 'support_detail':
        incidentRequireLogin();
        $userId = (int) $_SESSION['user']['id'];
        $incidentId = (int) ($_GET['id'] ?? 0);

        if ($incidentId <= 0) {
            $_SESSION['error_message'] = 'Invalid ticket id.';
            header('Location: index.php?action=support_list');
            exit;
        }

        $incident = getIncidentByIdForUser($incidentId, $userId);
        if (!$incident) {
            $_SESSION['error_message'] = 'Ticket not found.';
            header('Location: index.php?action=support_list');
            exit;
        }

        $messages = getIncidentMessages($incidentId);
        $messageCsrfToken = incidentGetCsrfToken('support_add_message_' . $incidentId);

        require_once __DIR__ . '/../views/pages/support_detail.php';
        exit;

    case 'support_add_message':
        incidentRequireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=support_list');
            exit;
        }

        $userId = (int) $_SESSION['user']['id'];
        $incidentId = (int) ($_POST['incident_id'] ?? 0);

        if ($incidentId <= 0) {
            $_SESSION['error_message'] = 'Invalid ticket id.';
            header('Location: index.php?action=support_list');
            exit;
        }

        $csrfToken = $_POST['csrf_token'] ?? '';
        $csrfKey = 'support_add_message_' . $incidentId;
        if (!incidentValidateCsrfToken($csrfKey, $csrfToken)) {
            $_SESSION['error_message'] = 'Invalid CSRF token.';
            header('Location: index.php?action=support_detail&id=' . $incidentId);
            exit;
        }

        $incident = getIncidentByIdForUser($incidentId, $userId);
        if (!$incident) {
            $_SESSION['error_message'] = 'Ticket not found.';
            header('Location: index.php?action=support_list');
            exit;
        }

        if (($incident['status'] ?? '') === 'closed') {
            $_SESSION['error_message'] = 'Closed ticket cannot be modified by user.';
            header('Location: index.php?action=support_detail&id=' . $incidentId);
            exit;
        }

        $message = incidentNormalizeValue($_POST['message'] ?? '');
        if ($message === '') {
            $_SESSION['error_message'] = 'Message cannot be empty.';
            header('Location: index.php?action=support_detail&id=' . $incidentId);
            exit;
        }

        addIncidentMessage($incidentId, 'user', $userId, $message);
        $_SESSION['success_message'] = 'Message sent successfully.';
        header('Location: index.php?action=support_detail&id=' . $incidentId);
        exit;

    case 'callback_request':
        incidentRequireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=support_create');
            exit;
        }

        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!incidentValidateCsrfToken('callback_request', $csrfToken)) {
            $_SESSION['error_message'] = 'Invalid CSRF token.';
            header('Location: index.php?action=support_create');
            exit;
        }

        $userId = (int) $_SESSION['user']['id'];
        $note = incidentNormalizeValue($_POST['note'] ?? '');

        if ($note === '') {
            $_SESSION['error_message'] = 'Please provide note content for callback.';
            header('Location: index.php?action=support_create');
            exit;
        }

        $db = getConnection();
        $userPhoneStmt = $db->prepare('SELECT phone FROM users WHERE id = :user_id LIMIT 1');
        $userPhoneStmt->execute([':user_id' => $userId]);
        $phoneNumber = incidentNormalizeValue((string) ($userPhoneStmt->fetchColumn() ?? ''));

        $preferredTime = date('Y-m-d H:i:s');

        if ($phoneNumber === '' || !preg_match('/^[0-9+\-\s]{8,20}$/', $phoneNumber)) {
            $_SESSION['error_message'] = 'Your profile phone number is invalid. Please update your account phone first.';
            header('Location: index.php?action=support_create');
            exit;
        }

        $incidentId = createIncident([
            'ticket_code' => generateTicketCode(),
            'user_id' => $userId,
            'booking_id' => null,
            'channel' => 'callback',
            'category' => 'other',
            'priority' => 'medium',
            'subject' => 'Callback request from customer',
            'description' => $note,
            'status' => 'new'
        ]);

        addIncidentMessage($incidentId, 'system', null, 'Callback request linked to this incident.');

        createCallbackRequest([
            'user_id' => $userId,
            'incident_id' => $incidentId,
            'phone_number' => $phoneNumber,
            'note' => $note,
            'preferred_time' => $preferredTime,
            'status' => 'new'
        ]);

        $_SESSION['success_message'] = 'Callback request submitted. Our support will call you soon.';
        header('Location: index.php?action=support_create');
        exit;

    case 'admin_incidents':
        incidentRequireAdmin();

        $filters = [
            'status' => incidentNormalizeValue($_GET['status'] ?? ''),
            'priority' => incidentNormalizeValue($_GET['priority'] ?? ''),
            'channel' => incidentNormalizeValue($_GET['channel'] ?? ''),
            'date_from' => incidentNormalizeValue($_GET['date_from'] ?? ''),
            'date_to' => incidentNormalizeValue($_GET['date_to'] ?? '')
        ];

        if (!in_array($filters['status'], INCIDENT_ALLOWED_STATUSES, true)) {
            $filters['status'] = '';
        }
        if (!in_array($filters['priority'], INCIDENT_ALLOWED_PRIORITIES, true)) {
            $filters['priority'] = '';
        }
        if (!in_array($filters['channel'], INCIDENT_ALLOWED_CHANNELS, true)) {
            $filters['channel'] = '';
        }

        $incidents = getAllIncidentsForAdmin($filters);
        $stats = getIncidentStatsForAdmin();

        require_once __DIR__ . '/../views/admin/admin_incidents.php';
        exit;

    case 'admin_incident_detail':
        incidentRequireAdmin();

        $incidentId = (int) ($_GET['id'] ?? 0);
        if ($incidentId <= 0) {
            $_SESSION['error_message'] = 'Invalid ticket id.';
            header('Location: index.php?action=admin_incidents');
            exit;
        }

        $incident = getIncidentByIdForAdmin($incidentId);
        if (!$incident) {
            $_SESSION['error_message'] = 'Ticket not found.';
            header('Location: index.php?action=admin_incidents');
            exit;
        }

        $messages = getIncidentMessages($incidentId);
        $updateCsrfToken = incidentGetCsrfToken('admin_incident_update_' . $incidentId);
        $messageCsrfToken = incidentGetCsrfToken('admin_incident_add_message_' . $incidentId);

        $db = getConnection();
        $adminStmt = $db->prepare("SELECT id, fullname FROM users WHERE role = 'admin' ORDER BY fullname ASC");
        $adminStmt->execute();
        $admins = $adminStmt->fetchAll(PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../views/admin/admin_incident_detail.php';
        exit;

    case 'admin_incident_update':
        incidentRequireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=admin_incidents');
            exit;
        }

        $incidentId = (int) ($_POST['incident_id'] ?? 0);
        if ($incidentId <= 0) {
            $_SESSION['error_message'] = 'Invalid ticket id.';
            header('Location: index.php?action=admin_incidents');
            exit;
        }

        $csrfToken = $_POST['csrf_token'] ?? '';
        $csrfKey = 'admin_incident_update_' . $incidentId;
        if (!incidentValidateCsrfToken($csrfKey, $csrfToken)) {
            $_SESSION['error_message'] = 'Invalid CSRF token.';
            header('Location: index.php?action=admin_incident_detail&id=' . $incidentId);
            exit;
        }

        $incident = getIncidentByIdForAdmin($incidentId);
        if (!$incident) {
            $_SESSION['error_message'] = 'Ticket not found.';
            header('Location: index.php?action=admin_incidents');
            exit;
        }

        $status = incidentValidateEnum(incidentNormalizeValue($_POST['status'] ?? ''), INCIDENT_ALLOWED_STATUSES, (string) $incident['status']);
        $priority = incidentValidateEnum(incidentNormalizeValue($_POST['priority'] ?? ''), INCIDENT_ALLOWED_PRIORITIES, (string) $incident['priority']);
        $assignedAdminRaw = incidentNormalizeValue($_POST['assigned_admin_id'] ?? '');
        $assignedAdminId = $assignedAdminRaw === '' ? null : (int) $assignedAdminRaw;

        $updated = updateIncidentByAdmin($incidentId, [
            'status' => $status,
            'priority' => $priority,
            'assigned_admin_id' => $assignedAdminId
        ]);

        if ($updated) {
            if ($status !== (string) $incident['status']) {
                $systemMsg = 'Status changed from ' . $incident['status'] . ' to ' . $status . '.';
                addIncidentMessage($incidentId, 'system', null, $systemMsg);
            }
            $_SESSION['success_message'] = 'Ticket updated successfully.';
        } else {
            $_SESSION['error_message'] = 'Failed to update ticket.';
        }

        header('Location: index.php?action=admin_incident_detail&id=' . $incidentId);
        exit;

    case 'admin_incident_add_message':
        incidentRequireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=admin_incidents');
            exit;
        }

        $incidentId = (int) ($_POST['incident_id'] ?? 0);
        if ($incidentId <= 0) {
            $_SESSION['error_message'] = 'Invalid ticket id.';
            header('Location: index.php?action=admin_incidents');
            exit;
        }

        $csrfToken = $_POST['csrf_token'] ?? '';
        $csrfKey = 'admin_incident_add_message_' . $incidentId;
        if (!incidentValidateCsrfToken($csrfKey, $csrfToken)) {
            $_SESSION['error_message'] = 'Invalid CSRF token.';
            header('Location: index.php?action=admin_incident_detail&id=' . $incidentId);
            exit;
        }

        $incident = getIncidentByIdForAdmin($incidentId);
        if (!$incident) {
            $_SESSION['error_message'] = 'Ticket not found.';
            header('Location: index.php?action=admin_incidents');
            exit;
        }

        $message = incidentNormalizeValue($_POST['message'] ?? '');
        if ($message === '') {
            $_SESSION['error_message'] = 'Message cannot be empty.';
            header('Location: index.php?action=admin_incident_detail&id=' . $incidentId);
            exit;
        }

        addIncidentMessage($incidentId, 'admin', (int) $_SESSION['user']['id'], $message);
        $_SESSION['success_message'] = 'Admin message added.';
        header('Location: index.php?action=admin_incident_detail&id=' . $incidentId);
        exit;

    case 'admin_callbacks':
        incidentRequireAdmin();

        $filters = [
            'status' => incidentNormalizeValue($_GET['status'] ?? ''),
            'date_from' => incidentNormalizeValue($_GET['date_from'] ?? ''),
            'date_to' => incidentNormalizeValue($_GET['date_to'] ?? '')
        ];

        if (!in_array($filters['status'], CALLBACK_ALLOWED_STATUSES, true)) {
            $filters['status'] = '';
        }

        $callbacks = getAllCallbackRequestsForAdmin($filters);
        $updateCallbackCsrfToken = incidentGetCsrfToken('admin_callback_update');

        require_once __DIR__ . '/../views/admin/admin_callbacks.php';
        exit;

    case 'admin_callback_update':
        incidentRequireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=admin_callbacks');
            exit;
        }

        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!incidentValidateCsrfToken('admin_callback_update', $csrfToken)) {
            $_SESSION['error_message'] = 'Invalid CSRF token.';
            header('Location: index.php?action=admin_callbacks');
            exit;
        }

        $callbackId = (int) ($_POST['callback_id'] ?? 0);
        if ($callbackId <= 0) {
            $_SESSION['error_message'] = 'Invalid callback request id.';
            header('Location: index.php?action=admin_callbacks');
            exit;
        }

        $callback = getCallbackRequestByIdForAdmin($callbackId);
        if (!$callback) {
            $_SESSION['error_message'] = 'Callback request not found.';
            header('Location: index.php?action=admin_callbacks');
            exit;
        }

        $newStatus = incidentValidateEnum(
            incidentNormalizeValue($_POST['status'] ?? ''),
            CALLBACK_ALLOWED_STATUSES,
            (string) ($callback['status'] ?? 'new')
        );

        if (updateCallbackRequestStatusByAdmin($callbackId, $newStatus)) {
            if (!empty($callback['incident_id']) && $newStatus !== (string) ($callback['status'] ?? '')) {
                $systemMessage = 'Callback status changed from ' . $callback['status'] . ' to ' . $newStatus . '.';
                addIncidentMessage((int) $callback['incident_id'], 'system', null, $systemMessage);
            }
            $_SESSION['success_message'] = 'Callback status updated successfully.';
        } else {
            $_SESSION['error_message'] = 'Failed to update callback status.';
        }

        header('Location: index.php?action=admin_callbacks');
        exit;

    default:
        http_response_code(404);
        echo 'Support action not found.';
        exit;
}
