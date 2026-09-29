    <div class="modal fade z-index-none" id="transfer_ownershipModal">
        <div class="modal-dialog setting-transaction-table modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?php echo html_entity_decode($title) ?></h4>
                </div>
                <?php 
                $id = '';
                
                if(isset($device)){
                    $id = $device->id;
                }

                ?>
                <?php echo form_open_multipart(admin_url('workshop/edit_transfer_ownwership/'.$id), array('id' => 'edit_transfer_ownwership', 'autocomplete'=>'off')); ?>
                <?php 

                $old_customer = '';
                

                if(isset($device)){
                    $name = $device->name;
                    
                }

                ?>
                <input type="hidden" name="id" value="<?php echo html_entity_decode($id); ?>">
                <div class="modal-body">

                    <div class="row">
                        <div class="col-md-12">
                            <p class="tw-font-semibold"><?php echo _l('wshop_current_customer') ?></p>
                            <h5><?php echo get_company_name($device->client_id); ?></h5>
                        </div>

                        <div class="col-md-12">
                            <hr class="">
                        </div>

                        <div class="col-md-6">
                            <?php echo render_select('client_id', $clients, ['userid', 'company'], 'wshop_new_customer'); ?>
                        </div>
                        <div class="clearfix"></div>
                        <div class="col-md-6">
                            <i class="fa-solid fa-location-dot mright5"></i><span class="client_address">---</span>
                        </div>
                        <div class="col-md-6">
                            <i class="fa-solid fa-phone mright5"></i><span class="client_phone">---</span><br>
                            <i class="fa-solid fa-mobile-screen-button mright5"></i><span class="contact_phone">---</span><br>
                            <i class="fa-solid fa-envelope mright5"></i><span class="contact_email">---</span>
                            
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                    <button type="submit" class="btn btn-info device_submit_button"><?php echo _l('confirm'); ?></button>
                </div>

            </div>

            <?php echo form_close(); ?>
        </div>
    </div>

    <?php require 'modules/workshop/assets/js/devices/transfer_ownership_modal_js.php';  ?>
