<?php defined('BASEPATH') or exit('No direct script access allowed');

$data = get_appointments_summary();
$total_appointments_in_database = $data['total_appointments'] ?? 0;
unset($data['total_appointments']);

?>

<?php
// Helper function to adjust color opacity
if (!function_exists('adjust_color')) {
    function adjust_color($hex, $opacity)
    {
        // If color is already rgba, return it with new opacity
        if (strpos($hex, 'rgba') !== false) {
            return preg_replace('/,[^,]*\)/', ',' . $opacity . ')', $hex);
        }

        // Remove any non-hex characters
        $hex = preg_replace('/[^0-9A-Fa-f]/', '', $hex);

        // If we have 3 digits, convert to 6
        if (strlen($hex) == 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        // Ensure we have exactly 6 digits
        if (strlen($hex) != 6) {
            return "rgba(200, 200, 200, $opacity)"; // fallback color
        }

        // Convert hex to rgb
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        // Return rgba
        return "rgba($r, $g, $b, $opacity)";
    }
}
?>
<div class="tw-mb-3 tw-flex tw-justify-between tw-items-center tw-px-4">
    <h4 class="tw-text-lg tw-m-0"><?= _l('appointments_summary'); ?></h4>
    <span class="tw-text-sm badge badge-primary"><?= date('d-m-Y'); ?></span>
</div>
<hr class="tw-mb-3 tw-mt-2 tw-mx-4">

<div class="row appointment_summary_parent" style="padding: -1px 15px;">
    <div class="col-lg-2 col-md-3 col-sm-4 col-xs-6 tw-mb-3">
        <a href="#" class="summary-card tw-bg-neutral-100" onclick="_toggleSummaryFilter('all'); return false;">
            <div class="tw-flex tw-flex-col tw-text-center">
                <h3 class="tw-font-bold tw-text-xl tw-mb-1"><?= $total_appointments_in_database ?></h3>
                <span class="tw-text-xs tw-text-gray-600"><?= _l('appointments_total_found'); ?></span>
            </div>
        </a>
    </div>

    <?php foreach ($data as $key => $summary): ?>
        <?php if (!empty($summary['name'])): ?>
            <div class="col-lg-2 col-md-3 col-sm-4 col-xs-6 tw-mb-3">
                <a href="#" class="summary-card"
                    style="background-color: <?= adjust_color($summary['color'], 0.08) ?>; border-color: <?= adjust_color($summary['color'], 0.2) ?>;" onclick="_toggleSummaryFilter('<?= htmlspecialchars($key, ENT_QUOTES) ?>'); return false;">
                    <div class="tw-flex tw-flex-col tw-text-center">
                        <h3 class="tw-font-bold tw-text-xl tw-mb-1"><?= $summary['total']; ?></h3>
                        <span class="tw-text-xs tw-text-gray-600"><?= $summary['name']; ?></span>
                    </div>
                </a>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>


<script>
    const _toggleSummaryFilter = (filter) => {
        if (!filter) return;

        const filtersDropdown = $('._filter_data ul.dropdown-menu li');
        const hiddenInputs = $('._filters._hidden_inputs input');

        // Reset filters
        if (hiddenInputs.length) {
            hiddenInputs.val('');
        }
        if (filtersDropdown.length) {
            filtersDropdown.removeClass('active');
        }

        // Convert summary card key to the actual filter name
        let filterName = filter;
        switch (filter) {
            case 'upcoming':
                filterName = 'upcoming';
                break;
            case 'not_approved':
                filterName = 'status_pending';
                break;
            case 'cancelled':
                filterName = 'status_cancelled';
                break;
            case 'no_show':
                filterName = 'status_no-show';
                break;
            case 'finished':
                filterName = 'status_completed';
                break;
            case 'all':
            default:
                filterName = filter;
                break;
        }

        // Find and trigger the filter
        let filterLink = filtersDropdown.find(`a[data-cview='${filterName}']`);

        // Fallback: try without 'status_' prefix
        if (!filterLink.length && filterName.startsWith('status_')) {
            const fallbackName = filterName.replace('status_', '');
            filterLink = filtersDropdown.find(`a[data-cview='${fallbackName}']`);
        }

        // Fallback: try with 'status_' prefix
        if (!filterLink.length && !filterName.startsWith('status_') && filter !== 'all' && filter !== 'upcoming') {
            filterLink = filtersDropdown.find(`a[data-cview='status_${filterName}']`);
        }

        // Final fallback: look for any element with the filter name
        if (!filterLink.length) {
            filterLink = $(`[data-cview='${filterName}']`);
        }

        if (filterLink.length) {
            filterLink.trigger('click');
        } else if (typeof serverTableFilter === 'function') {
            // If no filter link found, try direct table filtering
            serverTableFilter.trigger('click');
        }
    }
</script>
<style>
    .summary-card {
        display: block;
        padding: 1.25rem;
        border-radius: 0.5rem;
        border: 1px solid transparent;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        height: 100%;
        text-decoration: none;
        color: inherit;
    }

    .summary-card:hover {
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        transform: translateY(-2px);
        text-decoration: none;
        color: inherit;
    }

    .summary-card h3 {
        line-height: 1;
        margin: 0;
    }

    .summary-card span {
        line-height: 1.2;
        word-break: break-word;
    }

    /* Ensure proper spacing */
    .appointment_summary_parent {
        margin-left: -10px;
        margin-right: -10px;
    }

    .appointment_summary_parent>div {
        padding-left: 10px;
        padding-right: 10px;
    }

    /* Mobile Responsiveness */
    @media (max-width: 767px) {
        .summary-card {
            padding: 0.75rem;
            min-height: 75px;
        }

        .summary-card h3 {
            font-size: 1.5rem !important;
            margin-bottom: 0.25rem !important;
        }

        .summary-card span {
            font-size: 0.75rem !important;
            line-height: 1.1 !important;
        }

        .appointment_summary_parent {
            margin-left: -8px;
            margin-right: -8px;
        }

        .appointment_summary_parent>div {
            padding-left: 8px;
            padding-right: 8px;
        }
    }

    @media (max-width: 575px) {
        .summary-card {
            padding: 0.625rem;
            min-height: 65px;
        }

        .summary-card h3 {
            font-size: 1.25rem !important;
        }

        .summary-card span {
            font-size: 0.7rem !important;
        }

        .appointment_summary_parent {
            margin-left: -6px;
            margin-right: -6px;
        }

        .appointment_summary_parent>div {
            padding-left: 6px;
            padding-right: 6px;
        }
    }
</style>
