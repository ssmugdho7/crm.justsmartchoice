/**
 * Appointly Calendar Tooltips - Prevent HTML Code Overlay
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        setTimeout(enhanceCalendarEvents, 1000);
        
        // Refresh on calendar changes
        $(document).on('click', '.fc-button', function() {
            setTimeout(enhanceCalendarEvents, 700);
        });
    });

    function enhanceCalendarEvents() {
        // ONLY target actual event elements with href attributes
        $('.fc-event[href*="appointly/appointments/view"]').each(function() {
            var $event = $(this);
            var href = $event.attr('href') || '';
            var title = $event.text() || '';
            var eventData = $event.data('event') || {};
            
            // Skip if this is not a real appointment event
            if (!href || href === '#' || href.indexOf('appointly') === -1) {
                return;
            }
            
            // Only process Appointly events
            if (!isAppointlyEvent(href, title, eventData)) {
                return;
            }

            setupTooltip($event, title, href, eventData);
        });
    }

    function isAppointlyEvent(href, title, eventData) {
        // Must have a valid appointly URL
        if (href.indexOf('appointly/appointments/view') === -1) {
            return false;
        }
        
        // Exclude non-Appointly events
        var isExcluded = title.indexOf('INV-') !== -1 || 
                        title.indexOf('EST-') !== -1 || 
                        href.indexOf('/invoices/') !== -1 || 
                        href.indexOf('/estimates/') !== -1 ||
                        href.indexOf('/proposals/') !== -1 ||
                        href.indexOf('/tasks/') !== -1;
        
        if (isExcluded) {
            return false;
        }
        
        return true;
    }

    function setupTooltip($event, title, href, eventData) {
        // Remove any existing event handlers to prevent duplicates
        $event.off('mouseenter.appointly mouseleave.appointly');
        
        // PREVENT OTHER TOOLTIP SYSTEMS FROM INTERFERING
        $event.removeAttr('title'); // Remove title attribute that causes browser tooltips
        $event.removeAttr('data-original-title'); // Remove Bootstrap tooltip titles
        $event.removeAttr('data-toggle'); // Remove Bootstrap tooltip triggers
        $event.removeAttr('data-placement'); // Remove Bootstrap tooltip placement
        
        $event.addClass('appointly-event');
        
        var $tooltip = null;
        
        $event.on('mouseenter.appointly', function(e) {
            // STOP ALL EVENT PROPAGATION to prevent other tooltip systems
            e.stopPropagation();
            e.stopImmediatePropagation();
            e.preventDefault();
            
            // Remove any existing tooltips first
            $('.appointly-custom-tooltip').remove();
            
            var appointlyData = eventData.appointlyData || {};
            var appointmentId = extractAppointmentId(href);
            
            // Fetch data if needed, otherwise create tooltip directly
            if (appointmentId && (!appointlyData || Object.keys(appointlyData).length === 0)) {
                fetchAppointmentData(appointmentId, function(data) {
                    createTooltip(data || {}, title, href, appointmentId, $event);
                });
            } else {
                createTooltip(appointlyData, title, href, appointmentId, $event);
            }
        });
        
        $event.on('mouseleave.appointly', function(e) {
            e.stopPropagation();
            e.stopImmediatePropagation();
            setTimeout(function() {
                if ($tooltip && !$tooltip.data('hovered')) {
                    $tooltip.remove();
                }
            }, 200);
        });
        
        // DISABLE OTHER TOOLTIP EVENTS
        $event.on('mouseover mouseout hover', function(e) {
            if (!e.target.closest('.appointly-custom-tooltip')) {
                e.stopPropagation();
                e.stopImmediatePropagation();
            }
        });
        
        function fetchAppointmentData(appointmentId, callback) {
            $.ajax({
                url: admin_url + 'appointly/appointments/get_appointment_data',
                type: 'POST',
                data: { appointment_id: appointmentId },
                dataType: 'json',
                success: function(response) {
                    callback(response && response.success ? response.data : null);
                },
                error: function() {
                    callback(null);
                }
            });
        }
        
        function createTooltip(appointlyData, title, href, id, $event) {
            // Create tooltip container with unique class
            $tooltip = $('<div>')
                .addClass('appointly-custom-tooltip')
                .css(getTooltipBaseStyles());
            
            // Create and append header
            var $header = createHeader(title);
            $tooltip.append($header);
            
            // Create and append content
            if (appointlyData && Object.keys(appointlyData).length > 0) {
                appendRichContent($tooltip, appointlyData);
            } else {
                appendBasicContent($tooltip, id);
            }
            
            // Create and append footer
            if (href) {
                var $footer = createFooter(href);
                $tooltip.append($footer);
            }
            
            positionTooltip($tooltip, $event);
            setupTooltipEvents($tooltip);
        }
    }

    function extractAppointmentId(href) {
        var match = href.match(/appointment_id=(\d+)/);
        return match ? match[1] : '';
    }

    function getTooltipBaseStyles() {
        return {
            background: 'white',
            border: '1px solid #ddd',
            borderRadius: '12px',
            padding: '20px',
            boxShadow: '0 8px 32px rgba(0,0,0,0.15)',
            maxWidth: '400px',
            minWidth: '300px',
            zIndex: 10000,
            position: 'absolute',
            pointerEvents: 'auto'
        };
    }

    function createHeader(title) {
        var $header = $('<div>').css({
            borderBottom: '2px solid #f8f9fa',
            paddingBottom: '12px',
            marginBottom: '16px'
        });
        
        var $titleEl = $('<h5>').css({
            margin: 0,
            color: '#2c3e50',
            fontSize: '18px',
            fontWeight: '700',
            lineHeight: '1.3'
        });
        
        // Use text() to safely set the title
        $titleEl.text(title);
        $header.append($titleEl);
        return $header;
    }

    function appendRichContent($tooltip, data) {
        // Service information
        if (data.service_name) {
            $tooltip.append(createInfoRow('fa-briefcase', data.service_name, '#007bff', '600'));
        }
        
        // Time information
        if (data.start_hour && data.end_hour) {
            var timeText = formatTime(data.start_hour) + ' - ' + formatTime(data.end_hour);
            if (data.duration || data.service_duration) {
                timeText += ' (' + (data.duration || data.service_duration) + ' min)';
            }
            $tooltip.append(createInfoRow('fa-clock-o', timeText, '#007bff', '500'));
        }
        
        // Provider information
        if (data.provider_firstname && data.provider_lastname) {
            $tooltip.append(createInfoRow('fa-user', data.provider_firstname + ' ' + data.provider_lastname, '#007bff', '500'));
        }
        
        // Status badge
        if (data.status) {
            $tooltip.append(createStatusBadge(data.status));
        }
        
        // Contact information
        if (data.email) {
            $tooltip.append(createInfoRow('fa-envelope', data.email, '#6c757d', '400', '13px'));
        }
        if (data.phone) {
            $tooltip.append(createInfoRow('fa-phone', data.phone, '#6c757d', '400', '13px'));
        }
        
        // Address
        if (data.address) {
            $tooltip.append(createInfoRow('fa-map-marker', data.address, '#6c757d', '400', '13px'));
        }
    }

    function appendBasicContent($tooltip, id) {
        if (id) {
            $tooltip.append(createInfoRow('fa-calendar', 'Appointment ID: ' + id, '#007bff'));
        }
    }

    function createInfoRow(iconClass, text, color, fontWeight, fontSize) {
        color = color || '#666';
        fontWeight = fontWeight || '400';
        fontSize = fontSize || '14px';
        
        var $row = $('<div>').css({
            display: 'flex',
            alignItems: 'center',
            marginBottom: '10px',
            fontSize: fontSize,
            color: color,
            fontWeight: fontWeight
        });
        
        var $icon = $('<i>').addClass('fa ' + iconClass).css({
            width: '20px',
            marginRight: '12px',
            color: '#007bff',
            fontSize: '16px'
        });
        
        var $textSpan = $('<span>');
        $textSpan.text(text); // Use text() method to prevent HTML injection
        
        $row.append($icon);
        $row.append($textSpan);
        return $row;
    }

    function createStatusBadge(status) {
        var $statusDiv = $('<div>').css({
            margin: '12px 0',
            textAlign: 'center'
        });
        
        var $status = $('<span>').css({
            fontSize: '12px',
            padding: '6px 12px',
            borderRadius: '20px',
            fontWeight: '600',
            textTransform: 'uppercase',
            letterSpacing: '0.5px',
            background: getStatusColor(status),
            color: 'white'
        });
        
        // Use text() to safely set status text
        $status.text(status.charAt(0).toUpperCase() + status.slice(1));
        $statusDiv.append($status);
        return $statusDiv;
    }

    function createFooter(href) {
        var $footer = $('<div>').css({
            borderTop: '2px solid #f8f9fa',
            paddingTop: '16px',
            marginTop: '16px',
            textAlign: 'center'
        });
        
        var $button = $('<a>').attr('href', href).css({
            display: 'inline-flex',
            alignItems: 'center',
            gap: '8px',
            fontSize: '14px',
            padding: '10px 20px',
            borderRadius: '8px',
            textDecoration: 'none',
            background: 'linear-gradient(135deg, #007bff 0%, #0056b3 100%)',
            border: 'none',
            color: '#fff',
            fontWeight: '600',
            boxShadow: '0 4px 12px rgba(0, 123, 255, 0.3)',
            transition: 'all 0.3s ease'
        });
        
        var $buttonIcon = $('<i>').addClass('fa fa-eye').css({ fontSize: '14px' });
        var $buttonText = $('<span>');
        $buttonText.text(' View Details'); // Use text() method
        
        $button.append($buttonIcon);
        $button.append($buttonText);
        $footer.append($button);
        
        return $footer;
    }

    function positionTooltip($tooltip, $event) {
        $tooltip.appendTo('body');
        
        var offset = $event.offset();
        var tooltipHeight = $tooltip.outerHeight();
        var tooltipWidth = $tooltip.outerWidth();
        var eventWidth = $event.outerWidth();
        
        // Position above the event, centered
        var top = offset.top - tooltipHeight - 10;
        var left = offset.left + (eventWidth / 2) - (tooltipWidth / 2);
        
        // Keep tooltip on screen
        if (left < 10) left = 10;
        if (left + tooltipWidth > $(window).width() - 10) {
            left = $(window).width() - tooltipWidth - 10;
        }
        if (top < 10) {
            top = offset.top + $event.outerHeight() + 10;
        }
        
        $tooltip.css({ top: top, left: left });
    }

    function setupTooltipEvents($tooltip) {
        $tooltip.on('mouseenter', function() {
            $tooltip.data('hovered', true);
        });
        
        $tooltip.on('mouseleave', function() {
            $tooltip.remove();
        });
    }

    // Utility functions
    function formatTime(timeStr) {
        if (!timeStr) return '';

        var parts = timeStr.split(':');
        if (parts.length >= 2) {
            var hour = parseInt(parts[0], 10);
            var minute = parts[1];
            var ampm = hour >= 12 ? 'PM' : 'AM';
            hour = hour % 12;
            hour = hour ? hour : 12;
            return hour + ':' + minute + ' ' + ampm;
        }

        return timeStr;
    }

    function getStatusColor(status) {
        switch (status.toLowerCase()) {
            case 'approved':
            case 'confirmed':
            case 'in-progress':
                return '#28a745';
            case 'pending':
                return '#ffc107';
            case 'cancelled':
            case 'no-show':
                return '#dc3545';
            case 'completed':
                return '#17a2b8';
            default:
                return '#6c757d';
        }
    }

})(jQuery); 
