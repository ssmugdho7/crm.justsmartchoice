<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <h1 class="page-title"><?php echo isset($vlan) ? 'Edit VLAN' : 'Add VLAN'; ?></h1>
                <div class="panel_s">
                    <div class="panel-body">
                        <form method="post" action="<?php echo admin_url('ipam/' . (isset($vlan) ? 'edit_vlan/' . $vlan['id'] : 'add_vlan')); ?>">
                            <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                            <div class="form-group">
                                <label for="vlan_name">VLAN Name:</label>
                                <input type="text" name="vlan_name" id="vlan_name" class="form-control" value="<?php echo isset($vlan) ? $vlan['vlan_name'] : ''; ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="vlan_id">VLAN ID:</label>
                                <input type="number" name="vlan_id" id="vlan_id" class="form-control" value="<?php echo isset($vlan) ? $vlan['vlan_id'] : ''; ?>" required>
                            </div>
                            <button type="submit" class="btn btn-primary"><?php echo isset($vlan) ? 'Update VLAN' : 'Add VLAN'; ?></button>
                            <a href="<?php echo admin_url('ipam/vlans'); ?>" class="btn btn-default">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
