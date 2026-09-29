<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="tw-max-w-4xl tw-mx-auto">
            <h4 class="tw-mt-0 tw-font-bold tw-text-lg tw-text-neutral-700">
            <?=_l('domain_manager_edit')?></h4>
            <?php echo form_open(admin_url('domain_manager/update_domain_manager'),['id'=>'save_form']); ?>

                <div class="panel_s">
                <div class="panel-body">
                        <div class="container-fluid">
                            <input type="hidden" value="<?=$domain->id?>" name="id">
                        <?php echo render_input('name', 'Domain Name', $domain->domain_name, 'text', ['required' => 'required', 'id' => 'domain_manager_domain_name', 'placeholder' => 'Domain Name']); ?>
                            <?php echo render_input('domain_manager_registrar', 'Registrar', $domain->registrar, 'text', ['id' => 'domain_manager_registrar', 'placeholder' => 'Registrar', 'autocomplete' => 'off']); ?>
                            
                            <?php echo render_date_input('domain_manager_purchase_date', 'Purchase Date', $domain->purchase_date, ['id' => 'domain_manager_purchase_date', 'autocomplete' => 'off','class'=>'col-ms-6']); ?>
                            <?php echo render_date_input('domain_manager_expiry_date', 'Expiry Date', $domain->expiry_date, ['id' => 'domain_manager_expiry_date', 'autocomplete' => 'off']); ?>

                            <div class="form-group">
                                <label for="dns_hosting"><?php echo _l('DNS Hosting'); ?></label>
                                <select name="dns_hosting" class="selectpicker" data-width="100%" id="dns_hosting">
                                    <option value="enabled" <?=  $domain->dns_hosting == 'enabled' ?'selected':'' ?>><?=_l('domain_manager_enabled')?></option>
                                    <option value="disabled"  <?=  $domain->dns_hosting == 'disabled' ?'selected':'' ?>><?=_l('domain_manager_disabled')?></option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="registration_status"><?php echo _l('Registration Status'); ?></label>
                                <select name="registration_status" class="selectpicker" data-width="100%"
                                    id="registration_status">
                                    <?php $registration_status = [
                'active'=>  _l('domain_manager_active'),
                'expiring_soon'=>  _l('domain_manager_expiring_soon'),
                'registered_elsewhere'=>  _l('domain_manager_registered_elsewhere'),
                'expired'=>  _l('domain_manager_expired'),
            ]; foreach ($registration_status as $key => $rs) { ?>
                                    <option value="<?=$key?>" <?=  $domain->registration_status == $key ?'selected':'' ?>><?=$rs?></option>
                                    <?php } ?>

                                </select>
                            </div>

                            <div class="form-group">
                                <label for="status"><?php echo _l('Status'); ?></label>
                                <select name="status" class="selectpicker" data-width="100%" id="status">

                                    <?php $status = [
                'active'=>  _l('domain_manager_active'),
                'domain_manager_expiring_soon'=>  _l('domain_manager_expiring_soon'),
                'expired'=>  _l('domain_manager_expired'),
                'pending'=>  _l('domain_manager_pending'),
            ]; foreach ($status as $key => $s) { ?>
                                    <option value="<?=$key?>" <?= $domain->status == $key ?'selected':'' ?>><?=$s?></option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="project_id"><?php echo _l('Project'); ?></label>
                                <select name="project_id" id="project_id" class="form-control "
                                    data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                    <option value="">- Project -</option>
                                    <?php foreach ($projects as $project) { ?>
                                    <option value="<?= $project['id']; ?>" <?= $domain->project_id == $project['id'] ?'selected':'' ?>><?= $project['name']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="client_id"><?php echo _l('Client'); ?></label>
                                <select name="client_id" id="client_id" class="form-control">
                                    <option value="">- Client -</option>
                                    <?php foreach ($clients as $client) { ?>
                                    <option value="<?= $client['userid']; ?>" <?= $domain->client_id == $client['userid'] ?'selected':'' ?>><?= $client['company']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>

                            <?php echo render_textarea('description', 'Description', $domain->description, ['placeholder' => 'Description']); ?>
                        </div>


                    </div>
                    <div class="panel-footer text-right">
                        <button class="btn btn-primary" type="submit"><?=_l('Update')?></button>
                    </div>
                </div>
                <?php echo form_close(); ?>
        </div>
    </div>
</div>


<?php init_tail(); ?>
<script>
    $(".menu-item-domain_manager").addClass('active');
</script>
</body>
</html>