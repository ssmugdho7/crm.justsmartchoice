'use strict';

const $newAppointmentModal = $('#newAppointmentModal');
const $docBody = $('body');

$(function() {
    setupGlobalEventHandlers();
});

/**
 * Setup global event handlers for the application
 */
function setupGlobalEventHandlers() {
    // Modal backdrop fix
    $docBody.on('show.bs.modal', '.modal', function() {
        preventDoubleClicksModal();
    });
    
    // Email and SMS reminders checkboxes
    $docBody.on('change', '#by_sms, #by_email', function() {
        let anyChecked = $('#by_sms').prop('checked') || $('#by_email').prop('checked');
        const $appointmentReminder = $('.appointment-reminder');

        anyChecked
            ? $appointmentReminder.removeClass('hide')
            : $appointmentReminder.addClass('hide');
    });
    
    // Handle calendar filters
    $(document).on('change', '.calendar_filters', function() {
        setTimeout(enhanceCalendarAppointments, 500);
    });
    
    // Handle Outlook checkbox state
    $docBody.on('click', '#outlook-checkbox', function() {
        $(this).attr('checked', $(this).is(':checked') ? true : false);
    });
}

/**
 * Clear data after modals are closed
 */
$('.modal').on('hidden.bs.modal', function(e) {
    $('.xdsoft_datetimepicker').remove();
    $(this).removeData();
});

/**
 * Prevent doubleclicking on modal btn to open multiple backdrop background that block the open modal
 */
function preventDoubleClicksModal() {
    const backDrop = $('.modal-backdrop.fade');
    if (backDrop.length > 1) {
        backDrop.not(':first').remove();
    }
}

/**
 * Enhance the appearance of appointment events in the calendar
 */
document.addEventListener('DOMContentLoaded', function() {
    // Wait for fullcalendar to be initialized

        enhanceCalendarAppointments();

});

/**
 * Add additional styling and classes to appointment events
 */
function enhanceCalendarAppointments() {
    $('.fc-event').each(function() {
        var $event = $(this);
        
        // Add default class
        $event.addClass('appointly-event');
        
        // Check for Google Calendar events
        if ($event.find('.fc-title:contains("fa-google")').length) {
            $event.addClass('google-calendar-event');
            
            // Clean up the title by removing HTML
            var title = $event.find('.fc-title').text();
            title = title.replace(/(<([^>]+)>)/gi, "").replace('fa-google', '');
            $event.find('.fc-title').text(title);
        }
        
        // Add tooltip for the event
        if (!$event.hasClass('fc-tooltip-processed')) {
            $event.addClass('fc-tooltip-processed');
            
            // Check if event has tooltip data attribute
            var tooltipContent = $event.data('tooltip');
            if (!tooltipContent) {
                // Create tooltip content from event details
                var title = $event.find('.fc-title').text().trim();
                var timeText = $event.find('.fc-time').text().trim();
                tooltipContent = title;
                if (timeText) {
                    tooltipContent += '<br><small>' + timeText + '</small>';
                }
            }
            
            // Add tooltip
            $event.attr('title', tooltipContent)
                  .tooltip({
                      container: 'body',
                      html: true,
                      placement: 'auto'
                  });
        }
    });
    
    // Add tooltips to calendar popover content
    $('.fc-event').on('mouseenter', function() {
        setTimeout(function() {
            $('.fc-tooltip').addClass('appointly-tooltip');
        }, 10);
    });
}


/** Smart Choice v2.1.3: prevent duplicate selectpicker scrollbars/wrappers. */
function smartChoiceAppointlyCleanSelectpickers() {
    if (typeof $ === 'undefined') {
        return;
    }
    $('.bootstrap-select .bootstrap-select').each(function () {
        var $nested = $(this);
        var $select = $nested.find('select.selectpicker').first();
        if ($select.length) {
            $nested.replaceWith($select);
            try { $select.selectpicker('render').selectpicker('refresh'); } catch (e) {}
        }
    });
    $('select.selectpicker').each(function () {
        var $select = $(this);
        var selectId = $select.attr('id');
        if (!selectId) {
            return;
        }
        var $siblings = $select.siblings('.bootstrap-select');
        if ($siblings.length > 1) {
            $siblings.not(':last').remove();
        }
    });
    $('.bootstrap-select .dropdown-menu').css({'overflow':'visible','max-height':'none'});
    $('.bootstrap-select .dropdown-menu.inner').css({'overflow-y':'auto','overflow-x':'hidden','max-height':'260px'});
}
$(document).ready(function () {
    smartChoiceAppointlyCleanSelectpickers();
    setTimeout(smartChoiceAppointlyCleanSelectpickers, 400);
    setTimeout(smartChoiceAppointlyCleanSelectpickers, 1200);
});
$(document).on('shown.bs.dropdown shown.bs.select loaded.bs.select rendered.bs.select refreshed.bs.select', function () {
    setTimeout(smartChoiceAppointlyCleanSelectpickers, 50);
});
