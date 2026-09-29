<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$sources = [];
$types   = [];

$sourcesTable = db_prefix() . 'notes_sources';
$typesTable   = db_prefix() . 'notes_types';

if (!$CI->db->table_exists($sourcesTable) || !$CI->db->table_exists($typesTable)) {
    require module_dir_path('notes', 'install.php');
}

if ($CI->db->table_exists($sourcesTable)) {
    $sources = $CI->db->order_by('sort_order', 'ASC')->get($sourcesTable)->result();
}

if ($CI->db->table_exists($typesTable)) {
    $types = $CI->db->order_by('sort_order', 'ASC')->get($typesTable)->result();
}

/**
 * Render a compact, full-width settings row without horizontal scrolling.
 *
 * @param object $item
 * @param string $kind source|type
 */
function notes_render_settings_row($item, $kind)
{
    $isSource = $kind === 'source';
    $keyField = $isSource ? 'source_key' : 'type_key';
    $saveUrl  = $isSource ? 'notes/save_source' : 'notes/save_type';
    $deleteUrl = $isSource ? 'notes/delete_source/' : 'notes/delete_type/';
    ?>
    <?php echo form_open(admin_url($saveUrl), ['class' => 'notes-setting-row']); ?>
        <input type="hidden" name="id" value="<?php echo (int) $item->id; ?>">
        <div class="notes-settings-grid">
            <div class="notes-field notes-field-key">
                <label><?php echo _l('notes_key'); ?></label>
                <input
                    class="form-control"
                    name="<?php echo $keyField; ?>"
                    value="<?php echo html_escape($item->{$keyField}); ?>"
                    <?php echo !empty($item->is_system) ? 'readonly' : ''; ?>>
            </div>

            <div class="notes-field notes-field-name">
                <label><?php echo _l('notes_name'); ?></label>
                <input class="form-control" name="name" value="<?php echo html_escape($item->name); ?>" required>
            </div>

            <div class="notes-field notes-field-color">
                <label><?php echo _l('notes_color'); ?></label>
                <input type="color" class="form-control notes-color-picker" name="color" value="<?php echo html_escape($item->color); ?>">
            </div>

            <div class="notes-field notes-field-order">
                <label><?php echo _l('notes_sort_order'); ?></label>
                <input class="form-control" type="number" name="sort_order" value="<?php echo (int) $item->sort_order; ?>">
            </div>

            <div class="notes-field notes-field-active">
                <label><?php echo _l('notes_active'); ?></label>
                <div class="notes-checkbox-wrap">
                    <input type="checkbox" name="active" value="1" <?php echo !empty($item->active) ? 'checked' : ''; ?>>
                </div>
            </div>

            <div class="notes-field notes-field-actions">
                <label>&nbsp;</label>
                <div class="notes-action-buttons">
                    <button type="submit" class="btn btn-primary btn-xs" title="<?php echo _l('save'); ?>">
                        <i class="fa fa-save"></i>
                    </button>
                    <a class="btn btn-danger btn-xs _delete" href="<?php echo admin_url($deleteUrl . (int) $item->id); ?>" title="<?php echo _l('delete'); ?>">
                        <i class="fa fa-trash"></i>
                    </a>
                </div>
            </div>
        </div>
    <?php echo form_close(); ?>
    <?php
}

/**
 * Render the add-new form for a source or type.
 *
 * @param string $kind source|type
 */
function notes_render_add_form($kind)
{
    $isSource = $kind === 'source';
    $keyField = $isSource ? 'source_key' : 'type_key';
    $saveUrl  = $isSource ? 'notes/save_source' : 'notes/save_type';
    $defaultColor = $isSource ? '#3598DB' : '#169179';
    $placeholder = $isSource ? 'custom_source' : 'custom_type';
    ?>
    <div class="notes-add-block">
        <h5><?php echo _l($isSource ? 'notes_add_source' : 'notes_add_type'); ?></h5>
        <?php echo form_open(admin_url($saveUrl), ['class' => 'notes-setting-row notes-setting-row-new']); ?>
            <div class="notes-settings-grid">
                <div class="notes-field notes-field-key">
                    <label><?php echo _l('notes_key'); ?></label>
                    <input class="form-control" name="<?php echo $keyField; ?>" placeholder="<?php echo $placeholder; ?>" required>
                </div>

                <div class="notes-field notes-field-name">
                    <label><?php echo _l('notes_name'); ?></label>
                    <input class="form-control" name="name" placeholder="<?php echo _l('notes_name'); ?>" required>
                </div>

                <div class="notes-field notes-field-color">
                    <label><?php echo _l('notes_color'); ?></label>
                    <input type="color" class="form-control notes-color-picker" name="color" value="<?php echo $defaultColor; ?>">
                </div>

                <div class="notes-field notes-field-order">
                    <label><?php echo _l('notes_sort_order'); ?></label>
                    <input class="form-control" type="number" name="sort_order" value="100">
                </div>

                <div class="notes-field notes-field-active">
                    <label><?php echo _l('notes_active'); ?></label>
                    <div class="notes-checkbox-wrap">
                        <input type="checkbox" name="active" value="1" checked>
                    </div>
                </div>

                <div class="notes-field notes-field-actions">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-success btn-xs" title="<?php echo _l('add'); ?>">
                        <i class="fa fa-plus"></i>
                    </button>
                </div>
            </div>
        <?php echo form_close(); ?>
    </div>
    <?php
}
?>

<div class="row notes-settings-page">

    <div class="col-md-12">
        <div class="panel_s">
            <div class="panel-body">
                <h4><?php echo _l('notes_appearance'); ?></h4>
                <p class="text-muted"><?php echo _l('notes_appearance_help'); ?></p>
                <?php echo form_open(admin_url('notes/save_preferences'), ['class' => 'notes-setting-row notes-preferences-form']); ?>
                    <div class="notes-settings-grid notes-preferences-grid">
                        <div class="notes-field notes-field-color">
                            <label><?php echo _l('notes_default_note_color'); ?></label>
                            <input type="color" class="form-control notes-color-picker" name="notes_default_color" value="<?php echo html_escape(get_option('notes_default_color') ?: '#00A651'); ?>">
                        </div>
                        <div class="notes-field notes-field-actions">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-save"></i> <?php echo _l('save'); ?></button>
                        </div>
                    </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>

    <div class="col-md-12">
        <div class="panel_s">
            <div class="panel-body">
                <h4><?php echo _l('notes_sources'); ?></h4>
                <p class="text-muted"><?php echo _l('notes_sources_help'); ?></p>

                <div class="notes-settings-list">
                    <?php foreach ($sources as $source) {
                        notes_render_settings_row($source, 'source');
                    } ?>
                </div>

                <?php notes_render_add_form('source'); ?>
            </div>
        </div>
    </div>

    <div class="col-md-12">
        <div class="panel_s">
            <div class="panel-body">
                <h4><?php echo _l('notes_types'); ?></h4>
                <p class="text-muted"><?php echo _l('notes_types_help'); ?></p>

                <div class="notes-settings-list">
                    <?php foreach ($types as $type) {
                        notes_render_settings_row($type, 'type');
                    } ?>
                </div>

                <?php notes_render_add_form('type'); ?>
            </div>
        </div>
    </div>
</div>

<style>
.notes-settings-page .panel-body {
    overflow: visible;
}

.notes-settings-list {
    width: 100%;
}

.notes-setting-row {
    display: block;
    margin: 0;
    padding: 10px 0;
    border-bottom: 1px solid #e8e8e8;
}

.notes-setting-row-new {
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    padding: 12px;
    background: #fafafa;
}

.notes-settings-grid {
    display: grid;
    grid-template-columns: minmax(135px, 1.15fr) minmax(220px, 2fr) 88px 85px 70px 76px;
    gap: 10px;
    align-items: end;
    width: 100%;
}

.notes-field {
    min-width: 0;
}

.notes-field label {
    display: block;
    margin: 0 0 4px;
    font-size: 11px;
    line-height: 1.2;
    color: #6b7280;
    font-weight: 600;
}

.notes-field .form-control {
    width: 100%;
    height: 32px;
    min-width: 0;
    padding: 5px 8px;
    font-size: 12px;
}

.notes-color-picker {
    padding: 2px !important;
    cursor: pointer;
}

.notes-checkbox-wrap {
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #d2d6de;
    border-radius: 4px;
    background: #fff;
}

.notes-checkbox-wrap input {
    margin: 0;
}

.notes-action-buttons {
    display: flex;
    gap: 4px;
    align-items: center;
}

.notes-field-actions .btn {
    width: 30px;
    height: 30px;
    padding: 4px;
    line-height: 20px;
    border-radius: 4px;
}

.notes-add-block {
    margin-top: 16px;
}

.notes-add-block h5 {
    margin-bottom: 8px;
}

@media (max-width: 1199px) {
    .notes-settings-grid {
        grid-template-columns: minmax(130px, 1fr) minmax(200px, 1.7fr) 80px 75px 65px 72px;
        gap: 8px;
    }
}

@media (max-width: 991px) {
    .notes-settings-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .notes-field-actions {
        grid-column: 1 / -1;
    }

    .notes-field-actions label {
        display: none;
    }
}

@media (max-width: 600px) {
    .notes-settings-grid {
        grid-template-columns: 1fr;
    }

    .notes-field-actions {
        grid-column: auto;
    }

    .notes-action-buttons {
        justify-content: flex-start;
    }
}
</style>


<script>
(function($) {
    "use strict";

    $(document).on('submit', '.notes-setting-row', function(event) {
        event.preventDefault();
        var $form = $(this);
        var $button = $form.find('[type="submit"]');
        $button.prop('disabled', true);

        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: $form.serialize(),
            dataType: 'json'
        }).done(function(response) {
            if (response && response.success) {
                alert_float('success', response.message || '<?php echo js_escape(_l('notes_settings_saved')); ?>');
                window.onbeforeunload = null;
                $(window).off('beforeunload');
                window.location.href = admin_url + 'settings?group=notes_settings';
            } else {
                alert_float('danger', response && response.message ? response.message : '<?php echo js_escape(_l('notes_settings_save_failed')); ?>');
                $button.prop('disabled', false);
            }
        }).fail(function(xhr) {
            var message = '<?php echo js_escape(_l('notes_settings_save_failed')); ?>';
            if (xhr.responseJSON && xhr.responseJSON.message) { message = xhr.responseJSON.message; }
            alert_float('danger', message);
            $button.prop('disabled', false);
        });
    });
})(jQuery);
</script>
