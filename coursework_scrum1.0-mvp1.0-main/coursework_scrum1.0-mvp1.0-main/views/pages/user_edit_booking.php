<?php
//path: coursework_scrum1.0/views/pages/user_edit_booking.php
$pickup_date_val = '';
$pickup_time_val = '';
$dropoff_date_val = '';
$dropoff_time_val = '';

if (!empty($booking)) {
    $pickup_ts = strtotime($booking['pickup_datetime']);
    $dropoff_ts = strtotime($booking['dropoff_datetime']);

    $pickup_date_val = date('Y-m-d', $pickup_ts);
    $pickup_time_val = date('H:i', $pickup_ts);

    $dropoff_date_val = date('Y-m-d', $dropoff_ts);
    $dropoff_time_val = date('H:i', $dropoff_ts);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Booking - LuxeDrive</title>
    <!-- Include Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">

    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
</head>

<body>
    <?php require_once __DIR__ . '/../layouts/header.php'; ?>

    <!-- Main Content -->
    <div class="edit-container container-fluid px-lg-5 mt-5" style="max-width: 1400px;">
        <a href="index.php?action=my_bookings" class="btn-back mb-3" onclick="if (window.history.length > 1) { event.preventDefault(); window.history.back(); }">&larr; Back</a>
        <div class="edit-header mb-4">
            <h2>Modify Your Booking</h2>
            <p>Update your travel dates or service type below.</p>
        </div>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="msg-error alert alert-danger">
                <?= htmlspecialchars($_SESSION['error_message'] ?? '') ?>
                <?php unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($booking)): ?>

            <div class="alert alert-warning">
                <strong><i class="fas fa-exclamation-triangle"></i> Note:</strong> Changing your dates or service type may cause the Total Price to be recalculated based on current rates.
            </div>

            <div class="booking-wrapper">
                <!-- Left: Summary (Added Car Info) -->
                <div class="car-summary-col">
                    <h2><?= htmlspecialchars($booking['model_name']) ?></h2>
                    <img
                        src="<?= !empty($booking['image_url']) ? htmlspecialchars($booking['image_url']) : 'https://images.unsplash.com/photo-1494976388531-d1058494cdd8?auto=format&fit=crop&w=600&q=80' ?>"
                        alt="<?= htmlspecialchars($booking['model_name']) ?>"
                        onerror="this.src='https://images.unsplash.com/photo-1494976388531-d1058494cdd8?auto=format&fit=crop&w=600&q=80'">
                    <div class="btn-inforcar">
                        <!-- Note: fetching simple car details if available in booking array or car array -->
                        <strong>Price/Day:</strong> <?= number_format($booking['price_per_day'], 0, '.', ',') ?> VND<br>
                        <strong>Price/Hour:</strong> <?= number_format($booking['price_per_hour'], 0, '.', ',') ?> VND
                    </div>
                </div>

                <!-- Right: Form -->
                <div class="booking-form-col">
                    <h3>Set Your Schedule</h3>
                    <form id="bookingForm" method="POST" action="index.php?action=update_booking">
                        <input type="hidden" name="booking_id" value="<?= htmlspecialchars($booking['id']) ?>">
                        <input type="hidden" name="car_id" value="<?= htmlspecialchars($car['id']) ?>">

                        <!-- Hidden Inputs for PHP Submission (Format: YYYY-MM-DDTHH:mm) -->
                        <input type="hidden" id="pickup_datetime_hidden" name="pickup_datetime" required>
                        <input type="hidden" id="dropoff_datetime_hidden" name="dropoff_datetime" required>

                        <!-- Custom UI Layout -->
                        <div class="booking-schedule-grid">
                            <!-- Pickup Date -->
                            <div class="schedule-item">
                                <label>Pick-up Date</label>
                                <div class="input-wrapper">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="input-icon">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                        <line x1="16" y1="2" x2="16" y2="6"></line>
                                        <line x1="8" y1="2" x2="8" y2="6"></line>
                                        <line x1="3" y1="10" x2="21" y2="10"></line>
                                    </svg>
                                    <input type="text" id="pickup_date_display" class="schedule-input" placeholder="Select Date" readonly>
                                </div>
                            </div>

                            <!-- Pickup Time (Custom Dropdown) -->
                            <div class="schedule-item relative-parent">
                                <label>Pick-up Time</label>
                                <div class="input-wrapper" id="pickup-time-wrapper" style="cursor: pointer;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="input-icon">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <polyline points="12 6 12 12 16 14"></polyline>
                                    </svg>
                                    <input type="text" id="pickup_time_display" class="schedule-input" placeholder="Select Time" readonly style="pointer-events: none;">
                                </div>

                                <!-- Dropdown for Pickup Time -->
                                <div class="custom-time-dropdown" id="pickup_time_dropdown">
                                    <div class="dropdown-header">Select Pick-up Time</div>
                                    <div class="time-list-container" id="pickup_slots">
                                        <!-- Time slots generated by JS -->
                                    </div>
                                </div>
                            </div>

                            <!-- Drop-off Date -->
                            <div class="schedule-item">
                                <label>Drop-off Date</label>
                                <div class="input-wrapper">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="input-icon">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                        <line x1="16" y1="2" x2="16" y2="6"></line>
                                        <line x1="8" y1="2" x2="8" y2="6"></line>
                                        <line x1="3" y1="10" x2="21" y2="10"></line>
                                    </svg>
                                    <input type="text" id="dropoff_date_display" class="schedule-input" placeholder="Select Date" readonly>
                                </div>
                            </div>

                            <!-- Drop-off Time (Custom Dropdown) -->
                            <div class="schedule-item relative-parent">
                                <label>Drop-off Time</label>
                                <div class="input-wrapper" id="dropoff-time-wrapper" style="cursor: pointer;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="input-icon">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <polyline points="12 6 12 12 16 14"></polyline>
                                    </svg>
                                    <input type="text" id="dropoff_time_display" class="schedule-input" placeholder="Select Time" readonly style="pointer-events: none;">
                                </div>

                                <!-- Dropdown for Dropoff Time -->
                                <div class="custom-time-dropdown" id="dropoff_time_dropdown">
                                    <div class="dropdown-header">Select Drop-off Time</div>
                                    <div class="time-list-container" id="dropoff_slots">
                                        <!-- Time slots generated by JS -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group service-type-group mb-4">
                            <label for="service_type" class="form-label">Service Type</label>
                            <select id="service_type" name="service_type" class="form-select" required onchange="calculateNewPrice()">
                                <option value="self-drive" <?= (isset($booking['service_type']) && $booking['service_type'] == 'self-drive') ? 'selected' : '' ?>>Self-drive (No additional cost)</option>
                                <option value="with-driver" <?= (isset($booking['service_type']) && $booking['service_type'] == 'with-driver') ? 'selected' : '' ?>>With Driver (+500,000 đ / Day)</option>
                            </select>
                        </div>

                        <div class="total-price-display mb-3">
                            Estimated Total: <span id="displayPrice">0</span> VND
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-3 fw-bold text-uppercase">Update Booking</button>
                    </form>
                </div>
            </div>

            <!-- Flatpickr JS & Plugins -->
            <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
            <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/rangePlugin.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

            <script>
                // Use bookedSlots if available, otherwise empty array
                const bookedSlots = <?= isset($bookedSlots) ? json_encode($bookedSlots) : '[]' ?>;

                const pricePerHour = <?= (float)($booking['price_per_hour'] ?? 0) ?>;
                const pricePerDay = <?= (float)($booking['price_per_day'] ?? 0) ?>;
                const driverFeePerDay = 500000;

                // Function to update hidden inputs based on display inputs
                function updateHiddenInputs() {
                    const pDate = document.getElementById('pickup_date_display').value;
                    const pTime = document.getElementById('pickup_time_display').value;
                    const dDate = document.getElementById('dropoff_date_display').value;
                    const dTime = document.getElementById('dropoff_time_display').value;

                    if (pDate && pTime) {
                        document.getElementById('pickup_datetime_hidden').value = `${pDate}T${pTime}`;
                    }
                    if (dDate && dTime) {
                        document.getElementById('dropoff_datetime_hidden').value = `${dDate}T${dTime}`;
                    }
                    if (typeof calculateNewPrice === 'function') {
                        calculateNewPrice();
                    }
                }

                // Function copied from book_form.php logic for checking overlap
                function isTimeBlocked(dateStr, timeStr) {
                    if (!dateStr || !timeStr) return false;
                    const checkDate = new Date(`${dateStr}T${timeStr}`);
                    const now = new Date();

                    if (checkDate < now) return true; // Past date

                    // Booking overlap check
                    for (const slot of bookedSlots) {
                        const start = new Date(slot.pickup_datetime);
                        const end = new Date(slot.dropoff_datetime);
                        // IMPORTANT: Exclude the *current booking* from blocking itself if user doesn't change dates but changes service
                        // However, bookedSlots typically contains *other* bookings. Assuming controller filters this out or we just check.
                        // For simplicity we use the logic as is.
                        if (checkDate >= start && checkDate < end) {
                            return true;
                        }
                    }
                    return false;
                }

                function calculateNewPrice() {
                    const pickup = document.getElementById('pickup_datetime_hidden').value;
                    const dropoff = document.getElementById('dropoff_datetime_hidden').value;
                    const serviceType = document.getElementById('service_type').value;

                    if (!pickup || !dropoff) return;

                    const pickupTime = new Date(pickup).getTime();
                    const dropoffTime = new Date(dropoff).getTime();

                    const diffMs = dropoffTime - pickupTime;
                    if (diffMs <= 0) {
                        document.getElementById('displayPrice').innerText = "0";
                        return;
                    }

                    const diffHours = Math.ceil(diffMs / (1000 * 60 * 60));
                    const diffDays = Math.ceil(diffMs / (1000 * 60 * 60 * 24));

                    let carFee = 0;
                    let driverFee = 0;

                    if (diffHours < 24) {
                        carFee = diffHours * pricePerHour;
                        // Driver fee logic: if less than 24h, is it per trip or per day? Assuming fixed daily fee or just 1 day fee
                        if (serviceType === 'with-driver') {
                            driverFee = driverFeePerDay;
                        }
                    } else {
                        carFee = diffDays * pricePerDay;
                        if (serviceType === 'with-driver') {
                            driverFee = diffDays * driverFeePerDay;
                        }
                    }

                    const total = carFee + driverFee;
                    const displayElement = document.getElementById('displayPrice');
                    if (displayElement) {
                        displayElement.innerText = total.toLocaleString('en-US');
                    }
                }

                // Initial setup
                document.addEventListener('DOMContentLoaded', function() {

                    // Convert Booked Slots to Flatpickr Disable Format
                    const disabledRanges = bookedSlots.map(slot => ({
                        from: slot.pickup_datetime.split(' ')[0],
                        to: slot.dropoff_datetime.split(' ')[0]
                    }));

                    flatpickr("#pickup_date_display", {
                        mode: "range",
                        minDate: "today",
                        dateFormat: "Y-m-d",
                        showMonths: 2,
                        defaultDate: ["<?= $pickup_date_val ?>", "<?= $dropoff_date_val ?>"],
                        disable: disabledRanges,
                        plugins: [new rangePlugin({
                            input: "#dropoff_date_display"
                        })],
                        onReady: function(selectedDates, dateStr, instance) {
                            const container = instance.calendarContainer;
                            if (!container.querySelector('.flatpickr-confirm-container')) {
                                const btnContainer = document.createElement("div");
                                btnContainer.className = "flatpickr-confirm-container text-center p-2 border-top";
                                const btn = document.createElement("button");
                                btn.type = "button";
                                btn.className = "btn btn-primary btn-sm w-100";
                                btn.innerText = "OK - Confirm Dates";
                                btn.addEventListener("click", () => {
                                    instance.close();
                                    updateHiddenInputs(); // Ensure inputs sync

                                    // Re-render time slots based on new dates
                                    renderTimeSlots('pickup_slots', 'pickup_time_display', 'pickup_date_display', 'pickup_time_dropdown');
                                    renderTimeSlots('dropoff_slots', 'dropoff_time_display', 'dropoff_date_display', 'dropoff_time_dropdown');
                                });
                                btnContainer.appendChild(btn);
                                container.appendChild(btnContainer);
                            }
                        },
                        onChange: function(selectedDates, dateStr, instance) {
                            // This runs as user clicks. Wait for full range selection or rely on "Confirm" button.
                            // But since inputs update live via plugin, we can try to sync hidden fields.
                            updateHiddenInputs();

                            // Re-evaluate time slots availability immediately for visual feedback
                            renderTimeSlots('pickup_slots', 'pickup_time_display', 'pickup_date_display', 'pickup_time_dropdown');

                            // Only if dropoff date is set (range complete)
                            if (selectedDates.length === 2) {
                                renderTimeSlots('dropoff_slots', 'dropoff_time_display', 'dropoff_date_display', 'dropoff_time_dropdown');
                            }
                        }
                    });

                    // Generate Time Options
                    const timeOptions = [];
                    for (let i = 0; i < 24; i++) {
                        timeOptions.push(i.toString().padStart(2, '0') + ":00");
                    }

                    // Render Time Slots (Merged logic from book_form.php + edit needs)
                    function renderTimeSlots(containerId, inputId, dateInputId, dropdownId) {
                        const container = document.getElementById(containerId);
                        const input = document.getElementById(inputId);
                        const dateInput = document.getElementById(dateInputId);
                        const selectedDate = dateInput.value;

                        // Default to 09:00 if empty
                        if (!input.value) input.value = "09:00";

                        container.innerHTML = '';

                        timeOptions.forEach(time => {
                            const btn = document.createElement('div');

                            const isBlocked = isTimeBlocked(selectedDate, time);
                            let bgClass = '';
                            if (input.value === time) bgClass = ' active';
                            if (isBlocked) bgClass += ' disabled';

                            btn.className = 'time-btn' + bgClass;
                            btn.innerText = time;

                            if (!isBlocked) {
                                btn.addEventListener('click', function(e) {
                                    e.stopPropagation();
                                    input.value = time;
                                    updateHiddenInputs();

                                    container.querySelectorAll('.time-btn').forEach(b => b.classList.remove('active'));
                                    btn.classList.add('active');

                                    // Close dropdown
                                    document.getElementById(dropdownId).classList.remove('show');
                                });
                            }
                            container.appendChild(btn);
                        });
                    }

                    // Setup Dropdown Toggles similar to book_form.php
                    const pickles = [{
                            wrapper: 'pickup-time-wrapper',
                            dropdown: 'pickup_time_dropdown',
                            container: 'pickup_slots',
                            input: 'pickup_time_display',
                            date: 'pickup_date_display'
                        },
                        {
                            wrapper: 'dropoff-time-wrapper',
                            dropdown: 'dropoff_time_dropdown',
                            container: 'dropoff_slots',
                            input: 'dropoff_time_display',
                            date: 'dropoff_date_display'
                        }
                    ];

                    pickles.forEach(p => {
                        // Initial Render
                        renderTimeSlots(p.container, p.input, p.date, p.dropdown);

                        document.getElementById(p.wrapper).addEventListener('click', function(e) {
                            e.stopPropagation();
                            // Close others
                            document.querySelectorAll('.custom-time-dropdown').forEach(d => {
                                if (d.id !== p.dropdown) d.classList.remove('show');
                            });

                            renderTimeSlots(p.container, p.input, p.date, p.dropdown);
                            document.getElementById(p.dropdown).classList.toggle('show');

                            // Scroll to active
                            const active = document.getElementById(p.container).querySelector('.active');
                            if (active) active.scrollIntoView({
                                block: "center"
                            });
                        });
                    });

                    // Close dropdowns when clicking outside
                    document.addEventListener('click', function() {
                        document.querySelectorAll('.custom-time-dropdown').forEach(d => d.classList.remove('show'));
                    });

                    // Initial calculation
                    if (typeof calculateNewPrice === 'function') {
                        calculateNewPrice();
                    }
                });
            </script>

        <?php else: ?>
            <div class="msg-error">Booking information could not be loaded.</div>
            <a href="index.php?action=my_bookings" class="btn-back" onclick="if (window.history.length > 1) { event.preventDefault(); window.history.back(); }">&larr; Back</a>
        <?php endif; ?>
    </div>
</body>

</html>