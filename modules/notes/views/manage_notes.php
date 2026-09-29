<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="tw-flex tw-flex-wrap tw-gap-2 tw-items-center tw-mb-3">
                    <a href="#" class="btn btn-primary btn-sm" onclick="slideToggle('.usernote'); return false;">
                        <i class="fa-regular fa-plus tw-mr-1"></i><?php echo _l('new_note'); ?>
                    </a>
                    <button type="button" class="btn btn-default btn-sm" onclick="slideToggle('.notes-filter-wrapper'); return false;">
                        <i class="fa fa-filter tw-mr-1"></i><?php echo _l('notes_filter'); ?>
                    </button>
                    <div class="btn-group">
                        <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-file-export tw-mr-1"></i><?php echo _l('notes_export_selected'); ?> <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a href="#" onclick="notes_export_selected('csv'); return false;">CSV</a></li>
                            <li><a href="#" onclick="notes_export_selected('excel'); return false;">Excel</a></li>
                            <li><a href="#" onclick="notes_export_selected('pdf'); return false;">PDF</a></li>
                        </ul>
                    </div>
                    <?php echo form_open(admin_url('notes/notes/export_csv'), ['id' => 'notes_export_form', 'class' => 'hide']); ?>
                    <?php echo form_close(); ?>
                    <button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target="#notes_import_modal">
                        <i class="fa fa-upload tw-mr-1"></i><?php echo _l('notes_import'); ?>
                    </button>
                    <a href="<?php echo admin_url('notes/notes/sample_csv'); ?>" class="btn btn-default btn-sm">
                        <i class="fa fa-download tw-mr-1"></i><?php echo _l('notes_sample_header'); ?>
                    </a>
                    <button type="button" class="btn btn-danger btn-sm" onclick="notes_bulk_delete();">
                        <i class="fa fa-trash tw-mr-1"></i><?php echo _l('notes_mass_delete'); ?>
                    </button>
                    <button type="button" class="btn btn-default btn-sm" onclick="reload_the_table();">
                        <i class="fa fa-refresh tw-mr-1"></i><?php echo _l('notes_reload'); ?>
                    </button>
                </div>
            </div>
        </div>

        <div class="usernote hide">
            <?php echo form_open_multipart(admin_url('notes/notes/add_note'), ['id' => 'notes_new_note_form']); ?>
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group select-placeholder">
                                    <label class="control-label"><?php echo _l('lead_add_edit_source'); ?></label>
                                    <select class="selectpicker" name="rel_type" id="rel_type" data-width="100%" required>
                                        <?php foreach ($rel_type as $key => $type) { ?>
                                            <option value="<?php echo html_escape($key); ?>" <?php echo $key === 'customer' ? 'selected' : ''; ?>><?php echo html_escape(is_array($type) ? $type['name'] : _l($type)); ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4" id="rel_id_wrapper">
                                <div class="form-group select-placeholder">
                                    <label for="rel_id"><span class="rel_id_label"><?php echo _l('client'); ?></span></label>
                                    <div id="rel_id_select">
                                        <select name="rel_id" id="rel_id" class="ajax-search" data-width="100%" data-live-search="true" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>"></select>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4" id="assigned_staff_wrapper">
                                <div class="form-group select-placeholder">
                                    <label for="assigned_staff_id"><?php echo _l('notes_assign_to_staff'); ?></label>
                                    <select class="selectpicker" name="assigned_staff_id" id="assigned_staff_id" data-width="100%" data-live-search="true" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                        <option value=""></option>
                                        <?php foreach ($staffs as $staff) { ?>
                                            <option value="<?php echo (int) $staff->staffid; ?>" <?php echo get_staff_user_id() == $staff->staffid ? 'selected' : ''; ?>><?php echo html_escape($staff->fullname); ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group select-placeholder">
                                    <label for="note_type"><?php echo _l('notes_type'); ?></label>
                                    <select class="selectpicker" name="note_type" id="note_type" data-width="100%">
                                        <?php foreach ($note_types as $key => $type) { ?>
                                            <option value="<?php echo html_escape($key); ?>"><?php echo html_escape($type['name']); ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group select-placeholder">
                                    <label for="priority"><?php echo _l('notes_priority'); ?></label>
                                    <select class="selectpicker" name="priority" id="priority" data-width="100%">
                                        <option value="low"><?php echo _l('notes_priority_low'); ?></option>
                                        <option value="medium" selected><?php echo _l('notes_priority_medium'); ?></option>
                                        <option value="high"><?php echo _l('notes_priority_high'); ?></option>
                                        <option value="urgent"><?php echo _l('notes_priority_urgent'); ?></option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group select-placeholder">
                                    <label for="note_color"><?php echo _l('notes_brand_color'); ?></label>
                                    <?php $defaultNoteColor = strtoupper((string) get_option('notes_default_color')); ?>
                                    <select class="selectpicker" name="note_color" id="note_color" data-width="100%">
                                        <?php foreach ($brand_colors as $hex => $label) { ?>
                                            <option value="<?php echo html_escape($hex); ?>" <?php echo strtoupper($hex) === $defaultNoteColor ? 'selected' : ''; ?> data-content="<span class='note-color-option' style='background:<?php echo html_escape($hex); ?>'></span> <?php echo html_escape($label); ?>"><?php echo html_escape($label); ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <?php echo render_input('title', 'notes_title', '', 'text', ['maxlength' => 191, 'required' => true]); ?>
                            </div>

                            <div class="col-md-12">
                                <?php echo render_textarea('description', 'note_description', '', ['rows' => 8, 'class' => 'tinymce']); ?>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="attachment"><?php echo _l('notes_attachment'); ?></label>
                                    <input type="file" name="attachment" id="attachment" class="form-control">
                                    <p class="text-muted mtop5"><?php echo _l('notes_attachment_location'); ?>: modules/notes/uploads/{note_id}/</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="panel-footer">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> <?php echo _l('submit'); ?></button>
                    </div>
                </div>
            <?php echo form_close(); ?>
        </div>


        <?php if (!empty($notes_health)) { ?>
        <div class="panel_s notes-health-panel">
            <div class="panel-body">
                <div class="tw-flex tw-flex-wrap tw-justify-between tw-items-start tw-gap-3">
                    <div>
                        <h4 class="tw-mt-0 tw-mb-2"><i class="fa fa-heartbeat"></i> <?php echo _l('notes_health_and_locations'); ?></h4>
                        <div class="text-muted small">
                            <div><strong><?php echo _l('notes_active_table'); ?>:</strong> <?php echo html_escape($notes_health['active_table']); ?> (<?php echo (int) $notes_health['native_note_count']; ?> <?php echo _l('notes_records'); ?>)</div>
                            <div><strong><?php echo _l('notes_project_table'); ?>:</strong> <?php echo html_escape(db_prefix() . 'project_notes'); ?> (<?php echo (int) $notes_health['project_note_count']; ?> <?php echo _l('notes_records'); ?>)</div>
                            <div><strong><?php echo _l('notes_module_folder'); ?>:</strong> <?php echo html_escape($notes_health['module_path']); ?></div>
                            <div><strong><?php echo _l('notes_main_file'); ?>:</strong> <?php echo html_escape($notes_health['main_file']); ?></div>
                            <div><strong><?php echo _l('notes_upload_folder'); ?>:</strong> <?php echo html_escape($notes_health['upload_path']); ?></div>
                        </div>
                    </div>
                    <div class="notes-module-candidates">
                        <strong><?php echo _l('notes_detected_folders'); ?>:</strong>
                        <?php foreach ($notes_health['module_candidates'] as $candidate) { ?>
                            <span class="label label-default tw-mr-1"><?php echo html_escape($candidate); ?></span>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>

        <div class="panel_s">
            <div class="panel-body">
                <div class="notes-filter-wrapper hide">
                    <div class="row">
                        <?php $this->load->view('filter'); ?>
                    </div>
                    <hr>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="table table_notes notes-resizable-table">
                                <thead>
                                    <tr>
                                        <th><div class="checkbox"><input type="checkbox" id="notes_select_all"><label></label></div></th>
                                        <th><?php echo _l('notes_title'); ?></th>
                                        <th><?php echo _l('lead_add_edit_source'); ?></th>
                                        <th><?php echo _l('tasks_dt_name'); ?></th>
                                        <th><?php echo _l('clients_notes_table_description_heading'); ?></th>
                                        <th><?php echo _l('notes_priority'); ?></th>
                                        <th><?php echo _l('notes_brand_color'); ?></th>
                                        <th><?php echo _l('notes_created_date'); ?></th>
                                        <th><?php echo _l('notes_attachment'); ?></th>
                                        <th><?php echo _l('options'); ?></th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="note_module_modal" tabindex="-1" role="dialog" aria-labelledby="noteModuleModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="noteModuleModalLabel"><span class="edit-title"><?php echo _l('notes_smart_choice_notes'); ?></span></h4>
            </div>
            <div class="modal-body">
                <div id="description_modal"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="notes_import_modal" tabindex="-1" role="dialog" aria-labelledby="notesImportModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <?php echo form_open_multipart(admin_url('notes/notes/import_csv')); ?>
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="notesImportModalLabel"><?php echo _l('notes_import'); ?></h4>
                </div>
                <div class="modal-body">
                    <input type="file" name="import_file" class="form-control" accept=".csv" required>
                    <p class="text-muted mtop10"><?php echo _l('notes_import_help'); ?></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                    <button type="submit" class="btn btn-primary"><?php echo _l('notes_import'); ?></button>
                </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>

<?php init_tail(); ?>

<style>
    .notes-health-panel { border-left: 4px solid #169179; }
    .notes-health-panel .label { display:inline-block; margin-top:4px; }
    .notes-module-candidates { max-width:42%; }
    @media (max-width: 767px) { .notes-module-candidates { max-width:100%; width:100%; } }
    .note-color-dot,
    .note-color-option {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        display: inline-block;
        vertical-align: middle;
        margin-right: 6px;
        border: 1px solid #ddd;
    }
    .table_notes .staff-profile-image-small {
        width: 24px;
        height: 24px;
    }
    .table-responsive { overflow-x: visible !important; }
    .table_notes { width:100% !important; table-layout:fixed; }
    .table_notes th, .table_notes td { vertical-align:middle !important; white-space:normal !important; word-break:break-word; }
    .table_notes th:nth-child(1), .table_notes td:nth-child(1) { width:38px; }
    .table_notes th:nth-child(2), .table_notes td:nth-child(2) { width:14%; }
    .table_notes th:nth-child(3), .table_notes td:nth-child(3) { width:10%; }
    .table_notes th:nth-child(4), .table_notes td:nth-child(4) { width:15%; }
    .table_notes th:nth-child(5), .table_notes td:nth-child(5) { width:27%; }
    .table_notes th:nth-child(6), .table_notes td:nth-child(6) { width:9%; }
    .table_notes th:nth-child(7), .table_notes td:nth-child(7) { width:6%; text-align:center; }
    .table_notes th:nth-child(8), .table_notes td:nth-child(8) { width:11%; }
    .table_notes th:nth-child(9), .table_notes td:nth-child(9) { width:7%; }
    .table_notes th:nth-child(10), .table_notes td:nth-child(10) { width:150px; }
    .note-color-dot-only { margin-right:0; width:18px; height:18px; }
    .notes-action-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:3px; width:142px; }
    .notes-action-grid .btn { margin:0 !important; padding:2px 5px; min-height:24px; font-size:11px; line-height:18px; width:100%; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .notes-action-grid .btn i { margin-right:3px; }
    .table_notes .btn { margin:2px 0; }
    @media (max-width: 767px) {
        .table-responsive { overflow-x:auto !important; }
        .table_notes { min-width:760px; table-layout:auto; }
    }
</style>

<script>
(function($) {
    "use strict";

    $(function() {
        init_editor('.tinymce');
        init_ajax_search('customer', '#client_id.ajax-search');

        var serverParams = {};
        serverParams.from_date = '[name="from_date"]';
        serverParams.to_date = '[name="to_date"]';
        serverParams.source = '[name="source"]';
        serverParams.addedfrom = '[name="addedfrom"]';
        serverParams.client_id = '[name="client_id"]';
        serverParams.priority = '[name="filter_priority"]';
        serverParams.note_color = '[name="filter_note_color"]';
        serverParams.assigned_staff_id = '[name="filter_assigned_staff_id"]';

        initDataTable('.table_notes', admin_url + 'notes/notes/note_lists', [0, 9], [0, 9], serverParams, [7, 'desc']);

        note_rel_id_select();
        handle_note_source_change();

        $('#rel_type').on('change', function() {
            handle_note_source_change();
        });

        $('#notes_select_all').on('change', function() {
            $('.note-export-checkbox').prop('checked', $(this).prop('checked'));
        });

        $('#notes_new_note_form').on('submit', function(e) {
            if (typeof tinymce !== 'undefined') {
                tinymce.triggerSave();
            }

            var title = $.trim($('#title').val() || '');
            var description = $.trim($('#description').val() || '');

            if (title === '' || $('<div>').html(description).text().trim() === '') {
                e.preventDefault();
                alert_float('danger', '<?php echo _l('notes_required_fields'); ?>');
                return false;
            }

            var $submit = $(this).find('button[type="submit"]');
            $submit.prop('disabled', true);
        });

    });
})(jQuery);

function handle_note_source_change() {
    var relType = $('#rel_type').val();
    var staffOnly = relType === 'personal_note';

    if (staffOnly) {
        $('#rel_id_wrapper').addClass('hide');
        $('#rel_id').prop('required', false);
    } else {
        $('#rel_id_wrapper').removeClass('hide');
        $('#rel_id').prop('required', false);
        var clonedSelect = $('#rel_id').html('').clone();
        $('#rel_id').selectpicker('destroy').remove();
        $('#rel_id_select').append(clonedSelect);
        note_rel_id_select();
        $('.rel_id_label').html($('#rel_type').find('option:selected').text());
    }

    $('#assigned_staff_wrapper').removeClass('hide');
    $('.selectpicker').selectpicker('refresh');
}

function note_rel_id_select() {
    var serverData = {};
    serverData.rel_id = $('#rel_id').val();
    serverData.type = $('#rel_type').val();
    init_ajax_search($('#rel_type').val(), '#rel_id', serverData);
}

function reload_the_table() {
    var $statementPeriod = $('#range');
    var value = $statementPeriod.selectpicker('val');
    var period = [];

    if (value !== 'period') {
        period = JSON.parse(value);
    } else {
        period[0] = $('input[name="period-from"]').val();
        period[1] = $('input[name="period-to"]').val();
        if (period[0] === '' || period[1] === '') {
            return false;
        }
    }

    $('#from_date').val(period[0]);
    $('#to_date').val(period[1]);
    $('.table_notes').DataTable().ajax.reload();
}

function note_module_model_get_detail(record_id) {
    $.post(admin_url + 'notes/notes/note_module_model_get_detail', {record_id: record_id}).done(function(response) {
        response = JSON.parse(response);
        if (response.detail) {
            $('#description_modal').html(response.detail.description);
        }
        $('#note_module_modal').modal('show');
    });
}

function get_selected_note_ids() {
    var ids = [];
    $('.note-export-checkbox:checked').each(function() {
        ids.push($(this).val());
    });
    return ids;
}


function notes_export_selected(format) {
    var ids = get_selected_note_ids();
    if (ids.length === 0) {
        alert_float('warning', '<?php echo _l('notes_select_at_least_one'); ?>');
        return;
    }

    var action = admin_url + 'notes/notes/export_csv';
    if (format === 'excel') {
        action = admin_url + 'notes/notes/export_excel';
    }
    if (format === 'pdf') {
        action = admin_url + 'notes/notes/export_pdf';
    }

    var $form = $('#notes_export_form');
    $form.attr('action', action);
    $form.find('.notes-export-id-input').remove();
    for (var i = 0; i < ids.length; i++) {
        $form.append('<input type="hidden" name="ids[]" value="' + ids[i] + '" class="notes-export-id-input">');
    }
    $form.trigger('submit');
}

function notes_delete_one(noteId) {
    if (!confirm('<?php echo _l('notes_confirm_delete_one'); ?>')) {
        return;
    }

    $.post(admin_url + 'notes/notes/delete_note/' + noteId, {}).done(function(response) {
        var result = {};
        try { result = JSON.parse(response); } catch (e) {}
        if (result.success) {
            alert_float('success', '<?php echo _l('deleted'); ?>');
            reload_the_table();
        } else {
            alert_float('danger', result.message || '<?php echo _l('notes_delete_failed'); ?>');
        }
    });
}

function notes_make_columns_resizable(selector) {
    var $table = $(selector);
    $table.find('thead th').each(function() {
        var $th = $(this);
        if ($th.find('.notes-resize-handle').length === 0) {
            $th.append('<span class="notes-resize-handle"></span>');
        }
    });

    var startX = 0;
    var startWidth = 0;
    var $activeTh = null;

    $(document).on('mousedown', '.notes-resize-handle', function(e) {
        $activeTh = $(this).closest('th');
        startX = e.pageX;
        startWidth = $activeTh.outerWidth();
        $('body').addClass('notes-column-resizing');
        e.preventDefault();
    });

    $(document).on('mousemove', function(e) {
        if (!$activeTh) {
            return;
        }
        var newWidth = Math.max(60, startWidth + (e.pageX - startX));
        $activeTh.css('width', newWidth + 'px');
    });

    $(document).on('mouseup', function() {
        if ($activeTh) {
            $activeTh = null;
            $('body').removeClass('notes-column-resizing');
        }
    });
}

function notes_bulk_delete() {
    var ids = get_selected_note_ids();
    if (ids.length === 0) {
        alert_float('warning', '<?php echo _l('notes_select_at_least_one'); ?>');
        return;
    }

    if (!confirm('<?php echo _l('notes_confirm_mass_delete'); ?>')) {
        return;
    }

    $.post(admin_url + 'notes/notes/bulk_delete', {ids: ids}).done(function(response) {
        response = JSON.parse(response);
        if (response.success) {
            alert_float('success', '<?php echo _l('deleted'); ?>');
            $('.table_notes').DataTable().ajax.reload();
        }
    });
}
</script>

</body>
</html>
