<script>
    $(function() {
        $('[data-toggle="tooltip"]').tooltip();
        // Star rating functionality
        $(document).on('mouseover', '.feedback_star', function() {
            var onStar = parseInt($(this).data('count'));
            $(this).parent().children('.feedback_star').each(function(e) {
                if (e <= onStar) {
                    // If using SVG
                    if ($(this).find('svg').length) {
                        $(this).find('svg').removeClass('tw-text-neutral-300').addClass('tw-text-yellow-400');
                    } else {
                        $(this).find('i').removeClass('tw-text-neutral-300').addClass('tw-text-yellow-400');
                    }
                }
            });
        }).on('mouseout', '.feedback_star', function() {
            if (!$(this).hasClass('rated')) {
                $(this).parent().children('.feedback_star').each(function() {
                    // If using SVG
                    if ($(this).find('svg').length) {
                        $(this).find('svg').addClass('tw-text-neutral-300').removeClass('tw-text-yellow-400');
                    } else {
                        $(this).find('i').addClass('tw-text-neutral-300').removeClass('tw-text-yellow-400');
                    }
                });
            }
        });
    });

    const feedback_url = "<?= $appointment['feedback_url']; ?>";
    const hash = "<?= $this->input->get('hash'); ?>";
    let review_feedback_data = {};
    let _postClickEnabled = false;

    var _reviewHasComment = false;

    // Function to handle appointment feedback
    function handle_appointment_feedback(el) {
        <?php if (is_staff_logged_in()) { ?>
            alert("<?= _l('appointment_staff_cannot_provide_feedback'); ?>");
            return;
        <?php } else { ?>
            if (_postClickEnabled) return;

            const id = "<?= $appointment['id']; ?>";
            const rating = $(el).data('rating');
            _postClickEnabled = true;

            // Update stars visual
            const onStar = parseInt($(el).data('count') + 1);
            const stars = $(el).parent().children('.feedback_star');

            stars.removeClass('rated');

            // If using SVG
            if (stars.find('svg').length) {
                stars.find('svg').addClass('tw-text-neutral-300').removeClass('tw-text-yellow-400');
                stars.slice(0, onStar).addClass('rated').find('svg').removeClass('tw-text-neutral-300').addClass('tw-text-yellow-400');
            } else {
                stars.find('i').addClass('tw-text-neutral-300').removeClass('tw-text-yellow-400');
                stars.slice(0, onStar).addClass('rated').find('i').removeClass('tw-text-neutral-300').addClass('tw-text-yellow-400');
            }

            review_feedback_data = {
                id,
                rating,
                csrf_token_name: "<?= get_instance()->security->get_csrf_token_name(); ?>",
                csrf_token_hash: "<?= get_instance()->security->get_csrf_hash(); ?>"
            };

            // Show comment modal
            if (confirm("<?= _l('appointment_leave_a_comment'); ?>")) {
                showReviewModal();
            } else {
                handleReviewPost();
            }
        <?php } ?>
    }

    function showReviewModal() {
        document.getElementById('reviewModal').classList.remove('tw-hidden');
        document.getElementById('reviewModal').classList.add('tw-flex');
        document.getElementById('review-alert').innerHTML = "<?= _l('appointment_feedback_comment_textarea_info'); ?>";
        document.getElementById('review-alert').classList.add('tw-bg-blue-50', 'tw-text-blue-700', 'tw-p-4', 'tw-rounded-lg');
        document.getElementById('feedback_comment').focus();
    }

    function closeReviewModal() {
        document.getElementById('reviewModal').classList.add('tw-hidden');
        document.getElementById('reviewModal').classList.remove('tw-flex');
        document.getElementById('feedback_comment').value = '';
        document.getElementById('review-alert').innerHTML = '';
        document.getElementById('review-alert').className = 'tw-mb-3 tw-rounded-md';
        _postClickEnabled = false;
    }

    function handleReviewPost() {
        // Create loading overlay
        const loadingOverlay = document.createElement('div');
        loadingOverlay.className = 'tw-fixed tw-inset-0 tw-bg-white/80 tw-flex tw-items-center tw-justify-center tw-z-50';
        loadingOverlay.innerHTML = '<div class="tw-animate-spin tw-rounded-full tw-h-12 tw-w-12 tw-border-4 tw-border-primary-500 tw-border-t-transparent"></div>';
        document.body.appendChild(loadingOverlay);

        $.ajax({
            url: feedback_url,
            type: 'POST',
            data: review_feedback_data,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Create success notification
                    const successNotification = document.createElement('div');
                    successNotification.className = 'tw-fixed tw-top-4 tw-right-4 tw-bg-success-500 tw-text-white tw-px-6 tw-py-3 tw-rounded-lg tw-shadow-lg tw-flex tw-items-center tw-gap-2 tw-z-50';
                    successNotification.innerHTML = `
                        <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-5 tw-w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>${response.message || "<?= _l('appointment_thank_you_for_feedback'); ?>"}</span>
                    `;
                    document.body.appendChild(successNotification);

                    setTimeout(() => {
                        successNotification.remove();
                        location.reload();
                    }, 2000);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', xhr.responseText);
                showErrorNotification("<?= _l('appointment_error_occurred'); ?>");
                _postClickEnabled = false;
            },
            complete: function() {
                document.body.removeChild(loadingOverlay);
            }
        });
    }

    function showErrorNotification(message) {
        const errorNotification = document.createElement('div');
        errorNotification.className = 'tw-fixed tw-top-4 tw-right-4 tw-bg-danger-500 tw-text-white tw-px-6 tw-py-3 tw-rounded-lg tw-shadow-lg tw-flex tw-items-center tw-gap-2 tw-z-50';
        errorNotification.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-5 tw-w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            <span>${message}</span>
        `;
        document.body.appendChild(errorNotification);

        setTimeout(() => {
            errorNotification.remove();
        }, 3000);
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Setup review modal submit button
        const reviewSubmitBtn = document.getElementById('reviewModalSubmitBtn');
        if (reviewSubmitBtn) {
            reviewSubmitBtn.addEventListener('click', function(e) {
                e.preventDefault();

                const feedback_comment = document.getElementById('feedback_comment').value.trim();
                if (!feedback_comment) {
                    document.getElementById('review-alert').innerHTML = "<?= _l('appointment_feedback_comment_required'); ?>";
                    document.getElementById('review-alert').classList.remove('tw-bg-blue-50', 'tw-text-blue-700');
                    document.getElementById('review-alert').classList.add('tw-bg-red-50', 'tw-text-red-700', 'tw-p-4', 'tw-rounded-lg');
                    return;
                }

                review_feedback_data.feedback_comment = feedback_comment;
                closeReviewModal();
                handleReviewPost();
            });
        }
    });

    function appendLoadingAnimation(param) {
        if (param == 'removestar') {
            $('body').find('i').each(function(index, el) {
                $(el).removeClass('fa-star').addClass('pulse');
            });
        } else {
            $('body').find('i').each(function(index, el) {
                $(el).removeClass('pulse').addClass('fa-star');
            });
        }
    }

    function redirectToViewAppointment(href) {
        location.href = href;
    }

    // Cancellation functions
    function openCancelModal() {
        document.getElementById('cancelModal').classList.remove('tw-hidden');
        document.getElementById('notes').focus();
    }

    function closeCancelModal() {
        document.getElementById('cancelModal').classList.add('tw-hidden');
        document.getElementById('notes').value = '';
        const alert = document.getElementById('alert');
        alert.classList.add('tw-hidden');
        alert.classList.remove('tw-bg-red-100', 'tw-text-red-800', 'tw-bg-green-100', 'tw-text-green-800');
        alert.textContent = '';
    }

    function submitCancellation() {
        const notes = document.getElementById('notes').value.trim();
        const alert = document.getElementById('alert');

        if (notes === '') {
            alert.classList.remove('tw-hidden');
            alert.classList.add('tw-bg-red-100', 'tw-text-red-800');
            alert.textContent = "<?= _l('appointment_describe_reason_for_cancel'); ?>";
            return false;
        }

        // Show loading state
        const submitBtn = document.getElementById('cancelAppointmentForm');
        submitBtn.disabled = true;
        submitBtn.innerHTML = `
            <div class="tw-flex tw-items-center tw-justify-center">
                <svg class="tw-animate-spin tw-h-4 tw-w-4 tw-mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="tw-opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="tw-opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span><?= _l('appointment_processing'); ?></span>
            </div>
        `;

        const url = '<?= site_url('appointly/appointments_public/cancel_appointment'); ?>?hash=<?= $appointment['hash']; ?>&notes=' + encodeURIComponent(notes);

        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.response && data.response.success) {
                    alert.classList.remove('tw-hidden', 'tw-bg-red-100', 'tw-text-red-800');
                    alert.classList.add('tw-bg-green-100', 'tw-text-green-800');
                    alert.textContent = data.response.message;

                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    alert.classList.remove('tw-hidden', 'tw-bg-green-100', 'tw-text-green-800');
                    alert.classList.add('tw-bg-red-100', 'tw-text-red-800');
                    alert.textContent = data.response ? data.response.message : data.message || "<?= _l('appointment_error_occurred'); ?>";

                    // Reset button
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = "<?= _l('appointment_request_to_cancel'); ?>";
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert.classList.remove('tw-hidden', 'tw-bg-green-100', 'tw-text-green-800');
                alert.classList.add('tw-bg-red-100', 'tw-text-red-800');
                alert.textContent = "<?= _l('appointment_error_occurred'); ?>";

                // Reset button
                submitBtn.disabled = false;
                submitBtn.innerHTML = "<?= _l('appointment_request_to_cancel'); ?>";
            });
    }

    // Reschedule functions
    function openRescheduleModal() {
        document.getElementById('rescheduleModal').classList.remove('tw-hidden');
        document.getElementById('rescheduleModal').classList.add('tw-flex');

        // Initialize the reschedule datepicker when modal opens
        initializeRescheduleDatePicker();

        document.getElementById('reschedule_date').focus();
    }

    function closeRescheduleModal() {
        document.getElementById('rescheduleModal').classList.add('tw-hidden');
        document.getElementById('rescheduleModal').classList.remove('tw-flex');

        // Reset form fields
        document.getElementById('reschedule_date').value = '';
        document.getElementById('reschedule_date_field').value = '';
        document.getElementById('reschedule_time_field').value = '';
        document.getElementById('reschedule_reason').value = '';

        // Hide time slots section
        document.getElementById('reschedule-time-slots-section').classList.add('tw-hidden');
        document.getElementById('reschedule-time-slots-container').innerHTML = '';

        // Clear alerts
        const alert = document.getElementById('reschedule-alert');
        alert.classList.add('tw-hidden');
        alert.classList.remove('tw-bg-red-50', 'tw-text-red-700', 'tw-bg-green-50', 'tw-text-green-700');
        alert.textContent = '';

        // Destroy datepicker if it exists
        if ($('#reschedule_date').data('xdsoft_datetimepicker')) {
            $('#reschedule_date').datetimepicker('destroy');
        }
    }

    // Initialize reschedule datepicker
    function initializeRescheduleDatePicker() {
        // Show loading
        $('#reschedule_date_loading').removeClass('tw-hidden');

        // Get blocked days first
        $.ajax({
            url: site_url + 'appointly/appointments_public/get_blocked_days',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                var blockedDays = [];
                if (response && response.success && response.blocked_days) {
                    blockedDays = response.blocked_days.map(function(date) {
                        return date.trim();
                    });
                }

                // Initialize datepicker
                var datePickerOptions = {
                    format: 'Y-m-d',
                    timepicker: false,
                    datepicker: true,
                    scrollInput: false,
                    lazyInit: false,
                    minDate: 0, // No past dates
                    dayOfWeekStart: 0,
                    onSelectDate: function(ct, $input) {
                        var date = $input.val();
                        if (date) {
                            $('#reschedule_date_field').val(date);
                            loadRescheduleTimeSlots(date);
                            $('#reschedule-time-slots-section').removeClass('tw-hidden');
                        }
                    },
                    beforeShowDay: function(date) {
                        var dateStr = date.getFullYear() + '-' +
                            ('0' + (date.getMonth() + 1)).slice(-2) + '-' +
                            ('0' + date.getDate()).slice(-2);

                        // Check if date is blocked
                        if (blockedDays.indexOf(dateStr) !== -1) {
                            return [false, 'blocked-date'];
                        }

                        return [true, ''];
                    },
                    onGenerate: function(ct) {
                        console.log('Datepicker generated for reschedule');

                        // Add tooltips to date cells after calendar is generated
                        setTimeout(function() {
                            $('.xdsoft_date').each(function() {
                                var $this = $(this);
                                var dateData = $this.data();

                                if (dateData) {
                                    // Format as YYYY-MM-DD
                                    var month = parseInt(dateData.month) + 1; // months are 0-based
                                    var dateStr = dateData.year + '-' +
                                        ('0' + month).slice(-2) + '-' +
                                        ('0' + dateData.date).slice(-2);

                                    // Check if this date is blocked
                                    if (blockedDays.indexOf(dateStr) !== -1) {
                                        $this.addClass('xdsoft_disabled blocked-date');
                                        $this.attr('title', appointlyLang.appointment_blocked_days || 'Company Holiday/Blocked Date');
                                        $this.css('cursor', 'not-allowed');
                                    } else {
                                        // Check if this is a past date
                                        var jsDate = new Date(dateData.year, dateData.month, dateData.date);
                                        var isPastDate = jsDate < new Date().setHours(0, 0, 0, 0);

                                        if (isPastDate) {
                                            $this.addClass('xdsoft_disabled');
                                            $this.attr('title', 'Past date');
                                        } else {
                                            $this.attr('title', appointlyLang.appointment_available_days || 'Available for booking');
                                        }
                                    }
                                }
                            });
                        }, 100);
                    }
                };

                // Destroy existing datepicker if any
                if ($('#reschedule_date').data('xdsoft_datetimepicker')) {
                    $('#reschedule_date').datetimepicker('destroy');
                }

                // Initialize new datepicker
                $('#reschedule_date').datetimepicker(datePickerOptions);
                $('#reschedule_date_loading').addClass('tw-hidden');

            },
            error: function() {
                console.error('Error loading blocked days');
                $('#reschedule_date_loading').addClass('tw-hidden');

                // Initialize with basic settings
                var basicOptions = {
                    format: 'Y-m-d',
                    timepicker: false,
                    datepicker: true,
                    scrollInput: false,
                    lazyInit: false,
                    minDate: 0,
                    onSelectDate: function(ct, $input) {
                        var date = $input.val();
                        if (date) {
                            $('#reschedule_date_field').val(date);
                            loadRescheduleTimeSlots(date);
                            $('#reschedule-time-slots-section').removeClass('tw-hidden');
                        }
                    },
                    onGenerate: function(ct) {
                        console.log('Basic datepicker generated for reschedule');

                        // Add tooltips to date cells after calendar is generated
                        setTimeout(function() {
                            $('.xdsoft_date').each(function() {
                                var $this = $(this);
                                var dateData = $this.data();

                                if (dateData) {
                                    // Check if this is a past date
                                    var jsDate = new Date(dateData.year, dateData.month, dateData.date);
                                    var isPastDate = jsDate < new Date().setHours(0, 0, 0, 0);

                                    if (isPastDate) {
                                        $this.addClass('xdsoft_disabled');
                                        $this.attr('title', 'Past date');
                                    } else {
                                        $this.attr('title', appointlyLang.appointment_available_days || 'Available for booking');
                                    }
                                }
                            });
                        }, 100);
                    }
                };

                if ($('#reschedule_date').data('xdsoft_datetimepicker')) {
                    $('#reschedule_date').datetimepicker('destroy');
                }
                $('#reschedule_date').datetimepicker(basicOptions);
            }
        });
    }

    // Load reschedule time slots
    function loadRescheduleTimeSlots(date) {
        // Show loading
        $('#reschedule-slot-loading').removeClass('tw-hidden');
        $('#reschedule-time-slots-container').empty();

        // Get appointment data (we need service and provider info)
        var appointmentData = <?= json_encode($appointment); ?>;

        var ajaxData = {
            service_id: appointmentData.service_id,
            provider_id: appointmentData.provider_id,
            staff_id: appointmentData.provider_id,
            date: date,
            timezone: appointmentData.timezone || 'UTC'
        };

        // Add CSRF token
        ajaxData["<?= $this->security->get_csrf_token_name(); ?>"] = "<?= $this->security->get_csrf_hash(); ?>";

        $.ajax({
            url: site_url + 'appointly/appointments_public/get_available_time_slots',
            type: 'POST',
            data: ajaxData,
            dataType: 'json',
            success: function(response) {
                $('#reschedule-slot-loading').addClass('tw-hidden');

                if (response && response.success && response.time_slots && response.time_slots.length > 0) {
                    var hasAvailableSlots = false;
                    var container = $('#reschedule-time-slots-container');

                    response.time_slots.forEach(function(slot) {
                        if (slot.available !== false) {
                            hasAvailableSlots = true;

                            var timeSlotBtn = $('<button>', {
                                type: 'button',
                                class: 'reschedule-time-slot-btn tw-p-2 tw-border tw-border-neutral-200 tw-rounded-md tw-text-center tw-bg-white hover:tw-bg-neutral-50 tw-text-sm tw-transition-all',
                                'data-start-time': slot.value,
                                'data-end-time': slot.end_time,
                                'data-text': slot.text,
                                text: slot.text
                            });

                            timeSlotBtn.on('click', function(e) {
                                e.preventDefault();

                                // Remove selected class from all buttons
                                $('.reschedule-time-slot-btn').removeClass('selected tw-bg-blue-100 tw-border-blue-500');

                                // Add selected class to this button
                                $(this).addClass('selected tw-bg-blue-100 tw-border-blue-500');

                                // Store selected time
                                $('#reschedule_time_field').val($(this).data('start-time'));

                                console.log('Selected reschedule time:', $(this).data('start-time'));
                            });

                            container.append(timeSlotBtn);
                        }
                    });

                    if (!hasAvailableSlots) {
                        container.html('<div class="tw-col-span-full tw-text-center tw-py-4 tw-text-neutral-500"><?= _l("appointly_no_available_time_slots") ?>></div>');
                    }
                } else {
                    $('#reschedule-time-slots-container').html('<div class="tw-col-span-full tw-text-center tw-py-4 tw-text-neutral-500"><?= _l("appointly_no_available_time_slots") ?>></div>');
                }
            },
            error: function() {
                $('#reschedule-slot-loading').addClass('tw-hidden');
                $('#reschedule-time-slots-container').html('<div class="tw-col-span-full tw-text-center tw-py-4 tw-text-red-600">Error loading time slots</div>');
            }
        });
    }

    function submitReschedule() {
        const reschedule_date = document.getElementById('reschedule_date_field').value.trim();
        const reschedule_time = document.getElementById('reschedule_time_field').value.trim();
        const reschedule_reason = document.getElementById('reschedule_reason').value.trim();
        const alert = document.getElementById('reschedule-alert');

        // Validation
        if (reschedule_date === '') {
            showRescheduleAlert('error', "<?= _l('appointment_reschedule_date_required'); ?>");
            return false;
        }

        if (reschedule_time === '') {
            showRescheduleAlert('error', "<?= _l('appointment_reschedule_time_required'); ?>");
            return false;
        }

        // Check if the new date/time is in the future
        const selectedDateTime = new Date(reschedule_date + 'T' + reschedule_time);
        const now = new Date();
        if (selectedDateTime <= now) {
            showRescheduleAlert('error', "<?= _l('appointment_reschedule_future_datetime'); ?>");
            return false;
        }

        // Show loading state
        const submitBtn = document.getElementById('rescheduleSubmitBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = `
            <div class="tw-flex tw-items-center tw-justify-center">
                <svg class="tw-animate-spin tw-h-4 tw-w-4 tw-mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="tw-opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="tw-opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span><?= _l('appointment_processing') ?></span>
            </div>
        `;

        // Prepare data
        const data = {
            reschedule_date: reschedule_date,
            reschedule_time: reschedule_time,
            reschedule_reason: reschedule_reason,
            csrf_token_name: "<?= get_instance()->security->get_csrf_token_name() ?>",
            csrf_token_hash: "<?= get_instance()->security->get_csrf_hash() ?>"
        };

        // Submit reschedule request
        const url = '<?= site_url('appointly/appointments_public/reschedule_appointment'); ?>?hash=<?= $appointment['hash'] ?>';

        fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showRescheduleAlert('success', data.message);
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    showRescheduleAlert('error', data.message || "<?= _l('appointment_error_occurred'); ?>");
                    // Reset button
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = "<?= _l('appointment_request_reschedule'); ?>";
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showRescheduleAlert('error', "<?= _l('appointment_error_occurred'); ?>");
                // Reset button
                submitBtn.disabled = false;
                submitBtn.innerHTML = "<?= _l('appointment_request_reschedule'); ?>";
            });
    }

    function showRescheduleAlert(type, message) {
        const alert = document.getElementById('reschedule-alert');
        alert.classList.remove('tw-hidden', 'tw-bg-red-50', 'tw-text-red-700', 'tw-bg-green-50', 'tw-text-green-700');

        if (type === 'error') {
            alert.classList.add('tw-bg-red-50', 'tw-text-red-700');
        } else {
            alert.classList.add('tw-bg-green-50', 'tw-text-green-700');
        }

        alert.classList.add('tw-p-4', 'tw-rounded-md');
        alert.textContent = message;
    }
</script>
