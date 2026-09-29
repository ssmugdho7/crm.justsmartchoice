<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<table class="table dt-table table-invoices" data-order-col="1" data-order-type="desc">
    <thead>
        <tr>
            <th><?php echo _l('clients_dmn_tittle'); ?></th>
            <th><?php echo _l('clients_dmn_description'); ?></th>
            <th><?php echo _l('clients_dmn_staff'); ?></th>
            <th><?php echo _l('clients_dmn_created_at'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($dmn as $dmn){ ?>
            <tr>
                <td data-order="<?php echo $dmn->title; ?>">
                    <div class="row-options">
                    <a href="<?php echo site_url('admin/dmn/dmn_client/preview/'.$dmn->id);?>" target="_blank"><?php echo $dmn->title; ?></a>
                    </div>    
                </td>
                <td data-order="<?php echo $dmn->description; ?>"><?php echo $dmn->description; ?></td>
                <?php $oStaff = get_staff($dmn->staffid);?>
                <td data-order="<?php echo $dmn->staffid; ?>"><?php echo staff_profile_image($dmn->staffid, array('img', 'img-responsive', 'staff-profile-image-small', 'pull-left')). '<a href="' . admin_url('profile/' . $oStaff->staffid) . '">' . $oStaff->firstname . ' ' . $oStaff->lastname . '</a>'; ?></td>
                <td data-order="<?php echo $dmn->dateadded; ?>"><?php echo _d($dmn->dateadded); ?></td>
            </tr>
        <?php } ?>
    </tbody>
</table>
