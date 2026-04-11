<?php
// filepath: coursework_scrum1.0/views/pages/book_form.php

/** @var array $car */
$errorMessage = '';
if (isset($_SESSION['error_message'])) {
    $errorMessage = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book <?= htmlspecialchars($car['model_name']) ?> | Born Car</title>
    <link rel="stylesheet" href="css/style.css">
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
    <!-- Theme overridden in style.css -->
</head>

<body>
    <?php require_once __DIR__ . '/../layouts/header.php'; ?>

    <div class="container">
        <a href="index.php?action=car_detail&id=<?= htmlspecialchars($car['id']) ?>" class="btn-back" onclick="if (window.history.length > 1) { event.preventDefault(); window.history.back(); }">&larr; Back</a>

        <?php if ($errorMessage): ?>
            <div class="alert-danger"><?= htmlspecialchars($errorMessage) ?></div>
        <?php endif; ?>

        <div class="booking-wrapper">
            <!-- Left: Summary -->
            <div class="car-summary-col">
                <h2><?= htmlspecialchars($car['model_name']) ?></h2>
                <img
                    src="<?= !empty($car['image_url']) ? htmlspecialchars($car['image_url']) : 'https://images.unsplash.com/photo-1494976388531-d1058494cdd8?auto=format&fit=crop&w=600&q=80' ?>"
                    alt="<?= htmlspecialchars($car['model_name']) ?>"
                    onerror="this.src='https://images.unsplash.com/photo-1494976388531-d1058494cdd8?auto=format&fit=crop&w=600&q=80'">
                <div class="btn-inforcar">
                    <strong>Category:</strong> <?= htmlspecialchars($car['category']) ?> &bull;
                    <strong>Seats:</strong> <?= htmlspecialchars($car['seats']) ?> &bull;
                    <strong>Trans:</strong> <?= strtoupper(htmlspecialchars($car['transmission'])) ?>
                </div>

                <div class="price-box">
                    <div class="day-rate"><?= number_format($car['price_per_day'], 0, '.', ',') ?> VND / Day</div>
                    <div class="hour-rate"><?= number_format($car['price_per_hour'], 0, '.', ',') ?> VND / Hour</div>
                </div>
            </div>

            <!-- Right: Form -->
            <div class="booking-form-col">
                <h3>Set Your Schedule</h3>
                <form id="bookingForm" method="POST" action="index.php?action=book_preview">
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

                    <div class="form-group service-type-group">
                        <label for="service_type">Service Type</label>
                        <select id="service_type" name="service_type" class="form-control" required>
                            <option value="self-drive">Self-drive (No additional cost)</option>
                            <option value="with-driver">With Driver (+500,000 đ / Day)</option>
                        </select>
                    </div>

                    <button type="submit" class="btn-submit">Calculate Price & Continue</button>
                </form>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../layouts/footer.php'; ?>

    <!-- Flatpickr JS & Plugins -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/rangePlugin.js"></script>

    <script>
        const bookedSlots = <?= json_encode($bookedSlots ?? []) ?>;

        document.addEventListener('DOMContentLoaded', function() {
            // Update hidden inputs for form submission
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
            }

            // Check if time is blocked
            function isTimeBlocked(dateStr, timeStr) {
                if (!dateStr || !timeStr) return false;
                const checkDate = new Date(`${dateStr}T${timeStr}`);
                const now = new Date();

                if (checkDate < now) return true; // Past date

                // Booking overlap
                for (const slot of bookedSlots) {
                    const start = new Date(slot.pickup_datetime);
                    const end = new Date(slot.dropoff_datetime);
                    if (checkDate >= start && checkDate < end) {
                        return true;
                    }
                }
                return false;
            }

            // Convert Booked Slots to Flatpickr Disable Format {from, to}
            const disabledRanges = bookedSlots.map(slot => ({
                from: slot.pickup_datetime.split(' ')[0], // Extracts YYYY-MM-DD
                to: slot.dropoff_datetime.split(' ')[0]
            }));

            // Initialize Flatpickr for Dates
            flatpickr("#pickup_date_display", {
                mode: "range",
                minDate: "today",
                dateFormat: "Y-m-d",
                showMonths: 2, // Show 2 months side-by-side
                disable: disabledRanges, // Disable booked dates
                plugins: [new rangePlugin({
                    input: "#dropoff_date_display"
                })],
                onReady: function(selectedDates, dateStr, instance) {
                    // Custom 'OK' Button Logic
                    const container = instance.calendarContainer;
                    if (!container.querySelector('.flatpickr-confirm-container')) {
                        const btnContainer = document.createElement("div");
                        btnContainer.className = "flatpickr-confirm-container";

                        const btn = document.createElement("button");
                        btn.type = "button";
                        btn.className = "flatpickr-confirm-btn";
                        btn.innerText = "OK - Confirm Dates";

                        btn.addEventListener("click", () => {
                            instance.close();
                            // Trigger updates ensuring everything is synced
                            updateHiddenInputs();
                            renderTimeSlots('pickup_slots', 'pickup_time_display', 'pickup_date_display', 'pickup_time_dropdown');
                            renderTimeSlots('dropoff_slots', 'dropoff_time_display', 'dropoff_date_display', 'dropoff_time_dropdown');
                        });

                        btnContainer.appendChild(btn);
                        container.appendChild(btnContainer);
                    }
                },
                onChange: function(selectedDates, dateStr, instance) {
                    updateHiddenInputs();
                    // Re-render time slots when date changes to reflect blocked status
                    renderTimeSlots('pickup_slots', 'pickup_time_display', 'pickup_date_display', 'pickup_time_dropdown');
                    renderTimeSlots('dropoff_slots', 'dropoff_time_display', 'dropoff_date_display', 'dropoff_time_dropdown');
                }
            });

            // Generate Time Options (00:00 - 23:00)
            const timeOptions = [];
            for (let i = 0; i < 24; i++) {
                timeOptions.push(i.toString().padStart(2, '0') + ":00");
            }

            // Render Time Dropdown List
            function renderTimeSlots(containerId, inputId, dateInputId, dropdownId) {
                const container = document.getElementById(containerId);
                const input = document.getElementById(inputId);
                const dateInput = document.getElementById(dateInputId);
                const selectedDate = dateInput.value;

                if (!input.value) input.value = "09:00"; // Default

                container.innerHTML = ''; // Clear

                timeOptions.forEach(time => {
                    const btn = document.createElement('div');
                    const isBlocked = isTimeBlocked(selectedDate, time);

                    let className = 'time-btn';
                    if (input.value === time) className += ' active';
                    if (isBlocked) className += ' disabled';

                    btn.className = className;
                    btn.innerText = time;

                    if (!isBlocked) {
                        btn.addEventListener('click', function(e) {
                            e.stopPropagation();
                            input.value = time;
                            updateHiddenInputs();

                            // Visual feedback
                            container.querySelectorAll('.time-btn').forEach(b => b.classList.remove('active'));
                            btn.classList.add('active');

                            // Close dropdown
                            document.getElementById(dropdownId).classList.remove('show');
                        });
                    }
                    container.appendChild(btn);
                });
            }

            // Setup Dropdown Toggles and Initial Render
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
                // Initial Render delay to let dates settle
                setTimeout(() => renderTimeSlots(p.container, p.input, p.date, p.dropdown), 100);

                function scrollActiveTimeInsideDropdown(dropdown) {
                    const listContainer = dropdown.querySelector('.time-list-container');
                    const active = listContainer ? listContainer.querySelector('.time-btn.active') : null;
                    if (!listContainer || !active) return;

                    // Scroll only inside the dropdown list to avoid page jump.
                    listContainer.scrollTop = active.offsetTop - (listContainer.clientHeight / 2) + (active.clientHeight / 2);
                }

                // Toggle logic
                document.getElementById(p.wrapper).addEventListener('click', function(e) {
                    e.stopPropagation();
                    // Close others
                    document.querySelectorAll('.custom-time-dropdown').forEach(d => {
                        if (d.id !== p.dropdown) d.classList.remove('show');
                    });

                    // Re-render to ensure current date/blocked status is accurate
                    renderTimeSlots(p.container, p.input, p.date, p.dropdown);

                    const dropdown = document.getElementById(p.dropdown);
                    const willOpen = !dropdown.classList.contains('show');
                    dropdown.classList.toggle('show');

                    if (willOpen) {
                        scrollActiveTimeInsideDropdown(dropdown);
                    }
                });
            });

            // Close dropdowns when clicking outside
            document.addEventListener('click', function() {
                document.querySelectorAll('.custom-time-dropdown').forEach(d => d.classList.remove('show'));
            });

            // Form Submit Validation
            document.getElementById('bookingForm').addEventListener('submit', function(e) {
                const pVal = document.getElementById('pickup_datetime_hidden').value;
                const dVal = document.getElementById('dropoff_datetime_hidden').value;
                if (!pVal || !dVal || pVal.length < 15 || dVal.length < 15) {
                    e.preventDefault();
                    alert("Please select both Pickup and Drop-off dates and times.");
                }
            });
        });
    </script>
</body>

</html>