


        <div class="modal-header">

            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>

            <h4 class="modal-title">

                <span class="edit-title"><?php echo _l('email_template_manage_imap')?></span>

            </h4>

        </div>

        <?php echo form_open_multipart('email_template_manage/imap_save' ); ?>

            <input type="hidden" name="id" id="id" value="<?php echo $data->id?>">


            <div class="modal-body">

                <div class="row">

                    <div class="col-md-6">

                        <?php echo render_input('company_name', 'settings_general_company_name' , $data->company_name  , 'text' , [ 'required' => true ] ); ?>

                    </div>

                    <div class="col-md-6">

                        <div class="form-group">

                            <label for="encryption"><?php echo _l('smtp_encryption'); ?></label><br />

                            <select name="encryption" class="selectpicker" data-width="100%">

                                <option value="ssl" <?php echo $data->encryption == 'ssl' ? 'selected' : '' ?> >SSL</option>

                                <option value="tls" <?php echo $data->encryption == 'tls' ? 'selected' : '' ?> >TLS</option>

                                <option value="" <?php echo $data->encryption == '' ? 'selected' : '' ?> >No Encryption</option>

                            </select>

                        </div>

                    </div>


                    <div class="col-md-6">

                        <?php echo render_input('imap_server', 'leads_email_integration_imap' , $data->imap_server , 'text' , [ 'required' => true ] ); ?>

                    </div>

                    <div class="col-md-6">

                        <?php echo render_input('imap_port', 'port' , $data->imap_port  , 'text' , [ 'required' => true ]); ?>

                    </div>


                    <div class="col-md-6">

                        <?php echo render_input('user_name', 'email_template_manage_email' , $data->user_name  , 'text' , [ 'required' => true ]); ?>

                    </div>


                    <div class="col-md-6">

                        <?php echo render_input('password', 'email_template_manage_email_password' , $data->password , 'password', ['autocomplete' => 'off' , 'required' => true] ); ?>

                    </div>


                    <div class="col-md-6">

                        <?php echo render_input('last_search_days', 'email_template_manage_last_search_days' , $data->last_search_days , 'number', ['autocomplete' => 'off' , 'step' => 1 ] ); ?>

                    </div>

                </div>


                <div class="col-md-12">
                    <hr />
                </div>

                <div class="row">

                    <div class="col-md-12">
                        <strong>
                            <span class="text-info">
                                <?php echo _l( 'email_template_manage_imap_staff_info' , _l('task_public') )?>
                            </span>
                        </strong>
                    </div>

                    <div class="col-md-12">

                        <div class="form-group">

                            <select class="selectpicker" data-toggle="<?php echo _l('staff')?>"

                                    name="staff[]" id="staff" data-actions-box="true" multiple="true" data-width="100%"

                                    data-title="<?php echo _l('staff')?>">

                                <?php foreach ($staff as $stf ) {

                                    $selected = '';

                                    if ( in_array( $stf['staffid'] , $data->active_staff ) )
                                        $selected = 'selected';

                                    ?>

                                    <option <?php echo $selected ?> value="<?php echo $stf['staffid']; ?>" >

                                        <?php echo $stf['firstname'].' '.$stf['lastname']; ?></option>

                                    <?php

                                } ?>

                            </select>
                        </div>

                        <div class="checkbox checkbox-primary checkbox-inline">

                            <input type="checkbox" id="is_public" name="is_public" value="1" <?php echo $data->is_public == 1 ? 'checked' : '' ?> >

                            <label for="is_public" ><?php echo _l('task_public'); ?></label>

                        </div>

                    </div>

                </div>


            </div>

            <div class="modal-footer">

                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>

                <button type="submit" class="btn btn-primary"><?php echo _l('submit'); ?></button>

            </div>

        <?php echo form_close(); ?>


