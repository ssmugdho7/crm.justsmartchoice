<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$id = isset($link) && $link ? (int) $link->id : 0;
$title = isset($link) ? $link->title : '';
$url = isset($link) ? smart_choice_links_url_for_edit($link->url) : '';
$category = isset($link) ? $link->category : 'General';
$placement = isset($link) && !empty($link->placement) ? $link->placement : 'both';
$icon = isset($link) ? $link->icon : 'fa fa-link';
$target = isset($link) ? $link->target : '_self';
$rel = isset($link) ? $link->rel : 'noopener noreferrer';
$position = isset($link) ? $link->position : 1;
$is_active = isset($link) ? (int) $link->is_active : 1;
$is_internal = isset($link) ? (int) $link->is_internal : 1;
$notes = isset($link) ? $link->notes : '';
$text_color = isset($link) && !empty($link->text_color) ? $link->text_color : '#263238';
$text_shadow = isset($link) ? (int) $link->text_shadow : 0;
$selectedRoles = isset($link) && !empty($link->visible_to_roles) ? explode(',', $link->visible_to_roles) : [];
$selectedStaff = isset($link) && !empty($link->visible_to_staff) ? explode(',', $link->visible_to_staff) : [];
?>
<div class="modal-dialog smart-choice-links-modal-dialog">
    <div class="modal-content">
        <?php echo form_open(admin_url('smart_choice_links/save/' . $id), ['autocomplete' => 'off', 'id' => 'smart-choice-links-form']); ?>
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title"><?php echo $id ? _l('smart_choice_links_edit') : _l('smart_choice_links_add'); ?></h4>
        </div>
        <div class="modal-body scl-modal-compact">
            <div class="row">
                <div class="col-md-6"><?php echo render_input('title', 'smart_choice_links_title', $title, 'text', ['required' => true]); ?></div>
                <div class="col-md-3"><?php echo render_input('category', 'smart_choice_links_category', $category); ?></div>
                <div class="col-md-3">
                    <?php echo render_select('placement', [
                        ['id' => 'left', 'name' => _l('smart_choice_links_placement_left')],
                        ['id' => 'right', 'name' => _l('smart_choice_links_placement_right')],
                        ['id' => 'both', 'name' => _l('smart_choice_links_placement_both')],
                    ], ['id', 'name'], 'smart_choice_links_placement', $placement); ?>
                </div>

                <div class="col-md-12"><?php echo render_input('url', 'smart_choice_links_url', $url, 'text', ['required' => true, 'autocomplete' => 'off', 'data-scl-url-input' => '1']); ?></div>

                <div class="col-md-4"><?php echo render_input('icon', 'smart_choice_links_icon', $icon); ?></div>
                <div class="col-md-4">
                    <?php echo render_select('target', [
                        ['id' => '_self', 'name' => _l('smart_choice_links_same_tab')],
                        ['id' => '_blank', 'name' => _l('smart_choice_links_new_tab')],
                        ['id' => '_window', 'name' => _l('smart_choice_links_new_window')],
                        ['id' => '_popup', 'name' => _l('smart_choice_links_popup_window')],
                    ], ['id', 'name'], 'smart_choice_links_target', $target); ?>
                </div>
                <div class="col-md-4"><?php echo render_input('position', 'smart_choice_links_position', $position, 'number'); ?></div>

                <div class="col-md-6">
                    <?php echo render_input('text_color', 'smart_choice_links_text_color', $text_color, 'color'); ?>
                </div>
                <div class="col-md-6 scl-shadow-box">
                    <div class="checkbox checkbox-primary">
                        <input type="checkbox" name="text_shadow" id="text_shadow" value="1" <?php echo $text_shadow ? 'checked' : ''; ?>>
                        <label for="text_shadow"><?php echo _l('smart_choice_links_text_shadow'); ?></label>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="checkbox checkbox-primary">
                        <input type="checkbox" name="is_active" id="is_active" value="1" <?php echo $is_active ? 'checked' : ''; ?>>
                        <label for="is_active"><?php echo _l('smart_choice_links_active'); ?></label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="checkbox checkbox-primary">
                        <input type="checkbox" name="is_internal" id="is_internal" value="1" <?php echo $is_internal ? 'checked' : ''; ?>>
                        <label for="is_internal"><?php echo _l('smart_choice_links_internal'); ?></label>
                    </div>
                </div>
                <div class="col-md-6">
                    <?php echo render_select('visible_to_roles[]', $roles, ['roleid', 'name'], 'smart_choice_links_visible_roles', $selectedRoles, ['multiple' => true, 'data-actions-box' => true], [], '', '', false); ?>
                </div>
                <div class="col-md-6">
                    <?php echo render_select('visible_to_staff[]', $staff, ['staffid', ['firstname', 'lastname']], 'smart_choice_links_visible_staff', $selectedStaff, ['multiple' => true, 'data-actions-box' => true], [], '', '', false); ?>
                </div>
                <div class="col-md-12"><?php echo render_textarea('notes', 'smart_choice_links_notes', $notes, ['rows' => 2]); ?></div>
            </div>
            <div class="alert alert-info scl-help-box">
                <strong><?php echo _l('smart_choice_links_best_way'); ?></strong><br>
                <?php echo _l('smart_choice_links_best_way_text'); ?><br>
                <code>admin/settings</code> · <code>admin/invoices</code> · <code>admin/modules</code>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default btn-sm" data-dismiss="modal"><?php echo _l('close'); ?></button>
            <button type="submit" class="btn btn-primary btn-sm"><?php echo _l('submit'); ?></button>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>
