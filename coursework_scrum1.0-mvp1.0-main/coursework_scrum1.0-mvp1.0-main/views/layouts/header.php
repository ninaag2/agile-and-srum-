<?php
$currentAction = $_GET['action'] ?? 'home';
$currentRequestUri = $_SERVER['REQUEST_URI'] ?? 'index.php?action=home';

$isLoggedIn = isset($_SESSION['user']);
$isAdmin = $isLoggedIn && (($_SESSION['user']['role'] ?? '') === 'admin');

function headerNavActiveClass(array $actions, string $currentAction): string
{
    return in_array($currentAction, $actions, true) ? 'active' : '';
}
?>
<div class="navbar">
    <div class="logo"><strong>Born Car</strong></div>
    <div class="nav-links">
        <a href="index.php?action=home" class="<?= headerNavActiveClass(['home'], $currentAction) ?>">Home</a>
        <a href="index.php?action=browse_cars" class="<?= headerNavActiveClass(['browse_cars', 'car_detail', 'book_form', 'book_preview', 'payment_gateway', 'process_payment', 'booking_success', 'edit_booking', 'update_booking'], $currentAction) ?>">Browse Cars</a>

        <?php if ($isLoggedIn): ?>
            <a href="index.php?action=my_bookings" class="<?= headerNavActiveClass(['my_bookings'], $currentAction) ?>">My Bookings</a>
            <a href="index.php?action=support_create" class="<?= headerNavActiveClass(['support_create', 'support_list', 'support_detail'], $currentAction) ?>">Need Help?</a>
            <?php if ($isAdmin): ?>
                <a href="index.php?action=admin_dashboard" class="<?= headerNavActiveClass(['admin_dashboard', 'admin_cars', 'admin_delete_car', 'admin_add_car', 'admin_edit_car', 'admin_users', 'admin_edit_user', 'admin_delete_user', 'admin_bookings', 'admin_update_booking', 'admin_incidents', 'admin_incident_detail', 'admin_incident_update', 'admin_incident_add_message', 'admin_callbacks', 'admin_callback_update'], $currentAction) ?>">Admin Panel</a>
            <?php endif; ?>
            <a href="index.php?action=logout&return_to=<?= urlencode($currentRequestUri) ?>" class="nav-link-accent">Logout</a>
        <?php else: ?>
            <a href="#" data-auth-open="login">Login</a>
        <?php endif; ?>
    </div>
</div>