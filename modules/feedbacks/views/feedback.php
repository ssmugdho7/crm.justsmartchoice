<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content"><?php $this->load->view('feedbacks/_navigation'); ?>
        <div class="row">
            <div class="col-md-5" id="feedback-add-edit-wrapper">
                <div class="row">
                    <div class="col-md-12">
                        <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700">
                            <?php echo $title; ?>
                        </h4>
                        <?php if (isset($feedback) && $feedback) { $scSavedDesign = null; $scSavedSettings = []; if ($this->db->table_exists(db_prefix().'feedbacks_design')) { $scSavedDesign = $this->db->where('feedbackid', (int)$feedback->feedbackid)->get(db_prefix().'feedbacks_design')->row(); if ($scSavedDesign) { $scSavedSettings = json_decode($scSavedDesign->settings_json, true) ?: []; } } ?>
                        <div class="panel_s"><div class="panel-body"><h4><?php echo _l('fb_design_title'); ?></h4><div class="btn-group mtop10"><a class="btn btn-default" href="<?php echo admin_url('feedbacks/export_questionnaire/'.$feedback->feedbackid); ?>"><i class="fa fa-download"></i> <?php echo _l('fb_export_json'); ?></a></div><form method="post" action="<?php echo admin_url('feedbacks/save_design/'.$feedback->feedbackid); ?>" class="mtop15"><input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>"><div class="row"><div class="col-md-3"><label><?php echo _l('fb_theme'); ?></label><select name="theme" class="form-control"><option value="smartchoice"><?php echo _l('fb_theme_smartchoice'); ?></option><option value="light"><?php echo _l('fb_theme_light'); ?></option><option value="dark"><?php echo _l('fb_theme_dark'); ?></option></select></div><div class="col-md-3"><label><?php echo _l('fb_primary_color'); ?></label><input type="color" name="primary_color" class="form-control" value="<?php echo html_escape($scSavedSettings['primary_color'] ?? '#2f7d32'); ?>"></div><div class="col-md-3"><label><?php echo _l('fb_background_color'); ?></label><input type="color" name="background_color" class="form-control" value="<?php echo html_escape($scSavedSettings['background_color'] ?? '#ffffff'); ?>"></div><div class="col-md-3"><label><?php echo _l('fb_corner_radius'); ?></label><input type="number" name="corner_radius" min="0" max="32" value="<?php echo (int)($scSavedSettings['corner_radius'] ?? 12); ?>" class="form-control"></div><div class="col-md-3"><label><?php echo _l('fb_font_size'); ?></label><input type="number" name="font_size" min="12" max="22" value="<?php echo (int)($scSavedSettings['font_size'] ?? 16); ?>" class="form-control"></div></div><div class="row mtop10"><div class="col-md-6"><label><?php echo _l('fb_custom_html_header'); ?></label><textarea name="header_html" rows="4" class="form-control" placeholder="Optional custom header HTML"><?php echo html_escape($scSavedDesign->header_html ?? ''); ?></textarea></div><div class="col-md-6"><label><?php echo _l('fb_footer_mode'); ?></label><select name="footer_mode" class="form-control"><option value="crm"><?php echo _l('fb_footer_crm'); ?></option><option value="custom"><?php echo _l('fb_footer_custom'); ?></option></select><label><?php echo _l('fb_custom_html_footer'); ?></label><textarea name="footer_html" rows="4" class="form-control" placeholder="Optional custom footer HTML"><?php echo html_escape($scSavedDesign->footer_html ?? ''); ?></textarea></div></div><button class="btn btn-primary mtop10" type="submit"><?php echo _l('fb_save_design'); ?></button></form><hr><form method="post" action="<?php echo admin_url('feedbacks/add_advanced_question'); ?>" class="row"><input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>"><input type="hidden" name="feedbackid" value="<?php echo (int)$feedback->feedbackid; ?>"><div class="col-md-4"><label><?php echo _l('fb_advanced_question_type'); ?></label><select name="field_type" class="form-control"><option value="dropdown"><?php echo _l('fb_dropdown'); ?></option><option value="toggle"><?php echo _l('fb_yes_no_toggle'); ?></option><option value="rating"><?php echo _l('fb_rating'); ?></option><option value="linear_scale"><?php echo _l('fb_linear_scale'); ?></option><option value="number"><?php echo _l('fb_number_keypad'); ?></option><option value="currency"><?php echo _l('fb_currency_keypad'); ?></option><option value="percentage"><?php echo _l('fb_percentage'); ?></option><option value="date"><?php echo _l('fb_date'); ?></option><option value="time"><?php echo _l('fb_time'); ?></option><option value="email"><?php echo _l('fb_email'); ?></option><option value="phone"><?php echo _l('fb_phone'); ?></option><option value="url"><?php echo _l('fb_url'); ?></option></select></div><div class="col-md-3"><label><?php echo _l('fb_dropdown_options'); ?></label><input type="text" name="options_text" class="form-control" placeholder="Excellent|Good|Average|Poor"></div><div class="col-md-1"><label>Min</label><input type="number" name="min_value" class="form-control" value="1"></div><div class="col-md-1"><label>Max</label><input type="number" name="max_value" class="form-control" value="5"></div><div class="col-md-3"><label>&nbsp;</label><button class="btn btn-success btn-block" type="submit"><?php echo _l('fb_add_advanced_field'); ?></button></div></form><p class="text-muted mtop10"><?php echo _l('fb_advanced_field_note'); ?></p></div></div>
                        <?php } ?>
                        <?php echo form_open($this->uri->uri_string(), ['id' => 'feedback_form']); ?>
                        <div class="panel_s"><div class="panel-body">
                                <?php $value = (isset($feedback) ? $feedback->subject : ''); ?>
                                <?php $attrs = (isset($feedback) ? [] : ['autofocus' => true]); ?>
                                <?php echo render_input('subject', 'feedback_add_edit_subject', $value, 'text', $attrs); ?>
                                <p class="bold"><?php echo _l('feedback_add_edit_short_description_view'); ?></p>
                                <?php $value = (isset($feedback) ? $feedback->viewdescription : ''); ?>
                                <?php echo render_textarea('viewdescription', '', $value, [], [], '', 'tinymce-view-description'); ?>
                                <p class="bold"><?php echo _l('feedback_add_edit_email_description'); ?></p>
                                <?php $contents = ''; if (isset($feedback)) {
    $contents                                   = $feedback->description;
} ?>
                                <?php echo render_textarea('description', '', $contents, [], [], '', 'tinymce-email-description'); ?>
                                <p class="bold">
                                    <?php echo _l('feedback_include_feedback_link'); ?> : <span
                                        class="text-info">{feedback_link}</span>
                                </p>
                                <?php if ($found_custom_fields) { ?>
                                <hr />
                                <p class="bold tooltip-pointer" data-toggle="tooltip"
                                    title="<?php echo _l('feedback_mail_lists_custom_fields_tooltip'); ?>">
                                    <?php echo _l('feedback_available_mail_lists_custom_fields'); ?></p>
                                <?php } ?>
                                <?php
                        foreach ($mail_lists as $list) {
                            if (count($list['customfields']) == 0) {
                                continue;
                            } ?>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown"
                                        aria-haspopup="true" aria-expanded="false">
                                        <?php echo $list['name']; ?> <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <?php foreach ($list['customfields'] as $custom_field) { ?>
                                        <li><a href="#" class="add_email_list_custom_field_to_feedback"
                                                data-toggle="tooltip"
                                                title="{<?php echo $custom_field['fieldslug']; ?>}"
                                                data-slug="{<?php echo $custom_field['fieldslug']; ?>}"><?php echo $custom_field['fieldname']; ?></a>
                                        </li>
                                        <?php } ?>
                                    </ul>
                                </div>
                                <?php
                        } ?>
                                <hr />
                                <div class="clearfix"></div>
                                <?php $value = (isset($feedback) ? $feedback->fromname : ''); ?>
                                <?php echo render_input('fromname', 'feedback_add_edit_from', $value); ?>
                                <?php $value = (isset($feedback) ? $feedback->redirect_url : ''); ?>
                                <?php echo render_input('redirect_url', 'feedback_add_edit_redirect_url', $value, 'text', ['data-toggle' => 'tooltip', 'title' => 'feedback_add_edit_red_url_note']); ?>
                                <div class="checkbox checkbox-primary">
                                    <input type="checkbox" name="disabled" id="disabled" <?php if (isset($feedback) && $feedback->active == 0) {
                            echo 'checked';
                        } ?>>
                                    <label for="disabled"><?php echo _l('feedback_add_edit_disabled'); ?></label>
                                </div>
                                <div class="checkbox checkbox-primary">
                                    <input type="checkbox" name="onlyforloggedin" id="onlyforloggedin" <?php if (isset($feedback) && $feedback->onlyforloggedin == 1) {
                            echo 'checked';
                        } ?>>
                                    <label
                                        for="onlyforloggedin"><?php echo _l('feedback_add_edit_only_for_logged_in'); ?></label>
                                </div>
                            </div>
                            <div class="panel-footer text-right">
                                <button type="submit" class="btn btn-primary">
                                    <?php echo _l('submit'); ?>
                                </button>
                            </div>
                        </div>
                        <?php echo form_close(); ?>
                    </div>

                </div>
            </div>
            <div class="col-md-7 tw-mt-10" id="feedback_questions_wrapper">
                <div class="panel_s tw-mt-1">
                    <div class="panel-body">
                        <?php if (isset($feedback)) { ?>
                        <ul class="nav nav-tabs tabs-in-body-no-margin" role="tablist">
                            <li role="presentation" class="active">
                                <a href="#feedback_questions_tab" aria-controls="feedback_questions_tab" role="tab"
                                    data-toggle="tab">
                                    <?php echo _l('feedback_questions_string'); ?>
                                </a>
                            </li>
                            <li role="presentation">
                                <a href="#feedback_send_tab" aria-controls="feedback_send_tab" role="tab" data-toggle="tab">
                                    <?php echo _l('send_feedback_string'); ?>
                                </a>
                            </li>
                            <li class="toggle_view">
                                <a href="#" onclick="feedback_toggle_full_view(); return false;" data-toggle="tooltip"
                                    data-title="<?php echo _l('toggle_full_view'); ?>">
                                    <i class="fa fa-expand"></i></a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div role="tabpanel" class="tab-pane active" id="feedback_questions_tab">
                                <div class="_buttons">
                                    <?php
                           if (total_rows(db_prefix() . 'feedbackresultsets', 'feedbackid=' . $feedback->feedbackid) > 0) { ?>
                                    <a href="<?php echo admin_url('feedbacks/results/' . $feedback->feedbackid); ?>"
                                         class="btn btn-success pull-right mleft10 btn-with-tooltip"
                                        data-toggle="tooltip" data-placement="bottom"
                                        data-title="<?php echo _l('feedback_list_view_results_tooltip'); ?>"><i
                                            class="fa fa-area-chart"></i></a>
                                    <?php } ?>
                                    <!-- Single button -->
                                    <a href="<?php echo site_url('feedbacks/participate/index/' . $feedback->feedbackid . '/' . $feedback->hash); ?>"
                                         class="btn btn-success pull-right mleft10 btn-with-tooltip"
                                        data-toggle="tooltip" data-placement="bottom"
                                        data-title="<?php echo _l('feedback_list_view_tooltip'); ?>"><i
                                            class="fa fa-eye"></i></a>
                                    <?php if (has_permission('feedbacks', '', 'edit')) { ?>
                                    <div class="btn-group pull-right">
                                        <button type="button" class="btn btn-primary dropdown-toggle"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <?php echo _l('feedback_insert_field'); ?> <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a href="#"
                                                    onclick="add_feedback_question('checkbox',<?php echo $feedback->feedbackid; ?>);return false;">
                                                    <?php echo _l('feedback_field_checkbox'); ?></a>
                                            </li>
                                            <li>
                                                <a href="#"
                                                    onclick="add_feedback_question('radio',<?php echo $feedback->feedbackid; ?>);return false;">
                                                    <?php echo _l('feedback_field_radio'); ?></a>
                                            </li>
                                            <li>
                                                <a href="#"
                                                    onclick="add_feedback_question('input',<?php echo $feedback->feedbackid; ?>);return false;">
                                                    <?php echo _l('feedback_field_input'); ?></a>
                                            </li>
                                            <li>
                                                <a href="#"
                                                    onclick="add_feedback_question('textarea',<?php echo $feedback->feedbackid; ?>);return false;">
                                                    <?php echo _l('feedback_field_textarea'); ?></a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <?php } ?>
                                <div class="clearfix"></div>
                                <hr />
                                <?php
                        $question_area = '<ul class="list-unstyled feedback_question_callback" id="feedback_questions">';
                        if (count($feedback->questions) > 0) {
                            foreach ($feedback->questions as $question) {
                                $question_area .= '<li>';
                                $question_area .= '<div class="form-group question">';
                                $question_area .= '<div class="checkbox checkbox-primary required">';
                                if ($question['required'] == 1) {
                                    $_required = ' checked';
                                } else {
                                    $_required = '';
                                }
                                $question_area .= '<input type="checkbox" id="req_' . $question['questionid'] . '" onchange="update_question(this,\'' . $question['boxtype'] . '\',' . $question['questionid'] . ');" data-question_required="' . $question['questionid'] . '" name="required[]" ' . $_required . '>';
                                $question_area .= '<label for="req_' . $question['questionid'] . '">' . _l('feedback_question_required') . '</label>';
                                $question_area .= '</div>';
                                $question_area .= '<input type="hidden" value="" name="order[]">';
                                // used only to identify input key no saved in database
                                $question_area .= '<label for="' . $question['questionid'] . '" class="control-label display-block">' . _l('question_string') . '
                             <a href="#" onclick="update_question(this,\'' . $question['boxtype'] . '\',' . $question['questionid'] . '); return false;" class="pull-right update-question-button"><i class="fa fa-refresh text-success question_update"></i></a>
                             <a href="#" onclick="remove_question_from_database(this,' . $question['questionid'] . '); return false;" class="pull-right"><i class="fa fa-remove text-danger"></i></a>
                         </label>';
                                $question_area .= '<input type="text" onblur="update_question(this,\'' . $question['boxtype'] . '\',' . $question['questionid'] . ');" data-questionid="' . $question['questionid'] . '" class="form-control questionid" value="' . html_escape($question['question']) . '">';
                                if ($question['boxtype'] == 'textarea') {
                                    $question_area .= '<textarea class="form-control mtop20" disabled="disabled" rows="6">' . _l('feedback_question_only_for_preview') . '</textarea>';
                                } elseif ($question['boxtype'] == 'checkbox' || $question['boxtype'] == 'radio') {
                                    $question_area .= '<div class="row">';
                                    $x = 0;
                                    foreach ($question['box_descriptions'] as $box_description) {
                                        $box_description_icon_class = 'fa-minus text-danger';
                                        $box_description_function   = 'remove_box_description_from_database(this,' . $box_description['questionboxdescriptionid'] . '); return false;';
                                        if ($x == 0) {
                                            $box_description_icon_class = 'fa-plus';
                                            $box_description_function   = 'add_box_description_to_database(this,' . $question['questionid'] . ',' . $question['boxid'] . '); return false;';
                                        }
                                        $question_area .= '<div class="box_area">';

                                        $question_area .= '<div class="col-md-12">';
                                        $question_area .= '<a href="#" class="add_remove_action feedback_add_more_box" onclick="' . $box_description_function . '"><i class="fa ' . $box_description_icon_class . '"></i></a>';
                                        $question_area .= '<div class="' . $question['boxtype'] . ' ' . $question['boxtype'] . '-primary">';
                                        $question_area .= '<input type="' . $question['boxtype'] . '" disabled="disabled"/>';
                                        $question_area .= '
                            <label>
                                <input type="text" onblur="update_question(this,\'' . $question['boxtype'] . '\',' . $question['questionid'] . ');" data-box-descriptionid="' . $box_description['questionboxdescriptionid'] . '" value="' . html_escape($box_description['description']) . '" class="feedback_input_box_description">
                            </label>';
                                        $question_area .= '</div>';
                                        $question_area .= '</div>';
                                        $question_area .= '</div>';
                                        $x++;
                                    }
                                    // end box row
                                    $question_area .= '</div>';
                                } else {
                                    $question_area .= '<input type="text" class="form-control mtop20" disabled="disabled" value="' . _l('feedback_question_only_for_preview') . '">';
                                }
                                $question_area .= '</div>';
                                $question_area .= '</li>';
                            }
                        }
                        $question_area .= '</ul>';
                        echo $question_area;
                        ?>
                            </div>
                            <div role="tabpanel" class="tab-pane" id="feedback_send_tab">

                                <?php echo form_open('admin/feedbacks/send/' . $feedback->feedbackid); ?>

                                <p class="mbot30 text-warning">
                                    <?php echo _l('feedback_send_mail_lists_note_logged_in'); ?></p>
                                <div class="form-group">
                                    <div class="checkbox checkbox-primary">
                                        <input type="checkbox" name="send_feedback_to[clients]" id="ml_clients">
                                        <label
                                            for="ml_clients"><?php echo _l('feedback_send_mail_list_clients'); ?></label>
                                    </div>

                                    <div class="customer-groups" style="display:none;">
									<div class="form-group select-placeholder">
                                    <label for="clientid"
                                        class="control-label"><?php echo _l('project_customer'); ?></label>
                                    <select id="clientid" name="clientid" data-live-search="true" data-width="100%"
                                        class="ajax-search"
                                        data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                        <?php $selected = (isset($project) ? $project->clientid : '');
                             if ($selected == '') {
                                 $selected = (isset($customer_id) ? $customer_id: '');
                             }
                             if ($selected != '') {
                                 $rel_data = get_relation_data('customer', $selected);
                                 $rel_val  = get_relation_values($rel_data, 'customer');
                                 echo '<option value="' . $rel_val['id'] . '" selected>' . $rel_val['name'] . '</option>';
                             } ?>
                                    </select>
                                </div>

                                        <div class="clearfix"></div>
                                        <div class="checkbox checkbox-primary mleft10">
                                            <input type="checkbox" checked name="ml_customers_all"
                                                id="ml_customers_all">
                                            <label
                                                for="ml_customers_all"><?php echo _l('feedback_customers_all'); ?></label>
                                        </div>
                                        <hr class="hr-10" />
                                        <?php foreach ($customers_groups as $group) { ?>
                                        <div class="checkbox checkbox-primary mleft10 feedback-customer-groups">
                                            <input type="checkbox" name="customer_group[<?php echo $group['id']; ?>]"
                                                id="ml_customer_group_<?php echo $group['id']; ?>">
                                            <label
                                                for="ml_customer_group_<?php echo $group['id']; ?>"><?php echo $group['name']; ?></label>
                                        </div>
                                        <?php } ?>
                                        <?php
                                if (is_gdpr() && (get_option('gdpr_enable_consent_for_contacts') == '1')) { ?>
                                        <select name="contacts_consent[]" title="<?php echo _l('gdpr_consent'); ?>"
                                            multiple="true" id="contacts_consent" class="selectpicker"
                                            data-width="100%">
                                            <?php foreach ($purposes as $purpose) { ?>
                                            <option value="<?php echo $purpose['id']; ?>">
                                                <?php echo $purpose['name']; ?>
                                            </option>
                                            <?php } ?>
                                        </select>
                                        <?php } ?>
                                    </div>
                                    <hr />
                                    <div class="checkbox checkbox-primary">
                                        <input type="checkbox" name="send_feedback_to[leads]" id="ml_leads">
                                        <label for="ml_leads"><?php echo _l('leads'); ?></label>
                                    </div>
                                    <div class="leads-statuses" style="display:none;">
                                        <div class="clearfix"></div>
                                        <div class="checkbox checkbox-primary mleft10">
                                            <input type="checkbox" checked name="leads_all" id="ml_leads_all">
                                            <label for="ml_leads_all"><?php echo _l('leads_all'); ?></label>
                                        </div>
                                        <hr class="hr-10" />

                                        <?php foreach ($leads_statuses as $status) { ?>
                                        <div class="checkbox checkbox-primary mleft10 feedback-lead-status">
                                            <input type="checkbox" name="leads_status[<?php echo $status['id']; ?>]"
                                                id="ml_leads_status_<?php echo $status['id']; ?>">
                                            <label
                                                for="ml_leads_status_<?php echo $status['id']; ?>"><?php echo $status['name']; ?></label>
                                        </div>
                                        <?php } ?>
                                        <?php
                                if (is_gdpr() && (get_option('gdpr_enable_consent_for_leads') == '1')) { ?>
                                        <select name="leads_consent[]" title="<?php echo _l('gdpr_consent'); ?>"
                                            multiple="true" id="leads_consent" class="selectpicker" data-width="100%">
                                            <?php foreach ($purposes as $purpose) { ?>
                                            <option value="<?php echo $purpose['id']; ?>">
                                                <?php echo $purpose['name']; ?>
                                            </option>
                                            <?php } ?>
                                        </select>
                                        <?php } ?>
                                    </div>
                                    <hr />
                                    <div class="checkbox checkbox-primary">
                                        <input type="checkbox" name="send_feedback_to[staff]" id="ml_staff">
                                        <label for="ml_staff"><?php echo _l('feedback_send_mail_list_staff'); ?></label>
                                    </div>
                                    <?php if (count($mail_lists) > 0) { ?>
                                    <hr />
                                    <p class="bold"><?php echo _l('feedback_send_mail_lists_string'); ?></p>
                                    <?php foreach ($mail_lists as $list) { ?>
                                    <div class="checkbox checkbox-primary">
                                        <input type="checkbox" id="ml_custom_<?php echo $list['listid']; ?>"
                                            name="send_feedback_to[<?php echo $list['listid']; ?>]">
                                        <label
                                            for="ml_custom_<?php echo $list['listid']; ?>"><?php echo $list['name']; ?></label>
                                    </div>
                                    <?php } ?>
                                    <?php } ?>


                                    <?php
                                    $scAudiences = []; if ($this->db->table_exists(db_prefix().'feedbacks_audiences') && $this->db->table_exists(db_prefix().'feedbacks_audience_members') && has_permission('feedbacks','','view')) { $scAudiences = $this->db->order_by('name','ASC')->get(db_prefix().'feedbacks_audiences')->result_array(); }
                                    if (!empty($scAudiences)) { ?><hr><p class="bold">Custom audiences</p><?php foreach ($scAudiences as $scAudience) { $scCount=$this->db->where('audience_id',(int)$scAudience['id'])->count_all_results(db_prefix().'feedbacks_audience_members'); ?><div class="checkbox checkbox-primary"><input type="checkbox" name="audience_ids[]" id="sc_audience_<?php echo (int)$scAudience['id']; ?>" value="<?php echo (int)$scAudience['id']; ?>"><label for="sc_audience_<?php echo (int)$scAudience['id']; ?>"><?php echo html_escape($scAudience['name']); ?> (<?php echo $scCount; ?>)</label></div><?php } } ?>
                                    <?php if (total_rows(db_prefix() . 'feedbacksendlog', ['iscronfinished' => 0, 'feedbackid' => $feedback->feedbackid]) > 0) { ?>
                                    <p class="text-warning"><?php echo _l('feedback_send_notice'); ?></p>
                                    <?php } ?>
                                    <?php foreach ($send_log as $log) { ?>
                                    <p>
                                        <?php if (has_permission('feedbacks', '', 'delete')) { ?>
                                        <a href="<?php echo admin_url('feedbacks/remove_feedback_send/' . $log['id']); ?>"
                                            class="_delete text-danger"><i class="fa fa-remove"></i></a>
                                        <?php } ?>
                                        <?php echo _l('feedback_added_to_queue', _dt($log['date'])); ?>
                                        (
                                        <?php echo($log['iscronfinished'] == 0 ? _l('feedback_send_till_now') . ' ' : '') ?>
                                        <?php echo _l('feedback_send_to_total', $log['total']); ?> )
                                        <br />
                                        <b class="bold">
                                            <?php echo _l('feedback_send_finished', ($log['iscronfinished'] == 1 ? _l('settings_yes') : _l('settings_no'))); ?>
                                        </b>
                                    </p>
                                    <?php if (!empty($log['send_to_mail_lists'])) { ?>
                                    <p>
                                        <b><?php echo _l('feedback_send_to_lists'); ?>:</b> <?php
                                 $send_lists = unserialize($log['send_to_mail_lists']);
                                 foreach ($send_lists as $send_list) {
                                     $last = end($send_lists);
                                     echo _l($send_list, '', false) . ($last == $send_list ? '':',');
                                 }
                                 ?>
                                    </p>
                                    <?php } ?>
                                    <hr />
                                    <?php } ?>

                                    <button type="submit"
                                        class="btn btn-primary"><?php echo _l('feedback_send_string'); ?></button>
                                </div>
                                <?php echo form_close(); ?>

                            </div>
                            <?php } else { ?>
                            <p class="no-margin"><?php echo _l('feedback_create_first'); ?></p>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php init_tail(); ?>
<script src="<?php echo module_dir_url('feedbacks','assets/js/feedbacks.js'); ?>?v=255"></script>
    <script>
    $(function() {
        $('#feedback_questions_wrapper').on('click', '[data-toggle=dropdown]', function(e) { if (!$.fn.dropdown) { e.preventDefault(); $(this).parent().toggleClass('open'); } });
        $('#feedback_questions_wrapper').on('click', 'a[data-toggle=tab]', function(e) { if (!$.fn.tab) { e.preventDefault(); $(this).parent().addClass('active').siblings().removeClass('active'); $($(this).attr('href')).addClass('active').siblings('.tab-pane').removeClass('active'); } });
        if (location.search.indexOf('tab=feedback_send_tab') !== -1) $('a[href="#feedback_send_tab"]').tab('show');
        init_editor('.tinymce-email-description');
        init_editor('.tinymce-view-description');
    });
    </script>
    </body>

    </html>