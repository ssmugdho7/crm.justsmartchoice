<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>


<div class="panel_s">

    <div class="panel-body">


        <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700">

            <div class="tw-flex tw-justify-between tw-items-center">

                <?php echo _l('project_note_private'); ?>

                <a href="#" class="btn btn-success btn-sm" onclick="slideToggle('.usernote'); return false;">

                    <i class="fa-regular fa-plus tw-mr-1"></i>

                    <?php echo _l('new_note'); ?>

                </a>

            </div>


        </h4>

        <?php

        $project_notes = $this->misc_model->get_notes($project->id, 'project');

        ?>


        <div class="row">

            <div class="col-md-12">

                <div class="usernote hide">

                    <?php echo form_open(admin_url('notes/notes/add_note')); ?>

                        <?php echo form_hidden('rel_id' , $project->id);?>

                        <?php echo form_hidden('rel_type' , 'project');?>

                        <?php echo render_textarea('description', 'note_description', '', ['rows' => 5]); ?>

                        <div class="form-group">
                            <div class="checkbox checkbox-primary">

                                <input type="checkbox"   id="visible_on_client" name="visible_on_client" value="1">

                                <label for="visible_on_client"><?php echo _l('project_discussion_show_to_customer') ?></label>

                            </div>
                        </div>

                        <button class="btn btn-primary pull-right mbot15">

                            <?php echo _l('submit'); ?>

                        </button>

                    <?php echo form_close(); ?>

                </div>

                <table class="table dt-table" data-order-col="2" data-order-type="desc">

                    <thead>

                    <tr>

                        <th width="50%">

                            <?php echo _l('clients_notes_table_description_heading'); ?>

                        </th>

                        <th>

                            <?php echo _l('clients_notes_table_addedfrom_heading'); ?>

                        </th>

                        <th>

                            <?php echo _l('clients_notes_table_dateadded_heading'); ?>

                        </th>

                        <th>

                            <?php echo _l('project_discussion_show_to_customer'); ?>

                        </th>

                        <th>

                            <?php echo _l('options'); ?>

                        </th>

                    </tr>

                    </thead>

                    <tbody>

                    <?php foreach ($project_notes as $note) { ?>

                        <tr>

                            <td width="50%">

                                <div data-note-description="<?php echo $note['id']; ?>">

                                    <?php echo check_for_links($note['description']); ?>

                                </div>

                                <div data-note-edit-textarea="<?php echo $note['id']; ?>" class="hide">

                            <textarea name="description" class="form-control"

                                      rows="4"><?php echo clear_textarea_breaks($note['description']); ?></textarea>

                                    <div class="text-right mtop15">

                                        <button type="button" class="btn btn-default"

                                                onclick="toggle_edit_note(<?php echo $note['id']; ?>);return false;"><?php echo _l('cancel'); ?></button>

                                        <button type="button" class="btn btn-primary"

                                                onclick="edit_note(<?php echo $note['id']; ?>);"><?php echo _l('update_note'); ?></button>

                                    </div>

                                </div>

                            </td>

                            <td>

                                <?php echo '<a href="' . admin_url('profile/' . $note[ 'addedfrom']) . '">' . $note[ 'firstname'] . ' ' . $note[ 'lastname'] . '</a>' ?>

                            </td>

                            <td data-order="<?php echo $note['dateadded']; ?>">

                                <?php if (!empty($note['date_contacted'])) { ?>

                                    <span data-toggle="tooltip"

                                          data-title="<?php echo html_escape(_dt($note['date_contacted'])); ?>">

                                        <i class="fa fa-phone-square text-success font-medium valign" aria-hidden="true"></i>

                                    </span>

                                <?php } ?>

                                <?php echo _dt($note[ 'dateadded']); ?>

                            </td>

                            <td data-order="visible_on_client">
                                <?php

                                if ( !empty( $note['visible_on_client'] ) )
                                    echo _l('settings_yes');
                                else
                                    echo _l('settings_no');

                                ?>
                            </td>

                            <td>

                                <div class="tw-flex tw-items-center tw-space-x-3">

                                    <?php if ($note['addedfrom'] == get_staff_user_id() || is_admin()) { ?>

                                        <a href="#" onclick="toggle_edit_note(<?php echo $note['id']; ?>);return false;"

                                           class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700">

                                            <i class="fa-regular fa-pen-to-square fa-lg"></i>

                                        </a>

                                        <a href="<?php echo admin_url('misc/delete_note/' . $note['id']); ?>"

                                           class="tw-mt-px tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700 _delete">

                                            <i class="fa-regular fa-trash-can fa-lg"></i>

                                        </a>

                                    <?php } ?>

                                </div>

                            </td>

                        </tr>

                    <?php } ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

