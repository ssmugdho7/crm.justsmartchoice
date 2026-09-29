<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12>">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row _buttons">
                            <div class="col-md-8">
                                <h4 class=""><?php echo html_escape($title); ?></h4>
                            </div>
                            <div class="col-md-4">
                                <div class="btn-group pull-right">
                                    <?php
                                        $_data = '';
                                        $_data = '<a href="'.admin_url('perfex_popup/popups/builder/'. $item->code).'" class="btn btn-success mright5"><i class="fa fa-magic"></i> '._l('builder').'</a>';
                                        $_data .= '<a href="javascript:void(0)" data-code="'.$item->code.'" class="btn_install_popup btn btn-default mright5"><i class="fa fa-anchor"></i> '._l('install').'</a>';
                                        echo $_data;
                                    ?>
                                </div>
                            </div>
                        </div>
                        <hr class="hr-panel-heading" />
                       <?php echo form_open_multipart($this->uri->uri_string(),array('id'=>'form_setting')) ;?>

                        <div class="horizontal-scrollable-tabs">
                            <div class="scroller scroller-left arrow-left"><i class="fa fa-angle-left"></i></div>
                            <div class="scroller scroller-right arrow-right"><i class="fa fa-angle-right"></i></div>
                            <div class="horizontal-tabs">
                                <ul class="nav nav-tabs nav-tabs-horizontal border-0" role="tablist">
                                    <li role="presentation" class="active">
                                        <a href="#tab_general" aria-controls="tab_general" role="tab" data-toggle="tab">
                                            <?php echo _l('general'); ?>
                                        </a>
                                    </li>
                                    <li role="presentation">
                                        <a href="#tab_display_trigger"  aria-controls="tab_display_trigger" role="tab" data-toggle="tab">
                                            <?php echo _l('display_trigger'); ?>
                                        </a>
                                    </li>
                                    <li role="presentation">
                                        <a href="#tab_form"  aria-controls="tab_form" role="tab" data-toggle="tab">
                                            <?php echo _l('form'); ?>
                                        </a>
                                    </li>
                                </ul>
                                <div class="tab-content">
                                    <div role="tabpanel" class="tab-pane active" id="tab_general">
                                        <?php $attrs = array('autofocus'=>true); ?>
                                        <?php $value = ($item->name); ?>
                                        <?php echo render_input('name','name',$value,'text',$attrs); ?>
                                        <div class="checkbox checkbox-primary">
                                            <input type="checkbox" name="is_enabled" id="is_enabled" <?php if(isset($item)){if($item->is_enabled == 1){echo 'checked';} } else {echo 'checked';} ?>>
                                            <label for="is_enabled"><?php echo _l('is_enabled'); ?></label>
                                        </div>
                                        <div class="row">
                                          <div class="col-md-8">
                                            <?php
                                             $selected = '';
                                             foreach ($members as $staff) {
                                                 if (isset($item) && $item->responsible == $staff['staffid']) {
                                                     $selected = $staff['staffid'];
                                                 }
                                             }
                                             ?>
                                            <hr />
                                            <h4 class="title-tab-content pb-0"><?php echo _l('leads_import_assignee'); ?> & <?php echo _l('notification_settings'); ?></h4>
                                            <?php echo render_select('responsible', $members, array('staffid', array('firstname', 'lastname')), 'leads_import_assignee', $selected); ?>
                                            <div class="clearfix"></div>
                                            <div class="checkbox checkbox-primary">
                                              <input type="checkbox" name="notify_lead_imported" id="notify_lead_imported" <?php if (isset($item) && $item->notify_lead_imported == 1 || !isset($item)) {
                                              echo 'checked';
                                              } ?>>
                                              <label for="notify_lead_imported"><?php echo _l('leads_email_integration_notify_when_lead_imported'); ?></label>
                                            </div>
                                            <div class="select-notification-settings<?php if (isset($item) && $item->notify_lead_imported == '0') {
                                              echo ' hide';
                                              } ?>">
                                              <hr />
                                              <div class="radio radio-primary radio-inline">
                                                <input type="radio" name="notify_type" value="specific_staff" id="specific_staff" <?php if (isset($item) && $item->notify_type == 'specific_staff' || !isset($item)) {
                                                echo 'checked';
                                                } ?>>
                                                <label for="specific_staff"><?php echo _l('specific_staff_members'); ?></label>
                                              </div>
                                              <div class="radio radio-primary radio-inline">
                                                <input type="radio" name="notify_type" id="roles" value="roles" <?php if (isset($item) && $item->notify_type == 'roles') {
                                                echo 'checked';
                                                } ?>>
                                                <label for="roles"><?php echo _l('staff_with_roles'); ?></label>
                                              </div>
                                              <div class="radio radio-primary radio-inline">
                                                <input type="radio" name="notify_type" id="assigned" value="assigned" <?php if (isset($item) && $item->notify_type == 'assigned') {
                                                echo 'checked';
                                                } ?>>
                                                <label for="assigned"><?php echo _l('notify_assigned_user'); ?></label>
                                              </div>
                                              <div class="clearfix mtop15"></div>
                                              <div id="specific_staff_notify" class="<?php if (isset($item) && $item->notify_type != 'specific_staff') {
                                                echo 'hide';
                                                } ?>">
                                                <?php
                                                $selected = array();
                                                if (isset($item) && $item->notify_type == 'specific_staff') {
                                                $selected = unserialize($item->notify_ids);
                                                }
                                                ?>
                                                <?php echo render_select('notify_ids_staff[]', $members, array('staffid', array('firstname', 'lastname')), 'leads_email_integration_notify_staff', $selected, array('multiple'=>true)); ?>
                                              </div>
                                              <div id="role_notify" class="<?php if (isset($item) && $item->notify_type != 'roles' || !isset($item)) {
                                                echo 'hide';} ?>">
                                                <?php
                                                $selected = array();
                                                if (isset($item) && $item->notify_type == 'roles') {
                                                $selected = unserialize($item->notify_ids);
                                                }
                                                ?>
                                                <?php echo render_select('notify_ids_roles[]', $roles, array('roleid', array('name')), 'leads_email_integration_notify_roles', $selected, array('multiple'=>true)); ?>
                                              </div>
                                            </div>
                                          </div>
                                        </div>
                                    </div>
                                    <div role="tabpanel" class="tab-pane" id="tab_display_trigger">
                                         <div class="d-flex">
                                            <h4 class="title-tab-content pb-0"><?php echo _l('display_trigger'); ?></h4>
                                        </div>
                                        <div class="form-group" id="display_trigger">
                                            <label><?php echo _l('display_trigger'); ?></label>
                                            <div class="input-group d-flex">
                                                <select class="form-control trigger-type-select" name="display_trigger">
                                                    <option value="delay" data-placeholder="<?php echo _l('Number of seconds to wait until popup shows up'); ?>" <?php echo $item->settings->display_trigger == 'delay' ? 'selected' : null; ?>><?php echo _l('Delay'); ?></option>
                                                    <option value="exit_intent" <?php echo $item->settings->display_trigger == 'exit_intent' ? 'selected' : null; ?>><?php echo _l('Exit Intent'); ?></option>
                                                    <option value="scroll" data-placeholder="<?php echo _l('Percent of scrolling from the top down'); ?>" <?php echo $item->settings->display_trigger == 'scroll' ? 'selected' : null; ?>><?php echo _l('Scroll Percentage'); ?></option>
                                                </select>
                                                <input type="number" min="0" name="display_trigger_value" class="form-control" value="<?php echo $item->settings->display_trigger_value; ?>" />
                                            </div>
                                            <small class="form-text text-muted"><?php echo _l('On what event the popup should show up.'); ?></small>
                                        </div>
                                        <div class="custom-control custom-switch mright5 mbot10">
                                            <input type="checkbox" class="custom-control-input" id="trigger_all_pages" name="trigger_all_pages" <?php echo $item->settings->trigger_all_pages ? 'checked' : null; ?> >
                                            <label class="custom-control-label clickable" for="trigger_all_pages"><?php echo _l('Trigger on all pages'); ?></label>
                                            <div>
                                                <small class="form-text text-muted"><?php echo _l('Where should the popup show?'); ?></small>
                                            </div>
                                        </div>
                                        <div id="triggers" class="container-disabled">
                                            <?php 
                                                $triggers = $item->settings->triggers;
                                                if(gettype($triggers) == 'object') {
                                                    $triggers = (array) $triggers;
                                                }
                                            ?>
                                            <?php if (count($triggers)) : ?>
                                                <?php foreach($triggers as $trigger_type => $trigger_value){ ?>
                                                <div class="input-group mbot10 d-flex">
                                                    <select class="form-control trigger-type-select" name="trigger_type[]">
                                                        <option value="exact" data-placeholder="<?php echo _l('Full URL ( ex: https://domain.com )'); ?>" <?php echo $trigger_type == 'exact' ? 'selected' : null; ?>><?php echo _l('Exact match'); ?></option>
                                                        <option value="not_exact" data-placeholder="<?php echo _l('Full URL ( ex: https://domain.com )'); ?>" <?php echo $trigger_type == 'not_exact' ? 'selected' : null; ?>><?php echo _l('Does not match exact'); ?></option>
                                                        <option value="contains" data-placeholder="<?php echo _l('Part of the url ( ex: /product/102481 )'); ?>" <?php echo $trigger_type == 'contains' ? 'selected' : null; ?>><?php echo _l('Contains'); ?></option>
                                                        <option value="not_contains" data-placeholder="<?php echo _l('Part of the url ( ex: /product/102481 )'); ?>" <?php echo $trigger_type == 'not_contains' ? 'selected' : null; ?>><?php echo _l('Does not contain'); ?></option>
                                                        <option value="starts_with" data-placeholder="<?php echo _l('Part of the url'); ?>" <?php echo $trigger_type == 'starts_with' ? 'selected' : null; ?>><?php echo _l('Starts with'); ?></option>
                                                        <option value="not_starts_with" data-placeholder="<?php echo _l('Part of the url'); ?>" <?php echo $trigger_type == 'not_starts_with' ? 'selected' : null; ?>><?php echo _l('Does not start with'); ?></option>
                                                        <option value="ends_with" data-placeholder="<?php echo _l('Part of the url'); ?>" <?php echo $trigger_type == 'ends_with' ? 'selected' : null; ?>><?php echo _l('Ends with'); ?></option>
                                                        <option value="not_ends_with" data-placeholder="<?php echo _l('Part of the url'); ?>" <?php echo $trigger_type == 'not_ends_with' ? 'selected' : null; ?>><?php echo _l('Does not end with'); ?></option>
                                                        <option value="page_contains" data-placeholder="<?php echo _l('Text that is included in the website'); ?>" <?php echo $trigger_type == 'page_contains' ? 'selected' : null; ?>><?php echo _l('Page Contains'); ?></option>
                                                    </select>
                                                    <input type="text" name="trigger_value[]" class="form-control" value="<?php echo $trigger_value; ?>">
                                                    <button type="button" class="trigger-delete mleft5 btn btn-outline-danger btn-sm" aria-label="<?php echo _l('Delete'); ?>"><i class="fa fa-fw fa-times"></i></button>
                                                </div>
                                                <?php } ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mbot10">
                                            <button type="button" id="trigger_add" class="btn btn-primary btn-sm"><i class="fa fa-fw fa-plus-circle"></i> <?php echo _l('Add new trigger'); ?></button>
                                        </div>
                                       
                                        <div class="form-group">
                                            <label for="settings_display_frequency"><?php echo _l('Display frequency'); ?></label>
                                            <select class="form-control" name="display_frequency">
                                                <option value="all_time" <?php echo $item->settings->display_frequency == 'all_time' ? 'selected' : null; ?>><?php echo _l('All the time'); ?></option>
                                                <option value="once_per_session" <?php echo $item->settings->display_frequency == 'once_per_session' ? 'selected' : null; ?>><?php echo _l('Once per visit session'); ?></option>
                                                <option value="once_per_browser" <?php echo $item->settings->display_frequency == 'once_per_browser' ? 'selected' : null; ?>><?php echo _l('Once per browser'); ?></option>
                                            </select>
                                            <small class="form-text text-muted"><?php echo _l('How often should the popup trigger?.'); ?></small>
                                        </div>
                                        <div class="custom-control custom-switch mright5 mbot10">
                                            <input type="checkbox" class="custom-control-input" id="display_mobile" name="display_mobile" <?php echo $item->settings->display_mobile ? 'checked' : null; ?>>
                                            <label class="custom-control-label clickable" for="display_mobile"><i class="fa fa-fw fa-sm fa-mobile text-muted mr-1"></i> <?php echo _l('Display on small screens'); ?></label>
                                            <div>
                                                <small class="form-text text-muted"><?php echo _l('Wether or not to display the popup on when pixels available are smaller than 768px.'); ?></small>
                                            </div>
                                        </div>
                                        <div class="custom-control custom-switch mright5 mbot10">
                                            <input type="checkbox" class="custom-control-input" id="display_desktop" name="display_desktop" <?php echo $item->settings->display_desktop ? 'checked' : null; ?>>
                                            <label class="custom-control-label clickable" for="display_desktop"><i class="fa fa-fw fa-sm fa-desktop text-muted mr-1"></i> <?php echo _l('Display on large screens'); ?></label>
                                            <div>
                                                <small class="form-text text-muted"><?php echo _l('Wether or not to display the popup on when pixels available are bigger than 768px.'); ?></small>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="settings_display_duration"><?php echo _l('Display Duration'); ?></label>
                                            <input type="number" min="-1" id="settings_display_duration" name="display_duration" class="form-control" value="<?php echo $item->settings->display_duration; ?>" required="required" />
                                            <small class="form-text text-muted"><?php echo _l('How many seconds to display the popup. Set -1 to display forever.'); ?></small>
                                        </div>
                                        <div class="form-group">
                                            <label for="settings_display_position"><?php echo _l('Display Position'); ?></label>
                                            <select class="form-control" name="display_position">
                                                <option value="top_left" <?php echo $item->settings->display_position == 'top_left' ? 'selected' : null; ?>><?php echo _l('Top Left'); ?></option>
                                                <option value="top_center" <?php echo $item->settings->display_position == 'top_center' ? 'selected' : null; ?>><?php echo _l('Top Center'); ?></option>
                                                <option value="top_right" <?php echo $item->settings->display_position == 'top_right' ? 'selected' : null; ?>><?php echo _l('Top Right'); ?></option>
                                                <option value="middle_left" <?php echo $item->settings->display_position == 'middle_left' ? 'selected' : null; ?>><?php echo _l('Middle Left'); ?></option>
                                                <option value="middle_center" <?php echo $item->settings->display_position == 'middle_center' ? 'selected' : null; ?>><?php echo _l('Middle Center'); ?></option>
                                                <option value="middle_right" <?php echo $item->settings->display_position == 'middle_right' ? 'selected' : null; ?>><?php echo _l('Middle Right'); ?></option>
                                                <option value="bottom_left" <?php echo $item->settings->display_position == 'bottom_left' ? 'selected' : null; ?>><?php echo _l('Bottom Left'); ?></option>
                                                <option value="bottom_center" <?php echo $item->settings->display_position == 'bottom_center' ? 'selected' : null; ?>><?php echo _l('Bottom Center'); ?></option>
                                                <option value="bottom_right" <?php echo $item->settings->display_position == 'bottom_right' ? 'selected' : null; ?>><?php echo _l('Bottom Right'); ?></option>
                                            </select>
                                            <small class="form-text text-muted"><?php echo _l('Position of the popup on the screen. Position doesn\'t change on the preview.'); ?></small>
                                        </div>
                                        <div class="row">
                                            <div class="col-12 col-md-6">
                                                <div class="form-group">
                                                    <label for="settings_on_animation"><?php echo _l('Entrance Animation'); ?></label>
                                                    <select class="form-control" name="on_animation">
                                                        <option value="fadeIn" <?php echo $item->settings->on_animation == 'fadeIn' ? 'selected' : null; ?>><?php echo _l('Fade In'); ?></option>
                                                        <option value="slideInUp" <?php echo $item->settings->on_animation == 'slideInUp' ? 'selected' : null; ?>><?php echo _l('Slide In Up'); ?></option>
                                                        <option value="slideInDown" <?php echo $item->settings->on_animation == 'slideInDown' ? 'selected' : null; ?>><?php echo _l('Slide In Down'); ?></option>
                                                        <option value="zoomIn" <?php echo $item->settings->on_animation == 'zoomIn' ? 'selected' : null; ?>><?php echo _l('Zoom In'); ?></option>
                                                        <option value="bounceIn" <?php echo $item->settings->on_animation == 'bounceIn' ? 'selected' : null; ?>><?php echo _l('Bounce In'); ?></option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <div class="form-group">
                                                    <label for="settings_off_animation"><?php echo _l('Exit Animation'); ?></label>
                                                    <select class="form-control" name="off_animation">
                                                        <option value="fadeOut" <?php echo $item->settings->off_animation == 'fadeOut' ? 'selected' : null; ?>><?php echo _l('Fade Out'); ?></option>
                                                        <option value="slideOutUp" <?php echo $item->settings->off_animation == 'slideOutUp' ? 'selected' : null; ?>><?php echo _l('Slide Out Up'); ?></option>
                                                        <option value="slideOutDown" <?php echo $item->settings->off_animation == 'slideOutDown' ? 'selected' : null; ?>><?php echo _l('Slide Out Down'); ?></option>
                                                        <option value="zoomOut" <?php echo $item->settings->off_animation == 'zoomOut' ? 'selected' : null; ?>><?php echo _l('Zoom Out'); ?></option>
                                                        <option value="bounceOut" <?php echo $item->settings->off_animation == 'bounceOut' ? 'selected' : null; ?>><?php echo _l('Bounce Out'); ?></option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div role="tabpanel" class="tab-pane" id="tab_form">
                                         <div class="d-flex">
                                            <h4 class="title-tab-content pb-0"><?php echo _l('action_after_form_submit'); ?></h4>
                                        </div>
                                        
                                        <p class=""><?php echo _l('action_after_form_submit_note'); ?></p>

                                        <div class="form-group row">
                                            <div class="col-md-8">
                                                <select name="type_form_submit" id="type_form_submit" class="form-control">
                                                    <option value="thank_you_page" <?php if($item->type_form_submit == 'thank_you_page') echo 'selected'; ?> >
                                                    <?php echo _l('go_to_default_thank_you_page'); ?></option>
                                                    <option value="url" <?php if($item->type_form_submit == 'url') echo 'selected'; ?> >
                                                        <?php echo _l('redirect_to_any_url'); ?></option>
                                                </select>
                                            </div>
                                            
                                        </div>
                                        <div class="form-group row <?php if($item->type_form_submit == 'thank_you_page') echo 'd-none'; ?>" id="form_redirect_url">
                                            <div class="col-md-8">
                                                <label class="form-label"><?php echo _l('redirect_to'); ?>:</label>
                                                <input type="url" name="redirect_url" value="<?php echo $item->redirect_url ?>" class="form-control">
                                            </div>
                                        </div>
                                       
                                    </div>

                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-info pull-right"><?php echo _l('submit'); ?></button>
                        
                        <?php echo form_close(); ?>
                        <div style="display:none" id="trigger_rule_sample">
                             <div class="input-group mbot10 d-flex">
                                 <select class="form-control trigger-type-select" name="trigger_type[]">
                                     <option value="exact" data-placeholder="<?php echo 'Full URL ( ex: https://domain.com )'; ?>"><?php echo 'Exact match'; ?></option>
                                     <option value="not_exact" data-placeholder="<?php echo 'Full URL ( ex: https://domain.com )'; ?>"><?php echo 'Does not match exact'; ?></option>
                                     <option value="contains" data-placeholder="<?php echo 'Part of the url ( ex: /product/102481 )'; ?>"><?php echo 'Contains'; ?></option>
                                     <option value="not_contains" data-placeholder="<?php echo 'Part of the url ( ex: /product/102481 )'; ?>"><?php echo 'Does not contain'; ?></option>
                                     <option value="starts_with" data-placeholder="<?php echo 'Part of the url'; ?>"><?php echo 'Starts with'; ?></option>
                                     <option value="not_starts_with" data-placeholder="<?php echo 'Part of the url'; ?>"><?php echo 'Does not start with'; ?></option>
                                     <option value="ends_with" data-placeholder="<?php echo 'Part of the url'; ?>"><?php echo 'Ends with'; ?></option>
                                     <option value="not_ends_with" data-placeholder="<?php echo 'Part of the url'; ?>"><?php echo 'Does not end with'; ?></option>
                                     <option value="page_contains" data-placeholder="<?php echo 'Text that is included in the website'; ?>"><?php echo 'Page Contains'; ?></option>
                                 </select>
                                 <input type="text" name="trigger_value[]" class="form-control">
                                 <button type="button" class="trigger-delete mleft5 btn btn-dark btn-sm" aria-label="<?php echo 'Delete'; ?>"><i class="fa fa-fw fa-times"></i></button>
                             </div>
                        </div>
                        <div style="display:none" id="data_trigger_auto_rule_sample" class="p9">
                            <div class="input-group mbot10">
                                <select class="form-control trigger-type-select" name="data_trigger_auto_type[]">
                                    <option value="exact"><?php echo 'Exact match'; ?></option>
                                    <option value="contains"><?php echo 'Contains'; ?></option>
                                    <option value="starts_with"><?php echo 'Starts with'; ?></option>
                                    <option value="ends_with"><?php echo 'Ends with'; ?></option>
                                    <option value="page_contains"><?php echo 'Page Contains'; ?></option>
                                </select>
                                <input type="text" name="data_trigger_auto_value[]" class="form-control" placeholder="<?php echo 'Full URL ( ex: https://domain.com )'; ?>" aria-label="<?php echo 'Full URL ( ex: https://domain.com )'; ?>">
                                <button type="button" class="data-trigger-auto-delete mleft5 btn btn-outline-danger btn-sm" aria-label="<?php echo 'Delete'; ?>"><i class="fa fa-fw fa-times"></i></button>
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php $this->load->view('popups/modal_install'); ?>
</div>

<?php init_tail(); ?>
<script src="<?php echo base_url(PERFEX_POPUP_ASSETS_PATH.'/popups/js/setting.js'); ?>"></script>
<script src="<?php echo base_url(PERFEX_POPUP_ASSETS_PATH.'/popups/js/install_modal.js'); ?>"></script>

</body>
</html>