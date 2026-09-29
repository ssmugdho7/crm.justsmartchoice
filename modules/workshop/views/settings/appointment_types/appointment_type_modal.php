    <div class="modal fade z-index-none" id="appointment_typeModal">
        <div class="modal-dialog setting-transaction-table">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?php echo html_entity_decode($title) ?></h4>
                </div>
                <?php 
                $id = '';
                
                if(isset($appointment_type)){
                    $id = $appointment_type->id;
                }

                ?>
                <?php echo form_open_multipart(admin_url('workshop/add_edit_appointment_type/'.$id), array('id' => 'add_edit_appointment_type', 'autocomplete'=>'off')); ?>
                <?php 

                $code = '';
                $name = '';
                $estimated_hours = '';
                $description = '';
                $plate_renewal = 0;
                $warrant_of_fitness = 0;
                $next_service = 0;
                $item_id = '';
                $plate_renewal_checked = 'checked';
                $warrant_of_fitness_checked = 'checked';
                $next_service_checked = 'checked';

                if(isset($appointment_type)){
                    $code = $appointment_type->code;
                    $name = $appointment_type->name;
                    $estimated_hours = $appointment_type->estimated_hours;
                    $description = $appointment_type->description;
                    $plate_renewal = $appointment_type->plate_renewal;
                    $warrant_of_fitness = $appointment_type->warrant_of_fitness;
                    $next_service = $appointment_type->next_service;
                    if($plate_renewal == 1){
                        $plate_renewal_checked = 'checked';
                    }else{
                        $plate_renewal_checked = '';
                    }
                    if($warrant_of_fitness == 1){
                        $warrant_of_fitness_checked = 'checked';
                    }else{
                        $warrant_of_fitness_checked = '';
                    }
                    if($next_service == 1){
                        $next_service_checked = 'checked';
                    }else{
                        $next_service_checked = '';
                    }
                    
                }
                if(isset($appointment_type_products)){
                    $item_id = $appointment_type_products;
                }

                ?>
                <input type="hidden" name="id" value="<?php echo html_entity_decode($id); ?>">
                <div class="modal-body">

                    <div class="row">
                        <div class="col-md-12">
                            <?php echo render_input('code', 'wshop_code', $code ); ?>
                        </div>
                        <div class="col-md-12">
                            <?php echo render_input('name', 'wshop_name', $name ); ?>
                        </div>

                        <div class="col-md-12">
                            <?php echo render_select('item_id[]', $products, ['id', 'name'], 'wshop_labour_products', $item_id, ['multiple' => true, 'data-action-boxs' => 'true'], [], '', '', false ); ?>
                        </div>
                        <div class="col-md-12">
                            <?php echo  render_input('estimated_hours', 'wshop_estimated_hours', $estimated_hours, 'number', ['step' => 'any']); ?>
                        </div>
                        <div class="col-md-12">
                            <?php echo render_textarea('description', 'wshop_description', $description, ['rows' => 4, 'placeholder' => _l('wshop_description'),] ); ?>
                        </div>

                        <div class="col-md-6 hide">
                            <div class="row">
                                <div class="col-md-6 mtop10 border-right">
                                    <span><?php echo _l('wshop_plate_renewal'); ?></span>
                                </div>
                                <div class="col-md-6 mtop10">
                                    <div class="onoffswitch">
                                        <input type="checkbox" name="plate_renewal" class="onoffswitch-checkbox" id="plate_renewal" <?php echo html_entity_decode($plate_renewal_checked); ?>>
                                        <label class="onoffswitch-label" for="plate_renewal"></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <div class="col-md-6 hide">
                            <div class="row">
                                <div class="col-md-6 mtop10 border-right">
                                    <span><?php echo _l('wshop_warrant_of_fitness'); ?></span>

                                </div>
                                <div class="col-md-6 mtop10">
                                    <div class="onoffswitch">
                                        <input type="checkbox" name="warrant_of_fitness" class="onoffswitch-checkbox" id="warrant_of_fitness" <?php echo html_entity_decode($warrant_of_fitness_checked); ?>>
                                        <label class="onoffswitch-label" for="warrant_of_fitness"></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <div class="col-md-6  hide">
                            <div class="row">
                                <div class="col-md-6 mtop10 border-right">
                                    <span><?php echo _l('wshop_next_service'); ?></span>

                                </div>
                                <div class="col-md-6 mtop10">
                                    <div class="onoffswitch">
                                        <input type="checkbox" name="next_service" class="onoffswitch-checkbox" id="next_service" <?php echo html_entity_decode($next_service_checked); ?>>
                                        <label class="onoffswitch-label" for="next_service"></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="clearfix"></div>

                    </div>


                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                    <button type="submit" class="btn btn-info appointment_type_submit_button"><?php echo _l('submit'); ?></button>
                </div>

            </div>

            <?php echo form_close(); ?>
        </div>
    </div>

    <?php require 'modules/workshop/assets/js/settings/appointment_types/appointment_type_modal_js.php';  ?>
