    <div class="modal fade z-index-none" id="branchModal">
        <div class="modal-dialog setting-transaction-table">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?php echo html_entity_decode($title) ?></h4>
                </div>
                <?php 
                $id = '';
                
                if(isset($branch)){
                    $id = $branch->id;
                }

                ?>
                <?php echo form_open_multipart(admin_url('workshop/add_edit_branch/'.$id), array('id' => 'add_edit_branch', 'autocomplete'=>'off')); ?>
                <?php 

                $name = '';
                $email = '';
                $phonenumber = '';
                $address = '';
                $city = '';
                $state = '';
                $country = '';
                $zip = '';

                if(isset($branch)){
                    $name = $branch->name;
                    $email = $branch->email;
                    $phonenumber = $branch->phonenumber;
                    $address = $branch->address;
                    $city = $branch->city;
                    $state = $branch->state;
                    $country = $branch->country;
                    $zip = $branch->zip;
                }

                ?>
                <input type="hidden" name="id" value="<?php echo html_entity_decode($id); ?>">
                <div class="modal-body">
                    
                    <div class="row">
                        <div class="col-md-12">
                            <?php echo  render_input('name', 'wshop_name', $name, 'text', [], [], ''); ?>

                        </div>
                        <div class="col-md-12">
                            <?php echo  render_input('email', 'wshop_branch_email', $email, 'text', [], [], ''); ?>
                        </div>
                         <div class="col-md-12">
                            <?php echo  render_input('phonenumber', 'wshop_branch_phone', $phonenumber, 'text', [], [], ''); ?>
                        </div>

                        <div class="col-md-12">
                            <?php echo  render_input('address', 'wshop_address', $address, 'text', [], [], ''); ?>
                        </div>
                        <div class="col-md-12">
                            <?php echo  render_input('city', 'wshop_city', $city, 'text', [], [], ''); ?>
                        </div>
                        <div class="col-md-12">
                            <?php echo  render_input('state', 'wshop_state', $state, 'text', [], [], ''); ?>
                        </div>
                        <div class="col-md-12">
                            <?php echo  render_input('zip', 'wshop_zip_code', $zip, 'text', [], [], ''); ?>
                        </div>
                        
                        <div class="col-md-12">
                            <?php $countries       = get_all_countries();
                            $customer_default_country = get_option('customer_default_country');
                            $selected                 = (isset($branch) ? $branch->country : $customer_default_country);
                            echo render_select('country', $countries, [ 'country_id', [ 'short_name']], 'clients_country', $selected, ['data-none-selected-text' => _l('dropdown_non_selected_tex')]);
                            ?>

                        </div>
                        
                       
                        
                    </div>


                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                    <button type="submit" class="btn btn-info branch_submit_button"><?php echo _l('submit'); ?></button>
                </div>

            </div>

            <?php echo form_close(); ?>
        </div>
    </div>

    <?php require 'modules/workshop/assets/js/branches/branch_modal_js.php';  ?>
