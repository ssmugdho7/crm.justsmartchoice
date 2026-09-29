<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php echo form_open_multipart(admin_url('project_templates/upload_file/' . $project->id), ['class' => 'dropzone', 'id' => 'project-template-files-upload']); ?>
<input type="file" name="file" multiple />
<?php echo form_close(); ?>
<span class="tw-mt-4 tw-inline-block tw-text-sm"><?php echo _l('project_file_visible_to_customer'); ?></span><br />
<div class="onoffswitch">
    <input type="checkbox" name="visible_to_customer" id="pf_visible_to_customer" class="onoffswitch-checkbox">
    <label class="onoffswitch-label" for="pf_visible_to_customer"></label>
</div>
<div class="tw-flex tw-justify-end tw-items-center tw-space-x-2">
    <button class="gpicker" data-on-pick="projectFileGoogleDriveSave">
        <i class="fa-brands fa-google" aria-hidden="true"></i>
        <?php echo _l('choose_from_google_drive'); ?>
    </button>
    <div id="dropbox-chooser"></div>
</div>
<div class="clearfix"></div>
<div class="mtop20"></div>
<div class="modal fade bulk_actions" id="project_files_bulk_actions" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo _l('bulk_actions'); ?></h4>
            </div>
            <div class="modal-body">
                <?php if (is_admin()) { ?>
                <div class="checkbox checkbox-danger">
                    <input type="checkbox" name="mass_delete" id="mass_delete">
                    <label for="mass_delete"><?php echo _l('mass_delete'); ?></label>
                </div>
                <hr class="mass_delete_separator" />
                <?php } ?>
                <div id="bulk_change">
                    <div class="form-group">
                        <label class="mtop5"><?php echo _l('project_file_visible_to_customer'); ?></label>
                        <div class="onoffswitch">
                            <input type="checkbox" name="bulk_visible_to_customer" id="bulk_pf_visible_to_customer"
                                class="onoffswitch-checkbox">
                            <label class="onoffswitch-label" for="bulk_pf_visible_to_customer"></label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <a href="#" class="btn btn-primary"
                    onclick="project_template_files_bulk_action(this); return false;"><?php echo _l('confirm'); ?></a>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<a href="#" data-toggle="modal" data-target="#project_files_bulk_actions" class="bulk-actions-btn table-btn hide"
    data-table=".table-project-files">
    <?php echo _l('bulk_actions'); ?>
</a>
<a href="#"
    onclick="window.location.href = '<?php echo admin_url('project_templates/download_all_files/' . $project->id); ?>'; return false;"
    class="table-btn hide" data-table=".table-project-files"><?php echo _l('download_all'); ?></a>
<div class="clearfix"></div>
<div class="panel_s panel-table-full">
    <div class="panel-body">
        <table class="table dt-table table-project-files" data-order-col="4" data-order-type="desc">
            <thead>
                <tr>
                    <th data-orderable="false"><span class="hide"> - </span>
                        <div class="checkbox mass_select_all_wrap"><input type="checkbox" id="mass_select_all"
                                data-to-table="project-files"><label></label></div>
                    </th>
                    <th><?php echo _l('project_file_filename'); ?></th>
                    <th><?php echo _l('project_file__filetype'); ?></th>
                    <th><?php echo _l('project_file_visible_to_customer'); ?></th>
                    <th><?php echo _l('project_file_dateadded'); ?></th>
                    <th><?php echo _l('options'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($files as $file) {
    $path = PROJECT_TEMPLATES_ATTACHMENTS_FOLDER . $project->id . '/' . $file['file_name'];
    $url = prepare_project_template_file_url($project->id, $file['file_name']);
    ?>
                <tr>
                    <td>
                        <div class="checkbox"><input type="checkbox" value="<?php echo $file['id']; ?>"><label></label>
                        </div>
                    </td>
                    <td data-order="<?php echo $file['file_name']; ?>">
                        <a href="#"
                            onclick="view_project_template_file(<?php echo $file['id']; ?>,<?php echo $file['project_template_id']; ?>); return false;">
                            <?php if (is_image(PROJECT_TEMPLATES_ATTACHMENTS_FOLDER . $project->id . '/' . $file['file_name']) || (!empty($file['external']) && !empty($file['thumbnail_link']))) {
        echo '<div class="text-left"><i class="fa fa-spinner fa-spin mtop30"></i></div>';
        echo '<img class="project-file-image img-table-loading" src="#" data-orig="' . $url . '" width="100">';
        echo '</div>';
    }
    echo $file['subject']; ?></a>
                    </td>
                    <td data-order="<?php echo $file['filetype']; ?>"><?php echo $file['filetype']; ?></td>
                    <td data-order="<?php echo $file['visible_to_customer']; ?>">
                        <?php
            $checked = '';
    if ($file['visible_to_customer'] == 1) {
        $checked = 'checked';
    } ?>
                        <div class="onoffswitch">
                            <input type="checkbox"
                                data-switch-url="<?php echo admin_url(); ?>project_templates/change_file_visibility"
                                id="<?php echo $file['id']; ?>" data-id="<?php echo $file['id']; ?>"
                                class="onoffswitch-checkbox" value="<?php echo $file['id']; ?>" <?php echo $checked; ?>>
                            <label class="onoffswitch-label" for="<?php echo $file['id']; ?>"></label>
                        </div>

                    </td>
                    <td data-order="<?php echo $file['dateadded']; ?>"><?php echo _dt($file['dateadded']); ?></td>
                    <td>
                        <div class="tw-flex tw-items-center tw-space-x-3">
                            <?php if (staff_can('delete', 'project_templates')) { ?>
                            <a href="<?php echo admin_url('project_templates/remove_file/' . $project->id . '/' . $file['id']); ?>"
                                class="tw-mt-px tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700 _delete">
                                <i class="fa-regular fa-trash-can fa-lg"></i>
                            </a>
                            <?php } ?>
                        </div>
                    </td>
                </tr>
                <?php
} ?>
            </tbody>
        </table>
    </div>
</div>
<div id="project_file_data"></div>