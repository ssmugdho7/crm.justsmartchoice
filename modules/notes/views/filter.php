<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<input type="hidden" name="from_date" id="from_date" value="<?php echo _d(date('Y-m-d', strtotime('monday this week'))); ?>">
<input type="hidden" name="to_date" id="to_date" value="<?php echo _d(date('Y-m-d', strtotime('sunday this week'))); ?>">

<div class="col-md-3">
    <div class="form-group select-placeholder">
        <select class="selectpicker" name="range" id="range" data-width="100%" onchange="reload_the_table()">
            <?php $current_year = date('Y') + 10; ?>
            <option value='<?php echo json_encode([_d(date('1950-m-d')), _d(date($current_year . '-m-d'))]); ?>'><?php echo _l('all'); ?></option>
            <option value='<?php echo json_encode([_d(date('Y-m-d')), _d(date('Y-m-d'))]); ?>'><?php echo _l('today'); ?></option>
            <option value='<?php echo json_encode([_d(date('Y-m-d', strtotime('monday this week'))), _d(date('Y-m-d', strtotime('sunday this week')))]); ?>' selected><?php echo _l('this_week'); ?></option>
            <option value='<?php echo json_encode([_d(date('Y-m-01')), _d(date('Y-m-t'))]); ?>'><?php echo _l('this_month'); ?></option>
            <option value='<?php echo json_encode([_d(date('Y-m-01', strtotime('-1 MONTH'))), _d(date('Y-m-t', strtotime('-1 MONTH')))]); ?>'><?php echo _l('last_month'); ?></option>
            <option value='<?php echo json_encode([_d(date('Y-m-d', strtotime(date('Y-01-01')))), _d(date('Y-m-d', strtotime(date('Y-12-31'))))]); ?>'><?php echo _l('this_year'); ?></option>
            <option value='<?php echo json_encode([_d(date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-01-01')))), _d(date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-12-31'))))]); ?>'><?php echo _l('last_year'); ?></option>
            <option value="period"><?php echo _l('period_datepicker'); ?></option>
        </select>
    </div>
</div>

<div class="col-md-3">
    <div class="form-group select-placeholder">
        <select class="selectpicker" name="source" id="source" data-width="100%" onchange="reload_the_table()">
            <option value=""><?php echo _l('lead_add_edit_source'); ?></option>
            <?php foreach ($rel_type as $key => $type) { ?>
                <option value="<?php echo html_escape($key); ?>"><?php echo html_escape(is_array($type) ? $type['name'] : _l($type)); ?></option>
            <?php } ?>
        </select>
    </div>
</div>

<div class="col-md-3">
    <div class="form-group select-placeholder">
        <select class="selectpicker" name="filter_priority" id="filter_priority" data-width="100%" onchange="reload_the_table()">
            <option value=""><?php echo _l('notes_priority'); ?></option>
            <option value="low"><?php echo _l('notes_priority_low'); ?></option>
            <option value="medium"><?php echo _l('notes_priority_medium'); ?></option>
            <option value="high"><?php echo _l('notes_priority_high'); ?></option>
            <option value="urgent"><?php echo _l('notes_priority_urgent'); ?></option>
        </select>
    </div>
</div>

<div class="col-md-3">
    <div class="form-group select-placeholder">
        <select class="selectpicker" name="filter_note_color" id="filter_note_color" data-width="100%" onchange="reload_the_table()">
            <option value=""><?php echo _l('notes_brand_color'); ?></option>
            <?php foreach ($brand_colors as $hex => $label) { ?>
                <option value="<?php echo html_escape($hex); ?>" data-content="<span class='note-color-option' style='background:<?php echo html_escape($hex); ?>'></span> <?php echo html_escape($label); ?> <?php echo html_escape($hex); ?>"><?php echo html_escape($label); ?></option>
            <?php } ?>
        </select>
    </div>
</div>

<div class="col-md-3">
    <div class="form-group select-placeholder">
        <select class="selectpicker" name="addedfrom" id="addedfrom" data-width="100%" onchange="reload_the_table()" data-live-search="true" data-none-selected-text="<?php echo _l('clients_notes_table_addedfrom_heading'); ?>">
            <option value=""></option>
            <?php foreach ($staffs as $staff) { ?>
                <option value="<?php echo (int) $staff->staffid; ?>"><?php echo html_escape($staff->fullname); ?></option>
            <?php } ?>
        </select>
    </div>
</div>

<div class="col-md-3">
    <div class="form-group select-placeholder">
        <select class="selectpicker" name="filter_assigned_staff_id" id="filter_assigned_staff_id" data-width="100%" onchange="reload_the_table()" data-live-search="true" data-none-selected-text="<?php echo _l('notes_assigned_to'); ?>">
            <option value=""></option>
            <?php foreach ($staffs as $staff) { ?>
                <option value="<?php echo (int) $staff->staffid; ?>"><?php echo html_escape($staff->fullname); ?></option>
            <?php } ?>
        </select>
    </div>
</div>

<div class="col-md-3">
    <div class="form-group select-placeholder">
        <select id="client_id" name="client_id" data-live-search="true" data-width="100%" class="ajax-search" onchange="reload_the_table()" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>"></select>
    </div>
</div>

<div class="col-md-3">
    <button type="button" class="btn btn-default btn-block" onclick="reload_the_table()">
        <i class="fa fa-filter"></i> <?php echo _l('notes_apply_filters'); ?>
    </button>
</div>

<div class="col-md-12 period hide">
    <div class="row">
        <div class="col-md-6">
            <?php echo render_date_input('period-from', '', '', ['onchange' => isset($onChange) ? $onChange : '']); ?>
        </div>
        <div class="col-md-6">
            <?php echo render_date_input('period-to', '', '', ['onchange' => isset($onChange) ? $onChange : '']); ?>
        </div>
    </div>
</div>
